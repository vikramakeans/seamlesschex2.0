<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSubscriptionOthersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_subscription_others', function (Blueprint $table) {
            $table->increments('id');
			$table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->tinyInteger('stripe_active')->default(0);
			$table->string('stripe_id')->nullable();
			$table->string('stripe_subscription')->nullable();
			$table->string('stripe_plan', 100)->nullable();
			$table->string('stripe_plan_type', 100)->nullable();
			$table->double('amount', 15, 8);
			$table->longText('last_four', 4)->nullable();
			$table->timestamp('trial_starts_at')->nullable();
			$table->timestamp('trial_ends_at')->nullable();
			$table->timestamp('subscription_starts_at')->nullable();
			$table->timestamp('subscription_ends_at')->nullable();			
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
        Schema::drop('user_subscription_others');
    }
}
