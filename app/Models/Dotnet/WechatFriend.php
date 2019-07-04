<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 15:04:51
 */

namespace App\Models\Dotnet;

/**
 * 微信联系人表
 *
 * @package App\Models\Dotnet
 * @property string $doctorRepresentativeId 医生专员关系编号
 * @property string $username          微信ID
 * @property string $nickName          微信昵称
 * @property string $conRemark         微信备注名称
 * @property string $modifiedDate      最后修改时间
 */
class WechatFriend extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_wechat_friend";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "WechatFriendId";
}