<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatisticsCronTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics_cron', function (Blueprint $table) {
            $table->increments('id');

            $table->string('task_name', 50)
                ->default('')
                ->comment('任务名称');

            $table->text('exec_sql')
                ->default('')
                ->comment('需要执行的sql');

            $table->string('operator_id', 50)
                ->comment('操作人id');

            $table->string('file_name', 255)
                ->default('')
                ->comment('文件名');

            $table->string('excel_path', 255)
                ->default('')
                ->comment('存储路径');

            $table->tinyInteger('status')
                ->unsigned()
                ->default(0)
                ->comment('任务状态 0->未执行  1->执行中 2->已执行 3->执行出错');

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
        Schema::dropIfExists('statistics_cron');
    }
}
