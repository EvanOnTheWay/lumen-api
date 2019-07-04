<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:31:39
 */

namespace App\Models\Statistics;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * Class StatisticsCron
 *
 * @property integer        $id             主键ID
 * @property string         $task_name      任务名
 * @property string         $exec_sql       执行sql
 * @property string         $operator_id    操作人ID
 * @property string         $file_name      文件名
 * @property string         $excel_path     文件存储路径
 * @property integer        $status         状态
 * @property Carbon|null    $created_at     创建时间
 * @property Carbon|null    $updated_at     更新时间
 * @package App\Models
 * @method static where(string $string, String $operatorId)
 */
class StatisticsCron extends BaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "statistics_cron";
    /**
     * 执行状态: 尚未执行
     */
    public const EXECUTE_PENDING = 0;

    /**
     * 执行状态: 正在执行
     */
    public const EXECUTE_RUNNING = 1;

    /**
     * 执行状态: 执行成功
     */
    public const EXECUTE_SUCCESS = 2;

    /**
     * 执行状态: 执行失败
     */
    public const EXECUTE_FAILURE = 3;
}