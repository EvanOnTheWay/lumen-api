<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-15 15:20:57
 */

namespace App\Http\Controllers\WechatMassMessage;

use App\Http\ResponseWrapper;
use App\Services\Dotnet\DoctorService;
use App\Services\Dotnet\HospitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller;

/**
 * 微信群发: 联系人
 *
 * @package App\Http\Controllers\WechatMassMessage
 */
class ContactController extends Controller
{
    /**
     * 查询指定项目中的微信联系人
     *
     * @param Request $request
     * @return array
     */
    public function getWechatContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|uuid',
            'region_id' => 'string|size:6',
        ], [
            'project_id.*' => '请输入正确的项目编号',
            'region_id.*' => '请输入正确的行政区划(6 位数字)'
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $projectId = (string)$request->input('project_id');

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get('representative_id');

        // 查询"专员"关联的全部医生编号
        $doctorService = DoctorService::newInstance();
        $projectDoctorIds = $doctorService->getDoctorIdsByRepresentativeIdAndProjectId($representativeId, $projectId);

        Log::debug("专员在项目中的全部关联医生", $projectDoctorIds);

        if ($request->has('region_id')) {
            $regionId = (string)$request->input('region_id');
            $hospitalService = HospitalService::newInstance();

            // 查询"地区"的全部医院的编号
            $regionHospitalIds = $hospitalService->getHospitalIdsByRegion($regionId);
            Log::debug("指定地区下的全部医院", $regionHospitalIds);

            // 查询"专员"在"项目"中关联的全部医院的编号
            $projectHospitalIds = $hospitalService->getHospitalIdsByRepresentativeIdAndProjectId($representativeId, $projectId);
            Log::debug("专员在项目中关联的全部医院的编号", $projectHospitalIds);

            // 取交集，得到"专员"在"项目"中关联的"地区"的全部医院的编号
            $hospitalIds = array_intersect($regionHospitalIds, $projectHospitalIds);
            Log::debug('得到"专员"在"项目"中关联的"地区"的全部医院的编号', $hospitalIds);

            // 查询"医院"的全部医生的编号
            $hospitalDoctorIds = $doctorService->getDoctorIdsByHospitals($hospitalIds);
            Log::debug('"医院"的全部医生的编号', $hospitalDoctorIds);

            // 取交集，得到"专员"在"项目"中关联的"地区"的全部医生的编号
            $projectDoctorIds = array_intersect($projectDoctorIds, $hospitalDoctorIds);
            Log::debug('取交集，得到"专员"在"项目"中关联的"地区"的全部医生的编号', $projectDoctorIds);
        }

        $contacts = $doctorService->getWechatContactsByDoctorIds($projectDoctorIds);
        Log::debug('医生微信联系人集合', $contacts);

        return ResponseWrapper::success(["friends" => $contacts]);
    }
}