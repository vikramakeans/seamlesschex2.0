<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableColumnToSubscriptionPlanDetailsAndLoginActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plan_details', function (Blueprint $table) {
            $table->string('plan_name_in_stripe')->after('plan_name');
        });
		Schema::table('user_login_activities', function (Blueprint $table) {
            $table->longText('content')->after('company_id');
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
            $table->dropColumn('plan_name_in_stripe');
        });
		Schema::table('user_login_activities', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
}
