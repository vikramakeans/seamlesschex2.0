<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckCheckoutLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_checkout_links', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
			$table->integer('company_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('owner_id')->unsigned();
			$table->string('checkout_token');
			$table->text('transcation_fee');
			$table->string('memo');
			$table->string('fee_type')->comments('Basic fee=BF, Fund confirmation = FC');
			$table->string('signture_enable');
			$table->ipAddress('user_ipaddress');
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
        Schema::drop('check_checkout_links');
    }
}
