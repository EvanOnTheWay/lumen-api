<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 00:12:54
 */

namespace App\Models\WechatMassMessage;

use App\Models\BaseModel;
use Illuminate\Support\Carbon;

/**
 * 微信群发：消息模板表
 *
 * @package App\Models\WechatMassMessage
 * @property integer $id                模板编号
 * @property string $name               模板名称
 * @property string $content            模板内容
 * @property integer $state             可用状态 0:停用(默认) 1:启用
 * @property Carbon|null $created_at    创建时间
 * @property Carbon|null $updated_at    更新时间
 */
class Template extends BaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "wechat_mass_message_template";

    /**
     * 可用状态: 启用
     */
    public const STATE_ENABLE = 1;

    /**
     * 可用状态: 停用
     */
    public const STATE_DISABLE = 0;

    /**
     * 设置名称和内容
     *
     * @param string $name      模板名称
     * @param string $content   模板内容
     * @return bool 成功/失败
     */
    public function setNameContent(string $name, string $content)
    {
        // 当且仅当"名称"或"内容"真实变化时才更新到数据库
        if ($this->name !== $name || $this->content !== $content) {
            $this->name = $name;
            $this->content = $content;

            return $this->save();
        }

        return true;
    }

    /**
     * 启动模板
     *
     * @return bool 成功/失败
     */
    public function setEnable()
    {
        // 当且仅当"可用状态"真实变化时才更新到数据库
        if ($this->state !== Template::STATE_ENABLE) {
            $this->state = Template::STATE_ENABLE;

            return $this->save();
        }

        return true;
    }

    /**
     * 停用模板
     *
     * @return bool 成功/失败
     */
    public function setDisable()
    {
        // 当且仅当"可用状态"真实变化时才更新到数据库
        if ($this->state !== Template::STATE_DISABLE) {
            $this->state = Template::STATE_DISABLE;

            return $this->save();
        }

        return true;
    }
}