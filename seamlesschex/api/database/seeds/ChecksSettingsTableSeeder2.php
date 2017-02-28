<?php

use Illuminate\Database\Seeder;

class ChecksSettingsTableSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'fundconfirmation_permission', 'value' => 'no']);
        DB::table('check_settings')->insert(['settings_type' => 'REGISTER', 'settings_name' => 'fundconfirmation_permission_amount', 'value' => '0']);
    }
}
