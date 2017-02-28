<?php

namespace App\Http\Controllers;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use App\Http\Requests;
//use App\Http\Requests\PasswordFormRequest;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use DB;
use Validator;
use App\User;
use App\UserStep;
use App\PasswordReset as PasswordReset;
use App\UserDetail;
use App\Company;
use App\CompanyDetail;
use App\UserMailchimpInfo;
use App\UserSubscription;
use App\CheckFee;
use App\SubscriptionPlanDetail as SubscriptionPlanDetail;
use Exception;
use Response;
use Config;
use Hash;
use Mailchimp;
use App\UserRole as Role;
use App\UserStatus as Status;
use App\CheckSetting as CheckSetting;
use App\CheckMessage as CheckMessage;
use App\UserPermission as UserPermission;
use Webpatser\Uuid\Uuid;
use App\UserLoginAcitivity;
use Carbon\Carbon;

class AuthenticateController extends Controller
{
    use UserController;
	use UserTrait;
	private $user, $userDetail, $company, $companyDetail, $userMailchimpInfo, $userSubscription, $checkFee, $userPermission,$passwordReset, $userStep;
	private $jwtauth;
	
		
	public function __construct(User $user, UserDetail $userDetail, Company $company, CompanyDetail $companyDetail, UserMailchimpInfo $userMailchimpInfo, CheckFee $checkFee, UserSubscription $userSubscription, UserPermission $userPermission, PasswordReset $passwordReset, UserStep $userStep, JWTAuth $jwtauth)
	{
		//$this->beforeFilter('csrf', ['on' => '']);
		$this->user = $user;
		$this->userDetail = $userDetail;
		$this->company = $company;
		$this->companyDetail = $companyDetail;
		$this->userMailchimpInfo = $userMailchimpInfo;
		$this->checkFee = $checkFee;
		$this->userPermission = $userPermission;
		$this->userSubscription = $userSubscription;
		$this->passwordReset = $passwordReset;
		$this->userStep = $userStep;
		$this->listId = Config::get('services.mailchimp.listid');
		$this->listId = Config::get('services.klaviyo.listid2');
		$this->api_key = Config::get('services.klaviyo.apikey');
		// causing issue for register token
		//$this->middleware('jwt.auth', ['except' => ['authenticate, register']]);
		$this->jwtauth = $jwtauth;
	}

	/**
	 * Get user id by email or username.
	 *
	 * @param  string  $username or $email
	 * @return \Illuminate\Http\Response else null
	*/

	public function getUserIdByEmailOrUsername($username, $field)
	{
		$user = User::select('id')->where($field, $username)->first();
		//return json_decode($user[0]->id);
		return $user->id;
		
	}
	
	

	/**
	 * Return a JWT
	 *
	 * @return Response
	*/

	public function authenticate(Request $request)
	{
		// get our email input
		$email = $request->input('email');
		$password = $request->input('password');
		
		// check email field
		$field = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
		
		try {
			// verify the credentials and create a token for the user
			//$credentials = $request->only('email', 'password');
			// For email only
			//if (! $token = JWTAuth::attempt($credentials))
			
			//For username and email
			if (! $token = JWTAuth::attempt(array($field=> $request->input('email'), 'password' => $request->input('password')))) {
				$checkMessage = CheckMessage::select('message')->where('form_name', 'LOGIN')->where('position', 1)->first();
				$message = $checkMessage->message;
				return response()->json(['error' => $message], 401);
			}
			
			$userId = $this->getUserIdByEmailOrUsername($request->input('email'), $field);
			$status = $this->getUserStatus($userId);
			// for temporary 1= trialing
			$status = ($status == 1 || $status == 0) ? 2 : $status;
			// if active!=2
			if(($status != 2 && $userId && $token = JWTAuth::attempt(array($field=> $request->input('email'), 'password' => $request->input('password'))))){
				$checkMessage = CheckMessage::select('message')->where('position', 2)->where('form_name', 'LOGIN')->first();
				$message = $checkMessage->message;
				return response()->json(['error' => $message], 401);
			}
			// update the last logged in time
			
			$now = Carbon::now();
			$dt = Carbon::parse($now);
			$lastloggedin_at  = $dt->format('Y-m-d H:i:s');
			UserDetail::where('user_id', $userId)->update(['lastloggedin_at' => $lastloggedin_at]);
			
			// Login Activity start
           /* $userLoginAcitivity = new UserLoginAcitivity();
			$userLoginAcitivity->user_id = $userId;
			$userLoginAcitivity->company_id = $companyId[0]->id;
			$userLoginAcitivity->ip_address = $request->ip();
			$userLoginAcitivity->save();*/
			// Login Activity End
			
		} catch (JWTException $e) {
			// something went wrong
			return response()->json(['error' => 'could_not_create_token'], 500);
		}

		// if no errors are encountered we can return a JWT
		return response()->json(compact('token'));
	}
	
