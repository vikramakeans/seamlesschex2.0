<?php

namespace App;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Contracts\Billable as BillableContract;
use Illuminate\Database\Eloquent\Model;
use DB;

class UserSubscription extends Model implements BillableContract
{
    use Billable;
	//protected $dates = ['user_id', 'amount', 'stripe_transaction_id', 'stripe_active', 'stripe_id',  'stripe_subscription', 'stripe_plan',  'last_four', 'trial_starts_at',  'subscription_starts_at',  'created_at', 'updated_at', 'trial_ends_at', 'subscription_ends_at'];
	protected $fillable = [
        'user_id', 'amount', 'stripe_transaction_id', 'stripe_active', 'stripe_id',  'stripe_subscription', 'stripe_plan',  'last_four',
    ];
	protected $dates = ['trial_ends_at', 'subscription_ends_at'];
	
	
	/**
     * Get stripe_id from $sc_token.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserSubscription($sc_token)
    {
		try{
			
			$user_subscriptions = DB::table('users')
						->join('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
						->where('sc_token', '=', $sc_token)
						->select('user_subscriptions.id', 'user_subscriptions.stripe_id', 'user_subscriptions.stripe_plan', 'user_subscriptions.stripe_subscription', 'user_subscriptions.stripe_active', 'user_subscriptions.amount', 'user_subscriptions.trial_ends_at', 'user_subscriptions.subscription_ends_at')
						->first();
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $user_subscriptions;
    }
}
