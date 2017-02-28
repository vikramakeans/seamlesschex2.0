<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_batches', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->integer('company_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->bigInteger('printed_by');
			$table->dateTime('printed_timestamp');
			$table->dateTime('batch_timestamp');
			$table->string('file');
			$table->string('deposit_silip_file');
			$table->integer('no_of_checks');
			$table->string('seamlesscheck_file');
			$table->double('total_amount', 20, 8);
			$table->string('batch_type');
			$table->dateTime('deposited_date');
			$table->bigInteger('deposited_by');
			$table->text('notes');
			$table->tinyInteger('is_printed');
			$table->tinyInteger('is_diposited');
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
        Schema::drop('check_batches');
    }
}