	/**
	 * Return the authenticated user_steps
	 *
	 * @return Response
	*/

	/*public function getAuthenticatedStepUser(Request $request)
	{
		// get our email input
		$email = $request->input('email');
		$password = $request->input('password');
		
		// check email field
		$field = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
		try{
			$userStep = UserStep::select('password', 'token', 'name', 'email', 'status', 'created_at')->where($field , $email)->where('status', 0)->first();
			if (! $token = JWTAuth::attempt(array($field => $email, 'password' => $password)) && $userStep && Hash::check($password, $userStep->password)) {
				//return response()->json(['token' => $userStep->token]);
				$userData = array(
							"sc_token" => (string)$userStep->token,
							"name" => $userStep->name,
							"email" => $userStep->email,
							"status" => $userStep->status,
							"role" => 3,
							"created_at" =>strtotime($userStep->created_at),
						);		
				// Send only some user info
				//return response()->json(["user" => $userData, "token" => (string)$userStep->token]);
				
			}
		}catch (Exception $exception){
			 //$errormsg = 'Database error! ' . $exception->getCode();
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errorMessage = $message." ".$code;
			 return response()->json(['error' => $errorMessage, 'token' => false], 401);
		}
		return response()->json(["token" => (string)$userStep->token]);
	}*/

	/**
	 * Return the authenticated user
	 *
	 * @return Response
	*/

	public function getAuthenticatedUser()
	{
		
		try {
			$user = JWTAuth::parseToken()->authenticate();
			
			if (! $user = JWTAuth::parseToken()->authenticate()) {
				return response()->json(['user_not_found'], 404);
			}

		} catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

			return response()->json(['token_expired'], $e->getStatusCode());

		} catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

