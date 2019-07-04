<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 16:26:18
 */

namespace App\Http\Controllers\System;

use App\Http\ResponseScrmWrapper;
use App\Http\ResponseStatus;
use App\Http\ResponseWrapper;
use App\Models\System\SystemRep;
use App\Models\System\SystemRoleMenu;
use App\Models\System\SystemUser;
use App\Services\SystemMenuService;
use App\Services\SystemUserService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Validator;

/**
 * SCRM SYSTEM User控制器
 *
 * @package App\Http\Controllers\System
 */
class UserController extends Controller
{
    private $userService;


    public function __construct()
    {
        $this->userService = SystemUserService::newInstance();
    }

    /**
     * 获取指定用户可操作的菜单
     *
     * @param Request $request
     * @return array
     */
    public function getUserList(Request $request)
    {
        $name = $request->get('name')??'';
        $userList = $this->userService->getUserList($name);
        return ResponseScrmWrapper::success($userList);
    }

    /**
     * 获取专员列表
     *
     * @param Request $request
     * @return array
     */
    public function getRepList(Request $request)
    {
        $name = $request->get('name')??'';
        $repList = $this->userService->getRepList($name);
        return ResponseScrmWrapper::success($repList);
    }

    /**
     * 获取已分配的专员列表
     *
     * @param Request $request
     * @return array
     */
    public function getUserRepList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $repList = $this->userService->getUserRepList($id);
        return ResponseScrmWrapper::success($repList);
    }

    /**
     * 为用户分配专员
     *
     * @param Request $request
     * @return array
     */
    public function addUserRep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'rep_id' => 'present',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $userId = $request->get('user_id');
        $repIds = $request->get('rep_id');
        $flag = $this->userService->addUserRep($userId,$repIds);
        if ($flag) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_USER_REP_ERROR);
        }
    }

    /**
     * 获取单个用户详细信息
     *
     * @param Request $request
     * @return array
     */
    public function getUserById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $info = $this->userService->getUserById($id);
        if ($info) {
            return ResponseScrmWrapper::success($info);
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_USER_REP_ERROR);
        }
    }

    /**
     * 获取单个用户详细信息
     *
     * @param Request $request
     * @return array
     */
    public function getUserInfo(Request $request)
    {
        $id = $request->attributes->get('user_id');
        $info = $this->userService->getUserById($id);
        if ($info) {
            return ResponseScrmWrapper::success($info);
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_USER_REP_ERROR);
        }
    }

    /**
     * 获取单个用户详细信息
     *
     * @param Request $request
     * @return array
     */
    public function changeUserRep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $userId = $request->attributes->get('user_id');
        $token = (string)$request->header("access-token");
        $info = $this->userService->changeUserRep($userId,$id,$token);
        if ($info) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_CHANGE_REP_ERROR);
        }
    }

    /**
     * 用户退出登录
     *
     * @param Request $request
     * @return array
     */
    public function loginOut(Request $request)
    {
        $token = (string)$request->header("access-token");
        $info = $this->userService->loginOut($token);
        if ($info) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_CHANGE_REP_ERROR);
        }
    }
}