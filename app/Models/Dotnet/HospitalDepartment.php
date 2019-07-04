<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-09 16:57:31
 */

namespace App\Models\Dotnet;

/**
 * 医院科室关系表
 *
 * @package App\Models\Dotnet
 * @property string $hospitalDepartmentId 医院科室关系编号
 * @property string $hospitalId 医院编号
 */
class HospitalDepartment extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_hospital_department";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "hospitalDepartmentId";

}