<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCashierColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users_subscription', function(Blueprint $table)
		{
			$table->tinyInteger('stripe_active')->default(0);
			$table->string('stripe_id')->nullable();
			$table->string('stripe_subscription')->nullable();
			$table->string('stripe_plan', 100)->nullable();
			$table->string('last_four', 4)->nullable();
			$table->timestamp('trial_starts_at')->nullable();
			$table->timestamp('trial_ends_at')->nullable();
			$table->timestamp('subscription_starts_at')->nullable();
			$table->timestamp('subscription_ends_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users_subscription', function(Blueprint $table)
		{
			$table->dropColumn(
				'stripe_active', 'stripe_id', 'stripe_subscription', 'stripe_plan', 'last_four', 'trial_starts_at', 'trial_ends_at', 'subscription_starts_at', 'subscription_ends_at'
			);
		});
	}

}
