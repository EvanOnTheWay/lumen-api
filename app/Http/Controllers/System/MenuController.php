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
use App\Models\System\SystemMenu;
use App\Models\System\SystemRoleMenu;
use App\Services\SystemMenuService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;
use Validator;

/**
 * SCRM SYSTEM Menu控制器
 *
 * @package App\Http\Controllers\System
 */
class MenuController extends Controller
{

    private $menuService;

    public function __construct()
    {
        $this->menuService = SystemMenuService::newInstance();
    }

    /**
     * 获取指定用户可操作的菜单
     *
     * @param Request $request
     * @return array
     */
    public function getUserMenuList(Request $request)
    {
        $userId = $request->attributes->get('user_id');
        $listInfo = $this->menuService->getUserMenuList($userId);
        return ResponseScrmWrapper::success($listInfo);
    }

    /**
     * 获取全部菜单菜单
     *
     * @param Request $request
     * @return array
     */
    public function getMenuList(Request $request)
    {
        $listInfo = $this->menuService->getMenuList();
        return ResponseScrmWrapper::success($listInfo);
    }

    /**
     * 增加一个菜单
     *
     * @param Request $request
     * @return array
     */
    public function addMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required',
            'label' => 'required',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $requestData = $request->all();
        $systemMenu = new SystemMenu();
        $systemMenu->parent_id = $requestData['parent_id']??0;
        $systemMenu->title = $requestData['label']??'';
        $systemMenu->router_name = $requestData['name']??'';
        $systemMenu->icon = $requestData['icon']??'';

        $menuIdInfo = $this->menuService->addMenu($systemMenu);
        if ($menuIdInfo) {
            return ResponseScrmWrapper::success(['id'=>$menuIdInfo]);
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_REPEAT_URL);
        }
    }

    /**
     * 修改一个菜单
     *
     * @param Request $request
     * @return array
     */
    public function editMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $requestData = $request->all();
        $systemMenu = new SystemMenu();
        $systemMenu->id =  $requestData['id'];
        if(!empty($requestData['label'])){
            $systemMenu->title = $requestData['label'];
        }
        if(!empty($requestData['name'])){
            $systemMenu->router_name = $requestData['name'];
        }
        if(!empty($requestData['icon'])){
            $systemMenu->icon = $requestData['icon'];
        }
        $flag = $this->menuService->editMenu($systemMenu);
        if ($flag) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_UPDATE_ERROR);
        }
    }

    /**
     * 删除一个菜单
     *
     * @param Request $request
     * @return array
     */
    public function delMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $flag = $this->menuService->delMenu($id);
        if ($flag) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_DEL_ERROR);
        }
    }

    /**
     * 获取单个角色的所有菜单
     * @param Request $request
     * @return array
     */
    public function getRoleMenuList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $id = $request->get('id');
        $menuList = $this->menuService->getRoleMenuList($id);
        return ResponseScrmWrapper::success($menuList);

    }

    /**
     * 为单个角色分配菜单
     * @param Request $request
     * @return array
     */
    public function addRoleMenu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required',
            'menu_id' => 'present',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $roleId =  $request->get('role_id');
        $menuIds =  $request->get('menu_id');
        $menuList = $this->menuService->addRoleMenu($roleId,$menuIds);
        if ($menuList) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_ROLE_MENU_ERROR);
        }
    }

    /**
     * 更新所有菜单的排序
     * @param Request $request
     * @return array
     */
    public function updateMenuList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'list' => 'required',
        ]);
        if ($validator->fails()) {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_WRONG_PARAM, $validator->errors()->toArray());
        }
        $list = $request->get('list');
        $menuList = $this->menuService->updateMenuList($list);
        if ($menuList) {
            return ResponseScrmWrapper::success();
        } else {
            return ResponseScrmWrapper::failure(ResponseStatus::SCRM_ADD_ROLE_MENU_ERROR);
        }
    }


}