<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('check_details');
    }
}
