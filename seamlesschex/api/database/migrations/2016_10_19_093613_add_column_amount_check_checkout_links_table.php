<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAmountCheckCheckoutLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_checkout_links', function (Blueprint $table) {
			$table->double('amount', 20, 8)->after('owner_id')->nullable();
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
            $table->dropUnique('amount');
        });
    }
}
