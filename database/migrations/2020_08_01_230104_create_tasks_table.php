<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            
            $table->integer('operator_id')->default(0); // 发起人id
            $table->string('type', 32)->nullable();         // 消息类型
            $table->string('name', 32)->nullable();         // 名称
            $table->string('memo', 128)->nullable();        // 描述
            $table->json('params')->nullable();             // 调用对应消费者时候需要的参数
            $table->text('result')->nullable();             // 执行描述
            // 任务状态:-1终止任务；0等待执行；1进行中；2已完成;3挂起
            $table->enum('status', ['-1', '0', '1', '2', '3'])->default('0');

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
        Schema::dropIfExists('tasks');
    }
}
