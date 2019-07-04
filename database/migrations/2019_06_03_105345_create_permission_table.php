<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_permission', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')
                ->unsigned()
                ->default(0)
                ->index()
                ->comment('上级关系编号');
            $table->string('name',30)
                ->default("")
                ->comment("权限名字");
            $table->string("url",255)
                ->default("")
                ->comment("权限路由");
            $table->tinyInteger("active_state")
                ->default(1)
                ->comment("是否可用 1可用，0停用");
            $table->string('comment',255)
                ->default("")
                ->comment("权限备注");
            $table->timestamp("deleted_at")
                ->nullable();
            $table->timestamps();
        });

        Schema::create('system_role_permission',function (Blueprint $table){
            $table->increments('id');
            $table->integer('role_id')
                ->index()
                ->unsigned()
                ->default(0)
                ->comment("角色表主键");
            $table->integer('permission_id')
                ->unsigned()
                ->default(0)
                ->comment("权限表主键");
            $table->timestamp('deleted_at')
                ->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_permission');
    }
}
