<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 10:47:10
 */

namespace App\Models\Dotnet;

/**
 * 医生信息表
 *
 * @package App\Models\Dotnet
 * @property string $doctorId   医生编号
 * @property integer $doctorNo  医生序号
 * @property string $doctorName 医生名称
 */
class Doctor extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_doctor";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "doctorId";
}