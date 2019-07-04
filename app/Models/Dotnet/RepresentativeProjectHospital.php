<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:59:31
 */

namespace App\Models\Dotnet;

/**
 * 代表-项目-医院关系表
 *
 * @package App\Models\Dotnet
 * @property string $representativeId   代表编号
 * @property string $projectHospitalId  项目医院关系编号
 */
class RepresentativeProjectHospital extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_representative_project_hospital";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "representativeProjectHospitalId";
}