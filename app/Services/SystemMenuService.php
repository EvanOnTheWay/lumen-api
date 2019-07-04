<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:25:46
 */

namespace App\Services;

use App\Models\System\SystemMenu;
use App\Models\System\SystemRole;
use App\Models\System\SystemRoleMenu;
use App\Models\System\SystemUser;
use App\Models\System\SystemUserRole;
use App\Utils\FormatUtil;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;


/**
 * 系统菜单业务
 * @package App\Services
 */
class SystemMenuService extends BaseService
{
    private $menuEditKey = 'MENU_MODIFY_KEY';
    private $menuListRedisKey = 'MENU_LIST_REDIS_KEY';
    private $menuListSetTimeKey = "MENU_LIST_SET_TIME_KEY";

    /**
     * 获取为用户分配的所有可操作菜单
     * @package App\Services
     */
    public function getUserMenuList(int $userId)
    {
//        $time = time();
//        $userMenuListKey = $this->menuListRedisKey . $userId;
//        $userMenuListSetTimeKey = $this->menuListSetTimeKey . $userId;
//        //系统修改菜单的时间
//        $menuEditTime = Redis::get($this->menuEditKey) ?? 0;
//        //用户菜单存入缓存的时间
//        $menuSetTime = Redis::get($userMenuListSetTimeKey) ?? 0;
//
//        if (Redis::exists($userMenuListKey) && ($menuSetTime > $menuEditTime)) {
//            $menuListSort = json_decode(Redis::get($userMenuListKey));
//        } else {
        $roleIdArr = SystemUserRole::where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();
        //获取所有menu_id
        $menuIdArr = SystemRoleMenu::whereIn('role_id', $roleIdArr)
            ->pluck('menu_id')
            ->toArray();
        $allMenuList = $this->getAllMenu();
        $menuList = [];
        foreach ($menuIdArr as $menuId) {
            $this->getMenuListTree($allMenuList, $menuId, $menuList);
        }
        $menuListSort = FormatUtil::generateTree($menuList);
//            Redis::setex($userMenuListKey, 24 * 3600 * 30, json_encode($menuListSort, JSON_UNESCAPED_UNICODE));
//            Redis::setex($userMenuListSetTimeKey, 24 * 3600 * 30, $time);
//        }
        return $menuListSort;
    }

    /**
     * 获取所有菜单
     * @package App\Services
     */
    public function getMenuList()
    {
        $menuList = [];
        $listInfo = SystemMenu::select(['id', 'router_name AS name', 'parent_id', 'title as label', 'icon'])
            ->get()->toArray();
        if ($listInfo) {
            foreach ($listInfo as $key => $val) {
                $menuList[$val['id']] = $val;
            }
        }
        $menuListSort = FormatUtil::generateTree($menuList);
        return $menuListSort;
    }

    /**
     * 增加单个菜单
     * @package App\Services
     */
    public function addMenu(SystemMenu $systemMenu): ?int
    {
        $menuInfo = SystemMenu::where('router_name', $systemMenu->router_name)
            ->first();
        if ($menuInfo) {
            return null;
        }
        $systemMenuId = SystemMenu::insertGetId($systemMenu->toArray());
//        $this->updateEditMenuTime();
        return  $systemMenuId;
    }

    /**
     * 修改单个菜单
     * @package App\Services
     */
    public function editMenu(SystemMenu $systemMenu): ?int
    {
        //url 不能重复
        if ($systemMenu->router_name) {
            $menuInfo = SystemMenu::where('router_name', $systemMenu->router_name)
                ->where('id', '<>', $systemMenu->id)
                ->first();
            if ($menuInfo) {
                return null;
            }
        }

        $systemMenuId = SystemMenu::where('id',$systemMenu->id)->update($systemMenu->toArray());
//        $this->updateEditMenuTime();
        return $systemMenuId;
    }

    /**
     * 删除单个菜单
     * @package App\Services
     */
    public function delMenu(int $id)
    {
        $childMenuInfo = SystemMenu::where('parent_id', $id)
            ->first();
        if ($childMenuInfo !== null) {
            return false;
        }
        //        $this->updateEditMenuTime();
        return SystemMenu::where('id',$id)->delete();


    }

