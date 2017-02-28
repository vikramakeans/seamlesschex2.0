<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUserDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
			$table->dateTime('last_invoice_at')->after('is_loggedin')->nullable();
			$table->dateTime('lastloggedin_at')->after('is_loggedin')->nullable();
			$table->string('stripe_plan')->after('is_loggedin')->nullable();
			$table->double('amount', 20, 8)->after('is_loggedin')->nullable();
			$table->string('stripe_id')->after('is_loggedin')->nullable();
        });
		
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn('last_invoice_at');
            $table->dropColumn('lastloggedin_at');
            $table->dropColumn('stripe_plan');
            $table->dropColumn('amount');
            $table->dropColumn('stripe_id');
        });
    }
}
