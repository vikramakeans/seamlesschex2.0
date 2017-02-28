<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckGiactResponseCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_giact_response_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->text('details');
            $table->tinyInteger('pass');
            $table->text('description');
            $table->string('type');
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
        Schema::drop('check_giact_response_codes');
    }
}
