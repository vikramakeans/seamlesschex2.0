<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class UserSubscriptionOther extends Model
{
    
	/*
		List all Multiple Subscription
		
	*/
	
	public function getMultipleSubscriptions()
	{
		try{
			
			$multiSubscription = DB::table('users')
				->join('companies', 'users.id', '=', 'companies.user_id')
				->join('user_subscription_others', 'users.id', '=', 'user_subscription_others.user_id')
				->select('users.sc_token', 'users.name', 'users.email',  'companies.id', 'companies.company_name',   'user_subscription_others.stripe_id',  'user_subscription_others.stripe_subscription',  'user_subscription_others.stripe_plan_type', 'user_subscription_others.subscription_ends_at')
				->get();
				
			$json_response = array(); 
			$subscription_array = array();
			foreach ($multiSubscription as $subscription) {
					
					$subscription_array['data'][] = array(
						'sc_token' => $subscription->sc_token,
						'name' => $subscription->name,
						'email' => $subscription->email,
						'company_name' => $subscription->company_name,
						'company_admin' => $subscription->id,
						'stripe_subscription' => $subscription->stripe_subscription,
						'stripe_plan_type' => $subscription->stripe_plan_type,
						'stripe_id' => $subscription->stripe_id,
						'subscription_ends_at' => $subscription->subscription_ends_at,
					);
			}
			array_push($json_response, $subscription_array);
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return $json_response;
		
		
	}
	/*
		Get Multiple Subscription
		@param $sc_token
	*/
	
	public function getSubscriptionsByCompany($sc_token)
	{
		try{
			
			$multiSubscription = DB::table('users')
				->join('companies', 'users.id', '=', 'companies.user_id')
				->join('user_subscription_others', 'users.id', '=', 'user_subscription_others.user_id')
				->where('users.sc_token', '=', $sc_token)
				->where('user_subscription_others.stripe_active', '=', 2)
				->select('users.sc_token',  'companies.mc_token', 'user_subscription_others.stripe_id',  'user_subscription_others.stripe_subscription',  'user_subscription_others.stripe_plan_type', 'user_subscription_others.amount', 'user_subscription_others.subscription_starts_at','user_subscription_others.subscription_ends_at')
				->get();
				
			$json_response = array(); 
			$subscription_array = array();
			foreach ($multiSubscription as $subscription) {
					
					$subscription_array['data'][] = array(
						'sc_token' => $subscription->sc_token,
						'company_admin' => $subscription->mc_token,
						'stripe_subscription' => $subscription->stripe_subscription,
						'stripe_plan_type' => $subscription->stripe_plan_type,
						'amount' => $subscription->amount,
						'stripe_id' => $subscription->stripe_id,
						'subscription_starts_at' => date("m/d/Y", strtotime($subscription->subscription_starts_at)),
						'subscription_ends_at' => date("m/d/Y", strtotime($subscription->subscription_ends_at)),
					);
			}
			array_push($json_response, $subscription_array);
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return $json_response;
		
		
	}
	
	/*
		Duplicate subscription check for Merchant
		@param $sc_token
	*/
	
	public function checkDuplicateSubscription($user_id, $plan_id)
	{
		try{
			
			$duplicate = UserSubscriptionOther::where('user_id', $user_id)->where('stripe_plan', $plan_id)->where('stripe_active', 2)->count();
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value'=>$errormsg );
		}
		return $duplicate;
		
		
	}
}
