<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChnageColumnToallownullCheckCheckBankauthLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_bank_auth_links', function (Blueprint $table) {
			//$table->double('amount', 20, 8)->nullable()->change();
			$table->string('memo')->nullable()->change();
			$table->string('signture_enable')->nullable()->change();
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_bank_auth_links', function (Blueprint $table) {
			//$table->double('amount', 20, 8)->change();
			$table->string('memo')->change();
			$table->string('signture_enable')->change();
		});
    }
}
