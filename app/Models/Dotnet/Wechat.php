<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 15:04:51
 */

namespace App\Models\Dotnet;

/**
 * 专员微信表
 *
 * @package App\Models\Dotnet
 * @property string $wechatId           微信记录编号
 * @property string $representativeId   所属专员编号
 * @property string $userName           微信 ID
 * @property string $nickName           微信昵称
 */
class Wechat extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_wechat";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "wechatId";
}