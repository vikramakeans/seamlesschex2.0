<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_banks', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('routing');
            $table->longText('name');
            $table->longText('address');
            $table->longText('state_code');
            $table->longText('city');
            $table->longText('state');
            $table->longText('zip');
            $table->longText('phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('check_banks');
    }
}
