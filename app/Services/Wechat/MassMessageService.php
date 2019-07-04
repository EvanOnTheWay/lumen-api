<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-16 10:58:17
 */

namespace App\Services\Wechat;

use App\Models\Dotnet\Doctor;
use App\Models\Dotnet\DoctorRepresentative;
use App\Models\Dotnet\Project;
use App\Models\Dotnet\Representative;
use App\Models\Dotnet\User;
use App\Models\Dotnet\WechatFriend;
use App\Models\WechatMassMessage\Template;
use App\Services\BaseService;

/**
 * 微信群发: 批次
 *
 * @package App\Services\WechatMassMessage
 */
class MassMessageService extends BaseService
{
    /**
     * 格式化一个群发批次
     *
     * @param array $batch
     * @return array
     */
    public function formatBatch(array $batch)
    {
        $project = Project::select(["projectName"])
            ->where("projectId", $batch["project_id"])
            ->first();

        $template = Template::find($batch["template_id"]);

        $userIdNameMap = User::select(["userId", "userName"])
            ->whereIn("userId", [$batch["creator_id"], $batch["auditor_id"]])
            ->get()
            ->pluck("userName", "userId");

        $representative = Representative::select(["representativeName"])
            ->where("representativeId", $batch["representative_id"])
            ->first();

        $batch["project_name"] = $project->projectName;
        $batch["template_name"] = $template->name;
        $batch["creator_name"] = $userIdNameMap[$batch["creator_id"]];
        $batch["auditor_name"] = $userIdNameMap[$batch["auditor_id"]];
        $batch["representative_name"] = $representative->representativeName;

        return $batch;
    }

    /**
     * 格式化群发批次列表
     *
     * @param array $batches 批次数据
     * @return array
     */
    public function formatBatches(array $batches)
    {
        if (empty($batches)) {
            return $batches;
        }

        $creatorIds = array_column($batches, "creator_id");
        $auditorIds = array_column($batches, "auditor_id");
        $projectIds = array_column($batches, "project_id");
        $templateIds = array_column($batches, "template_id");
        $representativeIds = array_column($batches, "representative_id");

        $userIdNameMap = User::select(["userId", "userName"])
            ->whereIn("userId", array_merge($creatorIds, $auditorIds))
            ->pluck("userName", "userId");

        $projectIdNameMap = Project::select(["projectId", "projectName"])
            ->whereIn("projectId", $projectIds)
            ->pluck("projectName", "projectId");

        $templateIdNameMap = Template::select(["id", "name"])
            ->whereIn("id", $templateIds)
            ->pluck("name", "id");

        $representativeIdNameMap = Representative::select(["representativeId", "representativeName"])
            ->whereIn("representativeId", $representativeIds)
            ->pluck("representativeName", "representativeId");

        foreach ($batches as $index => $batch) {
            $batch["creator_name"] = $userIdNameMap[$batch["creator_id"]];
            $batch["auditor_name"] = $userIdNameMap[$batch["auditor_id"]];
            $batch["project_name"] = $projectIdNameMap[$batch["project_id"]];
            $batch["template_name"] = $templateIdNameMap[$batch["template_id"]];
            $batch["representative_name"] = $representativeIdNameMap[$batch["representative_id"]];

            $batches[$index] = $batch;
        }

        unset($userIdNameMap, $projectIdNameMap, $templateIdNameMap, $representativeIdNameMap);

        return $batches;
    }

    /**
     * 格式化任务列表
     *
     * @param array $tasks
     * @return array
     */
    public function formatTasks(array $tasks)
    {
        $doctorIds = array_column($tasks, "doctor_id");

        $doctors = Doctor::select(["doctorId", "doctorNo", "doctorName"])
            ->whereIn("doctorId", $doctorIds)
            ->get()
            ->toArray();
        $reindex = array_column($doctors, null, "doctorId");

        foreach ($tasks as $index => $task) {
            $doctor = $reindex[$task["doctor_id"]];

            $task["doctor_no"] = $doctor["doctorNo"];
            $task["doctor_name"] = $doctor["doctorName"];

            $tasks[$index] = $task;
        }

        unset($doctorIds, $doctors, $reindex);

        return $tasks;
    }


    /**
     * 渲染消息模板
     * @param string $content   模板内容
     * @param array $args       模板变量
     * @return string
     */
    public function renderTemplate(string $content, array $args = [])
    {
        foreach ($args as $key => $val) {
            $pattern = '{' . $key . '}';
            if (strpos($content, $pattern) !== false) {
                $content = str_replace($pattern, $val, $content);
            }
        }

        return $content;
    }


    /**
     * 批量查询医生的微信备注名称
     *
     * 注意不是每一个医生编号都有对应的微信备注名称(未记录微信或好友关系有变动)
     *
     * @param array $doctorIds 医生编号数组
     * @return array
     */
    public function multiQueryDoctorWechatRemarkName(array $doctorIds)
    {
        // 通过上一步医生编号，查询出对应的医生专员关系编号信息
        $doctorRepresentatives = DoctorRepresentative::whereIn('doctorId', $doctorIds)
            ->get(['doctorId', 'doctorRepresentativeId'])
            ->toArray();

        // 构建"医生编号 => 医生专员关系编号"的映射数组
        $doctorRepresentativeMapping = array_column($doctorRepresentatives, 'doctorRepresentativeId', 'doctorId');

        // 查询出符合条件的微信账号(医生专员关系编号, 微信备注名称)
        $wechatFriendRecords = WechatFriend::select(['doctorRepresentativeId', 'conRemark'])
            ->whereIn('doctorRepresentativeId', array_values($doctorRepresentativeMapping))
            ->orderBy('modifiedDate', 'desc')
            ->get()
            ->toArray();

        // 构建"医生专员关系编号 => 微信备注名称"的映射数组
        $wechatFriendMapping = [];
        foreach ($wechatFriendRecords as $wechatFriendRecord) {
            // 如果医生修改 username，导致 doctorRepresentativeId 有可能存在多个匹配项，这里去重复
            if (!isset($wechatFriendMapping[$wechatFriendRecord['doctorRepresentativeId']])) {
                $wechatFriendMapping[$wechatFriendRecord['doctorRepresentativeId']] =  $wechatFriendRecord['conRemark'];
            }
        }

        // 构建"医生编号 => 微信备注名称"的映射数组
        $doctorWechatMapping = [];
        foreach ($doctorRepresentativeMapping as $doctorId => $doctorRepresentativeId) {
            if (isset($wechatFriendMapping[$doctorRepresentativeId])) {
                $doctorWechatMapping[$doctorId] = $wechatFriendMapping[$doctorRepresentativeId];
            }
        }

        return $doctorWechatMapping;
    }
}