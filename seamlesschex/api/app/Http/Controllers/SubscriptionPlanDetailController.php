<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Validator;
Use DB;
Use Config;
use App\Http\Controllers\Controller;
use Exception;
use Response;
use Stripe\Stripe;
use Stripe\Token; 
use Stripe\Plan;
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Plan as StripePlan;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use App\CheckMessage as CheckMessage;

use App\SubscriptionPlanDetail as SubscriptionPlanDetail;

class SubscriptionPlanDetailController extends Controller
{
	private $subscriptionPlanDetail;
    
	public function __construct(SubscriptionPlanDetail $subscriptionPlanDetail)
	{
		Stripe::setApiKey(Config::get('services.stripe.secret'));
		Stripe::setApiVersion("2014-08-20"); // use your Stripe API version
		
		$this->subscriptionPlanDetail = $subscriptionPlanDetail;
	}
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		
    }
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPlans()
    {
		try{
			
			$plans = SubscriptionPlanDetail::all();
			$json_response = array(); 
			$plan_array = array();
			foreach ($plans as $plan) {
					$plan_array['data'][] = array(
						'id' => $plan->id,
						'plan_name' => $plan->plan_name,
						'interval' => $plan->interval,
						'trial_period' => $plan->trial_period,
						'plan_name_in_stripe' => $plan->plan_name_in_stripe,
						'amount' => $plan->amount,
						'no_of_check' => $plan->no_of_check,
						'no_of_users' => $plan->no_of_users,
						'no_of_companies' => $plan->no_of_companies,
						'settings' => $plan->settings
					);
			}
			array_push($json_response, $plan_array);
			
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}
		return response()->json($json_response);
    }
	/**
     * creating a new plan in stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPlanInStripe($amount, $interval, $planName, $planId, $trial_period_days)
    {
		try{
			Plan::create(array(
			  "amount" => $amount,
			  "interval" => $interval,
			  "name" => $planName,
			  "currency" => "usd",
			  "id" => $planId,
			  "trial_period_days" => $trial_period_days)
			);
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value'=>$errorMessage );
			
		}
		return array('type' => 'success', 'value'=>true );
		
	}
    /**
     * creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createPlan(Request $request)
    {
        try{
			// Get all post values
			$plan_name = $request->get('plan_name');
			$plan_name_in_stripe = $request->get('plan_name_in_stripe');
			$amount = $request->get('amount');
			$no_of_check = $request->get('no_of_check');
			$fundconfirmation_no_check = $request->get('fundconfirmation_no_check');
			$bank_auth_link_no_check = $request->get('bank_auth_link_no_check');
			$no_of_users = $request->get('no_of_users');
			$no_of_companies = $request->get('no_of_companies');
			$settings = $request->get('settings');
			$interval = $request->get('interval');
			$trial_period_days = $request->get('trial_period');
			
			$checkMessages = CheckMessage::where('form_name', 'CREATE_PLAN')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
			// Validate the input using laravel
					
			$validator = Validator::make($request->all(),
				array(
					'plan_name' => 'required',
					'plan_name_in_stripe' => 'required',
					'amount' => 'required|numeric',
					'interval' => 'required',
					'trial_period' => 'required|numeric',
				)
			);
			if ($validator->fails())
			{
				$messages = $validator->messages();
				foreach ($checkMessages as $checkMessage) {
					$message = $checkMessage->message;
					$field_name = $checkMessage->field_name;
					if ($messages->has('amount') && $amount != '') {
						return response()->json(['error' => $messages->first('amount')], 401);
					}
					if ($messages->has('trial_period') && $trial_period_days != '') {
						return response()->json(['error' => $messages->first('trial_period')], 401);
					}
					if ($messages->has($field_name)) {
						return response()->json(['error' => $message], 401);
					}
				}
				
			}
			
			$trial_period_days = isset($trial_period_days) ?$trial_period_days : null;
			// Create Plan Stripe
			$amountPlan = $amount*100;
			$resPlan = $this->createPlanInStripe($amountPlan, $interval, $plan_name, $plan_name_in_stripe, $trial_period_days);
			if($resPlan['type'] == 'error'){
				return response()->json(['error' => $resPlan['value'], 'token' => false], 401);
			}
			$this->subscriptionPlanDetail->plan_name = $plan_name;
			
			$this->subscriptionPlanDetail->interval = $interval;
			$this->subscriptionPlanDetail->trial_period = $trial_period_days;
			$this->subscriptionPlanDetail->plan_name_in_stripe = $plan_name_in_stripe;
			$this->subscriptionPlanDetail->amount = $amount;
			$this->subscriptionPlanDetail->no_of_check = $no_of_check;
			$this->subscriptionPlanDetail->fundconfirmation_no_check = $fundconfirmation_no_check;
			$this->subscriptionPlanDetail->bank_auth_link_no_check = $bank_auth_link_no_check;
			$this->subscriptionPlanDetail->no_of_users = $no_of_users;
			$this->subscriptionPlanDetail->no_of_companies = $no_of_companies;
			$this->subscriptionPlanDetail->settings = serialize($settings);
			$save = $this->subscriptionPlanDetail->save();
		
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}
		return response()->json(array('success' => true, 'token' => true), 200);
    }
	
	/**
     * Updating plan in stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function updatePlanInStripe($planId, $planName)
    {
		try{
			$plan = Plan::retrieve($planId);
			$plan->name = $planName;
			$plan->save();
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return Response::json(['success' => true, 'token' => true]);
	}

    /**
     * Editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePlan(Request $request, $id)
    {
		try{
			// Get all post values
			$plan_name = $request->get('plan_name');
			//$plan_name_in_stripe = $request->get('plan_name_in_stripe');
			$amount = $request->get('amount');
			$no_of_check = $request->get('no_of_check');
			$fundconfirmation_no_check = $request->get('fundconfirmation_no_check');
			$bank_auth_link_no_check = $request->get('bank_auth_link_no_check');
			$no_of_users = $request->get('no_of_users');
			$no_of_companies = $request->get('no_of_companies');
			$interval = $request->get('interval');
			$trial_period = $request->get('trial_period');
			$settings = $request->get('settings');
			
			$checkMessages = CheckMessage::where('form_name', 'CREATE_PLAN')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
			// Validate the input using laravel
					
			$validator = Validator::make($request->all(),
				array(
					'plan_name' => 'required',
					'plan_name_in_stripe' => 'required',
					'amount' => 'required|numeric',
					'interval' => 'required',
					'trial_period' => 'required|numeric',
				)
			);
			if ($validator->fails())
			{
				$messages = $validator->messages();
				foreach ($checkMessages as $checkMessage) {
					$message = $checkMessage->message;
					$field_name = $checkMessage->field_name;
					if ($messages->has('amount') && $amount != '') {
						return response()->json(['error' => $messages->first('amount')], 401);
					}
					if ($messages->has('trial_period') && $trial_period != '') {
						return response()->json(['error' => $messages->first('trial_period')], 401);
					}
					if ($messages->has($field_name)) {
						return response()->json(['error' => $message], 401);
					}
				}
				
			}
			
			$plan = SubscriptionPlanDetail::find($id);
			
			$plan->plan_name = $plan_name;
			//$plan->plan_name_in_stripe = $plan_name_in_stripe;
			$plan->amount = $amount;
			$plan->no_of_check = $no_of_check;
			$plan->fundconfirmation_no_check = $fundconfirmation_no_check;
			$plan->bank_auth_link_no_check = $bank_auth_link_no_check;
			$plan->no_of_users = $no_of_users;
			$plan->no_of_companies = $no_of_companies;
			$plan->interval = $interval;
			$plan->trial_period = $trial_period;
			$plan->settings = serialize($settings);
			
			// Update Plan in stripe
			$planId = $plan->plan_name_in_stripe;
			$this->updatePlanInStripe($planId, $plan_name);
			
			$save = $plan->save();
			
			
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}
		return response()->json(array('success' => true, 'token' => true), 200);
    }

	/**
     * delete plan in stripe.
     *
     * @return \Illuminate\Http\Response
     */
    public function deletePlanInStripe($planId)
    {
		try{
			$plan = Plan::retrieve($planId);
			$plan->delete();
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return Response::json(['success' => true, 'token' => true, "action" => 'delete']);
	}
    
    /**
     * Remove the specified resource from Plan.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletePlan($id)
    {
        try{
			$plan = SubscriptionPlanDetail::find($id);
			$planId = $plan->plan_name_in_stripe;
			
			// Delete Plan In stripe
			$this->deletePlanInStripe($planId);
			
			$plan->delete();
				
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return Response::json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}
		return response()->json(['success' => true, "id" => $id, "action" => 'delete']);
    }
	
	/**
     * Get the data for specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getPlanById($id)
    {
		
        try{
			$plan = SubscriptionPlanDetail::find($id);
			$json_response = array(); 
			$plan_array = array();
			$settings = unserialize($plan->settings);
			$plan_array['data'] = array(
				'id' => $plan->id,
						'plan_name' => $plan->plan_name,
						'plan_name_in_stripe' => $plan->plan_name_in_stripe,
						'interval' => $plan->interval,
						'trial_period' => $plan->trial_period,
						'amount' => $plan->amount,
						'no_of_check' => $plan->no_of_check,
						'fundconfirmation_no_check' => $plan->fundconfirmation_no_check,
						'bank_auth_link_no_check' => $plan->bank_auth_link_no_check,
						'no_of_users' => $plan->no_of_users,
						'no_of_companies' => $plan->no_of_companies,
						'settings' => $settings
			);
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}
		return response()->json($plan_array);
    }
	
	/**
     * Get the data for specified resource.
     *
     * 
     * @return \Illuminate\Http\Response
     */
    public function getPlanDetailsSettingsByStripePlanID(Request $request)
    {
		
        try{
			$plan_id = $request->get('stripe_plan');
			$planDetails = SubscriptionPlanDetail::getPlanDetailsByStripePlanID($plan_id);
				
			$no_of_check = isset($planDetails->no_of_check) ? $planDetails->no_of_check : 0;
			$fundconfirmation_no_check = isset($planDetails->fundconfirmation_no_check) ? $planDetails->fundconfirmation_no_check : 0;
			$bank_auth_link_no_check = isset($planDetails->bank_auth_link_no_check) ? $planDetails->bank_auth_link_no_check : 0;
			$no_of_users = isset($planDetails->no_of_users) ? $planDetails->no_of_users : 0;
			$no_of_companies = isset($planDetails->no_of_companies) ? $planDetails->no_of_companies : 0;
			$amount = isset($planDetails->amount) ? $planDetails->amount : 0;
			
			$settings = '';
			if(isset($planDetails->settings) && $planDetails->settings != ''){
				$settings = unserialize($planDetails->settings);
			}
			
			$plan_array = array();
			$plan_array['data'] = array(
						'stripe_plan' => $plan_id,
						'amount' => $amount,
						'no_of_check' => $no_of_check,
						'fundconfirmation_no_check' => $fundconfirmation_no_check,
						'bank_auth_link_no_check' => $bank_auth_link_no_check,
						'no_of_users' => $no_of_users,
						'no_of_companies' => $no_of_companies,
						'settings' => $settings
			);
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}
		return response()->json($plan_array);
    }
}
