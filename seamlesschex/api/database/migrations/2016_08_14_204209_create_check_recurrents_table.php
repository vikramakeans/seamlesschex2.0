<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckRecurrentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_recurrents', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('company_id')->unsigned();
			$table->integer('check_id')->unsigned();
            $table->foreign('check_id')->references('id')->on('checks')->onDelete('cascade');
			$table->string('runs_every');
			$table->dateTime('last_run');
			$table->string('time');
			$table->string('weekday');
			$table->integer('day');
			$table->dateTime('date');
			$table->string('how_many');
			$table->integer('count_run');
			$table->dateTime('next_run');
			$table->tinyInteger('fetch_lock');
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
        Schema::drop('check_recurrents');
    }
}
