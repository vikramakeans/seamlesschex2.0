<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableColumnToCompaniesUserDetailsLoginActivityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('ip_address')->after('role_id');
            $table->string('is_loggedin')->after('role_id');
			$table->text('permission_settings')->after('role_id')->nullable();
        });
		Schema::table('user_login_activities', function (Blueprint $table) {
             $table->string('ip_address')->after('last_login_at');
        });
		Schema::table('company_details', function (Blueprint $table) {
            $table->text('permissions')->after('status_id')->nullable();
        });
		
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn('ip_address');
            $table->dropColumn('is_loggedin');
            $table->dropColumn('permission_settings');
        });
		Schema::table('user_login_activities', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
		Schema::table('company_details', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
}
