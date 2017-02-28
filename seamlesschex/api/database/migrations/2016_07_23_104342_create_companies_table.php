<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->bigInteger('owner_id')->unsigned()->comments('id of users who is adding companies');
            $table->string('company_name');
			$table->string('cname');
			$table->string('business_type');
            $table->string('company_email')->unique();
			$table->string('address');
			$table->string('city');
			$table->string('state');
			$table->string('zip');
			$table->string('bank_routing');
			$table->string('bank_name');
			$table->string('bank_account_no');
			$table->string('authorised_signer');
			$table->string('settings');
			$table->tinyInteger('use_payliance');
			$table->string('payliance_marchant_id');
			$table->string('payliance_password');
			$table->string('payliance_location_id');
			$table->tinyInteger('is_enable_api');
			$table->string('api_app_id');
			$table->string('api_app_secret');
			$table->string('api_limit');
			$table->string('api_processing_day');
			$table->string('api_query_count');
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
        Schema::drop('companies');
    }
}
