<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrizeGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prize_groups', function (Blueprint $table) {
            $table->id();
            $table->integer('activity_id')->default(0); // 归属活动Id
            $table->string('name', 32); // 奖品组名称
            $table->string('bz', 256); // 奖品组描述
            // 用户中奖限制，no 不限制, once_per_group 每个奖品组一次, once_per_activity 每个活动一次
            $table->enum('user_limit_mode', ['no', 'once_per_group', 'once_per_activity'])->default('once_per_group');
            $table->json('config')->nullable(); // 奖品组配置
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
        Schema::dropIfExists('prize_groups');
    }
}
