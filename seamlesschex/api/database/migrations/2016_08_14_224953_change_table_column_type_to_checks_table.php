<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTableColumnTypeToChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::drop('check_details');
		Schema::drop('check_recurrents');
		Schema::drop('checks');
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
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
		
		Schema::create('check_details', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('company_id')->unsigned();
			$table->integer('check_id')->unsigned();
            $table->foreign('check_id')->references('id')->on('checks')->onDelete('cascade');
			$table->bigInteger('owner_id')->unsigned()->comments('id of users who is adding companies');
			$table->bigInteger('batch_id')->unsigned();
			$table->bigInteger('group_id')->unsigned();
			$table->text('email');
			$table->bigInteger('check_type')->unsigned()->comments('1=default enter check screen, 2=chekout_url, 3=bank_pay_auth_url(plaid) ');
			$table->tinyInteger('is_printed')->unsigned();
			$table->string('response_code');
			$table->tinyInteger('verify_before_save')->unsigned();
			$table->tinyInteger('is_fundconfirmation')->unsigned();
			$table->string('fundconfirmation_result');
			$table->string('item_reference_id');
			$table->string('check_return');
			$table->dateTime('return_entry_date');
            $table->ipAddress('user_ipaddress');
            $table->timestamps();
        });
		
		Schema::create('check_recurrents', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('company_id')->unsigned();
			$table->integer('check_id')->unsigned();
            $table->foreign('check_id')->references('id')->on('checks')->onDelete('cascade');
			$table->string('runs_every');
			$table->dateTime('last_run');
			$table->string('time');
			$table->string('weekday');
			$table->integer('day');
			$table->dateTime('date');
			$table->string('how_many');
			$table->integer('count_run');
			$table->dateTime('next_run');
			$table->tinyInteger('fetch_lock');
            $table->timestamps();
			
        });

    }
}
