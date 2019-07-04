<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:49:20
 */

namespace App\Models\Dotnet;

/**
 * 项目负责人关系表
 *
 * @package App\Models\Dotnet
 * @property string $projectId  项目编号
 * @property string $userId     负责人的用户编号
 */
class ProjectUser extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_project_user";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "projectUserId";
}