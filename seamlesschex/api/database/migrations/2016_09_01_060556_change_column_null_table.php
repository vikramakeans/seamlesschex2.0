<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnNullTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_name')->nullable()->change();
            $table->string('cname')->nullable()->change();
            $table->string('business_type')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('state')->nullable()->change();
            $table->string('zip')->nullable()->change();
            $table->string('bank_routing')->nullable()->change();
            $table->string('bank_name')->nullable()->change();
            $table->string('bank_account_no')->nullable()->change();
            $table->string('authorised_signer')->nullable()->change();
            $table->string('settings')->nullable()->change();
			$table->string('payliance_marchant_id')->nullable()->change();
			$table->string('payliance_password')->nullable()->change();
			$table->string('payliance_location_id')->nullable()->change();
			$table->string('api_app_id')->nullable()->change();
			$table->string('api_app_secret')->nullable()->change();
			$table->string('api_limit')->nullable()->change();
			$table->string('api_processing_day')->nullable()->change();
			$table->string('api_query_count')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::table('companies', function (Blueprint $table) {
            $table->string('company_name')->change();
            $table->string('cname')->change();
            $table->string('business_type')->change();
            $table->string('address')->change();
            $table->string('city')->change();
            $table->string('state')->change();
            $table->string('zip')->change();
            $table->string('bank_routing')->change();
            $table->string('bank_name')->change();
            $table->string('bank_account_no')->change();
            $table->string('authorised_signer')->change();
            $table->string('settings')->change();
			$table->string('payliance_marchant_id')->change();
			$table->string('payliance_password')->change();
			$table->string('payliance_location_id')->change();
			$table->string('api_app_id')->change();
			$table->string('api_app_secret')->change();
			$table->string('api_limit')->change();
			$table->string('api_processing_day')->change();
			$table->string('api_query_count')->change();
        });
    }
}
