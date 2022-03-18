<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWalletPassbooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_passbooks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->nullable();
            $table->integer('provider_id')->nullable();
            $table->integer('amount');
            $table->enum('status', [
                    'CREDITED',
                    'DEBITED',
                    'PAID',
                    'UNPAID'
                ]);
            $table->string('via')->nullable();
            $table->string('request_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->longText('payment_log')->nullable();
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
        Schema::dropIfExists('wallet_passbooks');
    }
}
