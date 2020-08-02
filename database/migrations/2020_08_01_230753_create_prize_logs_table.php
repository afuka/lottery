<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrizeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prize_logs', function (Blueprint $table) {
            $table->id();

            $table->integer('activity_id')->default(0); // 活动id，冗余
            $table->integer('group_id')->default(0); // 奖品组id，冗余
            $table->integer('prize_id')->default(0); // 奖品id
            $table->enum('source_type', ['', 'drive_reservation'])->default(''); // 抽奖资格来源, drive_reservation 预约试驾
            $table->integer('source_id')->default(0); // 资格来源id
            $table->string('mobile', 32)->default(''); // 用户手机号
            $table->string('code')->default(0); // 获奖券码
            $table->string('ip')->default('0.0.0.0'); // IP地址
            $table->enum('status', ['-1', '0', '1'])->default('1'); // 状态
            $table->json('ext_info')->nullable(); // 中奖留资信息

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
        Schema::dropIfExists('prize_logs');
    }
}
