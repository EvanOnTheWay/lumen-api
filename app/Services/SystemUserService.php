<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-17 10:25:46
 */

namespace App\Services;

use App\Http\RedisKey;
use App\Models\System\SystemMenu;
use App\Models\System\SystemRep;
use App\Models\System\SystemRole;
use App\Models\System\SystemRoleMenu;
use App\Models\System\SystemUser;
use App\Models\System\SystemUserRep;
use App\Models\System\SystemUserRole;
use App\Utils\FormatUtil;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Redis;


/**
 * 系统菜单业务
 * @package App\Services
 */
class SystemUserService extends BaseService
{
    private $userTokenKey = "USER_TOKEN_STORE_KEY";

    /**
     * 获取用户列表
     * @package App\Services
     */
    public function getUserList(string $name)
    {
        $list = [];
        $userList = SystemUser::select(['id', 'elu_id', 'email', 'username', 'realname', 'dingtalk_uid', 'comment', 'active_state'])
            ->when($name, function ($query, $name) {
                return $query->where('realname', 'like', "%$name%");
            })
            ->get();
        foreach ($userList as $user) {
            $roleInfo = SystemUserRole::where('user_id', $user->id)->get();
            $roleList = [];
            foreach ($roleInfo as $role) {
                $roleList[] = SystemRole::where('id', $role->role_id)->first();
            }
            $user->role = $roleList;
            $repInfo = SystemUserRep::where('user_id', $user->id)->get();
            $repList = [];
            foreach ($repInfo as $rep) {
                $repList[] = SystemRep::where('id', $rep->rep_id)->first();
            }
            $user->rep = $repList;
            $list[] = $user->toArray();
        }
        return $list;
    }

    /**
     * 获取单个用户
     * @package App\Services
     */
    public function getUserById(int $id)
    {
        $user = SystemUser::select(['id', 'elu_id', 'email', 'username', 'realname', 'dingtalk_uid', 'comment', 'active_state'])
            ->where('deleted_at', null)
            ->find($id);

        if ($user) {
            $roleInfo = SystemUserRole::where('user_id', $user->id)->get();
            $roleList = [];
            foreach ($roleInfo as $role) {
                $roleList[] = SystemRole::where('id', $role->role_id)->first();
            }
            $user->role = $roleList;
            $repInfo = SystemUserRep::where('user_id', $user->id)->get();
            $repList = [];
            foreach ($repInfo as $rep) {
                $repList[] = SystemRep::where('id', $rep->rep_id)->first();
            }
            $user->rep = $repList;
            $user = $user->toArray();
        }
        return $user;
    }

    /**
     * 获取专员列表
     * @package App\Services
     */
    public function getRepList(string $name = '')
    {
        $repList = SystemRep::when($name, function ($query, $name) {
            return $query->where('name', 'like', "%$name%");
        })
            ->get()->toArray();
        return $repList;
    }

    /**
     * 获取指定用户的专员分配信息
     * @package App\Services
     */
    public function getUserRepList(int $userId)
    {
        $roleIdArr = SystemUserRep::where('user_id', $userId)
            ->pluck('rep_id')
            ->toArray();
        $repList = SystemRep::whereIn('id', $roleIdArr)
            ->where('active_state', SystemRep::ACTIVE_YSE)->get()->toArray();
        return $repList;
    }

    /**
     * 为用户分配专员
     * @package App\Services
     */
    public function addUserRep(int $userId,array $repIds)
    {
        $roleList = $this->getRepList();
        $roleIdArr = array_column($roleList, 'id');
        //开启事务
        DB::beginTransaction();
        try {
            SystemUserRep::where('user_id', $userId)
                ->whereNotIn('rep_id', $repIds)
                ->delete();
            foreach ($repIds as $val) {
                if (in_array($val, $roleIdArr)) {
                    //更新或者创建
                    SystemUserRep::updateOrCreate(
                        ['user_id' => $userId, 'rep_id' => $val]
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
     * 为用户分配专员
     * @package App\Services
     */
    public function changeUserRep(int $userId,int $id,string $token)
    {
        //判断rep_id 是否存在
        $repInfo  =  SystemUserRep::where('user_id',$userId)
            ->where('rep_id',$id)
            ->first();
        if($repInfo === null){
            return false;
        }
        $userTokenKey = RedisKey::USER_TOKEN_KEY.$token;
        $tokenInfo = json_decode(Redis::get($userTokenKey),true);
        if(!empty($tokenInfo)){
            $tokenInfo['rep_id'] = $id;
            $flag = Redis::setex($this->userTokenKey,3600*2,json_encode($tokenInfo,JSON_UNESCAPED_UNICODE));
            return $flag;
        }else{
            return false;
        }
    }
    /**
     * 用户推出登录
     * @package App\Services
     */
    public function loginOut(string $token)
    {
        $userTokenKey = RedisKey::USER_TOKEN_KEY.$token;
        return Redis::del($userTokenKey);
    }

}