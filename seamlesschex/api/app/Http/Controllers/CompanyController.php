<?php

namespace App\Http\Controllers;
use App;
use Validator;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use App\Http\Requests;
Use DB;
use Config;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;
use App\User;
use App\UserDetail;
use App\Company as Company;
use App\CompanyDetail;
use App\UserMailchimpInfo;
use App\UserSubscription;
use App\PasswordReset;
use App\CheckFee;
use App\CheckBasicFee as CheckBasicFee;
use App\UserPermission as UserPermission;
use Exception;
use Response;
use Mailchimp;
use App\UserRole as Role;
use App\CheckSetting as CheckSetting;
use App\UserStatus as Status;
use App\CheckMessage as CheckMessage;
use Stripe\Stripe;
use Stripe\Token; 
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Invoice as StripeInvoice;
use Stripe\Plan as StripePlan;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use Webpatser\Uuid\Uuid;
use Mail;
use Excel;

class CompanyController extends Controller
{
	use UserController;
	use UserTrait;
	private $user, $userDetail, $company, $companyDetail, $userMailchimpInfo, $userSubscription, $checkFee, $checkBasicFee, $userPermission;
	private $jwtauth;
	
	
	public function __construct(User $user, UserDetail $userDetail, Company $company, CompanyDetail $companyDetail, UserMailchimpInfo $userMailchimpInfo, CheckFee $checkFee, UserSubscription $userSubscription, CheckBasicFee $checkBasicFee, UserPermission $userPermission, PasswordReset $passwordReset, JWTAuth $jwtauth)
	{
		
		Stripe::setApiKey(Config::get('services.stripe.secret'));
		Stripe::setApiVersion("2014-08-20"); // use your Stripe API version
		$this->listId = Config::get('services.mailchimp.listid');
		$this->error = false;
		$this->userSubscription = $userSubscription;
		$this->user = $user;
		$this->userDetail = $userDetail;
		$this->company = $company;
		$this->companyDetail = $companyDetail;
		$this->userMailchimpInfo = $userMailchimpInfo;
		$this->checkFee = $checkFee;
		$this->checkBasicFee = $checkBasicFee;
		$this->userPermission = $userPermission;
		$this->passwordReset = $passwordReset;
		$this->jwtauth = $jwtauth;
		
		
	}
	
	/*
		List Merchant
	*/
	
