<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableColumnToSubscriptionPlanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plan_details', function (Blueprint $table) {
            $table->string('settings')->after('no_of_users')->nullable();
			$table->string('no_of_companies')->after('no_of_users')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plan_details', function (Blueprint $table) {
            $table->dropColumn('settings');
            $table->dropColumn('no_of_companies');
        });
    }
}
