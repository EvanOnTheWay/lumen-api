<?php
/**
 * Created by PhpStorm.
 * @author wangjiwei
 */


namespace App\Console\Commands;

use App\Models\Dotnet\Representative;
use App\Models\Dotnet\User;
use App\Models\System\SystemRep;
use App\Models\System\SystemUser;
use Illuminate\Console\Command;

/**
 * 数据迁移
 *
 * @package App\Http\Controllers
 */
class RepTransferCommand extends Command
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'rep_transfer_command';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'Transfer the data in Representative table from elu to scrm';



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info( 'start');
        Representative::chunk(200,function ($reps){
            foreach ($reps as $rep){
                SystemRep::updateOrCreate(
                    ['elu_rep_id'=>$rep->RepresentativeId],
                [
                    'name'=>$rep->RepresentativeName,
                ]);
                $this->info( $rep->RepresentativeId);
            }
        });
        //这里编写需要执行的动作
        $this->info( 'finished');
    }
}