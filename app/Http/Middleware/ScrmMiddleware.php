<?php
/**
 * Created by PhpStorm.
 * @author Kihra
 */

namespace App\Http\Middleware;

use App\Http\RedisKey;
use App\Http\ResponseScrmWrapper;
use App\Http\ResponseStatus;
use App\Models\Dotnet\Token;
use App\Models\System\SystemUser;
use App\Utils\FormatUtil;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/**
 * 兼容 .NET 版 SCRM 的接口风格
 *
 * @package App\Http\Middleware
 */
class ScrmMiddleware
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
        $tokenString = (string)$request->header("access-token");
        if ($tokenString === "" || !FormatUtil::uuid($tokenString)) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_TOKEN);
        }
        $tokenInfo = [];
        //判断token是否在缓存中
        $userTokenKey = RedisKey::USER_TOKEN_KEY.$tokenString;
        if (Redis::exists($userTokenKey)) {
            $tokenInfo = json_decode(Redis::get($userTokenKey), true);
        }
        if (!empty($tokenInfo)) {
            $request->attributes->add([
                "user_id" => $tokenInfo['user_id'],
            ]);
            return $next($request);
        } else {
            // 登录令牌换取用户身份
            $tokenObject = Token::parse($tokenString);
            if (null === $tokenObject) {
                return ResponseScrmWrapper::failure(ResponseStatus::SCRM_TOKEN_EXPIRED);
            }
            $userInfo = SystemUser::getIdFromUUID($tokenObject->getUserId());
            if ($userInfo === null) {
                return ResponseScrmWrapper::failure(ResponseStatus::SCRM_USER_NOT_EXIST);
            }
            //存入redis
            $tokenInfo = ['user_id' => $userInfo->id];
            Redis::setex($userTokenKey,3600*2 ,json_encode($tokenInfo, JSON_UNESCAPED_UNICODE));
            $request->attributes->add([
                "user_id" => $userInfo->id,
            ]);
            return $next($request);

        }
    }
}
