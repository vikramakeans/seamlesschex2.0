<?php

use Illuminate\Database\Seeder;
use App\User;
use App\UserSubscription;
class CustomSeeds4 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 5; $i < 504; $i++)
		{
			$date = date("Y-m-d H:i:s");
			$start_date = strtotime($date);
			$free_trial_end_time = strtotime("+14 day", $start_date);
			$trial_ends_at = date("Y-m-d H:i:s",$free_trial_end_time);
			if($i>  4){
				$amount = 24.99;
				$stripe_plan = 'sub_7sif2rsnekKxu6';
				$stripe_subscription = 'SeamlessChex Starter Plan';
			}
			if($i>  200){
				$amount = 49.99;
				$stripe_plan = 'sub_8wwQcmsJ8sv8Jy';
				$stripe_subscription = 'SeamlessChex Pro Plan';
			}
			if($i>  300){
				$amount = 99.99;
				$stripe_plan = 'sub_8x8mlJc6dHDmsA';
				$stripe_subscription = 'SeamlessChex Premium Plan';
			}
			
			$userSubscription = UserSubscription::create(array(
				'user_id' => $i,
				'amount' => $amount,
				'stripe_active' => 1,
				'stripe_id' => 'cus_stripefakeuserid'.$i,
				'stripe_plan' => $stripe_plan,
				'stripe_subscription' => $stripe_subscription,
				'trial_ends_at' => $trial_ends_at
			  ));
		}
    }
}
