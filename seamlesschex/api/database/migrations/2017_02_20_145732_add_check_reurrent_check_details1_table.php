<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCheckReurrentCheckDetails1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_details', function (Blueprint $table) {
            $table->string('check_recurrent')->after('check_return')->nullable();
            $table->string('signature')->after('check_return')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_details', function (Blueprint $table) {
            $table->dropColumn('check_recurrent');
            $table->dropColumn('signature');
        });
    }
}
