<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-09 20:20:36
 */

namespace App\Services\Dotnet;

use App\Models\Dotnet\Doctor;
use App\Models\Dotnet\DoctorHospitalDepartment;
use App\Models\Dotnet\DoctorRepresentative;
use App\Models\Dotnet\HospitalDepartment;
use App\Models\Dotnet\ProjectDoctor;
use App\Models\Dotnet\WechatFriend;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

/**
 * 医生业务逻辑
 *
 * @package App\Services\Dotnet
 */
class DoctorService extends BaseService
{
    /**
     * 查询多个医院的全部医生编号
     * @param array $hospitalIds
     * @return array
     */
    public function getDoctorIdsByHospitals(array $hospitalIds)
    {
        if (empty($hospitalIds)) {
            return [];
        }

        $hospitalDepartmentIds = HospitalDepartment::select(["hospitalDepartmentId"])
            //->where(DB::raw("`hospitalDepartmentId` = `logicId`"))
            ->whereIn("hospitalId", $hospitalIds)
            ->get()
            ->pluck("hospitalDepartmentId")
            ->toArray();
        \Log::debug('医院部门关联编号', $hospitalDepartmentIds);

        if (empty($hospitalDepartmentIds)) {
            return [];
        }

        $result = DoctorHospitalDepartment::select(["doctorId"])
            //->where(DB::raw("`doctorHospitalDepartmentId` = `logicId`"))
            ->whereIn("hospitalDepartmentId", $hospitalDepartmentIds)
            ->get()
            ->pluck("doctorId")
            ->toArray();
        \Log::debug('多个医院的全部医生编号', $result);

        return $result;
    }

    /**
     * 查询主联系人为"指定专员"的全部医生的编号
     *
     * @param string $representativeId
     * @return array
     */
    public function getDoctorIdsByRepresentativeId(string $representativeId): array
    {
        $doctorRepresentativeIds = DoctorRepresentative::where('representativeId', $representativeId)
            ->get(['doctorRepresentativeId'])
            ->pluck('doctorRepresentativeId')
            ->toArray();
        if (empty($doctorRepresentativeIds)) {
            return [];
        }

        return Doctor::whereIn('majorDoctorRepresentativeId', $doctorRepresentativeIds)
            ->get(['doctorId'])
            ->pluck('doctorId')
            ->toArray();
    }

    /**
     * 查询"指定专员"在"指定项目"中绑定的全部医生的编号
     *
     * @param string $representativeId
     * @param string $projectId
     * @return array
     */
    public function getDoctorIdsByRepresentativeIdAndProjectId(string $representativeId, string $projectId)
    {
        $doctorIds = $this->getDoctorIdsByRepresentativeId($representativeId);

        if (empty($doctorIds)) {
            return [];
        }

        return ProjectDoctor::whereIn('doctorId', $doctorIds)
            ->where('projectId', $projectId)
            ->where('lostStatus', 'Active')
            ->where('isDeleted', '0')
            //->where('refused', '0')
            ->get(['doctorId'])
            ->pluck('doctorId')
            ->toArray();
    }

    /**
     * 批量查询医生的微信联系人备注名
     *
     * @param array $doctorIds
     * @return array
     */
    public function getWechatContactsByDoctorIds(array $doctorIds)
    {
        $doctorRepresentativeIds = DoctorRepresentative::whereIn('doctorId', $doctorIds)
            ->get(['doctorRepresentativeId', 'doctorId'])
            ->pluck('doctorRepresentativeId', 'doctorId')
            ->toArray();
        if (empty($doctorRepresentativeIds)) {
            return [];
        }

        $remarkNames = WechatFriend::whereIn('doctorRepresentativeId', array_values($doctorRepresentativeIds))
            ->where('conRemark', '!=' , '')
            ->whereNotNull('conRemark')
            ->where('status', 'Normal')
            ->get(['conRemark', 'doctorRepresentativeId'])
            ->pluck('conRemark', 'doctorRepresentativeId')
            ->toArray();

        $result = [];
        foreach ($doctorRepresentativeIds as $doctorId => $doctorRepresentativeId) {
            if (isset($remarkNames[$doctorRepresentativeId])) {
                // 使用医生编号重新索引是为了"去重"
                $result[$doctorId] = [
                    'doctor_id' => $doctorId,
                    'remark_name' => $remarkNames[$doctorRepresentativeId]
                ];
            }
        }

        return array_values($result);
    }
}