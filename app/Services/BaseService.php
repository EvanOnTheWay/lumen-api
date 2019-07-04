<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:20:19
 */

namespace App\Services;

/**
 * 业务服务: 基类
 *
 * @package App\Services
 */
abstract class BaseService
{
    /**
     * 返回新实例
     *
     * @return static
     */
    public static function newInstance()
    {
        return new static();
    }
}