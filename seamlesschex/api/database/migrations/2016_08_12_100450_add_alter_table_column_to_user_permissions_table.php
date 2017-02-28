<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAlterTableColumnToUserPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->string('permission_name')->after('role_id');
        });
		Schema::table('user_permissions', function($table){
			$table->renameColumn('permission', 'permission_value');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropColumn('permission_name');
        });
		Schema::table('user_permissions', function($table){
			$table->renameColumn('permission_value', 'permission');
		});
    }
}
