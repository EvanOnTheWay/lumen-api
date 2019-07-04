<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 11:14:59
 */

namespace App\Models\Dotnet;

/**
 * 代表信息表
 *
 * @package App\Models\Dotnet
 * @property string $representativeId   代表编号
 * @property string $representativeName 代表名称
 */
class Representative extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_data_representative";

    /**
     * 声明主键名称
     *
     * @var string
     */
    protected $primaryKey = "representativeId";
}