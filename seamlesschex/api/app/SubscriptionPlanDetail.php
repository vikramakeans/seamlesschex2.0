<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class SubscriptionPlanDetail extends Model
{
    protected $fillable = array('id', 'plan_name', 'interval', 'trial_period', 'plan_name_in_stripe','amount','no_of_check', 'no_of_users', 'no_of_companies', 'settings');
	
	/*
		Get Multiple Subscription
		@param $sc_token
	*/
	
	public static function getPlanDetailsByStripePlanID($stripe_plan_id)
	{
		try{
			
			$planDetails = DB::table('subscription_plan_details')
				->where('subscription_plan_details.plan_name_in_stripe', '=', $stripe_plan_id)
				->select('subscription_plan_details.no_of_check',  'subscription_plan_details.bank_auth_link_no_check', 'subscription_plan_details.fundconfirmation_no_check',  'subscription_plan_details.no_of_users',  'subscription_plan_details.no_of_companies', 'subscription_plan_details.amount', 'subscription_plan_details.settings')
				->first();
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return $planDetails;
		
		
	}
}
