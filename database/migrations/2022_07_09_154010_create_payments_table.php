<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('box_id')->unsigned()->nullable();
            $table->bigInteger('player_id')->unsigned()->nullable();
            $table->string('payment_id');
            $table->string('payment_status');
            $table->string('pay_address');
            $table->foreign('box_id')->references('id')->on('boxs')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('payments');
    }
}
