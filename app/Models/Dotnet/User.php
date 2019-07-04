<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:47:10
 */

namespace App\Models\Dotnet;

/**
 * 用户信息表
 *
 * @package App\Models\Dotnet
 * @property string $userId     用户编号
 * @property string $userName   用户名称
 * @property integer $disabled  停用状态 1:停用 0:启用
 */
class User extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_sys_user";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "userId";

    /**
     * 停用状态:已停用
     */
    public const DISABLED_YES = 1;

    /**
     * 停用状态:已启动
     */
    public const DISABLED_NO = 0;
}