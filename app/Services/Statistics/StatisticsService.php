<?php /** @noinspection ALL */

namespace App\Services\Statistics;

use App\Models\Statistics\StatisticsCron;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;


class StatisticsService extends BaseService
{
    /**
     * 获取所有统计任务
     *
     * @param String $operatorId
     * @param String $taskName
     * @param Int $page
     * @param Int $limit
     * @return mixed
     */
    public function getTaskList(string $operatorId, string $taskName, int $page, int $limit)
    {
        $statisticsTaskRes = StatisticsCron::where('operator_id', $operatorId)
            ->where('task_name', $taskName)
            ->select('id', 'file_name', 'status')
            ->orderBy('id','DESC')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get();

        return $statisticsTaskRes;
    }


    /**
     * 获取统计任务总数
     *
     * @param String $operatorId
     * @param String $taskName
     * @return mixed
     */
    public function getTaskCount(string $operatorId, string $taskName)
    {
        $statisticsTaskTotal = StatisticsCron::where('operator_id', $operatorId)
            ->where('task_name', $taskName)
            ->count();

        return $statisticsTaskTotal;
    }


    /**
     * 获取项目
     *
     * @return array
     */
    public function getProject()
    {
        $projectRes = DB::connection('dotnet')
            ->table('ent_data_project')
            ->select('projectName', 'projectCode')
            ->get();

        $projectArr = [];

        foreach ($projectRes as $val) {
            $row['project_code'] = $val->projectCode;
            $row['project_name'] = $val->projectName;

            $projectArr[] = $row;
        }

        return $projectArr;
    }


    /**
     * 获取省
     *
     * @return array
     */
    public function getProvince()
    {
        $provinceRes = DB::connection('dotnet')
            ->table('met_sys_region')
            ->where('regionGrade', 'Province')
            ->select('nameZH', 'regionId')
            ->get();

        $provinceArr = [];

        foreach ($provinceRes as $val) {
            $row['province_id'] = $val->regionId;
            $row['province_name'] = $val->nameZH;

            $provinceArr[] = $row;
        }

        return $provinceArr;
    }


    /**
     * 判断创建任务按钮是否可以使用
     *
     * @param String $operatorId
     * @param String $taskName
     * @return int
     */
    public function isSubmit(string $operatorId, string $taskName)
    {
        // 一个用户在一个统计周期内只能使用一次，上一次统计还没完成该就不能创建新任务
        // 判断所有任务中有没有是未执行和执行中
        $isSubmit = 1;

        $statisticsCrontabsCount = StatisticsCron::where('operator_id', $operatorId)
            ->where('task_name', $taskName)
            ->whereIn('status', [0, 1])
            ->count();

        if ($statisticsCrontabsCount > 0) {
            $isSubmit = 0;
        } else {
            $isSubmit = 1;
        }

        return $isSubmit;
    }


    /**
     * 获取沟通状态
     *
     * @return array
     */
    public function getCommunicationStatus()
    {
        $communicationStatusRes = DB::connection('dotnet')
            ->table('ent_bpm_communication_status')
            ->select('CommunicationStatusName', 'CommunicationStatusId')
            ->get();

        $communicationStatusArr = [];

        foreach ($communicationStatusRes as $val) {
            $row['communication_status_id'] = $val->CommunicationStatusId;
            $row['communication_status_name'] = $val->CommunicationStatusName;

            $communicationStatusArr[] = $row;
        }

        return $communicationStatusArr;
    }
}