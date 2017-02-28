<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies_details', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->integer('company_id')->unsigned();
			$table->integer('total_no_check');
			$table->integer('no_of_check_remaining');
			$table->integer('total_fundconfirmation');
			$table->integer('remaining_fundconfirmation');
			$table->integer('total_payauth');
			$table->integer('payauth_remaining');
			$table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
			$table->bigInteger('owner_id')->unsigned()->comments('id of users who is adding companies');
			$table->longText('token');
			$table->longText('checkout_token');
			$table->longText('pay_auth_token');
			$table->enum('companies_permission', array('yes', 'no'))->default('no')->nullable();
			$table->enum('payment_link_permission', array('yes', 'no'))->default('no')->nullable();
			$table->enum('signture_permission', array('yes', 'no'))->default('no')->nullable();
			$table->enum('pay_auth_permission', array('yes', 'no'))->default('no')->nullable();
			$table->integer('status_id')->unsigned()->comments('status id from users status table');
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
        Schema::drop('companies_details');
    }
}
