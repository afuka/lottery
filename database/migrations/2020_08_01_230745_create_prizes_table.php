<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prizes', function (Blueprint $table) {
            $table->id();

            $table->integer('group_id')->default(0); // 所属奖品组
            $table->string('name', 32); // 奖品名称
            $table->string('bz', 256)->default(''); // 奖品说明
            $table->enum('type', ['physical', 'coupon', 'virtual']); // 奖品类型，physical实物，coupon券，virtual虚拟奖
            $table->string('image', 128)->default(''); // 奖品图
            $table->integer('total')->default(0); // 奖品总数
            $table->integer('stock')->default(0); // 奖品已发放数
            $table->integer('probability')->default(0); // 中奖概率,中奖率:0-10000之间
            $table->enum('is_default', ['0', '1'])->default('0'); // 是否默认中奖，默认中奖的话会发送，并且默认中奖是不限量的
            $table->json('date_limit')->nullable(); // 中奖日期-数量限制
            $table->json('config')->nullable(); // 其他配置
            $table->integer('sort')->default(0); // 排序, 小的在前
            $table->enum('leave_info', ['0', '1'])->default('0'); // 是否需要留资
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
        Schema::dropIfExists('prizes');
    }
}
