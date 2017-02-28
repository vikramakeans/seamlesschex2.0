<?php

namespace App\Http\Controllers;

use Validator;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;
use Response;
use Illuminate\Database\Eloquent\Model;
use Config;
use Stripe\Stripe;
use Stripe\Token; 
use Stripe\Subscription as Subscription;
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use App\UserSubscription as UserSubscription;
use App\CheckMessage as CheckMessage;
use App\SubscriptionPlanDetail as SubscriptionPlanDetail;
use App\CompanyDetail;
use App\UserDetail;

class UserSubscriptionController extends Controller
{
	use UserController;
	use UserTrait;
	public function __construct()
	{
		Stripe::setApiKey(Config::get('services.stripe.secret'));
		Stripe::setApiVersion("2014-08-20"); // use your Stripe API version
	}
	
	/**
     * Return the stripe token details
     *
     * @return Response
    */
    public function getStripeToken(Request $request){
		try {
		$number = $request->get('number');
		$exp_month = $request->get('exp_month');
		$exp_year = $request->get('exp_year');
		$cvc = $request->get('cvc');
		
		// Get all the error messages from check_messages
		$checkMessages = CheckMessage::where('form_name', 'REGISTER')
									->where('type', 'ERROR')
									->orderBy('position', 'asc')
									->get();
									
		
		// Validate the input using laravel
				
		$validator = Validator::make($request->all(),
			array(
				'name' => 'required',
				'cname' => 'required',
				'saddress' => 'required',
				'city' => 'required',
				'state' => 'required',
				'zip' => 'required',
				'business_type' => 'required',
				'email' => 'required|email',
				'website' => 'required',
				'password' => 'required|min:8',
				'phone' => 'required|numeric',
				'plan' => 'required',
				'amount' => 'required',
				'number' => 'required',
				'exp_month' => 'required',
				'exp_year' => 'required',
				'cvc' => 'required',
				'user_token' => 'required',
				'agree' => 'required',
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
			//return response()->json(['error' => $messages], 401);
		}
		
		/*$stripeResponse = Token::create(array(
							  "card" => array(
								//"number" => "5105105105105100",
								"number" => "4242424242424242",
								"exp_month" => 6,
								"exp_year" => 2017,
								"cvc" => "314"
							  )
						));*/
		$stripeResponse = Token::create(array(
							  "card" => array(
								"number" => trim($number),
								"exp_month" => $exp_month,
								"exp_year" => $exp_year,
								"cvc" => $cvc
							  )
						));
		
		return response()->json($stripeResponse);
		}catch (StripeErrorCard $e) {
			//dd('Card was declined');
			$body = $e->getJsonBody();
			$err  = $body['error'];
			/*print('Status is:' . $e->getHttpStatus() . "\n");
			print('Type is:' . $err['type'] . "\n");
			print('Code is:' . $err['code'] . "\n");

			 // param is '' in this case
			print('Param is:' . $err['param'] . "\n");
			print('Message is:' . $err['message'] . "\n");*/
			return response()->json(["error"=> $err['message']]);
			
		}catch (StripeErrorInvalidRequest $e) {
			// Invalid parameters were supplied to Stripe's API
			return response()->json(['error'=>'Invalid Request']);
		} catch (StripeErrorAuthentication $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			return response()->json(["error"=>"Invalid API Key"]);
		} catch (StripeErrorApiConnection $e) {
			// Network communication with Stripe failed
			return response()->json(["error"=>"Network communication with Stripe failed"]);
		} catch (StripeErrorBase $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			return response()->json(["error"=>"Generic Error"]);
		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			return response()->json(["error"=>"Not Related to Stripe"]);
		}
		
	}
	
	/**
     * Active the subscription if it is trialing
     *
     * @return Response
    */
	
	public function activateSubscription(Request $request, $sc_token)
    {
	  try{
			$activateNowDetails = $request->get('activateNowDetails');
			
			// get user_id from $sc_token
			$user_id = $this->getUserId($sc_token);
			if(isset($user_id['type']) && $user_id['type'] == 'error'){
				return Response::json(['error' => $user_id['value']], 401);
			}
			// get company_id from sc_token
			$company_id = $this->getCompanyId($sc_token);
			if(isset($company_id['type']) && $company_id['type'] == 'error'){
				return Response::json(['error' => $company_id['value']], 401);
			}
			
			// get stripe_subscription (subscription id)
			$userSubscriptionRes = $this->getUserSubscriptionByUserId($user_id);
			$stripe_subscription = '';
			if(isset($userSubscriptionRes['type']) && $userSubscriptionRes['type'] == 'error'){
				return response()->json(['error' => $userSubscriptionRes['value']], 401);
			}
			
			if(isset($userSubscriptionRes['type']) && $userSubscriptionRes['type'] == 'success'){
				$resValue = $userSubscriptionRes['value'];
				$stripe_subscription = $resValue->stripe_subscription;
			}
			
			if(isset($stripe_subscription) && $activateNowDetails == true){
				$subscription = Subscription::retrieve($stripe_subscription);
				$subscription->trial_end = "now";
				$subscription->save();
			}
			//echo '<pre>';
			//print_r($subscription);
			
			$plan_id = $subscription->plan->id;
			//$plan_id = '29 Days Free Trial';
			$current_period_end  = $subscription->current_period_end;
			$current_period_end = date("Y-m-d H:i", $current_period_end);
			$current_period_start  = $subscription->current_period_start;
			$current_period_start = date("Y-m-d H:i", $current_period_start);
			$last_invoice_at  = $subscription->created;
			$last_invoice_at = date("Y-m-d H:i", $last_invoice_at);
			$planDetails = SubscriptionPlanDetail::getPlanDetailsByStripePlanID($plan_id);
			
			//print_r($planDetails);
			
			$no_of_check = isset($planDetails->no_of_check) ? $planDetails->no_of_check : 0;
			$fundconfirmation_no_check = isset($planDetails->fundconfirmation_no_check) ? $planDetails->fundconfirmation_no_check : 0;
			$bank_auth_link_no_check = isset($planDetails->bank_auth_link_no_check) ? $planDetails->bank_auth_link_no_check : 0;
			$no_of_users = isset($planDetails->no_of_users) ? $planDetails->no_of_users : 0;
			$no_of_companies = isset($planDetails->no_of_companies) ? $planDetails->no_of_companies : 0;
			
			$settings = '';
			if(isset($planDetails->settings) && $planDetails->settings != ''){
				$settings = unserialize($planDetails->settings);
			}
			
			$company = isset($settings['COMPANY']) ? $settings['COMPANY'] : 'no';
			$fundconfirmationp = isset($settings['FUNDCONFIRMATION']) ? $settings['FUNDCONFIRMATION'] : 'no';
			$basicVerification = isset($settings['BASICVERFICATIONS']) ? $settings['BASICVERFICATIONS'] : 'no';
			$bankAuthLink = isset($settings['BANKAUTHLINK']) ? $settings['BANKAUTHLINK'] : 'no';
			$checkoutLink = isset($settings['CHECKOUTLINK']) ? $settings['CHECKOUTLINK'] : 'no';
			$signature = isset($settings['SIGNTURE']) ? $settings['SIGNTURE'] : 'no';
			
			// Get the company permission by company_id
			$permissionSettingRes = $this->getCompanyPermissionByCompanyId($company_id);
			
			if(isset($permissionSettingRes['type']) && $permissionSettingRes['type'] == 'error'){
				return Response::json(['error' => $permissionSettingRes['value']], 401);
			}
			if(isset($permissionSettingRes['type']) && $permissionSettingRes['type'] == 'success'){
				$permissionSettings = $permissionSettingRes['value'];
			}
 													 													
			// No of check / no of user settings
			$permissionSettings['TOTALNOCHECK'] = $no_of_check;
			$permissionSettings['NOOFCHECKREMAINING'] = $no_of_check;
			
			$permissionSettings['COMPANY'] = $company;
			$permissionSettings['TOTAL_COMPANY'] = $no_of_companies;
			$permissionSettings['REMAINING_COMPANY'] = $no_of_companies;
			
			$permissionSettings['TOTAL_USER'] = $no_of_users;
			$permissionSettings['REMAINING_USER'] = $no_of_users;
			
			$permissionSettings['TOTALFUNDCONFIRMATION'] = $fundconfirmation_no_check;
			$permissionSettings['REMAININGFUNDCONFIRMATION'] = $fundconfirmation_no_check;
			
			$permissionSettings['TOTALPAYAUTH'] = $bank_auth_link_no_check;
			$permissionSettings['PAYAUTHREMAINING'] = $bank_auth_link_no_check;
			
			// setting company check permission
			$permissionSettings['FUNDCONFIRMATION'] = $fundconfirmationp;
			$fundCofirmation = $permissionSettings['FUNDCONFIRMATION'];
			
			$permissionSettings['SIGNTURE'] = $signature;
			$signature = $permissionSettings['SIGNTURE'];
		
			$permissionSettings['CHECKOUTLINK'] = $checkoutLink;
			$checkoutLink = $permissionSettings['CHECKOUTLINK'];
		
			$permissionSettings['BANKAUTHLINK'] = $bankAuthLink;
			$bankAuthLink = $permissionSettings['BANKAUTHLINK'];
			
			$permissionSettings['BASICVERFICATIONS'] = $basicVerification;
			$basicVerification = $permissionSettings['BASICVERFICATIONS'];
			
			$permissionSettings['BASICVERFICATIONS_START_DATE'] = $current_period_start;
			$permissionSettings['BASICVERFICATIONS_END_DATE'] = $current_period_end;
			
			// Update companies fundconfirmation, signture, checkoutlink, bankauthlinkPlanId
			$permissionSettings = serialize($permissionSettings);
			CompanyDetail::where('company_id', $company_id)
			->update(['payment_link_permission' => $checkoutLink, 'signture_permission' => $signature, 'pay_auth_permission' => $bankAuthLink, 'fundconfirmation_permission' => $fundCofirmation, 'permissions'=> $permissionSettings,'status_id' => 2]);
			// update user_details status_id = 2 (active)
			UserDetail::where('user_id', $user_id)
			->update(['status_id' => 2, 'last_invoice_at' => $last_invoice_at]);
			
			
			// update stripe status and date
			UserSubscription::where('user_id', $user_id)
			->update(['stripe_active' => 2, 'subscription_starts_at' => $current_period_start, 'subscription_ends_at' => $current_period_end,]);
			
			
			
	  }catch(Exception $e){
		   $code = $e->getCode();
		   $message = $e->getMessage();
		   $errorMessage = $message ." ".$code;
		   return response()->json(['error' => $errorMessage], 401);
	  }
	  return Response::json(['success' => true, 'token' => true]);
	 }
	 
	 /**
     * upgrade the subscription 
     *
     * @return Response
    */
	
	public function upgradeSubscription(Request $request, $sc_token)
    {
	  try{

			$updateNowDetails = $request->get('upgradeNowDetails');
			$stripe_plan_new = $request->get('stripe_plan_new');
			
			
			// get user_id from $sc_token
			$user_id = $this->getUserId($sc_token);
			if(isset($user_id['type']) && $user_id['type'] == 'error'){
				return Response::json(['error' => $user_id['value']], 401);
			}
			// get company_id from sc_token
			$company_id = $this->getCompanyId($sc_token);
			if(isset($company_id['type']) && $company_id['type'] == 'error'){
				return Response::json(['error' => $company_id['value']], 401);
			}
			
			
			// get stripe_subscription (subscription id)
			$userSubscriptionRes = $this->getUserSubscriptionByUserId($user_id);
			$stripe_subscription = '';
			$stripe_plan = '';
			$stripe_id = '';
			if(isset($userSubscriptionRes['type']) && $userSubscriptionRes['type'] == 'error'){
				return response()->json(['error' => $userSubscriptionRes['value']], 401);
			}
			
			if(isset($userSubscriptionRes['type']) && $userSubscriptionRes['type'] == 'success'){
				$resValue = $userSubscriptionRes['value'];
				$stripe_subscription = $resValue->stripe_subscription;
				$stripe_plan = $resValue->stripe_plan;
				$stripe_id = $resValue->stripe_id;
			}
			
			if($stripe_subscription != '' && $updateNowDetails == true){
				// cancel the subscription
				$subscription = Subscription::retrieve($stripe_subscription);
				$subscription->cancel();
				$plan_id = $stripe_plan_new;
				//if($stripe_plan_new == $stripe_plan){
					//$plan_id = $stripe_plan;
				//}
				
				// create the subscription again for exiting customer
				$subscriptionNew = Subscription::create(array(
				  "customer" => $stripe_id,
				  "plan" => $plan_id,
				  "trial_end" => "now",
				  "prorate" => false
				));
				//echo '<pre>';
				//print_r($subscriptionNew);
				$subscription_id = $subscriptionNew->id;
				$current_period_end  = $subscriptionNew->current_period_end;
				$current_period_end = date("Y-m-d H:i", $current_period_end);
				$current_period_start  = $subscriptionNew->current_period_start;
				$current_period_start = date("Y-m-d H:i", $current_period_start);
				
				$last_invoice_at  = $subscriptionNew->created;
				$last_invoice_at = date("Y-m-d H:i", $last_invoice_at);
				
				
				$amount_stripe  = $subscriptionNew->plan->amount;
				$amount = $amount_stripe/100;
				$planDetails = SubscriptionPlanDetail::getPlanDetailsByStripePlanID($plan_id);
				
				$no_of_check = isset($planDetails->no_of_check) ? $planDetails->no_of_check : 0;
				$fundconfirmation_no_check = isset($planDetails->fundconfirmation_no_check) ? $planDetails->fundconfirmation_no_check : 0;
				$bank_auth_link_no_check = isset($planDetails->bank_auth_link_no_check) ? $planDetails->bank_auth_link_no_check : 0;
				$no_of_users = isset($planDetails->no_of_users) ? $planDetails->no_of_users : 0;
				$no_of_companies = isset($planDetails->no_of_companies) ? $planDetails->no_of_companies : 0;
				
				$settings = '';
				if(isset($planDetails->settings) && $planDetails->settings != ''){
					$settings = unserialize($planDetails->settings);
				}
				
				$company = isset($settings['COMPANY']) ? $settings['COMPANY'] : 'no';
				$fundconfirmationp = isset($settings['FUNDCONFIRMATION']) ? $settings['FUNDCONFIRMATION'] : 'no';
				$basicVerification = isset($settings['BASICVERFICATIONS']) ? $settings['BASICVERFICATIONS'] : 'no';
				$bankAuthLink = isset($settings['BANKAUTHLINK']) ? $settings['BANKAUTHLINK'] : 'no';
				$checkoutLink = isset($settings['CHECKOUTLINK']) ? $settings['CHECKOUTLINK'] : 'no';
				$signature = isset($settings['SIGNTURE']) ? $settings['SIGNTURE'] : 'no';
				
				// Get the company permission by company_id
				$permissionSettingRes = $this->getCompanyPermissionByCompanyId($company_id);
				
				if(isset($permissionSettingRes['type']) && $permissionSettingRes['type'] == 'error'){
					return Response::json(['error' => $permissionSettingRes['value']], 401);
				}
				if(isset($permissionSettingRes['type']) && $permissionSettingRes['type'] == 'success'){
					$permissionSettings = $permissionSettingRes['value'];
				}
				
				// No of check / no of user settings
				$permissionSettings['TOTALNOCHECK'] = $no_of_check;
				$permissionSettings['NOOFCHECKREMAINING'] = $no_of_check;
				
				$permissionSettings['COMPANY'] = $company;
				$permissionSettings['TOTAL_COMPANY'] = $no_of_companies;
				$permissionSettings['REMAINING_COMPANY'] = $no_of_companies;
				
				$permissionSettings['TOTAL_USER'] = $no_of_users;
				$permissionSettings['REMAINING_USER'] = $no_of_users;
				
				$permissionSettings['TOTALFUNDCONFIRMATION'] = $fundconfirmation_no_check;
				$permissionSettings['REMAININGFUNDCONFIRMATION'] = $fundconfirmation_no_check;
				
				$permissionSettings['TOTALPAYAUTH'] = $bank_auth_link_no_check;
				$permissionSettings['PAYAUTHREMAINING'] = $bank_auth_link_no_check;
				
				// setting company check permission
				$permissionSettings['FUNDCONFIRMATION'] = $fundconfirmationp;
				$fundCofirmation = $permissionSettings['FUNDCONFIRMATION'];
				
				$permissionSettings['SIGNTURE'] = $signature;
				$signature = $permissionSettings['SIGNTURE'];
			
				$permissionSettings['CHECKOUTLINK'] = $checkoutLink;
				$checkoutLink = $permissionSettings['CHECKOUTLINK'];
			
				$permissionSettings['BANKAUTHLINK'] = $bankAuthLink;
				$bankAuthLink = $permissionSettings['BANKAUTHLINK'];
				
				$permissionSettings['BASICVERFICATIONS'] = $basicVerification;
				$basicVerification = $permissionSettings['BASICVERFICATIONS'];
				
				$permissionSettings['BASICVERFICATIONS_START_DATE'] = $current_period_start;
				$permissionSettings['BASICVERFICATIONS_END_DATE'] = $current_period_end;
				
				// Update companies fundconfirmation, signture, checkoutlink, bankauthlinkPlanId
				$permissionSettings = serialize($permissionSettings);
				CompanyDetail::where('company_id', $company_id)
				->update(['payment_link_permission' => $checkoutLink, 'signture_permission' => $signature, 'pay_auth_permission' => $bankAuthLink, 'fundconfirmation_permission' => $fundCofirmation, 'permissions'=> $permissionSettings]);
				
				// update plan and amount in user details
				UserDetail::where('user_id', $user_id)
				->update(['stripe_plan' => $plan_id, 'amount' => $amount, 'last_invoice_at' => $last_invoice_at]);
				
				// update stripe status, date and subscription id
				UserSubscription::where('user_id', $user_id)
				->update(['stripe_active' => 2, 'subscription_starts_at' => $current_period_start, 'subscription_ends_at' => $current_period_end, 'stripe_subscription' => $subscription_id, 'stripe_plan' => $plan_id]);				
				
			}
			
	   
	  }catch(Exception $e){
		   $code = $e->getCode();
		   $message = $e->getMessage();
		   $errorMessage = $message ." ".$code;
		   return response()->json(['error' => $errorMessage], 401);
	  }
	  return Response::json(['success' => true, 'token' => true]);
	 }
	
	
}
