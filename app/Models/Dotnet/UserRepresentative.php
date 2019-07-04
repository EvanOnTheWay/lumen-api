<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 11:19:40
 */

namespace App\Models\Dotnet;

/**
 * 用户-代表关系表
 *
 * @package App\Models\Dotnet
 * @property string $userId             用户编号
 * @property string $representativeId   代表编号
 * @property integer $disabled          停用状态 1:停用 0:启用
 */
class UserRepresentative extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_user_representative";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "userRepresentativeId";

    /**
     * 停用状态:已停用
     */
    public const DISABLED_YES = 1;

    /**
     * 停用状态:已启动
     */
    public const DISABLED_NO = 0;
}