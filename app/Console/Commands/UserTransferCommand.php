<?php
/**
 * Created by PhpStorm.
 * @author wangjiwei
 */

namespace App\Console\Commands;


use App\Models\Dotnet\User;
use App\Models\System\SystemUser;
use Illuminate\Console\Command;

/**
 * 数据迁移
 *
 * @package App\Http\Controllers
 */
class UserTransferCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'user_transfer_command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Transfer the data in user table from elu to scrm';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 'start';
        User::chunk(200,function ($users){
            foreach ($users as $user){
                $activeState  = $user->Disabled?0:1;
                SystemUser::updateOrCreate(
                    ['elu_id'=>$user->UserId],
                [
                    'username'=>$user->LoginName,
                    'realname'=>$user->UserName,
                    'password'=>$user->Password,
                    'active_state'=>$activeState,
                ]);
                $this->info($user->UserId);

            }
        });
        //这里编写需要执行的动作
        $this->info('finished');
    }
}