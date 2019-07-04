<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:25:46
 */

namespace App\Services;

use App\Models\System\SystemMenu;
use App\Models\System\SystemPermission;
use App\Models\System\SystemRep;
use App\Models\System\SystemRole;
use App\Models\System\SystemRoleMenu;
use App\Models\System\SystemRolePermission;
use App\Models\System\SystemUser;
use App\Models\System\SystemUserRep;
use App\Models\System\SystemUserRole;
use App\Utils\FormatUtil;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * 系统菜单业务
 * @package App\Services
 */
class SystemPermissionService extends BaseService
{
    /**
     * 获取权限列表
     * @package App\Services
     */
    public function getPermissionList(string $name = '')
    {
        $listInfo = SystemPermission::select(['id', 'name', 'url', 'comment', 'active_state', 'parent_id'])
            ->when($name, function ($query, $name) {
                return $query->where('name', 'like', "%$name%");
            })
            ->get()->toArray();
        $permissionList = [];
        if ($listInfo) {
            $permissionList =  array_combine(array_column($listInfo,'id'),$listInfo);
        }
        $permissionListSort = FormatUtil::generateTree($permissionList);
        return $permissionListSort;
    }


    /**
     *添加单个权限
     * @package App\Services
     */
    public function addPermission(SystemPermission $systemPermission)
    {
        $permissionInfo = SystemPermission::where('name', $systemPermission->name)
            ->first();
        if ($permissionInfo) {
            return false;
        }
        $systemPermissionId = SystemPermission::insertGetId($systemPermission->toArray());
        return ['id' => $systemPermissionId];
    }


    /**
     * 修改单个权限
     * @package App\Services
     */
    public function editPermission(SystemPermission $systemPermission)
    {
        //url 不能重复
        if (!empty($data['name'])) {
            $permissionInfo = SystemPermission::where('name', $systemPermission->name)
                ->where('id', '<>', $systemPermission->id)
                ->first();
            if ($permissionInfo) {
                return false;
            }
        }
        $systemPermissionId = SystemPermission::where('id', $systemPermission->id)
            ->update($systemPermission->toArray());
        return $systemPermissionId;
    }

    /**
     * 删除单个权限
     * @package App\Services
     */
    public function delPermission(int $id)
    {
        $date = date('Y-m-d H:i:s');
        $childInfo = SystemPermission::where('parent_id', $id)
            ->first();
        if ($childInfo) {
            return false;
        }
        $delFlag = SystemPermission::where('id', $id)->delete();
        return $delFlag;
    }

    /**
     * 为用户分配角色
     * @package App\Services
     */
    public function addRolePermission(int $roleId,array $permissionIds)
    {

        //判断role_id是否存在
        $roleInfo = SystemRole::where('id', $roleId)
            ->where('active_state', SystemRole::ACTIVE_YSE)
            ->exists();
        if (!$roleInfo) {
            return false;
        }

        $permissionList = $this->getPermissionList();
        $permissionIdArr = array_column($permissionList, 'id');

        $time = date("Y-m-d H:i:s");
        //开启事务
        DB::beginTransaction();
        try {
            SystemRolePermission::where('role_id', $roleId)
                ->whereNotIn('permission_id', $permissionIds)->delete();
            //递归所有permission_id 获取所有的父级菜单
            $permissionListTree = [];
            foreach ($permissionIds as $permissionId) {
                $this->getPemissionListTree($permissionList, $permissionId, $permissionListTree);
            }

            foreach ($permissionListTree as $key => $val) {
                //将子节点+父节点 菜单入库
                SystemRolePermission::updateOrCreate(
                    ['role_id' => $roleId, 'permission_id' => $key],
                    ['updated_at' => $time]
                );
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }


    /**
     * 通过子permission_id获取所有父级id
     * @package App\Services
     */
    private function getPemissionListTree($allMenuList, $menuId, &$menuList)
    {
        if (isset($allMenuList[$menuId])) {
            $menuList[$menuId] = $allMenuList[$menuId];
            if ($allMenuList[$menuId]['parent_id'] != 0) {
                $this->getPemissionListTree($allMenuList, $allMenuList[$menuId]['parent_id'], $menuList);
            } else {
                return $menuList;
            }
        } else {
            return $menuList;
        }
    }

}