<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('province', 32); // 归属省
            $table->string('city', 32); // 市
            $table->string('code', 32); // 经销商代码
            $table->string('name', 64); // 名称
            $table->string('addr', 128)->default(''); // 地址
            $table->string('simplify', 128); // 简称
            $table->enum('type', ['', 'sales'])->default(''); // 经销商类型
            $table->string('tel', 32)->nullable();
            $table->string('supports', 128)->default(''); // 支持的限制，例如支持的车型、支持的品牌等
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
        Schema::dropIfExists('dealers');
    }
}
