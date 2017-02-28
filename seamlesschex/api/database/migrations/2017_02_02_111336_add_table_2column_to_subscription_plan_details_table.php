<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTable2columnToSubscriptionPlanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plan_details', function (Blueprint $table) {
            $table->string('trial_period')->after('plan_name');
            $table->string('interval')->after('plan_name');
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
            $table->dropColumn('interval');
            $table->dropColumn('trial_period');
        });
    }
}
