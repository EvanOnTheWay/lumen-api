<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRbacTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_user', function (Blueprint $table) {
            $table->increments('id')
                ->comment('用户编号');

            $table->uuid('elu_id')
                ->comment('elu用户编号');

            $table->string('email')
                ->default('')
                ->index()
                ->comment('电子邮箱');

            $table->string('username')
                ->default('')
                ->index()
                ->comment('用户名称');
            $table->string('realname')
                ->default('')
                ->comment('用户真实姓名');
            $table->char('password', 40)
                ->default('')
                ->comment('登录密码');

            $table->string('dingtalk_uid')
                ->default('')
                ->index()
                ->comment('钉钉用户编号');

            $table->string('comment')
                ->default('')
                ->comment('备注信息');

            $table->tinyInteger("active_state")
                ->unsigned()
                ->default(0)
                ->comment("可用状态 0:停用(默认) 1:启用");

            $table->timestamp('deleted_at')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('system_role', function (Blueprint $table) {
            $table->increments('id')
                ->comment('角色编号');

            $table->string('name')
                ->default('')
                ->comment('角色名称');

            $table->string('comment')
                ->default('')
                ->comment('备注信息');

            $table->tinyInteger("active_state")
                ->unsigned()
                ->default(0)
                ->comment("可用状态 0:停用(默认) 1:启用");

            $table->timestamp('deleted_at')
                ->nullable();

            $table->timestamps();
        });
        //增加用户加色关系表
        Schema::create('system_user_role',function(Blueprint $table){
            $table->increments('id')
                ->comment('关系编号');
            $table->integer('user_id')
                ->unsigned()
                ->default(0)
                ->index()
                ->comment('用户表主键ID');
            $table->integer('role_id')
                ->unsigned()
                ->default(0)
                ->comment('角色表主键ID');
            $table->tinyInteger("active_state")
                ->unsigned()
                ->default(0)
                ->comment("可用状态 0:停用(默认) 1:启用");
            $table->timestamp('deleted_at')
                ->nullable();
            $table->timestamps();
        });
        Schema::create('system_menu', function (Blueprint $table) {
            $table->increments('id')
                ->comment('菜单编号');

            $table->integer('parent_id')
                ->unsigned()
                ->default(0)
                ->index()
                ->comment('上级关系编号');

            $table->string('title')
                ->default('')
                ->comment('菜单标题');

            $table->string('icon')
                ->default('')
                ->comment('菜单图标');

            $table->string('url')
                ->default('')
                ->comment('链接URL');
            $table->string('component_path')
                ->default('')
                ->comment('前端组建名称');
            $table->string('router_name')
                ->default('')
                ->comment('路由别名');
            $table->string('comment')
                ->default('')
                ->comment('备注信息');
            $table->integer('rank')
                ->unsigned()
                ->default(0)
                ->comment('排序权重 DESC');

            $table->tinyInteger("visible_state")
                ->unsigned()
                ->default(0)
                ->comment("可见状态 0:隐藏(默认) 1:可见");

            $table->timestamp('deleted_at')
                ->nullable();

            $table->timestamps();
        });

        Schema::create('system_role_menu', function (Blueprint $table) {
            $table->increments('id')
                ->comment('关系编号');

            $table->integer('role_id')
                ->unsigned()
                ->default(0)
                ->index()
                ->comment('角色编号');

            $table->integer('menu_id')
                ->unsigned()
                ->default(0)
                ->comment('菜单编号');

            $table->tinyInteger("active_state")
                ->unsigned()
                ->default(0)
                ->comment("可用状态 0:停用(默认) 1:启用");

            $table->timestamp('deleted_at')
                ->nullable();

            $table->timestamps();
        });


        DB::statement("ALTER TABLE `system_user` comment '系统用户表'");
        DB::statement("ALTER TABLE `system_role` comment '系统角色表'");
        DB::statement("ALTER TABLE `system_user_role` comment '系统用户角色关系表'");
        DB::statement("ALTER TABLE `system_menu` comment '系统菜单表'");
        DB::statement("ALTER TABLE `system_role_menu` comment '系统角色菜单关系表'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_user');
        Schema::dropIfExists('system_role');
        Schema::dropIfExists('system_menu');
        Schema::dropIfExists('system_role_menu');
        Schema::dropIfExists('system_user_role');

    }
}