			return response()->json(['token_invalid'], $e->getStatusCode());

		} catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

			return response()->json(['token_absent'], $e->getStatusCode());

		}
		
		$sc_token = $user->sc_token;
		if($sc_token == '' || empty($sc_token)){
			$sc_token_generate = Uuid::generate();
			$user->sc_token = $sc_token_generate;
			$user->save();
		}
		
		$userDetail = UserDetail::select('role_id', 'status_id', 'permission_settings')->where('user_id', $user->id)->first();
		$role_id = $userDetail->role_id;
		$status_id = $userDetail->status_id;
		$permissions = unserialize($userDetail->permission_settings);
		//$role = Role::find($role_id);
		//$status = Status::find($status_id);
		
		// For Temporary
		$company = Company::select('id', 'mc_token')->where('user_id', $user->id)->first();
		if(isset($company)){
			$mc_token = $company->mc_token;
			$company_id = $company->id;
			
			if($mc_token == '' || empty($mc_token)){
				$mc_token_generate = Uuid::generate();
				Company::where('id', '=',$company_id)->update(['mc_token' => $mc_token_generate]);
			}
		}
		
		$userData = array(
							"sc_token" => (string)$user->sc_token,
							"name" => $user->name,
							"email" => $user->email,
							"status" => $status_id,
							"role" => $role_id,
							"permissions" => $permissions,
							"created_at" =>strtotime($user->created_at),
						);
		
		// the token is valid and we have found the user via the sub claim
		//return response()->json(compact('user'));		
		// Send only some user info
		return response()->json(["user" => $userData]);
	}

	/**
	 * Return the register token
	 *
	 * @return Response
	*/

	public function register(Request $request)
	{
		// Get all the input
		$name = $request->get('name');
		$cname = $request->get('cname');
		$splitAr = explode(' ', $cname, 2);
		$first_name = isset($splitAr[0]) ? $splitAr[0] : '';
		$last_name = isset($splitAr[1]) ? $splitAr[1] : '';
		$saddress = $request->get('saddress');
		$city = $request->get('city');
		$state = $request->get('state');
		$zip = $request->get('zip');
		$business_type = $request->get('business_type');
		$email = $request->get('email');
		$password = bcrypt($request->get('password'));
		$custom_password = $request->get('password');
		$phone = $request->get('phone');
		$website = $request->get('website');
		$plan = $request->get('plan');
		$amount = $request->get('amount');
		$number = $request->get('number');
		$exp_month = $request->get('exp_month');
		$exp_year = $request->get('exp_year');
		$cvc = $request->get('cvc');
		$ip = $request->ip();
		$user_token = $request->get('user_token');
		$sc_token = $request->get('sc_token');
		
		try 
			{				
				// Get all the check settings for register
				$checkSettings = CheckSetting::where('settings_type', 'REGISTER')->get();
				$companies_permission = 'no';
				$fundconfirmation_permission = 'no';
				$payment_link_permission = 'no';
				$pay_auth_permission = 'no';
				$signture_permission = 'no';
				$no_of_check = 0;
				$total_payauth = 0;
				$total_fundconfirmation = 0;
				$settings = '';
				foreach ($checkSettings as $checkSetting) {
					if(($checkSetting->settings_name == 'companies_permission_amount') && (float)$checkSetting->value >= $amount){
						$companies_permission = 'yes';
					}
					if(($checkSetting->settings_name == 'fundconfirmation_permission_amount') && (float)$checkSetting->value >= $amount){
						$fundconfirmation_permission = 'yes';
					}
					if(($checkSetting->settings_name == 'signture_permission_amount') && (float)$checkSetting->value >= $amount){
						$signture_permission = 'yes';
					}
					if(($checkSetting->settings_name == 'pay_auth_permission_amount') && (float)$checkSetting->value >= $amount){
						$pay_auth_permission = 'yes';
					}
					if(($checkSetting->settings_name == 'payment_link_permission_amount') && (float)$checkSetting->value >= $amount){
						$payment_link_permission = 'yes';
					}
					if($checkSetting->settings_name == 'no_of_check'){
						$no_of_check = $checkSetting->value;
					}
					if($checkSetting->settings_name == 'check_process'){
						$settings = $checkSetting->value;
					}
					if($checkSetting->settings_name == 'total_payauth'){
						$total_payauth = $checkSetting->value;
					}
					if($checkSetting->settings_name == 'total_fundconfirmation'){
						$total_fundconfirmation = $checkSetting->value;
					}
				}
				
				// Serialize the array for all settings
				$permissionSettingsParam = array( 'COMPANY' => $companies_permission, 'CHECKOUTLINK' => $payment_link_permission, 'FUNDCONFIRMATION' => $fundconfirmation_permission, 'SIGNTURE' => $signture_permission, 'BANKAUTHLINK' => $pay_auth_permission, 'TOTALNOCHECK' => $no_of_check, 'NOOFCHECKREMAINING' => $no_of_check, 'TOTALFUNDCONFIRMATION' => $total_fundconfirmation, 'REMAININGFUNDCONFIRMATION' => $total_fundconfirmation, 'TOTALPAYAUTH' => $total_payauth, 'PAYAUTHREMAINING' => $total_payauth );
				$permissionSettings = serialize($permissionSettingsParam);
				
				
				// Get the stripe token
				$stripeToken = $request->get('stripeToken');
				if($stripeToken !=''){
					// Save to users
					/*$this->user->email = $email;
					$this->user->name = $name;
					$this->user->password = $password;
					//Generate sc_token
					$sc_token_generate = Uuid::generate();
					$this->user->sc_token = $sc_token_generate;
					$this->user->save();
					
					// Get last inserted id
					$user_id = $this->user->id;*/
					// Get user_id from sc_token
					$user_id = $this->getUserId($sc_token);
					
					
					// Save into companies
					$this->company->user_id = $user_id;
					$mc_token_generate = Uuid::generate();
					$this->company->mc_token = $mc_token_generate;
					$this->company->company_name = $name;
					$this->company->cname = $cname;
					$this->company->business_type = $business_type;
					$this->company->company_email = $email;
					$this->company->address = $saddress;
					$this->company->city = $city;
					$this->company->state = $state;
					$this->company->zip = $zip;
					$this->company->phone = $phone;
					$this->company->website = $website;
					$this->company->settings = $settings;
					$this->company->save();
					$company_id = $this->company->id;
					
					// Find the id for role_name=company-admin
					$role_id = 3;
					
					// Find the id for status=trailing
					$status_id = 1;
										
					/*$this->userDetail->user_id = $user_id;
					
					// Save the user_details
					$this->userDetail->company_id = $company_id;
					$this->userDetail->status_id = $status_id;
					$this->userDetail->role_id = $role_id;
					
					$user_settings = $this->userPermission->getDefaultCompanySetting(3);
					// else settings from the post need to write code that time
					$this->userDetail->permission_settings = $user_settings;
					
					$this->userDetail->ip_address = $ip;
					$this->userDetail->save();*/
					
					// Save comapny_details
					//$subscriptionPlanDetail = SubscriptionPlanDetail::find(4);
					//$total_no_check = $subscriptionPlanDetail->no_of_check;
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
					
					$this->companyDetail->permissions = $permissionSettings;
					
					$this->companyDetail->status_id = $status_id;
					$this->companyDetail->save();
					$last_company_detail_id = $this->companyDetail->id;
					
					
					
					// Save to check_fees
					$checkFeeData = array(
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'DAILY_DEPOSIT_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'PER_CHECK_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'MONTHLY_FEE', 'value' => $amount ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'CHECK_PROCESSING_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'FUNDCONFIRMATION_FEE', 'value' => 0.00 ),
						array('user_id'=>$user_id, 'company_id'=> $company_id, 'fees_name'=> 'CHECK_VERIFICATION_FEE', 'value' => 0.00 )
						
					);
					CheckFee::insert($checkFeeData);
					
					
					// Save to user_subscriptions
					$this->userSubscription->user_id = $user_id;
					$this->userSubscription->amount = $amount;
					$this->userSubscription->save();
					$last_subscription_id = $this->userSubscription->id;
								
					// Create customer in stripe and subscribing to the selected plan
					$response_stripe = $this->userSubscription->subscription($plan)->create($stripeToken, [
						'email' => $email, 'description' => 'subscribe to '.$plan
					]);
					
					$response = $this->userSubscription->getUserSubscription($sc_token);
					$next_invoice_date = $this->getNextInvoiceDate($response->stripe_id);
					
					// Update user_details
					UserDetail::where('user_id', $user_id)
					->update(['company_id' => $company_id, 'status_id' => $status_id, 'role_id' => $role_id, 'stripe_plan' => $plan, 'amount' => $amount, 'stripe_id' => $response->stripe_id, 'last_invoice_at' => $next_invoice_date]);
					
					// Insert into Klaviyo and update user_steps
					$properties = array('$email' => $email, '$title' => $name, '$phone_number' => $phone, '$organization' => $business_type, 'password' => $custom_password, 'website' => $website, '$city' => $city, '$region' => $state, '$zip' => $zip, '$first_name' => $first_name, '$last_name' => $last_name, '$address1' => $saddress);
					$listId = $this->listId;
					$api_key = $this->api_key;
					$response = $this->userStep->createUserInKlaviyo($listId, $api_key, $email, $properties);
					// Update status=1 in step_users (i.e step2 completed)
					UserStep::where('token', $user_token)->update(['status' => 1]);			
				
				}
				
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 //$errormsg = 'Database error! ' . $exception->getCode();
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errorMessage = $message." ".$code;
			 return response()->json(['error' => $errorMessage, 'token' => false], 401);
		}
		
		//return response()->json(['user_created']);
		$token = JWTAuth::fromUser($this->user);
		//return Response::json(compact('token'));
		return Response::json(['success' => true, 'token' => $token]);
		
	}
	
	
	
	public function test(Request $request){
		/*$permissionSettings = array(
							'COMPANY' => 'no',
							'CHECKOUTLINK' => 'yes',
							'FUNDCONFIRMATION' => 'no',
							'SIGNTURE' => 'no',
							'PAYAUTH' => 'no',
							'TOTALNOCHECK' => 3,
							'NOOFCHECKREMAINING' => 3,
							'TOTALFUNDCONFIRMATION' => 0,
							'REMAININGFUNDCONFIRMATION' => 0,
							'TOTALPAYAUTH' => 0,
							'PAYAUTHREMAINING' => 0
							);
		//$json = json_encode($permissionSettings);
		$serialize = serialize($permissionSettings);
		//$permissionSettings->toJson();
		
		//echo $permissionSettings;
		UserDetail::where('user_id', 4)
				->where('company_id', 1)
				->update(['permission_settings' => $serialize]);
				
		CompanyDetail::where('user_id', 4)
				->where('company_id', 1)
				->update(['permissions' => $serialize]);
		//echo $json;
		echo $serialize;
		
		
		echo $request->ip();
		
		
		$companyAdmin = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
			->join('user_subscriptions', 'users.id', '=', 'user_subscriptions.user_id')
            ->join('companies', 'user_details.company_id', '=', 'companies.id')
            ->join('company_details', 'companies.id', '=', 'company_details.company_id')
			->where('user_details.role_id', '=', 3)
			//->whereNotNull('user_details.permission_settings')
            ->select('users.sc_token', 'users.name', 'users.email', 'user_details.company_id', 'user_details.role_id', 'user_details.permission_settings', 'companies.cname', 'company_details.status_id', 'company_details.total_no_check', 'company_details.no_of_check_remaining', 'company_details.total_fundconfirmation', 'company_details.remaining_fundconfirmation', 'company_details.total_payauth', 'company_details.payauth_remaining', 'company_details.payment_link_permission', 'company_details.signture_permission', 'company_details.pay_auth_permission',  'company_details.fundconfirmation_permission', 'company_details.permissions', 'user_subscriptions.stripe_plan')
            ->get();
		
		
		//echo '<pre>';
		//print_r($companyAdmin);
		
		$json_response = array(); 
		$user_array = array();
		foreach ($companyAdmin as $key => $company) {
				$permission_settings = unserialize($company->permission_settings);
				$user_array['users'][] = array(
					'sc_token' => $company->sc_token,
					//'user_settings' => $permission_settings
					'settings' => $permission_settings
				);
		}
		array_push($json_response, $user_array);
		//print_r($json_response);
		echo json_encode($json_response);*/
		
		$user_permissions = DB::table('user_permissions')
				->where('role_id', '=', 3)
				->select('permission_name', 'permission_value')
				->get();
		
		//print_r($user_permissions);
		$settings = array();
		foreach($user_permissions as $permission){
			$settings[$permission->permission_name] = $permission->permission_value;
		}
		//print_r($settings);
		
		return serialize($settings);
		
	}
	
	/**
	 * @param  string  $email
	 * Parameters :email and verify email from db
	 */
  
	public function checkEmailForgetPassword(Request $request){

		try{
			$email = $request->get('email');
			$set_url = $request->get('set_url');
			$checkMessages = CheckMessage::where('form_name', 'FORGET_PASSWORD')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
				
			// Validate the input using laravel
			$validator = Validator::make($request->all(),
				array(
					'email' => 'required|email',
					'set_url' => 'required'
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
			
			// check valid email or not
			if ($email !== null) {
				$data = $this->user->checkEmailForgetPasswordQuery($email);
				if($data == 0){
					return response()->json(['error' => 'Email Address Not Available.'], 401);
				}
				$user = User::where('email','=', $email)->select('id')->first();
				$user_id = $user->id;
				
				// save tp password_resets 
				$token = Uuid::generate();
				$this->passwordReset->user_id = $user_id;
				$this->passwordReset->email = $email;
				$this->passwordReset->token = $token;
				$this->passwordReset->save();
				
				
				// Sent mail for setting the password
				$pass_url = $set_url.'set-password/'.$token;
				
				$userInfo = array('email' => $email, 'set_pass_url' => $pass_url);
				$sentOk = $this->sendMail(5, $userInfo);
				
				if(isset($sentOk['type']) == 'error'){
					return response()->json(['error' => $sentOk['value']], 401);
				}
			}
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => true, 'message' => $errormsg],401);
		}
		return Response::json(['success' => true, 'token' => $email]);
	}
		
	/**@Auther :Vikram Singh Prajapat
	 * Created Date :12/01/17
	 * Updated Date :
	 * @param  string  $confirm_token
	 * Parameters :sc_token or link  and verity merchant link from the urle
	 */
  
	public function  checkPasswordLink(Request $request){

		try{
			$sc_token = $request->get('confirm_token');
			$token = $request->get('token');
			// For invite user setting password
			// check valid confirm_token and user is pending else invalid token
			if ($sc_token !== null) {
				//$data = User::where('sc_token','=',$sc_token)->count();
				$data = $this->user->setPassURLCheck($sc_token);
				if($data == 0){
					return response()->json(['error' => true, 'message' => 'Your set password link has expired.'], 401);
				}
				return Response::json(array(['success' => true, 'token' => $sc_token]),200);
			}
			// For forget password
			if ($token !== null) {
				$data = $this->passwordReset->setForgetPassURLCheck($token);
				if($data == 0){
					return response()->json(['error' => true, 'message' => 'Your set password link has expired.'], 401);
				}
				return Response::json(array(['success' => true, 'token' => $token]),200);
			}
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => true, 'message' => $errormsg],401);
		}
		
	}
	
	/**@Auther :Vikram Singh Prajapat
	* Created Date :12/01/17
	* Updated Date :
	* @param  string  $sc_token
	* Parameters :Password,confirm password
	*/
    Public function setPassword(Request $request){

		$sc_token = $request->get('sc_token');
		$token = $request->get('token');
		$password = $request->get('password');
		$cpassword = $request->get('cpassword');

    	try{
			$checkMessages = CheckMessage::where('form_name', 'CREATE_PASSWORD')
											->where('type', 'ERROR')
											->orderBy('position', 'asc')
											->get();
				
			// Validate the input using laravel
			$validator = Validator::make($request->all(),
				array(
					'password' => 'min:8|required',
					'cpassword' => 'required|min:8',
				)
			);
			
			
				
			if ($validator->fails())
			{
				$messages = $validator->messages();
				foreach ($checkMessages as $checkMessage) {
					$message = $checkMessage->message;
					$field_name = $checkMessage->field_name;
					
					if ($messages->has('password') && $password != '') {
						return response()->json(['error' => $messages->first('password')], 401);
					}
					if ($messages->has('cpassword') && $cpassword != '') {
						return response()->json(['error' => 'The confirm password must be at least 8 characters.'], 401);
					}
					
					if ($messages->has($field_name)) {
						return response()->json(['error' => $message], 401);
					}
				}
			}
			
			if ($cpassword != $password) {
				return response()->json(['error' => 'The confirm password and password must match.'], 401);
			}
			
    		
			if ($sc_token !== null) {
				$bpassword   = bcrypt($password);
				// Get user_id from sc_token
				$user_id = $this->getUserId($sc_token);
				
				User::where('id', '=', $user_id)->update(['password' => $bpassword]);
				// Change the status=active
				UserDetail::where('user_id', '=',$user_id)->update(['status_id' => 2]);
				
				return Response::json(['success' => true, 'token' => $sc_token], 200);
			}
			
			if ($token !== null) {
				
				$bpassword   = bcrypt($password);
				// Get user_id from token
				$user = PasswordReset::where('token','=', $token)->select('user_id')->first();
				$user_id = $user->user_id;				
				User::where('id', '=', $user_id)->update(['password' => $bpassword]);
				$token_new = '';
				$status = $this->getUserStatus($user_id);
				// Change the status=active
				if($status == 7){
					UserDetail::where('user_id', '=',$user_id)->update(['status_id' => 2]);
					$token_new = Uuid::generate();
					PasswordReset::where('token', '=', $token)->update(['token' => $token_new]);
				}
				
				return Response::json(['success' => true, 'token' => $token_new], 200);
			}
			
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return response()->json(['success' => true, 'error' => $errormsg], 401);
		}
       
	  
    }
	
	/**
	 * Return the admin details
	 * @param $sc_token
	 * @return Response
	*/

	public function getAdminByToken($sc_token)
	{
		try {
			
			$response = $this->user->getAdminQueryByToken($sc_token);
		
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return response()->json(['success' => true, 'error' => $errormsg], 401);
		}
		if(isset($response) && empty($response[0])){
			return response()->json(['success' => true, 'error' => 'Token Error'], 401);
		}
		return response()->json($response);
	}
	
	/**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateAdminDetails(Request $request, $sc_token)
    {
		
		try {
			// Get all the input
			$updateAdminDetails = $request->get('updateAdminDetails');
			$name = $request->get('name');
			$password = bcrypt($request->get('password'));
			$custom_password = $request->get('password');
			
			$user_id = $this->getUserId($sc_token);
			
			if($updateAdminDetails == true){
				if($user_id){
					// For users
					$user = User::find($user_id);
				}
				$user->name = $name;
				if($custom_password){
					$user->password = $password;
				}
				$user->save();
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

		return Response::json(['success' => true, 'token' => $sc_token],200);
	}
		
}
