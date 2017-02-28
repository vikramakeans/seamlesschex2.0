<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('company_id')->unsigned();
			$table->bigInteger('owner_id')->unsigned()->comments('id of users who is adding companies');
			$table->longText('name');
			$table->longText('to_name');
			$table->longText('address');
			$table->longText('city');
			$table->longText('state');
			$table->longText('zip');
			$table->longText('phone');
			$table->longText('memo');
			$table->longText('memo2');
			$table->longText('routing');
			$table->longText('checking_account_number');
			$table->longText('confirm_account_number');
			$table->longText('checknum');
			$table->double('amount', 20, 8);
			$table->dateTime('date');
			$table->dateTime('authorisation_date');
			$table->string('month');
			$table->integer('status_id')->unsigned();
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
        Schema::drop('checks');
    }
}
