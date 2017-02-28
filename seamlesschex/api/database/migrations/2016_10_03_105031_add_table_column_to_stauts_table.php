<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTableColumnToStautsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_statuses', function (Blueprint $table) {
			$table->unique('status')->nullable();
			$table->text('color')->after('status')->nullable();
            $table->string('status_name')->after('status')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_statuses', function (Blueprint $table) {
            $table->dropUnique('status');
            $table->dropColumn('status_name');
            $table->dropColumn('color');
        });
    }
}
