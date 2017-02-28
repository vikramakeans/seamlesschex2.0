<?php

use Illuminate\Database\Seeder;

class UserDetailTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_details')->insert(['user_id' => 1, 'status_id' => 1,'role_id' => 1,]);
        DB::table('user_details')->insert(['user_id' => 2, 'status_id' => 1,'role_id' => 1,]);
        DB::table('user_details')->insert(['user_id' => 3, 'status_id' => 1,'role_id' => 1,]);
    }
}
