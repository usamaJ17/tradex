<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->bigInteger('wallet_id')->unsigned();
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->decimal('amount',19,8)->default(0);
            $table->decimal('btc',19,8)->default(0);
            $table->decimal('doller',19,8)->default(0);
            $table->tinyInteger('address_type');
            $table->string('address');
            $table->string('transaction_hash');
            $table->string('coin_type')->default('BTC');
            $table->string('receiver_wallet_id')->nullable();
            $table->string('confirmations')->nullable();
            $table->decimal('fees',29,18)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->longText('message')->nullable();
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
        Schema::dropIfExists('withdraw_histories');
    }
}
