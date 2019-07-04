<?php /** @noinspection ALL */

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Statistics\StatisticsCron;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class Statistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform statistical tasks regularly';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // 获取新任务
            DB::beginTransaction();
            $statisticsTask = StatisticsCron::where('status', StatisticsCron::EXECUTE_PENDING)
                ->lockForUpdate()
                ->first();
            if ($statisticsTask === null) {
                DB::rollback();
                throw new \Exception('No data to exec');
            }
            $statisticsTask->status = StatisticsCron::EXECUTE_RUNNING;
            $statisticsTask->updated_at = Carbon::now();
            $statisticsTask->save();
            DB::commit();

            $basePath = '/tmp/export/';
            $fileName = $statisticsTask->file_name . '.csv';
            // 创建文件流
            if (is_file($basePath . $fileName)) {
                unlink($basePath . $fileName);
            }
            $file = fopen($basePath . $fileName, "w");
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // 解决乱码
            // 先写表头
            $title = $this->allocateProcessingData($statisticsTask->task_name);
            fputcsv($file, $title);
            // 开始写内容
            $page = 0;
            // 查询出的结果为空则跳出循环
            do {
                // 拼接分页
                $execSql = $this->handleSql($statisticsTask->exec_sql, $page);
                // 执行任务sql
                $result = DB::connection('dotnet')->select($execSql);

                foreach ($result as $val) {
                    fputcsv($file, (array)$val);
                }

                unset($val);
                $page++;
            } while ($result);

            // 导出csv完毕 修改任务状态
            StatisticsCron::where('id', $statisticsTask->id)
                ->update([
                    'status' => StatisticsCron::EXECUTE_SUCCESS,
                    'updated_at' => Carbon::now()
                ]);
        } catch (\Exception $exception) {
            // 错误将任务状态修改
            /** @var StatisticsCron $statisticsTask */
            if (!empty($statisticsTask->id)) {
                StatisticsCron::where('id', $statisticsTask->id)
                    ->update([
                        'status' => StatisticsCron::EXECUTE_FAILURE,
                        'updated_at' => Carbon::now()
                    ]);
                Log::error('StatisticsCommand:&&' . $statisticsTask->id . '&&,' . $exception->getMessage());
            } else {
                Log::error('StatisticsCommand:' . $exception->getMessage());
            }
        }
    }

    /**
     * sql拼接分页
     *
     * @param string $str
     * @param int $page
     * @param int $limit
     * @return string
     */
    protected function handleSql(string $str, int $page, int $limit = 5000)
    {
        $offset = $page * $limit;

        return $str . " LIMIT $offset,$limit";
    }

    /**
     * 处理数据表头
     *
     * @param string $taskName
     * @return array
     * @throws \Exception
     */
    protected function allocateProcessingData(string $taskName)
    {
        if ($taskName === 'workOrder') {
            return [
                '工单项目记录ID',
                '工单记录ID',
                '工单编号',
                '工单项目',
                '医生编号',
                '医生姓名',
                'DS',
                '专员',
                '项目',
                '医院编号',
                '医院',
                '省',
                '市',
                '区',
                '执行日期',
                '沟通渠道',
                '工单目的',
                '是否项目医院内',
                '是否项目内医生',
                '实际执行项目',
                '沟通状态',
                '工单备注',
                '工单类型',
                '是否电话接通',
                '是否有效沟通',
                '是否成功沟通',
                '通话时长（秒）',
                '配合度',
                '医生接受度',
                '电话是否知情同意',
                '是否提及产品',
                '是否提及关键信息',
                '临床工具类',
                '临床关注类',
                '销量影响因素'
            ];
        } elseif ($taskName === 'projectHospital') {
            return [
                '医院ID',
                '项目',
                '医院编号',
                '医院',
                '省',
                '市',
                '区',
                '医院类型',
                '医院级别',
                '市场DS',
                '品牌',
                '医生招募指标',
                '是否有药',
                '区域',
                '项目医院备注',
                '客户备注'
            ];
        } elseif ($taskName === 'doctor') {
            return [
                '医院ID',
                '项目',
                '医院编号',
                '医院',
                '省',
                '市',
                '客户编号',
                '医生编号',
                '医生姓名',
                '是否脱落',
                '是否项目内',
                '医生分组',
                '阶段',
                '科室',
                '市场DS',
                '主管DS',
                '是否加微信',
                '微信好友',
                '微信状态',
                '加微信时间',
                '微网站状态',
                '关注时间',
                '认证时间',
                '有效沟通量',
                '成功沟通量',
                '微信发送',
                '微信回复',
                '末次电话成功沟通时间',
                '末次微信发送时间',
            ];
        } else {
            throw new \Exception('Unknown task_name');
        }
    }
}
