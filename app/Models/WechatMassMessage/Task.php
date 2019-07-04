<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 00:02:57
 */

namespace App\Models\WechatMassMessage;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * 微信群发: 任务表
 *
 * @package App\Models\WechatMassMessage
 * @property integer $id                任务编号
 * @property integer $batch_id          批次编号
 * @property string $doctor_id          医生编号
 * @property integer $template_id       模板编号
 * @property string $content            消息内容 模板内容渲染结果
 * @property integer $execute_state     执行状态 0:尚未执行 1:正在执行 2:执行成功 3:执行失败
 * @property string $execute_comment    执行备注
 * @property Carbon|null $executed_at   执行时间
 * @property Carbon|null $created_at    创建时间
 * @property Carbon|null $updated_at    更新时间
 */
class Task extends BaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "wechat_mass_message_task";

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

    /**
     * 批量修改消息执行状态
     *
     * @param array $ids
     * @param int $state
     * @param string $comment
     */
    public static function multiUpdateExecuteState(array $ids, int $state, string $comment = '')
    {
        static::whereIn('id', $ids)->update([
            'executed_at' => Carbon::now(),
            'execute_state' => $state,
            'execute_comment' => $comment
        ]);
    }
}