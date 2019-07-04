<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-12 09:26:36
 */

namespace App\Http\Controllers;

use App\Http\ResponseWrapper;
use App\Services\Dotnet\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Routing\Controller;

/**
 * 系统项目管理
 *
 * @package App\Http\Controllers
 */
class ProjectController extends Controller
{
    /**
     * 查询一个项目的负责人列表
     *
     * @param Request $request
     * @return array
     */
    public function getProjectLeaders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "project_id" => "required|string|uuid"
        ], [
            "project_id.*" => "请输入正确的项目编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $projectId = (string)$request->input("project_id");
        $leaders = ProjectService::newInstance()->getLeadersByProjectId($projectId);

        return ResponseWrapper::success(["leaders" => $leaders]);
    }

    /**
     * 查询我已参与的项目列表
     *
     * @param Request $request
     * @return array
     */
    public function getJoinedProjects(Request $request)
    {
        $repId = $request->attributes->get("representative_id");

        $projects = ProjectService::newInstance()->getProjectsByRepresentativeId($repId);

        return ResponseWrapper::success(["projects" => $projects]);
    }
}