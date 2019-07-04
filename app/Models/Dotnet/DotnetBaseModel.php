<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:27:28
 */

namespace App\Models\Dotnet;

use App\Models\BaseModel;

/**
 * 数据模型: Dotnet 系列模型基类
 * @package App\Models\Dotnet
 */
abstract class DotnetBaseModel extends BaseModel
{
    /**
     * 指定数据库连接
     *
     * @var string
     */
    protected $connection = "dotnet";

    /**
     * 声明主键类型为字符串(uuid)
     *
     * @var string
     */
    protected $keyType = "string";

    /**
     * 关闭主键自增
     *
     * @var bool
     */
    public $incrementing = false;
}