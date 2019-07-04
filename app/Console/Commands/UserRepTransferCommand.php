<?php
/**
 * Created by PhpStorm.
 * @author wangjiwei
 */

namespace App\Console\Commands;


use App\Models\Dotnet\User;
use App\Models\Dotnet\UserRepresentative;
use App\Models\System\SystemRep;
use App\Models\System\SystemUser;
use App\Models\System\SystemUserRep;
use Illuminate\Console\Command;

/**
 * 数据迁移
 *
 * @package App\Http\Controllers
 */
class UserRepTransferCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'user_rep_transfer_command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Transfer the data in user_representative table from elu to scrm';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        UserRepresentative::chunk(200,function ($userReps){
            $userInfo = SystemUser::get()->toArray();
            $userInfoArr = array_column($userInfo,'id','elu_id');
            $repInfo = SystemRep::get()->toArray();
            $repInfoArr = array_column($repInfo,'id','elu_rep_id');
            foreach ($userReps as $userRep){
                if(isset($userInfoArr[$userRep->UserId])&&isset($repInfoArr[$userRep->UserRepresentativeId])){
                    $userId = $userInfoArr[$userRep->UserId];
                    $repId  = $repInfoArr[$userRep->UserRepresentativeId];
                    $activeState  = $userRep->Disabled?0:1;
                    SystemUserRep::updateOrCreate(
                        ['user_id'=>$userId,'rep_id'=>$repId],
                        [
                            'active_state'=>$activeState,
                        ]);
                    $this->info( $userRep->UserId);
                }
            }
        });
        //这里编写需要执行的动作
        $this->info( 'finished');
    }
}