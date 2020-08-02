<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32); // 活动名称
            $table->string('code', 32); // 活动代码
            $table->string('bz', 256)->nullable(); // 活动描述
            $table->dateTime('started')->nullable(); // 开始时间
            $table->dateTime('ended')->nullable(); // 结束时间
            $table->json('config')->nullable(); // 活动的配置信息
            $table->enum('status', ['-1', '0', '1'])->default('1'); // 状态
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
        Schema::dropIfExists('activities');
    }
}
