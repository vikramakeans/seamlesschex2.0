<?php

use Illuminate\Database\Seeder;

class ChecksMessagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('check_messages')->insert(['field_label' => 'I agree', 'field_name' => 'agree', 'form_name' => 'REGISTER', 'message' => 'Please agree terms and conditions', 'type' => 'ERROR', 'position' => 1]);
        DB::table('check_messages')->insert(['field_label' => 'Business Name', 'field_name' => 'name', 'form_name' => 'REGISTER', 'message' => 'Please enter business name', 'type' => 'ERROR', 'position' => 2]);
        DB::table('check_messages')->insert(['field_label' => 'Contact Name', 'field_name' => 'cname', 'form_name' => 'REGISTER', 'message' => 'Please enter conatct name', 'type' => 'ERROR', 'position' => 3]);
        DB::table('check_messages')->insert(['field_label' => 'Street Address', 'field_name' => 'saddress', 'form_name' => 'REGISTER', 'message' => 'Please enter street address', 'type' => 'ERROR', 'position' => 4]);
        DB::table('check_messages')->insert(['field_label' => 'City', 'field_name' => 'city', 'form_name' => 'REGISTER', 'message' => 'Please enter city', 'type' => 'ERROR', 'position' => 5]);
        DB::table('check_messages')->insert(['field_label' => 'State', 'field_name' => 'state', 'form_name' => 'REGISTER', 'message' => 'Please enter state', 'type' => 'ERROR', 'position' => 6]);
        DB::table('check_messages')->insert(['field_label' => 'Zip', 'field_name' => 'zip', 'form_name' => 'REGISTER', 'message' => 'Please enter zip', 'type' => 'ERROR', 'position' => 7]);
        DB::table('check_messages')->insert(['field_label' => 'Industry Type', 'field_name' => 'business_type', 'form_name' => 'REGISTER', 'message' => 'Please enter industry type', 'type' => 'ERROR', 'position' => 8]);
        DB::table('check_messages')->insert(['field_label' => 'Busines Email', 'field_name' => 'email', 'form_name' => 'REGISTER', 'message' => 'Please enter valid email', 'type' => 'ERROR', 'position' => 9]);
        DB::table('check_messages')->insert(['field_label' => 'Password must be at least 8 digits', 'field_name' => 'password', 'form_name' => 'REGISTER', 'message' => 'Please enter valid password', 'type' => 'ERROR', 'position' => 10]);
        DB::table('check_messages')->insert(['field_label' => 'Phone Number* Required to Activate Account ', 'field_name' => 'phone', 'form_name' => 'REGISTER', 'message' => 'Please enter valid phone', 'type' => 'ERROR', 'position' => 11]);
        DB::table('check_messages')->insert(['field_label' => 'Plan', 'field_name' => 'plan', 'form_name' => 'REGISTER', 'message' => 'Please select plan', 'type' => 'ERROR', 'position' => 12]);
        DB::table('check_messages')->insert(['field_label' => 'Hidden', 'field_name' => 'amount', 'form_name' => 'REGISTER', 'message' => 'Amount is required', 'type' => 'ERROR', 'position' => 13]);
        DB::table('check_messages')->insert(['field_label' => 'Card Number', 'field_name' => 'number', 'form_name' => 'REGISTER', 'message' => 'Please enter card number', 'type' => 'ERROR', 'position' => 14]);
        DB::table('check_messages')->insert(['field_label' => 'MM', 'field_name' => 'exp_month', 'form_name' => 'REGISTER', 'message' => 'Please enter expire month', 'type' => 'ERROR', 'position' => 15]);
        DB::table('check_messages')->insert(['field_label' => 'YYYY', 'field_name' => 'exp_year', 'form_name' => 'REGISTER', 'message' => 'Please enter expire year', 'type' => 'ERROR', 'position' => 16]);
        DB::table('check_messages')->insert(['field_label' => 'CVC', 'field_name' => 'cvc', 'form_name' => 'REGISTER', 'message' => 'Please enter cvc', 'type' => 'ERROR', 'position' => 17]);
    }
}
