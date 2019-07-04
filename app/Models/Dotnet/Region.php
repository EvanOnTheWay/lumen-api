<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-09 16:57:31
 */

namespace App\Models\Dotnet;

/**
 * 行政区划表
 *
 * @package App\Models\Dotnet
 * @property string $regionId       区划编号
 * @property string $regionGrade    区划等级(Province|City|District)
 * @property string $parentRegionId 上级区划编号
 * @property string $nameZh         区划中文名称
 */
class Region extends DotnetBaseModel
{
    const GRADE_PROVINCE = "Province";

    const GRADE_CITY = "City";

    const GRADE_DISTRICT = "District";

    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "met_sys_region";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "regionId";
}