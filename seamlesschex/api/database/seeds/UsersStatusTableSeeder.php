<?php

use Illuminate\Database\Seeder;

class UsersStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_statuses')->insert(['status' => 'trialing']);
        DB::table('user_statuses')->insert(['status' => 'active']);
        DB::table('user_statuses')->insert(['status' => 'past_due']);
        DB::table('user_statuses')->insert(['status' => 'canceled']);
        DB::table('user_statuses')->insert(['status' => 'unpaid']);
        DB::table('user_statuses')->insert(['status' => 'unknown']);
        DB::table('user_statuses')->insert(['status' => 'inactive']);
        DB::table('user_statuses')->insert(['status' => 'delete']);
        DB::table('user_statuses')->insert(['status' => 'pending']);
    }
}
