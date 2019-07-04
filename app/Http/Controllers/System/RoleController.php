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
use App\Models\System\SystemRole;
use App\Services\SystemMenuService;
use App\Services\SystemRoleService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Validator;

/**
 * SCRM SYSTEM Menu控制器
 *
 * @package App\Http\Controllers\System
 */
class RoleController extends Controller
{
    private $roleService;

    public function __construct()
    {
        $this->roleService = SystemRoleService::newInstance();
    }

    /**
     * 获取指定用户的角色信息
     *
     * @param Request $request
     * @return array
     */
    public function getUserRoleList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }

        $id = $request->get('id');
        $listInfo = $this->roleService->getUserRoleList($id);
        return ResponseScrmWrapper::success($listInfo);
    }

    /**
     * 获取全部角色
     *
     * @param Request $request
     * @return array
     */
    public function getRoleList(Request $request)
    {
        $listInfo = $this->roleService->getRoleList();
        return ResponseScrmWrapper::success($listInfo);
    }

    /**
     * 增加一个角色
     *
     * @param Request $request
     * @return array
     */
    public function addRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $requestData = $request->all();
        $systemRole = new SystemRole();
        $systemRole->name  = $requestData['name'];
        if(!empty($requestData['comment'])){
            $systemRole->comment  = $requestData['comment'];
        }
        $menuIdInfo = $this->roleService->addRole($systemRole);
        if ($menuIdInfo) {
            return ResponseScrmWrapper::success(['id'=>$menuIdInfo]);
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_REPEAT_ROLE);
        }
    }

    /**
     * 修改一个角色
     *
     * @param Request $request
     * @return array
     */
    public function editRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $requestData = $request->all();
        $systemRole = new SystemRole();
        $systemRole->id  = $requestData['id'];
        if(!empty($requestData['comment'])){
            $systemRole->comment  = $requestData['comment'];
        }
        if(!empty($requestData['name'])){
            $systemRole->name  = $requestData['name'];
        }
        if(isset($requestData['active_state'])){
            $systemRole->active_state  = $requestData['active_state'];
        }
        $flag = $this->roleService->editRole($systemRole);
        if ($flag) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_UPDATE_ERROR);
        }
    }

    /**
     * 删除一个角色
     *
     * @param Request $request
     * @return array
     */
    public function delRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $flag = $this->roleService->delRole($id);
        if ($flag) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_DEL_ERROR);
        }
    }

    /**
     * 为用户分配角色
     *
     * @param Request $request
     * @return array
     */
    public function addUserRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'role_id' => 'present',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $userId = $request->get('user_id');
        $roleIds = $request->get('role_id');
        $flag = $this->roleService->addUserRole($userId,$roleIds);
        if ($flag) {
            return ResponseScrmWrapper::success($flag);
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_USER_ROLE_ERROR);
        }
    }

    /**
     * 获取单个角色信息
     *
     * @param Request $request
     * @return array
     */
    public function getRoleById(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $info = $this->roleService->getRoleById($id);
        if ($info) {
            return ResponseScrmWrapper::success($info);
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ROLE_ID_ERROR);
        }
    }

}