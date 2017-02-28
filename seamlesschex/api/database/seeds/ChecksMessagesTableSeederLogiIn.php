<?php

use Illuminate\Database\Seeder;

class ChecksMessagesTableSeederLogiIn extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //DB::table('check_messages')->insert(['field_label' => '', 'field_name' => '', 'form_name' => 'LOGIN', 'message' => 'Invalid Authentication Details!!!', 'type' => 'ERROR', 'position' => 1]);
        //DB::table('check_messages')->insert(['field_label' => '', 'field_name' => '', 'form_name' => 'LOGIN', 'message' => 'This account has been locked, please contact customer support', 'type' => 'ERROR', 'position' => 2]);
        DB::table('check_messages')->insert(['field_label' => 'Name', 'field_name' => 'name', 'form_name' => 'CREATE_COMPANY_USER', 'message' => 'Please Enter name', 'type' => 'ERROR', 'position' => 1]);
        DB::table('check_messages')->insert(['field_label' => 'Username', 'field_name' => 'username', 'form_name' => 'CREATE_COMPANY_USER', 'message' => 'Please Enter Username', 'type' => 'ERROR', 'position' => 2]);
        DB::table('check_messages')->insert(['field_label' => 'Email', 'field_name' => 'email', 'form_name' => 'CREATE_COMPANY_USER', 'message' => 'Please Enter Email', 'type' => 'ERROR', 'position' => 3]);
        DB::table('check_messages')->insert(['field_label' => 'Password', 'field_name' => 'password', 'form_name' => 'CREATE_COMPANY_USER', 'message' => 'Please Enter password', 'type' => 'ERROR', 'position' => 4]);
        DB::table('check_messages')->insert(['field_label' => 'Confirm Password', 'field_name' => 'cpassword', 'form_name' => 'CREATE_COMPANY_USER', 'message' => 'Please enter confirm password', 'type' => 'ERROR', 'position' => 5]);
        DB::table('check_messages')->insert(['field_label' => 'Company Admin', 'field_name' => 'company_admin', 'form_name' => 'CREATE_COMPANY_USER', 'message' => 'Please select company admin', 'type' => 'ERROR', 'position' => 6]);
    }
}
