<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Validator;
use App\Http\Requests;
use App\User;
use App\Company;
use App\CheckMessage;
use App\EmailTemplate;
use App\UserStatus as Status;
use App\Http\Controllers\Controller;

use Exception;
use Response;
use Stripe\Stripe;
use Stripe\Token; 
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Plan as StripePlan;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use Stripe\Invoice as StripeInvoice;
use Mail;
use App\Check;
use App\CheckDetail;
use Carbon\Carbon;
use Crypt;

trait UserController
{
	
	/**
     * Get stripe token from Card Details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getStripeTokenFromCardDetails($number, $exp_month, $exp_year, $cvc)
    {

		try {
			// Get all the error messages from check_messages
			$checkMessages = CheckMessage::where('form_name', 'REGISTER')
							->where('type', 'ERROR')
							->orderBy('position', 'asc')
							->get();
										
			// Validate the input using laravel
			   
			$validator = Validator::make( 
				array(
					'number' => $number,
					'exp_month' => $exp_month,
					'exp_year' => $exp_year,
					'cvc' => $cvc,
				),
				array(
					'number' => 'required',
					'exp_month' => 'required',
					'exp_year' => 'required',
					'cvc' => 'required',
				)
			);
			
			if ($validator->fails())
			{
				$messages = $validator->messages();
				
				foreach ($checkMessages as $checkMessage) {
					$message = $checkMessage->message;
					$field_name = $checkMessage->field_name;
					if ($messages->has($field_name)) {
						return array('type' => 'error', 'value'=>$message);
					}
				}
				
			}
			
		$stripeResponse = Token::create(array(
							  "card" => array(
								"number" => trim($number),
								"exp_month" => $exp_month,
								"exp_year" => $exp_year,
								"cvc" => $cvc
							  )
						));
		
		$token = $stripeResponse->id;
		$card_type = $stripeResponse->card->funding;
		
		if($card_type == 'prepaid'){
			return array('type' => 'error', 'value'=>'Unfortunately we do not accept prepaid cards');
		}
		
		
		}catch (StripeErrorCard $e) {
			$body = $e->getJsonBody();
			$err  = $body['error'];
			return array('type' => 'error', 'value'=>$err['message'] );
			
		}catch (StripeErrorInvalidRequest $e) {
			// Invalid parameters were supplied to Stripe's API
			return array('type' => 'error', 'value'=>'Invalid Request' );
		} catch (StripeErrorAuthentication $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			return array('type' => 'error', 'value'=>'Invalid API Key' );
		} catch (StripeErrorApiConnection $e) {
			// Network communication with Stripe failed
			return array('type' => 'error', 'value'=>'Network communication with Stripe failed' );
			
		} catch (StripeErrorBase $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			return array('type' => 'error', 'value'=>'Generic Error' );
			
		} catch (Exception $e) {
			// Something else happened, completely unrelated to Stripe
			return array('type' => 'error', 'value'=>'Not Related to Stripe' );
		}
		
		return array('type' => 'success', 'value'=>$token );
	}
	
	
	/**
    * generate_random_token
    *
    * @desc - generate random token
   
    * @return string salt 
    *
    */
    public function generateAuthToken($size = 35)
    {
        $salt = "";
        for ($i = 0; $i < $size; $i++) {
            $salt .= substr("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 61), 1);
        }
        return $salt;
    }
	
	/**
	* Get all plan from stripe.
	*
	* @param  strine  $stripe_id
	* @return true
	*/	
	public function getAllPlanStripe(){
		try {
			
			$stripePaln = StripePlan::all(array("limit" => 100));
			$plan_response = array(); 
			$plan_array = array();
			foreach ($stripePaln->data as $key => $plan) {
				$amount = (($plan->amount)/(100));
					$plan_array['plans'][] = array(
						'plan_id' => $plan->id,
						'plan_name' => $plan->name,
						'amount' => $amount
					);
			}
			array_push($plan_response, $plan_array);
			
		}catch (Exception $exception) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return response()->json(["error"=>$errormsg]);
		}
	
	return $plan_response;
	}
	
	
	/**
	* Update trial_ends_at in stripe
	*
	* @param  $stripe_id, $stripe_subscription, $date
	* @return true
	*/	
	public function updateTrialEndsAtStripe($stripe_id, $stripe_subscription, $date){
		try {
			
			$custemer = StripeCustomer::retrieve($stripe_id);
			$subscription = $custemer->subscriptions->retrieve( $stripe_subscription );

			// Update trial end date and save
			$subscription->trial_end = $date;
			$subscription->save();
			
		}catch (Exception $exception) {
			// Something else happened, completely unrelated to Stripe
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return response()->json(["error"=>$errormsg]);
		}
		//return response()->json($plan_response);
		return true;
	}
	
