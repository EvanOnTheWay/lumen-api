<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-10 10:31:55
 */

namespace App\Services\Dotnet;

use App\Models\Dotnet\Hospital;
use App\Models\Dotnet\ProjectHospital;
use App\Models\Dotnet\Region;
use App\Models\Dotnet\RepresentativeProjectHospital;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * 医院业务逻辑
 *
 * @package App\Services
 */
class HospitalService extends BaseService
{
    /**
     * 查询指定地区的医院编号列表
     *
     * @param string $regionId
     * @return array
     */
    public function getHospitalIdsByRegion(string $regionId): array
    {
        // 筛选指定行政区划的医院
        // 这里要加上这个条件，鬼扯的 logicId
        // $hospitalQuery = Hospital::where(DB::raw('`hospitalId` = `logicId`'));
        $hospitalQuery = Hospital::select(['hospitalId']);

        // 查询传入区划的区划等级
        $regionService = RegionService::newInstance();
        $regionGrade = $regionService->getRegionGrade($regionId);
        Log::debug('查询传入区划的区划等级', [$regionGrade]);

        switch ($regionGrade) {
            case Region::GRADE_PROVINCE:
                $hospitalQuery->where('provinceId', $regionId);
                break;
            case Region::GRADE_CITY:
                $hospitalQuery->where('cityId', $regionId);
                break;
            case Region::GRADE_DISTRICT:
                $hospitalQuery->where('districtId', $regionId);
                break;
            default:
                // 无效的行政区划，无法匹配医院
                return [];
        }

        $result = $hospitalQuery->get()
            ->pluck('hospitalId')
            ->toArray();

        return $result;
    }

    /**
     * 查询"专员"在"项目"中关联的全部医院的编号
     *
     * @param string $representativeId
     * @param string $projectId
     * @return array
     */
    public function getHospitalIdsByRepresentativeIdAndProjectId(string $representativeId, string $projectId): array
    {
        $projectHospitalIds = RepresentativeProjectHospital::where('representativeId', $representativeId)
            ->get(['projectHospitalId'])
            ->pluck('projectHospitalId')
            ->toArray();

        if (empty($projectHospitalIds)) {
            return [];
        }

        return ProjectHospital::where('projectId', $projectId)
            ->whereIn('projectHospitalId', $projectHospitalIds)
            // isDeleted 是 bit 类型
            ->where('isDeleted', '0')
            ->get(['hospitalId'])
            ->pluck('hospitalId')
            ->toArray();
    }
}