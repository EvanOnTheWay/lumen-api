<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 15:00:00
 */

namespace App\Models\Dotnet;

use Carbon\Carbon;

/**
 * 登录令牌表
 *
 * @package App\Models\Dotnet
 * @property string $tokenId            登录令牌
 * @property string $targetAccountId    用户编号
 * @property string $expiredTime        过期时间
 * @property string $accountDetail      会话详情
 * @property string $authScheme         认证方式
 */
class Token extends DotnetBaseModel
{
    /**
     * 数据库表名
     *
     * @var string
     */
    protected $table = "ent_sys_token";

    /**
     * OP 系统对应的认证方式
     */
    public const AUTH_SCHEME_OP = "Qingyun.Zhiyunelu.API.Facade.Operation.Basic.OperationAuthHandler";

    /**
     * 解析登录令牌
     *
     * @param string $string
     * @return Token|null
     */
    public static function parse(string $string)
    {
        return Token::where("tokenId", $string)
            ->where("expiredTime", ">", Carbon::now())
            ->where("authScheme", static::AUTH_SCHEME_OP)
            ->first([
                "tokenId",
                "targetAccountId",
                "expiredTime",
                "accountDetail",
                "authScheme"
            ]);
    }

    /**
     * 读取用户编号
     * @return string
     */
    public function getUserId(): string
    {
        return (string)$this->targetAccountId;
    }

    /**
     * 读取会话详情中的代表编号
     *
     * @return string
     */
    public function getRepresentativeId(): string
    {
        $accountDetail = json_decode($this->accountDetail, true);
        if (isset($accountDetail["representativeId"])) {
            return (string)$accountDetail["representativeId"];
        }

        return "";
    }
}