<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-09 16:57:31
 */

namespace App\Models\Dotnet;

/**
 * 医院表
 *
 * @package App\Models\Dotnet
 * @property string $hospitalId 医院编号
 * @property string $provinceId 所属省份编号
 * @property string $cityId     所属城市编号
 * @property string $districtId 所属区县编号
 */
class Hospital extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_hospital";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "hospitalId";

}