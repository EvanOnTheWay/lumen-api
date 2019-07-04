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
use App\Models\System\SystemPermission;
use App\Services\SystemPermissionService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Validator;

/**
 * SCRM SYSTEM Permission控制器
 *
 * @package App\Http\Controllers\System
 */
class PermissionController extends Controller
{
    private $Service;


    public function __construct()
    {
        $this->service = SystemPermissionService::newInstance();
    }

    /**
     * 获取指定用户可操作的菜单
     *
     * @param Request $request
     * @return array
     */
    public function getPermissionList(Request $request)
    {
        $name = $request->get('name')??'';
        $permissionList = $this->service->getPermissionList($name);
        return ResponseScrmWrapper::success($permissionList);
    }

    /**
     * 添加权限信息
     *
     * @param Request $request
     * @return array
     */
    public function addPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'url' => 'required',
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $requestData = $request->all();
        $systemPermission = new SystemPermission();
        $systemPermission->name = $requestData['name']??'';
        $systemPermission->url = $requestData['url']??'';
        $systemPermission->comment = $requestData['comment']??'';

        $permissionList = $this->service->addPermission($systemPermission);
        return ResponseScrmWrapper::success($permissionList);
    }

    /**
     * 修改权限信息
     *
     * @param Request $request
     * @return array
     */
    public function editPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }

        $requestData = $request->all();
        $systemPermission = new SystemPermission();
        $systemPermission->id =  $requestData['id'];
        if(!empty($requestData['name'])){
            $systemPermission->name = $requestData['name'];
        }
        if(!empty($requestData['name'])){
            $systemPermission->url = $requestData['url'];
        }
        if(!empty($requestData['comment'])){
            $systemPermission->icon = $requestData['comment'];
        }
        $permissionList = $this->service->editPermission($systemPermission);
        if ($permissionList) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_UPDATE_ERROR);
        }
    }

    /**
     * 删除一个权限
     *
     * @param Request $request
     * @return array
     */
    public function delPermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $flag = $this->service->delPermission($id);
        if ($flag) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_DEL_ERROR);
        }
    }

    /**
     * 为单个角色分配权限
     * @param Request $request
     * @return array
     */
    public function addRolePermission(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'permission_id' => 'present',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $roleId = $request->get('role_id');
        $permissionIds = $request->get('permission_id');

        $menuList = $this->service->addRolePermission($roleId,$permissionIds);
        if ($menuList) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_ROLE_PERMISSION_ERROR);
        }
    }
}