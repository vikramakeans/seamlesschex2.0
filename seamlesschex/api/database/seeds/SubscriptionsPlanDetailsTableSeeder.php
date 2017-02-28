<?php

use Illuminate\Database\Seeder;

class SubscriptionsPlanDetailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('subscriptions_plan_details')->insert(['plan_name' => 'starterPlan', 'amount' => 24.99, 'no_of_check' => 20, 'no_of_users' => 1]);
         DB::table('subscriptions_plan_details')->insert(['plan_name' => 'proPlan', 'amount' => 49.99, 'no_of_check' => 45, 'no_of_users' => 5]);
         DB::table('subscriptions_plan_details')->insert(['plan_name' => 'premiumPlan', 'amount' => 99.99, 'no_of_check' => 100, 'no_of_users' => 10]);
         DB::table('subscriptions_plan_details')->insert(['plan_name' => 'trialPeriod', 'amount' => 0, 'no_of_check' => 3, 'no_of_users' => 1]);
    }
}
