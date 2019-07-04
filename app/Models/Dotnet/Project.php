<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:49:20
 */

namespace App\Models\Dotnet;

/**
 * 项目表
 *
 * @package App\Models\Dotnet
 * @property string $projectId      项目编号
 * @property string $projectName    项目名称
 */
class Project extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_project";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "projectId";
}