<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 15:04:51
 */

namespace App\Models\Dotnet;

/**
 * 医生专员关系表
 *
 * @package App\Models\Dotnet
 * @property string $doctorRepresentativeId  医生专员关系编号
 * @property string $doctorId          医生编号
 * @property string $representativeId  专员编号
 */
class DoctorRepresentative extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_doctor_representative";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "doctorRepresentativeId";
}