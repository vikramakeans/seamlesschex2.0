<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChnageColumnToallownullCheckCheckoutLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::table('check_checkout_links', function (Blueprint $table) {
			$table->text('transcation_fee')->nullable()->change();
			$table->string('memo')->nullable()->change();
			$table->string('fee_type')->nullable()->change();
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
		Schema::table('check_checkout_links', function (Blueprint $table) {
			$table->text('transcation_fee')->change();
			$table->string('memo')->change();
			$table->string('fee_type')->change();
			$table->string('signture_enable')->change();
		});
    }
}
