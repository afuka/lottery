<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrizeTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prize_tickets', function (Blueprint $table) {
            $table->id();

            $table->integer('prize_id')->default(0); // 奖品id
            $table->string('ticket', 32)->default(''); // 兑换券码
            $table->enum('in_pool', ['0', '1'])->default('0'); // 状态
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
        Schema::dropIfExists('prize_tickets');
    }
}
