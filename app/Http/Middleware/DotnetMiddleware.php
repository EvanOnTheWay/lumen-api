<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 15:00:00
 */

namespace App\Http\Middleware;

use App\Http\ResponseStatus;
use App\Models\Dotnet\Token;
use App\Utils\FormatUtil;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * 兼容 .NET 版 SCRM 的接口风格
 *
 * @package App\Http\Middleware
 */
class DotnetMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 校验登录令牌
        $tokenString = (string)$request->header("token");
        if ($tokenString === "" || !FormatUtil::uuid($tokenString)) {
            return \response("登录令牌无效", Response::HTTP_UNAUTHORIZED);
        }

        // 登录令牌换取用户身份
        $tokenObject = Token::parse($tokenString);
        if (null === $tokenObject) {
            return \response("登录令牌已过期", Response::HTTP_UNAUTHORIZED);
        }

        // 添加信息到 $request, 控制器可通过 $request->attributes->get() 读取
        $request->attributes->add([
            "user_id" => $tokenObject->getUserId(),
            "representative_id" => $tokenObject->getRepresentativeId(),
        ]);

        /**
         * @var Response $response
         */
        $response = $next($request);
        if ($response instanceof Response) {
            if ($response->getStatusCode() === Response::HTTP_OK) {
                $wrapper = ["timestamp" => $this->getTimestamp()];

                $content = $response->getOriginalContent();
                if ($content["code"] === ResponseStatus::SUCCESS[0]) {
                    $wrapper["data"] = $content["data"];
                } else {
                    $wrapper["error"] = $content;
                }
                return $wrapper;
            }
        }

        return $response;
    }

    /**
     * 判断是否成功响应
     *
     * @param string $code
     * @return bool
     */
    protected function isSuccessCode(string $code)
    {
        return $code === ResponseStatus::SUCCESS[0];
    }

    /**
     * 获取当前时间戳(13 位)
     * @return string
     */
    protected function getTimestamp()
    {
        return number_format(microtime(true), 3, '', '');
    }
}
