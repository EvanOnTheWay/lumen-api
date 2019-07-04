<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 16:26:18
 */

namespace App\Http\Controllers\WechatMassMessage;

use App\Http\ResponseStatus;
use App\Http\ResponseWrapper;
use App\Models\Dotnet\Wechat;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Routing\Controller;

/**
 * 微信虚拟客户端
 *
 * @package App\Http\Controllers\Wechat
 */
class ClientController extends Controller
{
    /**
     * 查询虚拟客户端登录状态
     *
     * @param Request $request
     * @return array
     */
    public function getWechatLoginState(Request $request)
    {
        // 当前登录用户使用的专员身份
        $repId = $request->attributes->get("representative_id");

        $wechat = Wechat::select(["userName"])
            ->where("representativeId", $repId)
            ->first();
        if (null === $wechat) {
            return ResponseWrapper::invalid();
        }

        try {
            $response = (new Client())->post('http://127.0.0.1:8000/wechatWebRobot/getLoginState', [
                'json' => [
                    'rep_id' => $repId
                ]
            ]);
            $responseData = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
            switch ($responseData['code']) {
                case 200:
                    if (in_array($responseData['data']['state'], ['LOGGING', 'ONLINE'])) {
                        return ResponseWrapper::success(["state" => true]);
                    }
                    return ResponseWrapper::success(["state" => false]);
                case 404:
                    return ResponseWrapper::success(["state" => false]);
                case 400:
                    return ResponseWrapper::invalid();
                default:
                    return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_CODE_UNDEFINED);
            }

        } catch (TransferException $exception) {
            Log::error("WECHAT_MASS_MESSAGE_ISERVICE_UNREACHED", [
                'exception' => [
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTraceAsString(),
                ]
            ]);
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_UNREACHED);
        }
    }

    /**
     * 读取虚拟客户端登录二维码
     *
     * @param Request $request
     * @return array
     */
    public function getWechatLoginQrcode(Request $request)
    {
        // 当前登录用户使用的专员身份
        $repId = $request->attributes->get("representative_id");

        $wechat = Wechat::select(["userName"])
            ->where("representativeId", $repId)
            ->first();
        if (null === $wechat) {
            return ResponseWrapper::invalid();
        }

        try {
            $response = (new Client())->post('http://127.0.0.1:8000/wechatWebRobot/getLoginQrcode', [
                'json' => [
                    'rep_id' => $repId
                ]
            ]);
            $responseData = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
            switch ($responseData['code']) {
                case 200:
                    return ResponseWrapper::success([
                        "qrcode" => $responseData['data']['qrcode']
                    ]);
                case 403:
                    return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_ALREADY_ONLINE);
                case 400:
                    return ResponseWrapper::invalid();
                case 502:
                    return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_BAD_GATEWAY);
                case 504:
                    return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_GATEWAY_TIMEOUT);
                default:
                    return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_CODE_UNDEFINED);
            }
        } catch (TransferException $exception) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_UNREACHED);
        }
    }
}
