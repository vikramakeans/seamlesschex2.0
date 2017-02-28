<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTableNameIssueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('checks_basic_fees', 'check_basic_fees');
        Schema::rename('checks_fees', 'check_fees');
        Schema::rename('checks_settings', 'check_settings');
        Schema::rename('companies_details', 'company_details');
        Schema::rename('subscriptions_plan_details', 'subscription_plan_details');
        Schema::rename('users_login_activities', 'user_login_activities');
        Schema::rename('users_mailchimp_info', 'user_mailchimp_infos');
        Schema::rename('users_notifications', 'user_notifications');
        Schema::rename('users_subscription', 'user_subscriptions');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('check_basic_fees');
        Schema::drop('check_fees');
        Schema::drop('check_settings');
        Schema::drop('company_details');
        Schema::drop('subscription_plan_details');
        Schema::drop('user_login_activities');
        Schema::drop('user_mailchimp_infos');
        Schema::drop('user_notifications');
        Schema::drop('user_subscriptions');
    }
}
