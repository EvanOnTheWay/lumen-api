<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserRepAndRep extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //增加用户加色关系表
        Schema::create('system_user_rep',function(Blueprint $table){
            $table->increments('id')
                ->comment('关系编号');
            $table->integer('user_id')
                ->unsigned()
                ->default(0)
                ->index()
                ->comment('用户主键ID');
            $table->integer('rep_id')
                ->unsigned()
                ->default(0)
                ->comment('代表表主键ID');
            $table->tinyInteger("active_state")
                ->unsigned()
                ->default(0)
                ->comment("可用状态 0:停用(默认) 1:启用");
            $table->timestamp('deleted_at')
                ->nullable();
            $table->timestamps();
        });
        //增加代表表
        Schema::create('system_representative', function (Blueprint $table) {
            $table->increments('id')
                ->comment('用户编号');

            $table->uuid('elu_rep_id')
                ->comment('elu用户编号');

            $table->string('name')
                ->default('')
                ->index()
                ->comment('代表名称');

            $table->string('comment')
                ->default('')
                ->comment('备注信息');

            $table->tinyInteger("active_state")
                ->unsigned()
                ->default(1)
                ->comment("可用状态 0:停用(默认) 1:启用");

            $table->timestamp('deleted_at')
                ->nullable();

            $table->timestamps();
        });

        DB::statement("ALTER TABLE `system_user_rep` comment '系统角色代表关系表'");
        DB::statement("ALTER TABLE `system_representative` comment '系统代表表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_user_rep');
        Schema::dropIfExists('system_representative');
    }
}