	public function index()
	{
		try{
			
			$response = $this->company->getAllMerchant();
			
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
		List admin (scx)
	*/
	
	public function listScxAdmin()
	{
		try{
			
			$response = $this->user->getScxAdmin();
			
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
		List company-user
	*/
	
	public function listCompanyUsers()
	{
		try{
			
			$response = $this->user->getMerchantUser();
			
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
		List company-sub
	*/
	
	public function listCompanySub()
	{
		try{
			
			$response = $this->company->getCompanySub();
			
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

	
	

    /**
     * creating a new company-admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCompany(Request $request)
    {
				
		// Get all the input
		$saveCompany = $request->get('saveCompany');
		$saveBankDetails = $request->get('saveBankDetails');
		$saveFeeSettings = $request->get('saveFeeSettings');
		$savePlanDetails = $request->get('savePlanDetails');
		$saveCreditCardDetails = $request->get('saveCreditCardDetails');
		$savePermissions = $request->get('savePermissions');
		$saveBatchSettings = $request->get('saveBatchSettings');
		
		$name = $request->get('name');
		$cname = $request->get('cname');
		$saddress = $request->get('saddress');
		$city = $request->get('city');
		$state = $request->get('state');
		$zip = $request->get('zip');
		$business_type = $request->get('business_type');
		$email = $request->get('email');
		//$username = $request->get('username');
		$password = bcrypt($request->get('password'));
		$custom_password = $request->get('password');
		$website = $request->get('website');
		$taxid = $request->get('taxid');
		$phone = $request->get('phone');
		$plan = $request->get('stripe_plan');
		//$planType = $plan. 'Trial';
		$planType = $plan;
		$amount = $request->get('amount');
		$number = $request->get('number');
		$exp_month = $request->get('exp_month');
		$exp_year = $request->get('exp_year');
		$cvc = $request->get('cvc');
		$ip = $request->ip();
		
		//Bank Details
		$bank_routing = $request->get('bank_routing');
		$bank_name = $request->get('bank_name');
		$bank_account_no = $request->get('bank_account_no');
		$authorised_signer = $request->get('authorised_signer');
		
		$total_no_check = $request->get('total_no_check');
		$no_of_check = $total_no_check; 
		$no_of_check_remaining = $request->get('no_of_check_remaining');
		$total_fundconfirmation = $request->get('total_fundconfirmation');
		$remaining_fundconfirmation = $request->get('remaining_fundconfirmation');
		$total_payauth = $request->get('total_payauth');
		$payauth_remaining = $request->get('payauth_remaining');
		$companies_permission = $request->get('companies_permission');
		$pay_auth_permission = $request->get('pay_auth_permission');
		$payment_link_permission = $request->get('payment_link_permission');
		$signture_permission = $request->get('signture_permission');
		$fundconfirmation_permission = $request->get('fundconfirmation_permission');
		$permissions_company = $request->get('permissions');
		$permissions_company = serialize($permissions_company);
		
		
		$user_settings = $request->get('user_settings');
		$user_settings = serialize($user_settings);
		
		$batch_settings = $request->get('batch_settings');
		$settings = serialize($batch_settings);
		//Hidden Param
		
		
		$is_stripe = 0;
		$stripeToken = '';
		if($plan != '' && $number != '' && $exp_year != '' && $exp_month != '' && $cvc != ''){
			$is_stripe = 1;
		}
		
		//$is_stripe_update = ($saveCreditCardDetails == true):1:0;
		
		try 
			{				
				if($saveCompany == true){
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
						'email' => 'required|email|unique:users',
						'password' => 'min:8|required'
					)
				);
				if ($validator->fails())
				{
					$messages = $validator->messages();
					foreach ($checkMessages as $checkMessage) {
						$message = $checkMessage->message;
						$field_name = $checkMessage->field_name;
						if ($messages->has('email') && $email != '') {
							return response()->json(['error' => $messages->first('email')], 401);
						}
						if ($messages->has('password') && $custom_password != '') {
							return response()->json(['error' => $messages->first('password')], 401);
						}
						if ($messages->has($field_name)) {
							return response()->json(['error' => $message], 401);
						}
					}
				}
				
				
				
				// Get the stripe token
				if($is_stripe == 1){
					$stripeTokenResponse = $this->getStripeTokenFromCardDetails($number, $exp_month, $exp_year, $cvc);
					$resccType = $stripeTokenResponse['type'];
					$resccValue = $stripeTokenResponse['value'];
					
					if($resccType == 'error'){
						return Response::json(['error' => $resccValue], 500);
					}
					
					if($resccType == 'success'){
						$stripeToken = $resccValue;
					}
				}
				
				// Save to users
				
				$this->user->email = $email;
				//$this->user->username = $username;
				$this->user->name = $name;
				$this->user->password = $password;
				//Generate sc_token
				$sc_token_generate = Uuid::generate();
				$this->user->sc_token = $sc_token_generate;
				$this->user->save();
				
				// Get last inserted id
				$user_id = $this->user->id;
				
				// Save into companies
				$mc_token_generate = Uuid::generate();
				$this->company->mc_token = $mc_token_generate;
				$this->company->user_id = $user_id;
				$this->company->company_name = $name;
				$this->company->cname = $cname;
				$this->company->business_type = $business_type;
				$this->company->company_email = $email;
				$this->company->address = $saddress;
				$this->company->city = $city;
				$this->company->state = $state;
				$this->company->zip = $zip;
				$this->company->phone = $phone;
				$this->company->settings = $settings;
				$this->company->website = $website;
				$this->company->taxid = $taxid;
				
				//Bank Details
				$this->company->bank_routing = $bank_routing;
				$this->company->bank_name = $bank_name;
				$this->company->bank_account_no = $bank_account_no;
				$this->company->authorised_signer = $authorised_signer;
				$this->company->save();
				$company_id = $this->company->id;
				
				$this->userDetail->user_id = $user_id;
				
				// Find the id for role_name=company-admin
				$role_id = 3;
				
				// Find the id for status=active
				$status_id = 2;
				
				// Save the user_details
				$this->userDetail->company_id = $company_id;
				$this->userDetail->status_id = $status_id;
				$this->userDetail->role_id = $role_id;
				$this->userDetail->amount = $amount;
				$this->userDetail->stripe_plan = $planType;
				
				// user_seetings to access the links
				$this->userDetail->permission_settings = $user_settings;
				
				$this->userDetail->ip_address = $ip;
				$this->userDetail->save();
				
				// Save comapny_details
				
				$this->companyDetail->user_id = $user_id;
				$this->companyDetail->company_id = $company_id;
				$this->companyDetail->total_no_check = $no_of_check;
				$this->companyDetail->no_of_check_remaining = $no_of_check;
				$this->companyDetail->total_fundconfirmation = $total_fundconfirmation;
				$this->companyDetail->remaining_fundconfirmation = $total_fundconfirmation;
				$this->companyDetail->total_payauth = $total_payauth;
				$this->companyDetail->payauth_remaining = $total_payauth;
				$this->companyDetail->companies_permission = $companies_permission;
				$this->companyDetail->fundconfirmation_permission = $fundconfirmation_permission;
				$this->companyDetail->payment_link_permission = $payment_link_permission;
				$this->companyDetail->signture_permission = $signture_permission;
				$this->companyDetail->pay_auth_permission = $pay_auth_permission;
				$this->companyDetail->status_id = $status_id;
				$this->companyDetail->permissions = $permissions_company;
			
				
				$this->companyDetail->save();
				$last_company_detail_id = $this->companyDetail->id;
				
				// Save to check_fees
				$checkFeeData = array(
					array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'DAILY_DEPOSIT_FEE', 'value' => 0.00 ),
					array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'PER_CHECK_FEE', 'value' => 0.00 ),
					array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'MONTHLY_FEE', 'value' => $amount ),
					array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'CHECK_PROCESSING_FEE', 'value' => 0.00 ),
					array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'CHECK_VERIFICATION_FEE', 'value' => 0.00 ),
					array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'FUNDCONFIRMATION_FEE', 'value' => 0.00 )
					
				);
				CheckFee::insert($checkFeeData);
					
					
				// Save to user_subscriptions
				$this->userSubscription->user_id = $user_id;
				$this->userSubscription->amount = $amount;
				if($is_stripe != 1){
					$this->userSubscription->stripe_plan = $planType;
					$this->userSubscription->stripe_active = 2;
				}
				$this->userSubscription->save();
				$last_subscription_id = $this->userSubscription->id;
				
				// Create customer in stripe and subscribing to the selected plan
				if($stripeToken !='' && $is_stripe == 1){
					$this->userSubscription->subscription($plan)->create($stripeToken, [
						'email' => $email, 'description' => 'subscribe to '.$plan
					]);
					$response = $this->userSubscription->getUserSubscription($sc_token_generate);
					if(is_array($response) && $response->stripe_id != ''){
						$next_invoice_date = $this->getNextInvoiceDate($response->stripe_id);
						UserDetail::where('user_id', $user_id)
						->update(['stripe_plan' => $plan, 'amount' => $amount, 'stripe_id' => $response->stripe_id, 'last_invoice_at' => $next_invoice_date]);
					}
				}
				
			}
				
				
		}catch (Exception $exception){
			 //$errormsg = 'Database error! ' . $exception->getCode();
			 //return response()->json(['error' => $errormsg]);
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 
			 return response()->json(['error' => $errormsg],401);
		}
		
		$token = JWTAuth::fromUser($this->user);
		return Response::json(['success' => true, 'token' => $token]);
    }
	
	
	
	
    /**
     * Display the specified resource.
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
    public function getCompanyByToken($sc_token)
    {
		
		try {
			
			$company = $this->company->getMerchantQueryByScToken($sc_token);
			
			if(is_array($company) && isset($company['type']) && $company['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $company['value'], 401]);
			}
			
			
			$fee = $this->checkFee->getCompanyFee($sc_token);
			$allPlan = $this->getAllPlanStripe();
			$default_settings = $this->userPermission->getDefaultCompanySettingArray(3);
			$default_settings = unserialize($default_settings);
			$json_response = array(); 
			$user_array = array();
			$status_array = array();
			$next_invoice_date = '';

			$user_settings = unserialize($company->permission_settings);
			$company_settings = unserialize($company->permissions);
			$settings = $company->settings;
			if($company->settings){
				$settings = unserialize($company->settings);
			}
			$response = $this->userSubscription->getUserSubscription($sc_token);
			//$stripe_active = $response->stripe_active;
			// Send status from user_details not for user_subscriptions(i.e stripe_active)
			$status_id = $company->status_id;
			if($status_id){
				$status = Status::find($status_id);
				$status_array = array('status_name' => $status->status_name, 'status_code' => $status->status, 'color' => $status->color, 'id' => $status_id);
			}
			
			if($company->status_id){
				$status = Status::find($company->status_id);
				$status_array = array('status_name' => $status->status_name, 'status_code' => $status->status, 'color' => $status->color, 'id' => $status_id);
			}
			$stripe_id = '';
			$stripe_subscription = '';
			$stripe_plan = '';
			$stripe_details = '';
			$trial_ends_at = '';
			$subscription_ends_at = '';
			$next_invoice_date = '';
			$amount = '';
			if(!empty($response)){
				if(isset($response->stripe_id)){
					
					$stripe_id = $response->stripe_id;
					
					$next_invoice_date = $this->getNextInvoiceDate($stripe_id);
					$stripe_details = $this->getCustomerStripe($stripe_id);
				}
				
				$stripe_subscription = (isset($response->stripe_subscription))? $response->stripe_subscription : '';
				$stripe_plan = (isset($response->stripe_plan))? $response->stripe_plan : '';
				$trial_ends_at = (isset($response->trial_ends_at))? $response->trial_ends_at : '';
				$subscription_ends_at = (isset($response->subscription_ends_at))? $response->subscription_ends_at : '';
				$amount = (isset($response->amount))? $response->amount : '0.00';
				
			}
			
			
			$user_array = array(
				'sc_token' => $company->sc_token,
				'mc_token' => $company->mc_token,
				'name' => $company->name,
				'cname' => $company->cname,
				'email' => $company->email,
				'saddress' => $company->address,
				'city' => $company->city,
				'state' => $company->state,
				'zip' => $company->zip,
				'phone' => $company->phone,
				'website' => $company->website,
				'taxid' => $company->taxid,
				'business_type' => $company->business_type,
				'bank_name' => $company->bank_name,
				'bank_account_no' => $company->bank_account_no,
				'authorised_signer' => $company->authorised_signer,
				'bank_routing' => $company->bank_routing,
				'amount' => $amount,
				'user_settings' => $user_settings,
				'company_settings' => $company_settings,
				'fee' => $fee,
				'all_plan' => $allPlan,
				'stripe_id' => $stripe_id,
				'stripe_plan' => $stripe_plan,
				'stripe_subscription' => $stripe_subscription,
				'stripe_details' => $stripe_details,
				//'status' => $stripe_active,
				'status' => $status_array,
				'trial_ends_at' => $trial_ends_at,
				'subscription_ends_at' => $subscription_ends_at,
				'next_invoice_date' => $next_invoice_date,
				'default_settings' => $default_settings,
				'batch_settings' => $settings
			);
		
		array_push($json_response, $user_array);
		
		
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		
		return response()->json($json_response);
        
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCompany(Request $request, $sc_token)
    {
		try {
			
			$user_id = $this->getUserId($sc_token);
			$company_id = $this->getCompanyId($sc_token);
			
			// Get all the input
			
			$updateCompanyDetails = $request->get('updateCompanyDetails');
			$updateBankDetails = $request->get('updateBankDetails');
			$updateFeeSettings = $request->get('updateFeeSettings');
			$updatePlanDetails = $request->get('updatePlanDetails');
			$updateCreditCardDetails = $request->get('updateCreditCardDetails');
			$is_stripe_email_update = $request->get('stripeUpdateEmail');
			$updatePermissions = $request->get('updatePermissions');
			$updatebatchSettings = $request->get('batchSettings');
			
			$name = $request->get('name');
			$cname = $request->get('cname');
			$saddress = $request->get('saddress');
			$city = $request->get('city');
			$state = $request->get('state');
			$zip = $request->get('zip');
			$business_type = $request->get('business_type');
			$email = $request->get('email');
			//$email = 'sumantaupdatetest@akeans.com';
			$password = bcrypt($request->get('password'));
			$custom_password = $request->get('password');
			$website = $request->get('website');
			$taxid = $request->get('taxid');
			$phone = $request->get('phone');
			//$plan = $request->get('plan');
			//$amount = $request->get('amount');
			$number = $request->get('number');
			$exp_month = $request->get('exp_month');
			$exp_year = $request->get('exp_year');
			$cvc = $request->get('cvc');
			$ip = $request->ip();
			
			//Bank Details
			$bank_routing = $request->get('bank_routing');
			$bank_name = $request->get('bank_name');
			$bank_account_no = $request->get('bank_account_no');
			$authorised_signer = $request->get('authorised_signer');
			
			$total_no_check = $request->get('total_no_check');
			$no_of_check_remaining = $request->get('no_of_check_remaining');
			$total_fundconfirmation = $request->get('total_fundconfirmation');
			$remaining_fundconfirmation = $request->get('remaining_fundconfirmation');
			$total_payauth = $request->get('total_payauth');
			$payauth_remaining = $request->get('payauth_remaining');
			$companies_permission = $request->get('companies_permission');
			$pay_auth_permission = $request->get('pay_auth_permission');
			$payment_link_permission = $request->get('payment_link_permission');
			$signture_permission = $request->get('signture_permission');
			$fundconfirmation_permission = $request->get('fundconfirmation_permission');
			$permissions_company = $request->get('permissions');
			$permissions_company = serialize($permissions_company);
			
			// Fee Details Param
			$daily_deposite_fee = $request->get('daily_deposite_fee');
			$per_check_fee = $request->get('per_check_fee');
			$check_verification_fee = $request->get('check_verification_fee');
			$check_processing_fee = $request->get('check_processing_fee');
			
			//  batch_settings param
			$batch_settings = $request->get('batch_settings');
			
			// Permission settings param
			$user_settings = $request->get('user_settings');
			
			// Stripe update in plan details section
			$stripe_update = $request->get('stripe_update');
			$stripe_plan = $request->get('stripe_plan');
			$amount = $request->get('amount');
			$monthly_fee = $request->get('monthly_fee');
			$fundconfirmation_fee = $request->get('fundconfirmation_fee');
			$trial_ends_at = $request->get('trial_ends_at');
			$subscription_ends_at = $request->get('subscription_ends_at');
			$status_name = $request->get('status');
			
			
			if($status_name){
				$statusRes = $this->getStatusId($status_name);
			}
			$status = '';
			if(isset($statusRes) && isset($statusRes['type']) && $statusRes['type'] == 'error'){
				return Response::json(['error' => $statusRes['value']], 401);
			}
			if(isset($statusRes) && isset($statusRes['type']) && $statusRes['type'] == 'success'){
				$status = $statusRes['value'];
			}
			
			//Hidden Param
			$stripe_id = $request->get('stripe_id');
			
			// Set stripe card details update to true if card details true
			$is_stripe_card_update = ($updateCreditCardDetails == true)?1:0;
			
			// Stripe email update check
			$is_stripe_email_update = ($is_stripe_email_update == true)?1:0;
			// For plan details
			$stripe_update = ($stripe_update == true)?1:0;
			
			if($user_id){
				// For users
				$user = User::find($user_id);
				$emailold = $user->email;				
			}
			if($company_id){
				// For companies
				$company = Company::find($company_id);
				
			}
			
			// Update the user and company details 
			if($updateCompanyDetails == true){
				// users
				$user->name = $name;
				$user->email = $email;
				if($custom_password){
					$user->password = $password;
				}
				$saveUser = $user->save();
				
				
				// companies
				//if($company->mc_token == '' || empty($company->mc_token)){
					//$mc_token_generate = Uuid::generate();
					//$company->mc_token = $mc_token_generate;
				//}
				$company->company_name = $name;
				$company->company_email = $email;
				$company->business_type = $business_type;
				$company->cname = $cname;
				$company->address = $saddress;
				$company->city = $city;
				$company->state = $state;
				$company->zip = $zip;
				$company->phone = $phone;
				$company->website = $website;
				$company->taxid = $taxid;
				$saveCom = $company->save();
				
				
			}
			
			//Email Update in Stripe with save company details
			if(($is_stripe_email_update == 1) && ($stripe_id!='') && ($updateCompanyDetails == true) ){
				$resUpdateEmail = $this->updateEmailStripe($stripe_id);
				if($resUpdateEmail['type'] == 'error'){
					return response()->json(array('error' => $resUpdateEmail['value'], 'token' => false), 401);
				}
			}
			
			
			// Update the company bank details 
			if($updateBankDetails == true){
				//Bank Details
				$company->bank_routing = $bank_routing;
				$company->bank_name = $bank_name;
				$company->bank_account_no = $bank_account_no;
				$company->authorised_signer = $authorised_signer;
				
				$saveCom = $company->save();
				
				
			}
			
			// Update the company bank details 
			if($updateFeeSettings == true){
				// For check_fees
				//DAILY_DEPOSIT_FEE
				$feeUp1 = CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'DAILY_DEPOSIT_FEE')
				->update(['value' => $daily_deposite_fee]);
				//PER_CHECK_FEE
				$feeUp2 = CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'PER_CHECK_FEE')
				->update(['value' => $per_check_fee]);
				//MONTHLY_FEE
				CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'MONTHLY_FEE')
				->update(['value' => $monthly_fee]);
				//CHECK_PROCESSING_FEE
				$feeUp3 = CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'CHECK_PROCESSING_FEE')
				->update(['value' => $check_processing_fee]);
				//CHECK_VERIFICATION_FEE
				$feeUp4 = CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'CHECK_VERIFICATION_FEE')
				->update(['value' => $check_verification_fee]);
				
				
			}
			
			// Update the plan details details 
			if($updatePlanDetails == true){
				
				//die;
				// Serialize the array for all settings
				/*$permissionSettingsParam = array('COMPANY' => $companies_permission, 'CHECKOUTLINK' => $payment_link_permission, 'FUNDCONFIRMATION' => $fundconfirmation_permission, 'SIGNTURE' => $signture_permission, 'BANKAUTHLINK' => $pay_auth_permission, 'TOTALNOCHECK' => $total_no_check, 'NOOFCHECKREMAINING' => $total_no_check, 'TOTALFUNDCONFIRMATION' => $total_fundconfirmation, 'REMAININGFUNDCONFIRMATION' => $total_fundconfirmation, 'TOTALPAYAUTH' => $total_payauth, 'PAYAUTHREMAINING' => $total_payauth );
				$permissionSettings = serialize($permissionSettingsParam);*/
				$permissionSettings = $permissions_company;
				
				//For company_details
				CompanyDetail::where('user_id', $user_id)
				->where('company_id', $company_id)
				->update(['total_no_check' => $total_no_check, 'no_of_check_remaining' => $no_of_check_remaining, 'total_fundconfirmation' => $total_fundconfirmation, 'remaining_fundconfirmation' => $remaining_fundconfirmation, 'total_payauth' => $total_payauth, 'payauth_remaining' => $payauth_remaining, 'companies_permission' => $companies_permission, 'payment_link_permission' => $payment_link_permission, 'signture_permission' => $signture_permission, 'pay_auth_permission' => $pay_auth_permission, 'fundconfirmation_permission' => $fundconfirmation_permission, 'permissions'=> $permissionSettings]);
				
				
				//MONTHLY_FEE
				CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'MONTHLY_FEE')
				->update(['value' => $monthly_fee]);
				
				
				
				//FUNDCONFIRMATION_FEE
				CheckFee::where('user_id', $user_id)
				->where('company_id', $company_id)
				->where('fees_name', 'FUNDCONFIRMATION_FEE')
				->update(['value' => $fundconfirmation_fee]);
				
				
				//For user_subscriptions
				// Later need to decide whta other fileds needs to update
				// Update in stripe like subscription id, trial_ends_at, subscription_ends_at etc
				
				$userSubscriptionIdArray = $this->userSubscription->getUserSubscription($sc_token);
				$userSubscriptionId = $userSubscriptionIdArray->id;
				$userSubscription = UserSubscription::find($userSubscriptionId);
				
				$trial_ends_at_old = $userSubscription->trial_ends_at;
				$subscription_ends_at_old = $userSubscription->subscription_ends_at;
				$stripe_subscription_old = $userSubscription->stripe_subscription;
				$stripe_id_old = $userSubscription->stripe_id;
				$stripe_plan_old = $userSubscription->stripe_plan;
				$amount_old = $userSubscription->amount;
				
				if($stripe_update == 1){
					//if($stripe_plan != $stripe_plan_old){
						$userSubscription->subscription($stripe_plan)
						->skipTrial()
						->swap();
					//}
					// Update the trial_ends_at
					if(strtotime($trial_ends_at) != strtotime($trial_ends_at_old)){
						$trial_ends_at = strtotime($trial_ends_at);
						$this->updateTrialEndsAtStripe($stripe_id_old, $stripe_subscription_old, $trial_ends_at);
						$userSubscription->setTrialEndDate( $trial_ends_at )->save();
						
					}
					//if($amount != $amount_old){
						$userSubs = UserSubscription::where('user_id', $user_id)
						->where('company_id', $company_id)
						->update(['amount' => $amount]);
						
					//}
				}
				// Change the subscription if post subscription_id and old subscription id does not match
				// downgrade or upgrade the plan
				/*if($stripe_plan != $stripe_plan_old){
					$userSubscription->subscription($stripe_plan)
					->skipTrial()
					->swap();
				}
				// Update the trial_ends_at
				if(strtotime($trial_ends_at) != strtotime($trial_ends_at_old)){
					$trial_ends_at = strtotime($trial_ends_at);
					$this->updateTrialEndsAtStripe($stripe_id_old, $stripe_subscription_old, $trial_ends_at);
					$userSubscription->setTrialEndDate( $trial_ends_at )->save();
					
				}
				if($amount != $amount_old){
					$userSubs = UserSubscription::where('user_id', $user_id)
					->where('company_id', $company_id)
					->update(['amount' => $amount]);
					
				}*/
				
				// Status Update
				$userDetails = UserDetail::where('user_id', $user_id)
				->update(['status_id' => $status]);
				
				$comDetail = companyDetail::where('user_id', $user_id)
				->update(['status_id' => $status]);
				
			}
			
			// Update Credit card
			if($updateCreditCardDetails == true){
								
				$userSubscriptionIdArray = $this->userSubscription->getUserSubscription($sc_token);
				$userSubscriptionId = $userSubscriptionIdArray->id;
				$userSubscription = UserSubscription::find($userSubscriptionId);
				
				
				//Card Update in stripe
				if($is_stripe_card_update == 1){
					$rescc = $this->getStripeTokenFromCardDetails($number, $exp_month, $exp_year, $cvc);
					$resccType = $rescc['type'];
					$resccValue = $rescc['value'];
					
					if($resccType == 'error'){
						return response()->json(['error' => $resccValue], 401);
					}
					
					if($resccType == 'success'){
						$userSubscription->updateCard($resccValue);
					}
				}
				
			}
			
			// Update the plan details details 
			if($updatePermissions == true){
				$settings = serialize($user_settings);
				$userDetailsToken = UserDetail::where('user_id', $user_id)
				->where('company_id', $company_id)
				->update(['permission_settings' => $settings]);
				
			}
			
			// Update batchSettings
			if($updatebatchSettings == true){
				//$batch_settings_array = array('same_day_processing_cutoff' => $batch_settings);
				$settings = serialize($batch_settings);
				Company::where('id', $company_id)
				->update(['settings' => $settings]);
				
			}
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}catch (JWTException $e) {
			// something went wrong
			return response()->json(['error' => 'could_not_get_token'], 500);
		}
		
		$token = JWTAuth::fromUser($user);
		return response()->json(array('success' => true, 'token' => $token), 200);
		//return response()->json(compact('token'));
    }
	
	
	
    /**
     * Delete the company-admin.
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function deleteCompany(Request $request, $sc_token)
    {
		try{
			$user_id = $this->getUserId($sc_token);
			$status_type = $request->get('status_type');
			
			// type delete=8
			if($status_type == 8){
				UserDetail::where('user_id', $user_id)
						->update(['status_id' => 8]);
				companyDetail::where('user_id', $user_id)
						->update(['status_id' => 8]);
			}
			//type inactive=7 (lock, disable and inactive is 2)
			if($status_type == 7){
				UserDetail::where('user_id', $user_id)
						->update(['status_id' => 7]);
				CompanyDetail::where('user_id', $user_id)
						->update(['status_id' => 7]);
				$userSubscriptionIdArray = $this->userSubscription->getUserSubscription($sc_token);
				$stripe_id = $userSubscriptionIdArray->stripe_id;
				$userSubscriptionId = $userSubscriptionIdArray->id;
				
				$userSubscription = UserSubscription::find($userSubscriptionId);
				// Change stripe_active to canceled
				UserSubscription::where('user_id', $user_id)
						->update(['stripe_active' => 4]);
				
				// Need to cancel the subscription in stripe also
				if($stripe_id){
					$userSubscription->cancelNow();
				}
			}
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		return response()->json(["sc_token" => $sc_token, "action" => $status_type]);
    }
	
	/**
     * Ghost Login for company-admin (Only superadmin can acess).
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function ghostLogin($sc_token)
    {
		
		try{
			$response = $this->user->getGhostLoginQuery($sc_token);
			
			if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $response['value'], 401]);
			}
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		// Send only some user info
		return response()->json(["user" => $response]);
	}
	
	/**
     * Create Seamlesschex Admin (admin)
     *
     * @param  $request  
     * @return \Illuminate\Http\Response
     */
	
    public function createScxAdmin(Request $request)
    {
		$saveScxAdmin = $request->get('createScxAdmin');
		$name = $request->get('name');
		$email = $request->get('email');
		$set_url = $request->get('set_url');
		// Created by Super admin or company-admin
		$created_by = $request->get('created_by');
		$ip = $request->ip();
		$role_id = $request->get('user_role');
		
		try 
			{
				// check email field
				//$field = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
				// Get all the error messages from check_messages
				$checkMessages = CheckMessage::where('form_name', 'CREATE_SCX_ADMIN')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
				
				// Validate the input using laravel
						
				$validator = Validator::make($request->all(),
					array(
						'name' => 'required',
						'email' => 'required|email|unique:users,email',
						'user_role' => 'required',
					)
				);
				if ($validator->fails())
				{
					//echo $validator->errors();
					
					$messages = $validator->messages();
					foreach ($checkMessages as $checkMessage) {
						$message = $checkMessage->message;
						$field_name = $checkMessage->field_name;
						
						if ($messages->has('email') && $email != '') {
							return response()->json(['error' => $messages->first('email')], 401);
						}
						
						if ($messages->has($field_name)) {
							return response()->json(['error' => $message], 401);
						}
					}
					
				}
				
				if($saveScxAdmin == true){
					
					$this->user->email = $email;
					$this->user->name = $name;
					$sc_token_generate = Uuid::generate();
					$this->user->sc_token = $sc_token_generate;
					$this->user->save();
					
					// Get last inserted id
					$user_id = $this->user->id;
					
					// Save the user_details
					$this->userDetail->user_id = $user_id;
					// set status=pending
					$this->userDetail->status_id = 9;
					// set role_id=seamlesschex admin
					$this->userDetail->role_id = $role_id;
					$this->userDetail->created_by = $created_by;
					
					// Save permission for the company user
					$settings = '';
					if($role_id == 2){
						$settings = $this->userPermission->getDefaultCompanySetting($role_id);
					}
					// else settings from the post need to write code that time
					$this->userDetail->permission_settings = $settings;
					$this->userDetail->ip_address = $ip;
					$this->userDetail->save();
					
					// Sent mail for setting the password
					$pass_url = $set_url.'invite-muser/'.$sc_token_generate;
					
					$userInfo = array('name' => $name, 'email' => $email, 'set_pass_url' => $pass_url);
					$sentOk = $this->sendMail(4, $userInfo);
					
					if(isset($sentOk['type']) == 'error'){
						return response()->json(['error' => $sentOk['value']], 401);
					}
				}
				
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Admin already exists.'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 if($code == 23000){
				 $errormsg = 'Email already exists';
				 return response()->json(['error' => $errormsg], 401);
			 }
			 return response()->json(['error' => $errormsg],401);
		}
		
		$token = JWTAuth::fromUser($this->user);
		
		return Response::json(['success' => true, 'token' => $token]);
		
	}
	
	/**
     * Create Company User for company-admin (superadmin and company-admin can do this).
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function createCompanyUser(Request $request)
    {
		$saveCompanyUser = $request->get('createCompanyUser');
		$email = $request->get('email');
		$mc_token = $request->get('company_admin');
		// Created by Super admin or company-admin
		$sc_token = $request->get('created_by');
		$set_url = $request->get('set_url');
		$user_settings = $request->get('user_settings');
		$ip = $request->ip();
		$role_id = 4;
		
		try 
			{
				// check email field
				//$field = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
				// Get all the error messages from check_messages
				$checkMessages = CheckMessage::where('form_name', 'CREATE_COMPANY_USER')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
				
				// Validate the input using laravel
				$validator = Validator::make($request->all(),
					array(
						'email' => 'required|email|unique:users,email',
						'company_admin' => 'required',
						'user_settings' => 'required'
					)
				);
				if ($validator->fails())
				{
					$messages = $validator->messages();
					foreach ($checkMessages as $checkMessage) {
						$message = $checkMessage->message;
						$field_name = $checkMessage->field_name;
						if ($messages->has('email') && $email != '') {
							return response()->json(['error' => $messages->first('email')], 401);
						}
						if ($messages->has($field_name)) {
							return response()->json(['error' => $message], 401);
						}
					}
				}
				// Get company_id from mc_token
				$company_id = $this->getCompanyIdFromMcToken($mc_token);
				// GET THE COMPANY SETTINGS FROM COMPANY ID
				$settingsCompany = $this->getCompanySettingsByCompanyId($company_id);
				
				if(isset($settingsCompany['error'])){
					return response()->json(array('error' => $settingsCompany['message'], 'token' => false), 401);
				}
				$permissions = unserialize($settingsCompany);
				$userRemaining = $permissions['REMAINING_USER'];
				
				if($userRemaining <=0 ){
					return response()->json(['error' => 'Please upgrade your account to add more users or Contact Support',  'details'=>'User Limit Reached', 'token' => false], 401);
				}
				
				if($saveCompanyUser == true){
					
					// Save to users
					$this->user->email = $email;
					$sc_token_generate = Uuid::generate();
					$this->user->sc_token = $sc_token_generate;
					$this->user->save();

					// Get last inserted id
					$user_id = $this->user->id;

					// Save the user_details
					$this->userDetail->user_id = $user_id;
					$this->userDetail->company_id = $company_id;
					// set status=pending
					$this->userDetail->status_id = 9;
					// Set role_name=company-user
					$this->userDetail->role_id = $role_id;
					$created_by = $this->getUserId($sc_token);
					$this->userDetail->created_by = $created_by;


					// Save permission for the company user
					$settings = $this->userPermission->getDefaultCompanySetting($role_id);
					$unserialize_settings = unserialize($settings);
					$new_settings = array();
					if($user_settings == 'VIEWPRINT'){
						foreach($unserialize_settings as $key => $un_value){
							$new_settings[$key] = 'no';		
							if($key == 'VIEWCHECK' || $key == 'PRINTCHECK'){
								$new_settings[$key] = 'yes';
							}
						}
					}
					if($user_settings == 'VIEWPRINTENTER'){
						foreach($unserialize_settings as $key => $un_value){
							$new_settings[$key] = 'no';		
							if($key == 'VIEWCHECK' || $key == 'PRINTCHECK' || $key == 'ADDCHECK'){
								$new_settings[$key] = 'yes';
							}
						}
					}
					//print_r($new_settings);
					// else settings from the post need to write code that time
					$this->userDetail->permission_settings = serialize($new_settings);
					$this->userDetail->ip_address = $ip;
					$this->userDetail->save();
					
					// Need to do for number users decrement by one
					if($userRemaining > 0 ){
						$permissions['REMAINING_USER'] = $permissions['REMAINING_USER'] - 1;
						$userRemaining = $permissions['REMAINING_USER'];
					}
					
					// Update COMPANY SETTINGS
					CompanyDetail::where('company_id', $company_id)
					->update(['permissions' => serialize($permissions)]);	
					
					// Sent mail for setting the password
					$pass_url = $set_url.'invite-muser/'.$sc_token_generate;
					
					$userInfo = array('email' => $email, 'set_pass_url' => $pass_url);
					$sentOk = $this->company->sendMail(3, $userInfo);
					
					if(isset($sentOk['type']) == 'error'){
						return response()->json(['error' => $sentOk['value']], 401);
					}
				}
				
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 if($code == 23000){
			    $errormsg = 'Email  already exists';
			    return response()->json(['error' => $errormsg], 401);
			}
			return response()->json(['error' => $errormsg], 401);
		}
		
		$token = JWTAuth::fromUser($this->user);
		$status = Status::find(9);
		$status_array = array('status_name' => $status->status_name, 'color' => $status->color, 'id' => $status->id);
		$company_name = $this->company->getCompanyDetails($company_id);
		
		$now = Carbon::now();
		$dt = Carbon::parse($now);
		$invited_date  = $dt->format('d/m/Y');
		$time = $dt->format("H:i:s");  
		
		return Response::json(['success' => true, 'token' => $token, 'email' => $email, 'status' => $status_array, 'company_name' => $company_name['value']->company_name, 'user_settings' => $new_settings, 'invited_date' => $invited_date, 'time' => $time]);
		
	}
	
	/**
     * Create Company User for company-admin (superadmin and company-admin can do this).
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function createCompanySub(Request $request)
    {
		$saveCompanySub = $request->get('createCompanySub');
		$name = $request->get('name');
		$email = $request->get('email');
		$mc_token = $request->get('company_admin');
		// Created by Super admin or company-admin
		$sc_token = $request->get('created_by');
		$ip = $request->ip();
		$created_by = $this->getUserId($sc_token);
		
		
		try 
			{
				// Get all the error messages from check_messages
				$checkMessages = CheckMessage::where('form_name', 'CREATE_COMPANY_SUB')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
				
				// Validate the input using laravel
						
				$validator = Validator::make($request->all(),
					array(
						'name' => 'required',
						'email' => 'required|email|unique:users,email',
						'company_admin' => 'required'
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
				// Get company_id from mc_token
				$company_admin = $this->getCompanyIdFromMcToken($mc_token);
				
				// GET THE COMPANY SETTINGS FROM COMPANY ID
				$settingsCompany = $this->getCompanySettingsByCompanyId($company_admin);
				
				if(isset($settingsCompany['error'])){
					return response()->json(array('error' => $settingsCompany['message'], 'token' => false), 401);
				}
				$permissions = unserialize($settingsCompany);
				$businessRemaining = $permissions['REMAINING_COMPANY'];
		
				if($businessRemaining <=0 ){
					return response()->json(['error' => 'Please upgrade your account to add more business or Contact Support',  'details'=>'Business Limit Reached', 'token' => false], 401);
				}
				
				
				if($saveCompanySub == true){
					
					// Save to users
					$this->user->email = $email;
					$this->user->name = $name;
					$sc_token_generate = Uuid::generate();
					$this->user->sc_token = $sc_token_generate;
					$this->user->save();
					
					// Get last inserted id
					$user_id = $this->user->id;
					
					// Save into companies
					$mc_token_generate = Uuid::generate();
					$this->company->mc_token = $mc_token_generate;
					$this->company->user_id = $user_id;
					$this->company->company_name = $name;
					$this->company->company_email = $email;
					$this->company->owner_id = $company_admin;
					$this->company->save();
					$company_id = $this->company->id;
					
					
					// Find the id for role_name=company-admin-companies
					$role_id = 5;
					
					// Find the id for status=active
					$status_id = 2;
					
					// Save comapny_details
					$this->companyDetail->user_id = $user_id;
					$this->companyDetail->company_id = $company_id;
					$this->companyDetail->status_id = $status_id;
					$this->companyDetail->owner_id = $company_admin;
					//$this->companyDetail->permissions = $permissionSettings;
					
					$this->companyDetail->save();
					
					// Save the user_details
					$this->userDetail->user_id = $user_id;
					$this->userDetail->company_id = $company_id;
					$this->userDetail->status_id = $status_id;
					$this->userDetail->role_id = $role_id;
					
					$this->userDetail->created_by = $created_by;
					$this->userDetail->ip_address = $ip;
					
					$user_id_for_company_admin = $this->getUserIdFromCompanyId($company_admin);
					// Save permission for the company user
					$settings = $this->userPermission->getPermissionByUserId($user_id_for_company_admin['value']);
					$this->userDetail->permission_settings = $settings;
					$this->userDetail->save();
					
					
					// Need to do for number sub company decrement by one
					if($businessRemaining > 0 ){
						$permissions['REMAINING_COMPANY'] = $permissions['REMAINING_COMPANY'] - 1;
						$businessRemaining = $permissions['REMAINING_COMPANY'];
					}
					
					// Update COMPANY SETTINGS
					CompanyDetail::where('company_id', $company_admin)
					->update(['permissions' => serialize($permissions)]);				
					
					
					// Save to check_fees
					$checkFeeData = array(
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'DAILY_DEPOSIT_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'PER_CHECK_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'MONTHLY_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'CHECK_PROCESSING_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'CHECK_VERIFICATION_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'FUNDCONFIRMATION_FEE', 'value' => 0.00 )
						
					);
					CheckFee::insert($checkFeeData);
					
					
				}
				
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 if($code == 23000){
			    $errormsg = 'Email  already exists';
			    return response()->json(['error' => $errormsg], 401);
			}
			 return response()->json(['error' => $errormsg]);
		}
		
		$token = JWTAuth::fromUser($this->user);
		
		return Response::json(['success' => true, 'token' => $token,'email' => $email, 'name' => $name]);
		
	}
	
	/**
     * Update Scx Admin
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function updateScxAdmin(Request $request, $sc_token)
    {
		$updateScxAdmin = $request->get('updateScxAdmin');
		$name = $request->get('name');
		$username = $request->get('username');
		$password = bcrypt($request->get('password'));
		$custom_password = $request->get('password');
		$cpassword = $request->get('cpassword');
		//$user_settings = $request->get('user_settings');
		// Created by Super admin 
		$created_by = $request->get('created_by');
		$status_name = $request->get('status');
		
		$statusRes = $this->company->getStatusId($status_name);
		if($statusRes['type'] == 'error'){
			return Response::json(['error' => $statusRes['value']], 401);
		}
		if($statusRes['type'] == 'success'){
			$status = $statusRes['value'];
		}
		
		
		try 
			{
				// check email field
				$field = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
				// Get all the error messages from check_messages
				$checkMessages = CheckMessage::where('form_name', 'CREATE_SCX_ADMIN')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
				
				// Validate the input using laravel
						
				$validator = Validator::make($request->all(),
					array(
						'name' => 'required',
						'username' => 'required'
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
				
				if (isset($custom_password) && $custom_password != '' && strlen($custom_password) < 8) {
					return response()->json(['error' => 'Password must be at least 8 characters'], 401);
				}
				
				if(($custom_password != $cpassword) && ($custom_password != '' || $cpassword != '')){
					return response()->json(['error' => 'Password and confirm password should match'], 401);
				}
				
				if($updateScxAdmin == true){
					
					$user_id = $this->getUserId($sc_token);
					// For users
					$user = User::find($user_id);
					$user->username = $username;
					
					$user->name = $name;
					if($custom_password != ''){
						$user->password = $password;
					}
					
					$user->save();
					
					// Update user_details
					
					// Update permission for the company user
					//$settings = serialize($user_settings);
					
					UserDetail::where('user_id', $user_id)
					->update(['created_by' => $created_by, 'status_id' => $status]);
					
				}
				
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Admin already exists.'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 if($code == 23000){
				 $errormsg = 'Username already exists';
				 return response()->json(['error' => $errormsg], 401);
			 }
			 return response()->json(['error' => $errormsg], 401);
		}
		
		$token = JWTAuth::fromUser($this->user);
		
		return Response::json(['success' => true, 'token' => $token]);
		
	}
	
	/**
     * Create Company User for company-admin (superadmin and company-admin can do this).
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function updateCompanyUser(Request $request, $sc_token)
    {
		$updateCompanyUser = $request->get('updateCompanyUser');
		$name = $request->get('name');
		$username = $request->get('username');
		$password = bcrypt($request->get('password'));
		$custom_password = $request->get('password');
		$cpassword = $request->get('cpassword');
		$company_admin = $request->get('company_admin');
		$user_settings = $request->get('user_settings');
		// Created by Super admin or company-admin
		$created_by = $request->get('created_by');
		$status = $request->get('status');
		if($status == 'delete' ){
			$status = 8;
		}
		if($status == 'active'){
			$status = 2;
		}
		if($status == 'inactive'){
			$status = 7;
		}
		
		try 
			{
				// check email field
				$field = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
				// Get all the error messages from check_messages
				$checkMessages = CheckMessage::where('form_name', 'CREATE_COMPANY_USER')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
				
				// Validate the input using laravel
						
				$validator = Validator::make($request->all(),
					array(
						'name' => 'required',
						'username' => 'required',
						'company_admin' => 'required'
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
				if($updateCompanyUser == true){
					// Need to check if the user is already exits
					// Need to write code
					$user_id = $this->getUserId($sc_token);
					//$company_id = $this->getCompanyId($sc_token);
				
					// For users
					$user = User::find($user_id);
					// Update to users
					if($field == 'email'){
						$user->email = $username;
					}
					if($field == 'username'){
						$user->username = $username;
					}
					//$user->email = $email;
					//$user->username = $username;
					$user->name = $name;
					if($custom_password != ''){
						$user->password = $password;
					}
					$sc_token_generate = Uuid::generate();
					if($user->sc_token == ''){
						$user->sc_token = $sc_token_generate;
					}
					$user->save();
					
					// Update user_details
					
					// Update permission for the company user
					$settings = serialize($user_settings);
					
					UserDetail::where('user_id', $user_id)
					->update(['company_id' => $company_admin, 'created_by' => $created_by, 'permission_settings' => $settings, 'status_id' => $status]);
					
				}
				
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		
		$token = JWTAuth::fromUser($this->user);
		
		return Response::json(['success' => true, 'token' => $token]);
		
	}
	
	/**
     * Update Company Sub for company-admin-companies
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function updateCompanySub(Request $request, $sc_token)
    {
		$updateCompanySub = $request->get('updateCompanySub');
		$name = $request->get('name');
		$email = $request->get('email');
		$mc_token = $request->get('company_admin');
		// Created by Super admin or company-admin
		$created_by = $request->get('created_by');
		$status_name = $request->get('status');
		
		$statusRes = $this->company->getStatusId($status_name);
		$status = '';

		if(isset($statusRes['type']) && $statusRes['type'] == 'error'){
			return Response::json(['error' => $statusRes['value']], 401);
		}
		if(isset($statusRes['type']) && $statusRes['type'] == 'success'){
			$status = $statusRes['value'];
		}

		try 
			{
				// check email field
				
				// Get all the error messages from check_messages
				$checkMessages = CheckMessage::where('form_name', 'CREATE_COMPANY_SUB')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
											
				
				// Validate the input using laravel
						
				$validator = Validator::make($request->all(),
					array(
						'name' => 'required',
						//'email' => 'required|email|unique:users',
						'email' => 'required',
						'company_admin' => 'required'
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
				
				if($updateCompanySub == true){
					// Need to check if the user is already exits
					// Need to write code
					$user_id = $this->getUserId($sc_token);
					$company_id = $this->getCompanyId($sc_token);
				
					// For users
					$user = User::find($user_id);
					// Update to users
					$user->email = $email;
					$user->name = $name;
					$user->save();
					
					// Update user_details
					UserDetail::where('user_id', $user_id)
					->update(['created_by' => $created_by, 'status_id' => $status]);
					
					// Update company
					$company = Company::find($company_id);
					if($company->mc_token == '' || empty($company->mc_token)){
						$mc_token_generate = Uuid::generate();
						$company->mc_token = $mc_token_generate;
					}
					// Get company_id from mc_token
					$company_admin = $this->getCompanyIdFromMcToken($mc_token);
					
					$company->owner_id = $company_admin;
					$company->company_name = $name;
					$company->company_email = $email;
					$company->save();
					
					// Update company_details
					CompanyDetail::where('company_id', $company_id)
					->update(['owner_id' => $company_admin, 'status_id' => $status]);
					
				}
				
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		
		$token = JWTAuth::fromUser($this->user);
		
		return Response::json(['success' => true, 'token' => $token]);
		
	}
	
	
	
	/**
     * Get All Default Settings for Diffrent role.
     *
     * @param  int  $role_id
     * @return \Illuminate\Http\Response
     */
	
    public function getAllDefaultCompanySetting(Request $request)
    {
		try{
			$role_id = $request->get('role_id');
			// Get company permission settings
			$user_settings = $this->userPermission->getDefaultCompanySetting($role_id);
			$user_settings_array = unserialize($user_settings);
			// Get the Check sutt off settings
			//$checkSettings = CheckSetting::find(19);
			//$check_cutoff_seetings = unserialize($checkSettings->value);
			// Get All fees amount
			$fees = $this->checkBasicFee->getDefaultFeeSetting();
			// Get all Stripe Plan
			$all_paln = $this->getAllPlanStripe();
			$amount=0;
			// Get Plan Settings like no_of_check, total_fundconfirmation etc
			$plan_settings = $this->getPlanSettings($amount);
			if($plan_settings['type'] == 'success'){
				$plan_settings_array = unserialize($plan_settings['value']);
				
			}
			if($plan_settings['type'] == 'error'){
				return response()->json(["error" => $plan_settings['value']], 401);
			}
			
			//Get all permission with label
			$default_settings = $this->userPermission->getDefaultCompanySettingArray($role_id);
			$default_settings_array = unserialize($default_settings);
			
			$settingsData = array(
								"user_settings" => $user_settings_array,
								//"check_cutoff_seetings" => $check_cutoff_seetings,
								"fees" => $fees,
								"all_paln" => $all_paln,
								"plan_settings" => $plan_settings_array,
								"default_settings" => $default_settings_array,
							);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		return response()->json(["data" => $settingsData]);
	}
	
	/**
     * Get company users by company_admin.
     *
     * @param  int  $company_admin
     * @return \Illuminate\Http\Response
     */
	
    public function getCompanyUsersByCompany(Request $request)
    {
		try{
			$getCompanyUsers = $request->get('getCompanyUsers');
			$sc_token = $request->get('sc_token');
			$mc_token = $request->get('company_admin');
			
			if($getCompanyUsers == true && $sc_token !='' && $mc_token != ''){
				// Get company_id from mc_token
				$company_admin = $this->getCompanyIdFromMcToken($mc_token);
				$response = $this->user->getCompanyUsersQueryByCompany($company_admin);
			
				if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
					return response()->json(['error' => true, 'message' => $response['value'], 401]);
				}
			
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
	
	/**
     * Get company users by sc_token.
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function getCompanyUsersByToken($sc_token)
    {
		
		try{

			$response = $this->user->getCompanyUsersQueryByScToken($sc_token);
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
			return response()->json(['error' => true, 'message' => $response['value'], 401]);
		}
		if(isset($response) && empty($response[0])){
			return response()->json(['success' => true, 'token' => false, 'message' => 'Record Not found']);
		}
		return response()->json($response);			
		
	}
	
	/**
     * Get business (company-admin-sub) by sc_token.
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function getBusinessByToken($sc_token)
    {
		
		try{
			
			$response = $this->company->getBusinessQueryByScToken($sc_token);
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
			return response()->json(['error' => true, 'message' => $response['value'], 401]);
		}
		if(isset($response) && empty($response[0])){
			return response()->json(['success' => true, 'token' => false, 'message' => 'Record Not found']);
		}
		return response()->json($response);			
		
	}
	
	/**
     * Get Plan settings
     *
     * @param 
     * @return \Illuminate\Http\Response
     */
	
	public function getPlanSettings($amountVal){
		try{
			// Get all the check settings for register
			$checkSettings = CheckSetting::where('settings_type', 'REGISTER')->get();
			$companies_permission = 'no';
			$fundconfirmation_permission = 'no';
			$payment_link_permission = 'no';
			$pay_auth_permission = 'no';
			$signture_permission = 'no';
			$no_of_check = 0;
			$total_no_check = 0;
			$total_payauth = 0;
			$total_fundconfirmation = 0;
			$settings = '';
			$amount = 0;
			if(isset($amountVal) && ($amountVal) > 0){
				$amount = $amountVal;
			}
			
			foreach ($checkSettings as $checkSetting) {
				if(($checkSetting->settings_name == 'companies_permission_amount') && (float)$checkSetting->value >= $amount && $amount > 0){
					$companies_permission = 'yes';
				}
				if(($checkSetting->settings_name == 'fundconfirmation_permission_amount') && (float)$checkSetting->value >= $amount && $amount > 0){
					$fundconfirmation_permission = 'yes';
				}
				if(($checkSetting->settings_name == 'signture_permission_amount') && (float)$checkSetting->value >= $amount && $amount > 0){
					$signture_permission = 'yes';
				}
				if(($checkSetting->settings_name == 'pay_auth_permission_amount') && (float)$checkSetting->value >= $amount && $amount > 0){
					$pay_auth_permission = 'yes';
				}
				if(($checkSetting->settings_name == 'payment_link_permission_amount') && (float)$checkSetting->value >= $amount && $amount > 0){
					$payment_link_permission = 'yes';
				}
				if($checkSetting->settings_name == 'no_of_check'){
					$no_of_check = $checkSetting->value;
				}
				if($checkSetting->settings_name == 'check_process'){
					$settings = unserialize($checkSetting->value);
				}
				if($checkSetting->settings_name == 'total_payauth'){
					$total_payauth = $checkSetting->value;
				}
				if($checkSetting->settings_name == 'total_fundconfirmation'){
					$total_fundconfirmation = $checkSetting->value;
				}
			}
			
			// Serialize the array for all settings
			$permissionSettingsParam = array( 'COMPANY' => $companies_permission,'CHECKOUTLINK' => $payment_link_permission, 'FUNDCONFIRMATION' => $fundconfirmation_permission, 'SIGNTURE' => $signture_permission, 'BANKAUTHLINK' => $pay_auth_permission, 'TOTALNOCHECK' => $no_of_check, 'NOOFCHECKREMAINING' => $no_of_check, 'TOTALFUNDCONFIRMATION' => $total_fundconfirmation, 'REMAININGFUNDCONFIRMATION' => $total_fundconfirmation, 'TOTALPAYAUTH' => $total_payauth, 'PAYAUTHREMAINING' => $total_payauth, 'check_cutoff_seetings' => $settings );
			
			$permissionSettings = serialize($permissionSettingsParam);
		}catch (JWTException $e) {
			// something went wrong
			return array('type' => 'error', 'value' => 'Connection Error');
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		return array('type' => 'success', 'value' => $permissionSettings);
	}
	
	
	
	/**
     * Display the specified resource. (admin)
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
    public function getScxAdminByToken($sc_token)
    {
		try {
			
			$response = $this->user->getScxAdminQuesryByToken($sc_token);
			
			if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $response['value'], 401]);
			}
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		
		return response()->json($response);
        
    }
	
	/**
     * Display the specified resource. (company-user)
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
    public function getCompanyUserByToken($sc_token)
    {
		try {
			
			$companyUser = $this->user->getCompanyUserQueryByToken($sc_token);
			
			if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $response['value'], 401]);
			}
			
			$default_settings = $this->userPermission->getDefaultCompanySettingArray(3);
			$default_settings = unserialize($default_settings);
			$json_response = array(); 
			$user_array = array();

			$status_id = $companyUser->status_id;
			if($status_id){
				$status = Status::find($status_id);
				$status_array = array('status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
			}
			$user_settings = unserialize($companyUser->permission_settings);
			$company_settings = unserialize($companyUser->permissions);

			$user_array = array(
				'sc_token' => $companyUser->sc_token,
				'mc_token' => $companyUser->mc_token,
				'name' => $companyUser->name,
				'company_name' => $companyUser->company_name,
				'company_admin' => $companyUser->company_id,
				'company_email' => $companyUser->company_email,
				'email' => $companyUser->email,
				'username' => $companyUser->username,
				'status' => $status,
				'user_settings' => $user_settings,
				'company_settings' => $company_settings,
				'default_settings' => $default_settings
			);

			array_push($json_response, $user_array);
		
		}catch (JWTException $e) {
			// something went wrong
			return response()->json(['error' => 'could_not_get_token'], 500);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		
		return response()->json($json_response);
        
    }
	
	
	/**
     * Display the specified resource. (company-sub)
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
    public function getCompanySubByToken($sc_token)
    {
		try {
			
			$companySub = $this->user->getCompanySubQueryByToken($sc_token);
			
			if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $response['value'], 401]);
			}
		
			$json_response = array(); 
			$user_array = array();
			$status_array = array();

			$status_id = $companySub->status_id;
			if($status_id){
				$status = Status::find($status_id);
				$status_array = array('status_name' => $status->status_name, 'status_code' => $status->status, 'color' => $status->color, 'id' => $status_id);
			}
			
			$ownerDetails = Company::find($companySub->owner_id);
			$company_admin_name = '';
			$company_admin_email = '';
			$mc_token = '';
			if(isset($ownerDetails)){
					$company_admin_name = $ownerDetails->company_name;
					$company_admin_email = $ownerDetails->company_email;
					$mc_token = $ownerDetails->mc_token;
			}
			$user_array = array(
				'sc_token' => $companySub->sc_token,
				'name' => $companySub->name,
				'company_name' => $companySub->company_name,
				'mc_token' => $mc_token,
				'company_admin_name' => $company_admin_name,
				'company_admin_email' => $company_admin_email,
				'company_email' => $companySub->company_email,
				'email' => $companySub->email,
				'status' => $status_array,
			);
		
		array_push($json_response, $user_array);
		
		}catch (JWTException $e) {
			// something went wrong
			return response()->json(['error' => 'could_not_get_token'], 500);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		
		return response()->json($json_response);
        
    }
	
	/**
     * Delete the admin.
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function deleteScxAdmin(Request $request, $sc_token)
    {
		try{
			$deleteCompanyUser = $request->get('deleteScxAdmin');
			$status_type = $request->get('status_type');
			$user_id = $this->getUserId($sc_token);
			
			if($deleteCompanyUser == true){
				// type delete=8
				UserDetail::where('user_id', $user_id)->update(['status_id' => 8]);
			}
			
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		$status = Status::find($status_type);
		$status_code = $status->status;
		return Response::json(['success' => true, 'token' => $sc_token, "action" => $status_code]);
    }
	
	/**
     * Delete the company-user.
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function deleteCompanyUser(Request $request, $sc_token)
    {
		try{
			$deleteCompanyUser = $request->get('deleteCompanyUser');
			$status_type = $request->get('status_type');
			
			$user_id = $this->getUserId($sc_token);
			
			if($deleteCompanyUser == true){
				// type delete=8
			
				UserDetail::where('user_id', $user_id)
							->update(['status_id' => 8]);
			}
			
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		//return response()->json(["sc_token" => $sc_token, "action" => $status_type]);
		return Response::json(['success' => true, 'token' => $sc_token, "action" => $status_type]);
    }
	
	/**
     * Delete the company-sub.
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function deleteCompanySub(Request $request, $sc_token)
    {
		try{
			$deleteCompanyUser = $request->get('deleteCompanySub');
			$status_type = $request->get('status_type');
			
			$user_id = $this->getUserId($sc_token);
	      
			$company_id = $this->getCompanyId($sc_token);
			
			if($deleteCompanyUser == true){
				// type delete=8
				UserDetail::where('user_id', $user_id)
							->update(['status_id' => 8]);
				CompanyDetail::where('company_id', $company_id)
							->update(['status_id' => 8]);
				
			}
			
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		//return response()->json(["sc_token" => $sc_token, "action" => $status_type]);
		return Response::json(['success' => true, 'token' => $sc_token, "action" => $status_type]);
    }
	
	/**
     * get Permissions by sc_token
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function getPermissionByToken($sc_token)
    {
		try{
			$user_id = $this->getUserId($sc_token);
					
			$permissionSettings = $this->userPermission->getPermissionByUserId($user_id);
			$permission_settings = unserialize($permissionSettings);
			
			$role_id = $this->user->getRoleId($sc_token);
			$role_id = ($role_id == 5) ? 3 : $role_id;
			$default_settings = $this->userPermission->getDefaultCompanySettingArray($role_id);
			$default_settings = unserialize($default_settings);

			$permission_array = array();
			$permission_array['data'] = array(
				'sc_token' => $sc_token,
				'default_settings' => $default_settings,
				'permission_settings' => $permission_settings
			);
	
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		return response()->json($permission_array);
    }
	/**
     * get Merchants Permissions by sc_token
     *
     * @param  int  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function getMerchantPermission($sc_token)
    {
		try{
			$user_id = $this->getUserId($sc_token);
			
			// get user permission settings
			$permissionSettings = $this->userPermission->getPermissionByUserId($user_id);
			$permission_settings = unserialize($permissionSettings);
			
			$role_id = $this->user->getRoleId($sc_token);
			$role_id = ($role_id == 5) ? 3 : $role_id;
			$default_settings = $this->userPermission->getDefaultCompanySettingArray($role_id);
			$default_settings = unserialize($default_settings);
			//echo '<pre>';
			//print_r($default_settings);
			
			//print_r($default_settings_array);
			//die;
			// get company permission settings
			if ($sc_token !== null) {
				$company_id = $this->company->getCompanyId($sc_token);
				if($company_id['type'] == 'error'){
					return response()->json(['error' => true, 'message' => $company_id['value']], 401);
				}
			}
			$user_id_from_company_id =  $this->getUserIdFromCompanyId($company_id);

			$companyDetail = UserDetail::select('permission_settings')->where('user_id', $user_id_from_company_id['value'])->first();
			$permissions = unserialize($companyDetail->permission_settings);
			//$permissionsArray = array();
			/*if(!empty($permissions)){
				foreach($permissions as $key => $pValue){
					//if($pValue == 'yes'){
						$permissionsArray[$key] = $pValue;
					//}
				}
			}*/
			//print_r($permissions);
			$default_settings_array = array();
			$response_array = array();
			if(!empty($default_settings[0]['per_set'])){
				$default_settings_per_set = $default_settings[0]['per_set'];
				foreach( $default_settings_per_set as $key => $pValue){
					$permission_name = $pValue['permission_name'];
					
					if($permissions[$permission_name] == 'yes'){
						$default_settings_array['per_set'][] = array(
							'sl_no' => $pValue['sl_no'],
							'permission_label' => $pValue['permission_label'],
							'permission_type' => $pValue['permission_type'],
							'permission_name' => $pValue['permission_name'],
							'permission_value' => $pValue['permission_value']
						);
					}
				}
			}
			array_push($response_array, $default_settings_array);
			
			$permission_array['data'] = array(
				'sc_token' => $sc_token,
				'default_settings' => $response_array,
				//'company_settings' => $permissionsArray,
				'permission_settings' => $permission_settings
			);
			
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		return response()->json($permission_array);
    }
	
	
	
	
	/**
     * get Company Permissions by sc_token
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
	
    public function getCompanyPermissionByToken($sc_token)
    {
		try{
			if ($sc_token !== null) {
				$company_id = $this->getCompanyId($sc_token);
				if($company_id['type'] == 'error'){
					return response()->json(['error' => true, 'message' => $company_id['value']], 401);
				}
			}
			$companyDetail = CompanyDetail::select('permissions')->where('company_id', $company_id)->first();
			$permissions = unserialize($companyDetail->permissions);

			$permission_array = array();
			$permission_array['data'] = array(
				'sc_token' => $sc_token,
				'company_permissions' => $permissions
			);
	
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		return response()->json($permission_array);
    }
	

    
	
	/** @Auther :Vikram Singh Prajapat
	* Created Date :17/01/17
	* Updated Date :
	* @param  string  :user_id,status_id
	*/

    public function actionAccess(Request $request){

    	try{
    		$status_id = $request->get('status_id');
    		$sc_token = $request->get('sc_token');
    		$set_url = $request->get('set_url');
			
    		// Get user_id from sc_token
			$user_id = $this->getUserId($sc_token);
			if ($status_id !== null && $status_id == 9) {
				$revokeAccess = UserDetail::where('user_id', '=',$user_id)->update(['status_id' => 2]);
			}
			if ($status_id !== null && $status_id == 2) {
				$invokeAccess = UserDetail::where('user_id', '=',$user_id)->update(['status_id' => 7]);
			}
			if ($status_id !== null && $status_id == 7) {
				
				$data = User::where('sc_token','=',$sc_token)->select('id', 'email', 'name')->first();
				
				// save tp password_resets 
				$token = Uuid::generate();
				$this->passwordReset->user_id = $user_id;
				$this->passwordReset->email = $data->email;
				$this->passwordReset->token = $token;
				$this->passwordReset->save();
				
				// Sent mail for setting the password
				$pass_url = $set_url.'set-password/'.$token;
				
				$userInfo = array('name' => $data->name, 'email' => $data->email, 'set_pass_url' => $pass_url);
				$sentOk = $this->sendMail(5, $userInfo);
				return Response::json(['success' => true, 'token' => true ,'invoke'=> 'setpass'],200);
				//$invokeAccess = UserDetail::where('user_id', '=',$user_id)->update(['status_id' => 2]);
			}
			
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return response()->json(['error' => $errormsg],401);
		}
		if(!empty($revokeAccess)){
			return Response::json(['success' => true, 'token' => true ,'revoke'=>$revokeAccess],200);
		}
		if(!empty($invokeAccess)){
			return Response::json(['success' => true, 'token' => true ,'invoke'=>$invokeAccess],200);
		}
    }
	
	/**
     * Get All company admin
     *
     * @param 
     * @return \Illuminate\Http\Response
     */
	
	public function getCompanyAdmin(){
		try{
			$response = $this->company->getCompanyAdminQuery();
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
		return response()->json(['company_admin' => $response]);
		
	}
	
	/** @Auther :Vikram Singh Prajapat
	 * Created Date :31/01/17
	 * Updated Date :
	 * @param  string  :
	 * method scope :Export  all merchant details.
	 */
    public function exportMerchantDetails(Request $request){

	    try{
    		
		   $companyAdmin = $this->company->getMerchantForExport();
			
			if(is_array($companyAdmin) && isset($companyAdmin['type']) && $companyAdmin['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $companyAdmin['value'], 401]);
			}

	        $json_response = array(); 
			$user_array = array();
			$status_array = array();
			foreach ($companyAdmin as $company) {

					$user_settings = unserialize($company->permission_settings);
					$company_settings = unserialize($company->permissions);
					$status_id = $company->status_id;
					if($status_id){
						$status = Status::find($status_id);
						$status_array = array('status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
					}

					$user_array['data'] = array(
						'sc_token' => $company->sc_token,
						'name' => $company->name,
						'business_type' => $company->business_type,
						'cname' => $company->cname,
						'email' => $company->email,
						'address' => $company->address,
						'city' => $company->city,
						'zip' => $company->zip,
						'phone' => $company->phone,
						'bank_name' => $company->bank_name,
						'bank_account_no' => $company->bank_account_no,
						'bank_routing' => $company->bank_routing,
						'authorised_signer' => $company->authorised_signer,
						'stripe_plan' => $company->stripe_plan,
						'amount' => $company->amount,
						'user_settings' => $user_settings,
						'pay_auth_permission' => $company->pay_auth_permission,
						'company_settings' => $company_settings,
						'status' => $status_array,
						'created_at' => date("d-m-Y", strtotime($company->created_at)),
						'last_invoice_at' => date("d-m-Y", strtotime($company->last_invoice_at)),
						'lastloggedin_at' => date("d-m-Y", strtotime($company->lastloggedin_at))
					);
					array_push($json_response, $user_array);
					
			}
			


		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return response()->json(['error' => $errormsg],401);
		}

		return  Excel::create('users', function($excel) use($json_response) {

		    $excel->sheet('Sheet 1', function($sheet) use($json_response) {
		    		 // Add heading row
		        $dataa = array(
		        	    'Merchant name',
		        	    'Merchant Admin', 
		        	    'address',
		        	    'City',
		        	    'Zip',
		        	    'Phone',
		        	    'Bank name',
		        	    'Bank Account no',
		        	    'Bank Routing',
		        	    'Authorised Signer',
		        	    'Industry', 
		        	    'Plan',
		        	    'Status',
		        	    'Total NO check',
		        	    'No Of check Remaining',
		        	    'Total Fund Conformation',
		        	    'Remaining Fund Conformation',
		        	    'Signature',
		        	    'Checkout Link',
		        	    'Total Pay Auth',
		        	    'Pay Auth Remaining',
		        	    'Add Check',
		        	    'Edit Check',
		        	    'Delete Check',
		        	    'View Check',
		        	    'Print Check',
		        	    'Check Out Link',
		        	    'Bank Auth Link',
		        	    'Report',
		        	    'Company',
		        	    'User',
		        	    'User Add',
		        	    'User Edit',
		        	    'User Delete',
		        	    'Pay Auth Permissions',
		        	    'Created_at',
		        	    'last_invoice_at',
		        	    'lastloggedin_at'
		        	    );
		        $sheet->fromArray(array($dataa), null, 'A1', false, false);

		        foreach ($json_response as $row) {
		        	
		            $data = array(
		            	$row['data']['cname'],
		            	$row['data']['email'],
		                
 		            	$row['data']['address'],
 		            	$row['data']['city'],
 		            	$row['data']['zip'],
                        $row['data']['phone'],

                        $row['data']['bank_name'],
 		            	$row['data']['bank_account_no'],
 		            	$row['data']['bank_routing'],
                        $row['data']['authorised_signer'],

		            	$row['data']['business_type'],
		            	$row['data']['stripe_plan'],
		            	$row['data']['status']['status_name'],
		            	$row['data']['company_settings']['TOTALNOCHECK'],
		            	$row['data']['company_settings']['NOOFCHECKREMAINING'],
		            	$row['data']['company_settings']['TOTALFUNDCONFIRMATION'],
		            	$row['data']['company_settings']['REMAININGFUNDCONFIRMATION'],
		            	
		            	$row['data']['company_settings']['SIGNTURE'],
		            	$row['data']['company_settings']['CHECKOUTLINK'],
		            	$row['data']['company_settings']['TOTALPAYAUTH'],
		            	$row['data']['company_settings']['PAYAUTHREMAINING'],
		            	$row['data']['user_settings']['ADDCHECK'] ,
		            	$row['data']['user_settings']['EDITCHECK'],
		            	$row['data']['user_settings']['DELETECHECK'],
		            	$row['data']['user_settings']['VIEWCHECK'],
		            	$row['data']['user_settings']['PRINTCHECK'],
		            	$row['data']['user_settings']['CHECKOUTLINK'],
		            	$row['data']['user_settings']['BANKAUTHLINK'],
		            	$row['data']['user_settings']['REPORT'],
		            	$row['data']['user_settings']['COMPANY'],
		            	$row['data']['user_settings']['USER'],
		            	$row['data']['user_settings']['USERADD'],
		            	$row['data']['user_settings']['USEREDIT'],
		            	$row['data']['user_settings']['USERDELETE'],
		            	$row['data']['pay_auth_permission'],
		            	$row['data']['created_at'],
					    $row['data']['last_invoice_at'],
					    $row['data']['lastloggedin_at']

		            	);
		            $sheet->fromArray(array($data), null, 'A1', false, false);
		        }
				
		    });
	    })->export('csv');
	
    }
}
