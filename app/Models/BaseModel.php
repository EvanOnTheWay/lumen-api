<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:23:06
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseModel
 * @package App\Models
 * @mixin \Eloquent
 */
abstract class BaseModel extends Model
{
    /**
     * 保护属性
     *
     * 将此属性设置在基类的目的是希望所有属性均可批量赋值
     * @link https://laravel.com/docs/5.5/eloquent
     *
     * @var array
     */
    protected $guarded = [];
}