<?php
/**
 * Created by PhpStorm.
 * @author Wangjiwei
 */

namespace App\Models\System;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 数据模型: SCRM 系列模型基类
 * @package App\Models\Dotnet
 */
abstract class ScrmBaseModel extends BaseModel
{
    use SoftDeletes;
    /**
     * 指定数据库连接
     *
     * @var string
     */
    protected $connection = "mysql";

    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}