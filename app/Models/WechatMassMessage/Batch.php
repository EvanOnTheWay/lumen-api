<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 01:34:48
 */

namespace App\Models\WechatMassMessage;

use App\Models\BaseModel;
use Carbon\Carbon;

/**
 * 微信群发: 批次表
 *
 * @package App\Models\WechatMassMessage
 * @property integer $id                批次编号
 * @property string $representative_id  专员编号
 * @property string $project_id         项目编号
 * @property integer $template_id       模板编号
 * @property string $creator_id         创建人的用户编号
 * @property string $auditor_id         审核人的用户编号
 * @property integer $audit_state       审核状态 0:已创建 1:待审核 2:已批准 3:已驳回
 * @property integer $execute_state     执行状态 0:尚未执行 1:正在执行 2:执行成功 3:执行失败
 * @property string $audit_opinion      审核意见
 * @property Carbon|null $audited_at    审核时间
 * @property Carbon|null $executed_at   执行时间
 * @property Carbon|null $created_at    创建时间
 * @property Carbon|null $updated_at    更新时间
 */
class Batch extends BaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "wechat_mass_message_batch";

    /**
     * 审核状态: 待提审
     */
    public const AUDIT_PENDING = 0;

    /**
     * 审核状态: 已提审
     */
    public const AUDIT_AUDITING = 1;

    /**
     * 审核状态: 已审核
     */
    public const AUDIT_APPROVED = 2;

    /**
     * 审核状态: 已驳回
     */
    public const AUDIT_REJECTED = 3;

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
     * 更新批次的执行状态
     *
     * @param int $state
     */
    public function updateExecuteState(int $state)
    {
        $this->where('id', $this->id)->update([
            'executed_at' => Carbon::now(),
            'execute_state' => $state,
        ]);
    }
}