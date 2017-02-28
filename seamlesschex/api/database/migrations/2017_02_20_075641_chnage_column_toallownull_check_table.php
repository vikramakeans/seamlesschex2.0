<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChnageColumnToallownullCheckTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
			$table->string('company_email')->nullable()->change();
			$table->string('phone')->nullable()->change();
			//$table->bigInteger('owner_id')->unsigned()->nullable()->change();
			//$table->tinyInteger('use_payliance')->unsigned()->nullable()->change();
			//$table->tinyInteger('is_enable_api')->unsigned()->nullable()->change();
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
			$table->string('company_email')->change();
			$table->string('phone')->change();
			//$table->bigInteger('owner_id')->change();
			//$table->tinyInteger('use_payliance')->change();
			//$table->tinyInteger('is_enable_api')->change();
		});
    }
}
