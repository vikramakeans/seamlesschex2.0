<?php

use Illuminate\Database\Seeder;

class UserPermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_permissions')->insert(['role_id' => 1, 'sl_no' => 0, 'permission_label' => 'ALL Access', 'permission_type' => '', 'permission_name' => 'ALL', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 2, 'sl_no' => 0,'permission_label' => 'ALL Access', 'permission_type' => '', 'permission_value' => 'yes']);
		
		DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 1,'permission_label' => 'ADD CHECK', 'permission_type' => 'LINK', 'permission_name' => 'ADDCHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 1,'permission_label' => 'EDIT CHECK', 'permission_type' => 'BUTTON', 'permission_name' => 'EDITCHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 2,'permission_label' => 'DELETE CHECK', 'permission_type' => 'BUTTON', 'permission_name' => 'DELETECHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 2,'permission_label' => 'VIEW CHECK', 'permission_type' => 'LINK', 'permission_name' => 'VIEWCHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 3,'permission_label' => 'PRINT CHECK', 'permission_type' => 'BUTTON', 'permission_name' => 'PRINTCHECK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 3,'permission_label' => 'CHECKOUT LINK', 'permission_type' => 'LINK', 'permission_name' => 'CHECKOUTLINK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 4,'permission_label' => 'BANK AUTH LINK', 'permission_type' => 'LINK', 'permission_name' => 'BANKAUTHLINK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 5,'permission_label' => 'REPORT', 'permission_type' => 'LINK', 'permission_name' => 'REPORT', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 6,'permission_label' => 'COMPANY', 'permission_type' => 'LINK', 'permission_name' => 'COMPANY', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 7,'permission_label' => 'USER', 'permission_type' => 'LINK', 'permission_name' => 'USER', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 4,'permission_label' => 'USER ADD', 'permission_type' => 'BUTTON', 'permission_name' => 'USERADD', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 5,'permission_label' => 'USER EDIT', 'permission_type' => 'BUTTON', 'permission_name' => 'USEREDIT', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 3, 'sl_no' => 6,'permission_label' => 'USER DELETE', 'permission_type' => 'BUTTON', 'permission_name' => 'USERDELETE', 'permission_value' => 'yes']);
		
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 8,'permission_label' => 'ADD CHECK', 'permission_type' => 'LINK', 'permission_name' => 'ADDCHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 7,'permission_label' => 'EDIT CHECK', 'permission_type' => 'BUTTON', 'permission_name' => 'EDITCHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 8,'permission_label' => 'DELETE CHECK', 'permission_type' => 'BUTTON', 'permission_name' => 'DELETECHECK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 9,'permission_label' => 'VIEW CHECK', 'permission_type' => 'LINK', 'permission_name' => 'VIEWCHECK', 'permission_value' => 'yes']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 9,'permission_label' => 'PRINT CHECK', 'permission_type' => 'BUTTON', 'permission_name' => 'PRINTCHECK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 10,'permission_label' => 'CHECKOUT LINK', 'permission_type' => 'LINK', 'permission_name' => 'CHECKOUTLINK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 11,'permission_label' => 'BANK AUTH LINK', 'permission_type' => 'LINK', 'permission_name' => 'BANKAUTHLINK', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 12,'permission_label' => 'REPORT', 'permission_type' => 'LINK', 'permission_name' => 'REPORT', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 13,'permission_label' => 'COMPANY', 'permission_type' => 'LINK', 'permission_name' => 'COMPANY', 'permission_value' => 'no']);
		
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 14,'permission_label' => 'USER', 'permission_type' => 'LINK', 'permission_name' => 'USER', 'permission_value' => 'no']);
		DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 10,'permission_label' => 'USER ADD', 'permission_type' => 'BUTTON', 'permission_name' => 'USERADD', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 11,'permission_label' => 'USER EDIT', 'permission_type' => 'BUTTON', 'permission_name' => 'USEREDIT', 'permission_value' => 'no']);
        DB::table('user_permissions')->insert(['role_id' => 4, 'sl_no' => 12,'permission_label' => 'USER DELETE', 'permission_type' => 'BUTTON', 'permission_name' => 'USERDELETE', 'permission_value' => 'no']);
    }
}
