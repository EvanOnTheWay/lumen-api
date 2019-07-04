<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 微信群发:消息模板表
 */
class CreateWechatMassMessageTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_mass_message_template', function (Blueprint $table) {
            $table->increments('id')
                ->comment("模板编号");

            $table->string("name", 30)
                ->default("")
                ->comment("模板名称");

            $table->string("content", 255)
                ->default("")
                ->comment("模板内容");

            $table->tinyInteger("state")
                ->unsigned()
                ->default(0)
                ->comment("可用状态 0:停用(默认) 1:启用");

            $table->timestamps();
        });

        Schema::create('wechat_mass_message_batch', function (Blueprint $table) {
            $table->increments('id')
                ->comment("批次编号");

            $table->uuid("representative_id")
                ->comment("专员编号")
                ->default("")
                ->index();

            $table->uuid("project_id")
                ->comment("项目编号")
                ->default("")
                ->index();

            $table->integer("template_id")
                ->comment("模板编号")
                ->unsigned()
                ->default(0)
                ->index();

            $table->uuid("creator_id")
                ->comment("创建人的用户编号")
                ->default("")
                ->index();

            $table->uuid("auditor_id")
                ->comment("审核人的用户编号")
                ->default("");

            $table->tinyInteger("audit_state")
                ->comment("审核状态 0:待提审 1:已提审 2:已批准 3:已驳回")
                ->unsigned()
                ->default(0);

            $table->string("audit_opinion", 50)
                ->comment("审核意见")
                ->default("");

            $table->timestamp("audited_at")
                ->comment("审核时间")
                ->nullable();

            $table->tinyInteger("execute_state")
                ->comment("执行状态 0:尚未执行 1:正在执行 2:执行成功 3:执行失败")
                ->unsigned()
                ->default(0);

            $table->timestamp("executed_at")
                ->comment("执行时间")
                ->nullable();

            $table->timestamps();
        });

        Schema::create('wechat_mass_message_task', function (Blueprint $table) {
            $table->increments('id')
                ->comment("消息编号");

            $table->integer("batch_id")
                ->comment("批次编号")
                ->unsigned()
                ->default(0)
                ->index();

            $table->uuid("doctor_id")
                ->comment("医生编号")
                ->default("")
                ->index();

            $table->integer("template_id")
                ->comment("模板编号")
                ->unsigned()
                ->default(0)
                ->index();

            $table->string("content")
                ->comment("消息内容 模板内容渲染结果")
                ->default("");

            $table->tinyInteger("execute_state")
                ->comment("执行状态 0:尚未执行 1:正在执行 2:执行成功 3:执行失败")
                ->default(0);

            $table->string("execute_comment")
                ->comment("执行备注")
                ->default("");

            $table->timestamp("executed_at")
                ->comment("执行时间")
                ->nullable();

            $table->timestamps();
        });

        DB::statement("ALTER TABLE `wechat_mass_message_template` comment'微信群发:消息模板表'");

        DB::statement("ALTER TABLE `wechat_mass_message_batch` comment'微信群发:批次表'");

        DB::statement("ALTER TABLE `wechat_mass_message_task` comment'微信群发:任务表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_mass_message_template');

        Schema::dropIfExists('wechat_mass_message_batch');

        Schema::dropIfExists('wechat_mass_message_task');
    }
}
