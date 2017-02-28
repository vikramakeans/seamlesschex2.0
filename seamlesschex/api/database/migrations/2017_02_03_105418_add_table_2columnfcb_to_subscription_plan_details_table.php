<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTable2columnfcbToSubscriptionPlanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plan_details', function (Blueprint $table) {
            $table->string('fundconfirmation_no_check')->after('no_of_check');
            $table->string('bank_auth_link_no_check')->after('no_of_check');
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
            $table->dropColumn('fundconfirmation_no_check');
            $table->dropColumn('bank_auth_link_no_check');
        });
    }
}
