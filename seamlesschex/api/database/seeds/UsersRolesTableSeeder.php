<?php

use Illuminate\Database\Seeder;

class UsersRolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_roles')->insert(['role_name' => 'su-admin']);
        DB::table('user_roles')->insert(['role_name' => 'admin']);
        DB::table('user_roles')->insert(['role_name' => 'company-admin']);
        DB::table('user_roles')->insert(['role_name' => 'company-user']);
        DB::table('user_roles')->insert(['role_name' => 'company-admin-companies']);
    }
}
