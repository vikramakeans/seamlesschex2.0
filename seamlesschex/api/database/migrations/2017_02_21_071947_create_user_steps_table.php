<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserStepsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_steps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('business_type');
            $table->string('phone');
            $table->string('name');
            $table->string('cname');
            $table->string('status');
            $table->string('website');
            $table->string('saddress');
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('persion_id')->nullable();
            $table->string('ip_address')->nullable();
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
        Schema::drop('user_steps');
    }
}
