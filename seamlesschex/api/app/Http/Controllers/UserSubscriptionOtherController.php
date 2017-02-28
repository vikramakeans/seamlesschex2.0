<?php

namespace App\Http\Controllers;

use Validator;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\UserSubscription;
use App\UserSubscriptionOther as UserSubscriptionOther;
use App\CompanyDetail;
use App\CheckMessage as CheckMessage;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;
use Response;
use Illuminate\Database\Eloquent\Model;
use Config;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Token; 
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Charge as StripeCharge;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;


use PDF;


class UserSubscriptionOtherController extends Controller
{
    
	use UserController;
	use UserTrait;
	public function __construct(UserSubscriptionOther $userSubscriptionOther)
	{
		Stripe::setApiKey(Config::get('services.stripe.secret'));
		Stripe::setApiVersion("2014-08-20"); // use your Stripe API version
		$this->fundconfirmationPlanId = Config::get('services.multisubscription.fundconfirmationPlanId');
		$this->signturePlanId = Config::get('services.multisubscription.signturePlanId');
		$this->checkoutlinkPlanId = Config::get('services.multisubscription.checkoutlinkPlanId');
		$this->bankauthlinkPlanId = Config::get('services.multisubscription.bankauthlinkPlanId');
		$this->userSubscriptionOther = $userSubscriptionOther;
	}
	
	/*
		List Multiple Subscription
	*/
	