    /**
     * 获取单个角色的所有菜单
     * @package App\Services
     */
    public function getRoleMenuList(int $roleId)
    {
        $menuList = $this->getAllMenu();
        $parantIdInfo = array_column($menuList, 'parent_id');
        $roleMenuInfo = SystemRoleMenu::where('role_id', $roleId)
            ->get(['menu_id'])->toArray();
        //剔除父节点
        $roleMenuInfo = array_values(array_column($roleMenuInfo, 'menu_id'));
        foreach ($roleMenuInfo as $key => $val) {
            if (in_array($val, $parantIdInfo)) {
                unset($roleMenuInfo[$key]);
            }
        }
        return array_values($roleMenuInfo);
    }

    /**
     * 为角色分配菜单
     * @package App\Services
     */
    public function addRoleMenu(int $roleId,array $menuIds)
    {
        //判断role_id是否存在
        $roleInfo = SystemRole::where('id',$roleId)
            ->where('active_state', SystemRole::ACTIVE_YSE)
            ->exists();
        if (!$roleInfo) {
            return false;
        }
        $time = date("Y-m-d H:i:s");
        $menuList = $this->getAllMenu();
        //开启事务
        DB::beginTransaction();
        try {
            $flagUpdate = SystemRoleMenu::where('role_id', $roleId)
                ->whereNotIn('menu_id', $menuIds)
                ->delete();
            //递归所有menu_id 获取所有的父级菜单
            $menuListTree = [];
            foreach ($menuIds as $menuId) {
                $this->getMenuListTree($menuList, $menuId, $menuListTree);
            }
            foreach ($menuListTree as $key => $val) {
                //将子节点+父节点 菜单入库
                SystemRoleMenu::updateOrCreate(
                    ['role_id' => $roleId, 'menu_id' => $key],
                    ['updated_at' => $time]
                );
            }
            DB::commit();
//            $this->updateEditMenuTime();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 更新所有菜单排序&父子级关系
     * @package App\Services
     */
    public function updateMenuList(string $list)
    {
        $list = json_decode($list, true);
        $listSort = [];
        //多维数组分解成一维
        $listSort = $this->getListSort($list, $listSort);
        $listIdArr = array_column($listSort, 'id');
        //总的菜单数量
        $menuCount = SystemMenu::where('deleted_at', null)->count();
        $menuCountInput = SystemMenu::whereIn('id', $listIdArr)
            ->count();
        if ($menuCount != $menuCountInput) {
            return false;
        }
        //开启事务
        DB::beginTransaction();
        try {
            foreach ($listSort as $val) {
                SystemMenu::where('id', $val['id'])
                    ->update(['parent_id' => $val['parent_id'], 'rank' => $val['rank']]);
            }
            DB::commit();
//            $this->updateEditMenuTime();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 获取所有菜单数组
     * @package App\Services
     */
    private function getAllMenu()
    {
        $menuList = [];
        $allMenus = SystemMenu::select(['id', 'router_name AS name', 'parent_id', 'title as label', 'icon'])
            ->get()->toArray();
        if ($allMenus) {
            $menuList =  array_column($allMenus,null,'id');
        }
        return $menuList;
    }

    /**
     * 通过子menuid获取所有父级id
     * @package App\Services
     */
    private function getMenuListTree($allMenuList, $menuId, &$menuList)
    {
        if (isset($allMenuList[$menuId])) {
            $menuList[$menuId] = $allMenuList[$menuId];
            if ($allMenuList[$menuId]['parent_id'] != 0) {
                $this->getMenuListTree($allMenuList, $allMenuList[$menuId]['parent_id'], $menuList);
            } else {
                return $menuList;
            }
        } else {
            return $menuList;
        }
    }

    /**
     * 通过子menuid获取所有父级id
     * @package App\Services
     */
    private function getListSort($list, &$listSort, $parentId = 0)
    {
        foreach ($list as $key => $val) {
            $listSort[] = [
                'id' => $val['id'],
                'parent_id' => $parentId,
                'rank' => $key
            ];
            if (isset($val['children']) && !empty($val['children'])) {
                $this->getListSort($val['children'], $listSort, $val['id']);
            }
        }
        return $listSort;
    }

    /**
     * 更新redis菜单修改时间
     * @package App\Services
     */
    private function updateEditMenuTime()
    {
        $key = $this->menuEditKey;
        $time = time();
        Redis::setex($key, 24 * 3600 * 30, $time);
    }
}