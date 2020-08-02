<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriveReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drive_reservations', function (Blueprint $table) {
            $table->id();
            $table->integer('activity_id')->default(0); // 活动的id
            $table->string('source', 32)->default('default'); // 预约来源, 查重的时候，按照 activity_id,source,mobile 联合查重
            $table->string('mobile', 32); // 手机号
            $table->string('name', 64)->default(''); // 姓名
            $table->enum('gender', ['0', '1', '2'])->default('0'); // 性别，0 未知，1 男，2 女
            $table->string('car', 32); // 车型
            $table->string('province', 32); // 归属省
            $table->string('city', 32); // 市
            $table->string('dealer_code', 32); // 经销商代码
            $table->string('dealer', 64); // 经销商名称
            $table->string('media', 32)->default(''); // 媒体来源
            $table->string('ip')->default('0.0.0.0'); // IP地址
            $table->enum('crm_sync', ['0', '1', '2', '-1'])->default('0'); // 是否需要同步到crm，0不需要同步，1待同步，2，同步成功，-1 同步失败
            $table->string('ordertime', 32)->default(''); // 预约时间
            $table->string('buytime', 32)->default(''); // 预计购买时间
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
        Schema::dropIfExists('drive_reservations');
    }
}
