<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 15:04:51
 */

namespace App\Models\Dotnet;

/**
 * 医生医院科室关系表
 *
 * @package App\Models\Dotnet
 * @property string $doctorHospitalDepartmentId     医生医院科室关系编号
 * @property string $doctorId                       医生编号
 * @property string $hospitalDepartmentId           医院科室编号
 */
class DoctorHospitalDepartment extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_doctor_hospital_department";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "doctorHospitalDepartmentId";
}