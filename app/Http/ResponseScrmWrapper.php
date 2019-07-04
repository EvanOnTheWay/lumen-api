<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 00:14:44
 */

namespace App\Http;

/**
 * ResponseWrapper
 * 负责对控制器中的返回数据，按照规定的格式进行封装
 *
 * @package App\Http
 */
class ResponseScrmWrapper
{
    /**
     * 成功的响应
     *
     * @param array $data
     * @param string $message
     * @return array
     */
    public static function success(array $data = []): array
    {
        return self::build(ResponseStatus::SCRM_SUCCESS, $data);
    }

    /**
     * 失败的响应
     *
     * @param array $status 预定义错误
     * @param string $message 自定义消息
     * @return array
     */
    public static function failure(int $status,array $data = []): array
    {
        return self::build($status, $data);
    }

    /**
     * 入参校验失败的响应
     *
     * @param string $message 自定义消息
     * @return array
     */
    public static function invalid(string $message = ""): array
    {
        return self::build(ResponseStatus::INVALID_PARAMETER, [], $message);
    }

    /**
     * 执行响应组装
     *
     * @param array $status     预定义状态，见 ResponseStatus 中定义
     * @param array $data       业务数据
     * @param string $message   业务消息
     * @return array
     */
    protected static function build(int $status, array $data)
    {
        return [
            "code" => $status,
            "data" => $data,
            "message" => ResponseStatus::CODE_MESSAGE[$status],
        ];
    }
}