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
use App\Models\System\SystemUser;
use App\Models\System\SystemUserRole;
use App\Utils\FormatUtil;
use DB;

/**
 * 系统菜单业务
 * @package App\Services
 */
class SystemRoleService extends BaseService
{
    /**
     * 获取指定用户的校色信息
     * @package App\Services
     */
    public function getUserRoleList(int $userId)
    {
        $roleIdArr = SystemUserRole::where('user_id', $userId)
            ->pluck('role_id')
            ->toArray();
        $roleList = SystemRole::whereIn('id', $roleIdArr)
            ->get()->toArray();
        return $roleList;
    }

    /**
     * 获取所有角色
     * @package App\Services
     */
    public function getRoleList()
    {
        $listInfo = SystemRole::get()->toArray();
        return $listInfo;
    }

    /**
     * 增加单个角色
     * @package App\Services
     */
    public function addRole(SystemRole $systemRole): ?int
    {
        $roleInfo = SystemRole::where('name', $systemRole->name)
            ->first();
        if ($roleInfo) {
            return null;
        }
        $systemRoleId = SystemRole::insertGetId($systemRole->toArray());
        return $systemRoleId;
    }

    /**
     * 修改单个菜单
     * @package App\Services
     */
    public function editRole(SystemRole $systemRole)
    {
        if (!empty($systemRole->name)) {
            $roleInfo = SystemRole::where('name', $systemRole->name)
                ->where('id', '<>', $systemRole->id)
                ->first();
            if ($roleInfo) {
                return false;
            }
        }
        $systemRoleId = SystemRole::where('id', $systemRole->id)
            ->update($systemRole->toArray());
        return $systemRoleId;
    }

    /**
     * 删除单个角色
     * @package App\Services
     */
    public function delRole(int $id)
    {
        return SystemRole::where('id', $id)->delete();
    }

    /**
     * 为用户分配角色
     * @package App\Services
     */
    public function addUserRole(int $userId,array $roleIds)
    {
        $roleList = $this->getRoleList();
        $roleIdArr = array_column($roleList, 'id');
        $time = date("Y-m-d H:i:s");
        //开启事务
        DB::beginTransaction();
        try {
            SystemUserRole::where('user_id', $userId)
                ->whereNotIn('role_id', $roleIds)
                ->delete();
            foreach ($roleIds as $val) {
                if (in_array($val, $roleIdArr)) {
                    //更新或者创建
                    SystemUserRole::updateOrCreate(
                        ['user_id' =>$userId, 'role_id' => $val],
                        ['updated_at' => $time]
                    );
                }
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * 获取指定角色的详情
     * @package App\Services
     */
    public function getRoleById(int $id)
    {
        $roleInfo = SystemRole::find($id);
        if ($roleInfo) {
            return $roleInfo->toArray();
        } else {
            return false;
        }
    }
}