	public function listMultipleSubscriptions()
	{
		try{
			
			$response = $this->userSubscriptionOther->getMultipleSubscriptions();
			
			if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $response['value'], 401]);
			}
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		if(isset($response) && empty($response[0])){
			return response()->json(['success' => true, 'token' => false, 'message' => 'Record Not found']);
		}
		return response()->json($response);
		
	}
	
	
	/*
		List Multiple Subscription by merchant
	*/
	
	public function listMultipleSubscriptionsByCompany($sc_token)
	{
		try{
			
			$response = $this->userSubscriptionOther->getSubscriptionsByCompany($sc_token);
			//$response = $this->userSubscriptionOther->getSubscriptionsByCompany($sc_token);
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		if(empty($response[0])){
			return response()->json(['success' => true, 'token' => false, 'message' => 'No Subscriptions']);
		}
		return response()->json($response);
		
	}
	
	/**
     * Save to subscription_others
     *
    */
	public function saveSubscriptionOthers($user_id, $subscriptionType, $stripe_id, $subscription_id, $stripe_plan, $amount, $current_period_start, $current_period_end, $status){
			
		try{
			$this->userSubscriptionOther->user_id = $user_id;
			$this->userSubscriptionOther->stripe_plan_type = $subscriptionType;
			//$this->userSubscriptionOther->last_four = $last_four;
			$this->userSubscriptionOther->stripe_id = $stripe_id;
			$this->userSubscriptionOther->stripe_subscription = $subscription_id;
			$this->userSubscriptionOther->stripe_plan = $stripe_plan;
			$this->userSubscriptionOther->amount = $amount;
			$this->userSubscriptionOther->subscription_starts_at = $current_period_start;
			$this->userSubscriptionOther->subscription_ends_at = $current_period_end;
			$this->userSubscriptionOther->stripe_active = $status;
			$this->userSubscriptionOther->save();
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
	}
	/**
     * Add multiple subscription to company-admin
     *
     * @return Response
    */
    public function addAnotherSubscription(Request $request){
		try {
			
			$sc_token = $request->get('sc_token');
			$plan_id = $request->get('plan_type');
			$subscription_type = $request->get('subscription');
			
			$checkMessages = CheckMessage::where('form_name', 'SUBSCRIPTION')
										->where('type', 'ERROR')
										->orderBy('position', 'asc')
										->get();
			$validator = Validator::make($request->all(),
				array(
					'sc_token' => 'required',
					'plan_type' => 'required',
					'subscription' => 'required'
				)
			);
			if ($validator->fails())
			{

				$messages = $validator->messages();
				foreach ($checkMessages as $checkMessage) {
					$message = $checkMessage->message;
					$field_name = $checkMessage->field_name;
					if ($messages->has($field_name)) {
						return response()->json(['error' => $message], 401);
					}
				}
			}
			
			// get user_id from $sc_token
			$user_id = $this->getUserId($sc_token);
			if(isset($user_id['type']) && $user_id['type'] == 'error'){
				return Response::json(['error' => $user_id['value']], 401);
			}
			
			// Duplicate check for active subscription
			$isDuplicateSubscription = $this->userSubscriptionOther->checkDuplicateSubscription($user_id, $plan_id);
			if(isset($isDuplicateSubscription['type']) && $isDuplicateSubscription['type'] == 'error'){
				return Response::json(['error' => $isDuplicateSubscription['value']], 401);
			}
			if($isDuplicateSubscription > 0){
				return Response::json(['error' => 'This subscription has been already added'], 401);
			}
			
			// get company_id from sc_token
			$company_id = $this->getCompanyId($sc_token);
			if(isset($company_id['type']) && $company_id['type'] == 'error'){
				return Response::json(['error' => $company_id['value']], 401);
			}
			
			$userSubscriptionRes = $this->getUserSubscriptionByUserId($user_id);
			$checkoutLink = 'no';
			$bankAuthLink = 'no';
			$signture = 'no';
			$fundCofirmation = 'no';
			
			// Get the company permission by company_id
			$permissionSettingRes = $this->getCompanyPermissionByCompanyId($company_id);
			
			if(isset($permissionSettingRes['type']) && $permissionSettingRes['type'] == 'error'){
				return Response::json(['error' => $permissionSettingRes['value']], 401);
			}
			if(isset($permissionSettingRes['type']) && $permissionSettingRes['type'] == 'success'){
				$permissionSettings = $permissionSettingRes['value'];
			}
			
			$stripe_id = '';
			//print_r($userSubscriptionRes);
			if(isset($userSubscriptionRes['type']) && $userSubscriptionRes['type'] == 'error'){
				return response()->json(['error' => $userSubscriptionRes['value']], 401);
			}
			
			if(isset($userSubscriptionRes['type']) && $userSubscriptionRes['type'] == 'success'){
				$resValue = $userSubscriptionRes['value'];
				$stripe_id = $resValue->stripe_id;
			}
			
			// Multiple Subscription
			if($stripe_id != ''){
				
					$subscriptionMultiRes = $this->multipleSubscriptions($stripe_id, $plan_id);
					
					if(isset($subscriptionMultiRes['type']) && $subscriptionMultiRes['type'] == 'error'){
						return response()->json(['error' => $subscriptionMultiRes['value']], 401);
					}
					
					$subscriptionMulti = $subscriptionMultiRes;
					if(isset($subscription_type) && $subscription_type == 'FUNDCONFIRMATION'){
						$permissionSettings['FUNDCONFIRMATION'] = 'yes';
						$fundCofirmation = $permissionSettings['FUNDCONFIRMATION'];
						$subscriptionType = 'FUNDCONFIRMATION';
					}
					if(isset($subscription_type) && $subscription_type == 'SIGNTURE'){
						$permissionSettings['SIGNTURE'] = 'yes';
						$signture = $permissionSettings['SIGNTURE'];
						$subscriptionType = 'SIGNTURE';
					}
					if(isset($subscription_type) && $subscription_type == 'CHECKOUTLINK'){
						$permissionSettings['CHECKOUTLINK'] = 'yes';
						$checkoutLink = $permissionSettings['CHECKOUTLINK'];
						$subscriptionType = 'CHECKOUTLINK';
					}
					if(isset($subscription_type) && $subscription_type == 'BANKAUTHLINK'){
						$permissionSettings['BANKAUTHLINK'] = 'yes';
						$bankAuthLink = $permissionSettings['BANKAUTHLINK'];
						$subscriptionType = 'BANKAUTHLINK';
					}
					$subscription_id  = $subscriptionMulti->id;
					$current_period_end  = $subscriptionMulti->current_period_end;
					$current_period_end = date("Y-m-d H:i", $current_period_end);
					$current_period_start  = $subscriptionMulti->current_period_start;
					$current_period_start = date("Y-m-d H:i", $current_period_start);
					$stripe_plan  = $subscriptionMulti->plan->id;
					$stripe_amount  =$subscriptionMulti->plan->amount;
					$amount = $stripe_amount/100;
					$stripe_status  = $subscriptionMulti->status;
					
					$statusRes = $this->getStatusId($stripe_status);
					$status = '';
					if(isset($statusRes['type']) && $statusRes['type'] == 'error'){
						return Response::json(['error' => $statusRes['value']], 401);
					}
					if(isset($statusRes['type']) && $statusRes['type'] == 'success'){
						$status = $statusRes['value'];
					}
					//StartDate and EndDate forSubscription
					if(isset($subscription_type) && $subscription_type == 'FUNDCONFIRMATION'){
						$permissionSettings['FUNDCONFIRMATION_START_DATE'] = $current_period_start;
						$permissionSettings['FUNDCONFIRMATION_END_DATE'] = $current_period_end;
					}
					if(isset($subscription_type) && $subscription_type == 'SIGNTURE'){
						$permissionSettings['SIGNTURE_START_DATE'] = $current_period_start;
						$permissionSettings['SIGNTURE_END_DATE'] = $current_period_end;
					}
					if(isset($subscription_type) && $subscription_type == 'CHECKOUTLINK'){
						$permissionSettings['CHECKOUTLINK_START_DATE'] = $current_period_start;
						$permissionSettings['CHECKOUTLINK_END_DATE'] = $current_period_end;
					}
					if(isset($subscription_type) && $subscription_type == 'BANKAUTHLINK'){
						$permissionSettings['BANKAUTHLINK_START_DATE'] = $current_period_start;
						$permissionSettings['BANKAUTHLINK_END_DATE'] = $current_period_end;
					}
					
					// Save to subscription_others
					$saveSubscriptionOthersRes = $this->saveSubscriptionOthers($user_id, $subscriptionType, $stripe_id, $subscription_id, $stripe_plan, $amount, $current_period_start, $current_period_end, $status);
					if(isset($saveSubscriptionOthersRes['type']) && $saveSubscriptionOthersRes['type'] == 'error'){
						return response()->json(['error' => $saveSubscriptionOthersRes['value']], 401);
					}
				
				// Update companies fundconfirmation, signture, checkoutlink, bankauthlinkPlanId
				$permissionSettings = serialize($permissionSettings);
				CompanyDetail::where('company_id', $company_id)
				->update(['payment_link_permission' => $checkoutLink, 'signture_permission' => $signture, 'pay_auth_permission' => $bankAuthLink, 'fundconfirmation_permission' => $fundCofirmation, 'permissions'=> $permissionSettings]);
				
			}
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => true, 'message' => $errorMessage], 401);
		}
		return response()->json(['success' => true, 'sc_token' => $sc_token, 'stripe_plan_type' => $subscription_type, 'subscription_starts_at' => date("m/d/Y", strtotime($current_period_start)), 'subscription_ends_at' => date("m/d/Y", strtotime($current_period_end))]);
	}
	
	/* 
	*	
	*	Multiple subscription in stripe 
	*
	*/
	
	public function multipleSubscriptions($stripe_id, $planId){
		try{
			$subscriptionMulti = Subscription::create(array(
				  "customer" => $stripe_id,
				  "plan" => $planId
				));
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return $subscriptionMulti;
	}
	
	/*
	* Cancel a subscription in stripe
	*
	*/
	public function cancelSubscriptionMutiple(Request $request){
		try{
			$mc_token = $request->get('company_admin');
			$stripe_subscription = $request->get('stripe_subscription');
			$subscription_type = $request->get('stripe_plan_type');
			$subscription = Subscription::retrieve($stripe_subscription);
			$subscription->cancel();
			$stripe_status  = $subscription->status;
			
			$company_id = $this->getCompanyIdFromMcToken($mc_token);
				
			$statusRes = $this->getStatusId($stripe_status);
			$status = '';
			if($statusRes['type'] == 'error'){
				return Response::json(['error' => $statusRes['value']], 401);
			}
			if($statusRes['type'] == 'success'){
				$status = $statusRes['value'];
			}
			
			// Update the status in subscription_others
			$subscription_ends_at = date("Y-m-d H:i");
			UserSubscriptionOther::where('stripe_subscription', $stripe_subscription)
			->update(['stripe_active'=> $status, 'subscription_ends_at' => $subscription_ends_at]);
			
			// Get the company permission by company_id
			$permissionSettingRes = $this->getCompanyPermissionByCompanyId($company_id);
			if($permissionSettingRes['type'] == 'error'){
				return Response::json(['error' => $permissionSettingRes['value']], 401);
			}
			
			if($permissionSettingRes['type'] == 'success'){
				$permissionSettings = $permissionSettingRes['value'];
			}
			if($subscription_type == 'FUNDCONFIRMATION'){
				$permissionSettings['FUNDCONFIRMATION'] = 'no';
				$field = 'fundconfirmation_permission';
				$fieldValue = 'no';
			}
			if($subscription_type == 'SIGNTURE'){
				$permissionSettings['SIGNTURE'] = 'no';
				$field = 'signture_permission';
				$fieldValue = 'no';
			}
			if($subscription_type == 'CHECKOUTLINK'){
				$permissionSettings['CHECKOUTLINK'] = 'no';
				$field = 'payment_link_permission';
				$fieldValue = 'no';
			}
			if($subscription_type == 'BANKAUTHLINK'){
				$permissionSettings['BANKAUTHLINK'] = 'no';
				$field = 'pay_auth_permission';
				$fieldValue = 'no';
			}
			
			$permissionSettings = serialize($permissionSettings);
			CompanyDetail::where('company_id', $company_id)
			->update([ "$field" => "$fieldValue" , "permissions" => $permissionSettings]);
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return response()->json(['success' => true , 'token' => true]);
	}
	
	
	
	
	/*test*/
	function test(){
		/*$subscription = Subscription::retrieve('sub_9FQ1ZCBu0SQdlt');
		echo '<pre>';
		print_r($subscription);
		echo $stripe_status  = $subscription->status;*/
		$company_id = 501;
		$subscription_type = 'FUNDCONFIRMATION';
		// Get the company permission by company_id
			$permissionSettingRes = $this->getCompanyPermissionByCompanyId($company_id);
			if($permissionSettingRes['type'] == 'error'){
				return Response::json(['error' => $permissionSettingRes['value']], 401);
			}
			
			if($permissionSettingRes['type'] == 'success'){
				$permissionSettings = $permissionSettingRes['value'];
			}
			if($subscription_type == 'FUNDCONFIRMATION'){
				$permissionSettings['FUNDCONFIRMATION'] = 'no';
				$field = 'fundconfirmation_permission';
				$fieldValue = 'no';
			}
			if($subscription_type == 'SIGNTURE'){
				$permissionSettings['SIGNTURE'] = 'no';
				$field = 'signture_permission';
				$fieldValue = 'no';
			}
			if($subscription_type == 'CHECKOUTLINK'){
				$permissionSettings['CHECKOUTLINK'] = 'no';
				$field = 'payment_link_permission';
				$fieldValue = 'no';
			}
			if($subscription_type == 'BANKAUTHLINK'){
				$permissionSettings['BANKAUTHLINK'] = 'no';
				$field = 'pay_auth_permission';
				$fieldValue = 'no';
			}
			
			$permissionSettings = serialize($permissionSettings);
			CompanyDetail::where('company_id', $company_id)
			->update([ "$field" => "$fieldValue" , "permissions" => $permissionSettings]);
	}


}
