<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:59:31
 */

namespace App\Models\Dotnet;

/**
 * 项目-医院关系表
 *
 * @package App\Models\Dotnet
 * @property string $projectHospitalId  项目医院关系编号
 * @property string $projectId          项目编号
 * @property string $hospitalId         医院编号
 */
class ProjectHospital extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_project_hospital";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "projectHospitalId";
}