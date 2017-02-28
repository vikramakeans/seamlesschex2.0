<?php

use Illuminate\Database\Seeder;

class ChecksSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('check_settings')->insert(['settings_type' => 'EMAIL', 'settings_name' => 'NOTIFY_EMAIL_CHECK_PROCESSING', 'value' => 'ealbert@seamlesschex.com']);
        DB::table('check_settings')->insert(['settings_type' => 'EMAIL', 'settings_name' => 'NOTIFY_EMAIL_CHECK_OVERWRITTEN', 'value' => 'ealbert@seamlesschex.com']);
        DB::table('check_settings')->insert(['settings_type' => 'EMAIL', 'settings_name' => 'NOTIFY_USER_ADDED', 'value' => 'ealbert@seamlesschex.com']);
        DB::table('check_settings')->insert(['settings_type' => 'EMAIL', 'settings_name' => 'NOTIFY_BATCH_READY_PRINTING', 'value' => 'ealbert@seamlesschex.com']);
        DB::table('check_settings')->insert(['settings_type' => 'EMAIL', 'settings_name' => 'NOTIFY_INVOICE', 'value' => 'ealbert@seamlesschex.com']);
        DB::table('check_settings')->insert(['settings_type' => 'EMAIL', 'settings_name' => 'NOTIFY_COMMON', 'value' => 'ealbert@seamlesschex.com']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'companies_permission', 'value' => 'no']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'companies_permission_amount', 'value' => '0']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'payment_link_permission', 'value' => 'no']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'payment_link_permission_amount', 'value' => '49.99']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'signture_permission', 'value' => 'no']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'signture_permission_amount', 'value' => '0']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'pay_auth_permission', 'value' => 'no']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'pay_auth_permission_amount', 'value' => '0']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'no_of_check', 'value' => '3']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'no_of_users', 'value' => '1']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'total_fundconfirmation', 'value' => '0']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'total_payauth', 'value' => '0']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'check_process', 'value' => 'a:1:{s:26:"same_day_processing_cutoff";a:14:{s:8:"mon.time";s:8:"03:00 PM";s:12:"mon.timezone";s:3:"EST";s:8:"tue.time";s:8:"03:00 PM";s:12:"tue.timezone";s:3:"EST";s:8:"wed.time";s:8:"03:00 PM";s:12:"wed.timezone";s:3:"EST";s:8:"thu.time";s:8:"03:00 PM";s:12:"thu.timezone";s:3:"EST";s:8:"fri.time";s:8:"03:00 PM";s:12:"fri.timezone";s:3:"EST";s:8:"sat.time";s:8:"03:00 PM";s:12:"sat.timezone";s:3:"EST";s:8:"sun.time";s:8:"03:00 PM";s:12:"sun.timezone";s:3:"EST";}}']);
    }
}