	/**
	* Update email in stripe.
	*
	* @param  strine  $stripe_id
	* @return true
	*/	
	public function updateEmailStripe($stripe_id){
		try {
			$stripeCustomer = StripeCustomer::retrieve($stripe_id);
			$stripeCustomer->description = "Customer email address changed to $email";
			$stripeCustomer->email = $email; 
			$stripeCustomer->save();
		}catch (Exception $exception) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value'=>$errormsg );
		}
		return array('type' => 'success', 'value'=> true );
	}
	
	/**
	* retrieve customer info using stripe_id
	*
	* @param  strine  $stripe_id
	* @return date
	*/	
	
	public function getCustomerStripe($stripe_id){
		try {
			$customer = StripeCustomer::retrieve($stripe_id);
			$last4 = $customer->sources->data[0]->last4;
			$exp_year = $customer->sources->data[0]->exp_year;
			$exp_month = $customer->sources->data[0]->exp_month;
			$brand = $customer->sources->data[0]->brand;
		}catch (Exception $exception) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value'=>$errormsg );
		}
		return array('brand' => $brand, 'last4'=> $last4, 'exp_month' => $exp_month, 'exp_year' => $exp_year );
	}
	
	/**
	* get upcoming invoice date by using stripe_id
	*
	* @param  strine  $stripe_id
	* @return date
	*/	
	public function getNextInvoiceDate($stripe_id){
		try {
			$stripeInvoice = StripeInvoice::upcoming(array("customer" => $stripe_id));
			$next_invoice_date = date("m/d/Y H:i:s", $stripeInvoice->date);
		}catch (Exception $exception) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value'=>$errormsg );
		}
		return $next_invoice_date;
	}
	
	//get check by check token
    public function getCheckByToken($check_token)
    {
    	try{

    		$checkObj = new Check;
    		$checks = $checkObj->editCheck($check_token);
			$json_response = array(); 
			$check_array = array();

			foreach ($checks as $value) {

				$checkDetail = CheckDetail::select('*')->where('check_id', $value->id)->get();
				//print_r($checkDetail);

				$id = $checkDetail[0]->id;
				$encryptedCheckEmailSendReceipt = $checkDetail[0]->email;
				$ownerId = $checkDetail[0]->owner_id;
				$batchId = $checkDetail[0]->batch_id;
				$groupId = $checkDetail[0]->group_id;

				$verifyBeforeSave = $checkDetail[0]->verify_before_save;
				

				$isFundconfirmation = $checkDetail[0]->is_fundconfirmation;


				 	$create   	= Carbon::parse($value->date);

	                $date     	= $create->format('Y-m-d H:i:s');
	             
	          
	                $createa    = Carbon::parse($value->authorisation_date);
	                $authorisation_date  = $createa->format('d-m-Y');
					$check_array = array(

						'id'                     =>  $value->id,
						'company_id'             =>  $value->company_id,
						'owner_id'               =>  $value->owner_id,
						'user_id'                =>  $value->user_id,
						'email'                  =>  Crypt::decrypt( $value->email),
						'name'                   =>  Crypt::decrypt($value->name),
						'to_name'                =>  Crypt::decrypt($value->to_name),
						'address'         		 =>  Crypt::decrypt($value->address),
						'city'                   =>  Crypt::decrypt($value->city),
						'state'                  =>  Crypt::decrypt($value->state),
						'zip'                    =>  Crypt::decrypt($value->zip),
						'memo1'                   =>  Crypt::decrypt($value->memo),
						'memo2'                  =>  Crypt::decrypt($value->memo2),
						'routing_number'         =>  $value->routing,
						'account_number'         =>  Crypt::decrypt($value->checking_account_number),
						'confirm_account_number' =>  Crypt::decrypt($value->confirm_account_number),
						'check_number'           =>  $value->checknum,
						'check_amount'           =>  $value->amount,
						'date'                   =>  $date,
						'authorisation_date'     =>  $authorisation_date,
						'month'                  =>  $value->month,
						'verify_before_save'     =>  $verifyBeforeSave
					);
				array_push($json_response, $check_array);
			}
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}

		return $json_response;
    }
}
