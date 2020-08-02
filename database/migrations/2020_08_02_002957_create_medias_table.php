<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('medias', function (Blueprint $table) {
            $table->id();

            $table->string('name', 32); // 媒体位名称
            $table->string('code', 32)->uniqid(); // 媒体位代码
            $table->json('ext_info')->nullable(); // 配置
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
        Schema::dropIfExists('medias');
    }
}
