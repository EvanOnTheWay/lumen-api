<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:59:31
 */

namespace App\Models\Dotnet;

/**
 * 项目-医生关系表
 *
 * @package App\Models\Dotnet
 * @property string $projectDoctorId  项目医生关系编号
 * @property string $projectId        项目编号
 * @property string $doctorId         医生编号
 */
class ProjectDoctor extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_project_doctor";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "projectDoctorId";
}