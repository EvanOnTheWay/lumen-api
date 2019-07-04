<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 16:41:29
 */

namespace App\Services\Dotnet;

use App\Models\Dotnet\Project;
use App\Models\Dotnet\ProjectHospital;
use App\Models\Dotnet\ProjectUser;
use App\Models\Dotnet\RepresentativeProjectHospital;
use App\Models\Dotnet\User;
use App\Services\BaseService;

/**
 * 项目业务逻辑
 *
 * @package App\Services\Dotnet
 */
class ProjectService extends BaseService
{
    /**
     * 查询指定专员所属的全部项目列表
     *
     * @param string $representativeId
     * @return array
     */
    public function getProjectsByRepresentativeId(string $representativeId): array
    {
        $projectHospitalIdArr = RepresentativeProjectHospital::select(["projectHospitalId"])
            ->where("representativeId", $representativeId)
            ->pluck("projectHospitalId")
            ->toArray();

        if ($projectHospitalIdArr) {
            $projectIdArr = ProjectHospital::select(["projectId"])
                ->whereIn("projectHospitalId", $projectHospitalIdArr)
                ->pluck("projectId")
                ->toArray();

            if ($projectIdArr) {
                return Project::select(["projectId as id", "projectName as name"])
                    ->whereIn("projectId", $projectIdArr)
                    ->get()
                    ->toArray();
            }
        }

        return [];
    }

    /**
     * 查询指定项目的负责人列表
     *
     * @param string $projectId
     * @return array
     */
    public function getLeadersByProjectId(string $projectId): array
    {
        $userIds = ProjectUser::select("userId")
            ->where("projectId", $projectId)
            ->pluck("userId")
            ->toArray();

        if ($userIds) {
            // TODO 此处存在 $userIds 只有一个元素的情况，whereIn 可优化
            return User::select(["userId as id", "userName as name"])
                ->where("disabled", User::DISABLED_NO)
                ->whereIn("userId", $userIds)
                ->get()
                ->toArray();
        }

        return [];
    }
}