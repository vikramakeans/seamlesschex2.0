<?php

namespace App\Http\Controllers;
use App;
use App\Traits\UserTrait;
use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use Crypt;
Use DB;
use Config;
use SoapClient;
use SoapHeader;
use GuzzleHttp\Client;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\User;
use App\UserDetail;
use App\Company;
use App\CompanyDetail;
use App\Check;
use App\CheckDetail;
use App\CheckRecurrent;
use App\CheckCheckoutLink;
use App\CheckBankAuthLink;
use App\CheckVerification;
use App\CheckBank as CheckBank;
use App\CheckFee;
use App\CheckBasicFee;
use Exception;
use Response;
use App\UserRole as Role;
use App\CheckSetting as CheckSetting;
use App\UserStatus as Status;
use App\CheckMessage as CheckMessage;
use App\CheckGiactResponseCode as GiactResponseCode;
use Webpatser\Uuid\Uuid;
use Illuminate\Contracts\Encryption\DecryptException;
use Mail;
use App\CheckSignture;
use PDF;

class CheckController extends Controller
{
	use UserController;
	use UserTrait;
    private $check, $checkDetail, $checkVerification, $checkCheckoutLink, $checkRecurrent, $giactResponseCode;
	
	public function __construct(Check $check, CheckDetail $checkDetail, CheckVerification $checkVerification, CheckCheckoutLink $checkCheckoutLink, CheckBankAuthLink $checkBankAuthLink, CheckBank $checkBank, GiactResponseCode $giactResponseCode, CheckRecurrent $checkRecurrent)
	{
		$this->apiMode = Config::get('services.giact.ApiMode');
		$this->apiCurrentMode = ($this->apiMode == 'live') ? false : true;
		$this->apiUsername = Config::get('services.giact.ApiUsername');
		$this->apiPassword = Config::get('services.giact.ApiPassword');
		$this->check = $check;
		$this->checkDetail = $checkDetail;
		$this->checkVerification = $checkVerification;
		$this->checkCheckoutLink = $checkCheckoutLink;
		$this->checkBankAuthLink = $checkBankAuthLink;
		$this->checkBank = $checkBank;
		$this->giactResponseCode = $giactResponseCode;
		$this->checkRecurrent = $checkRecurrent;
	}
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    	
    /**
     * Store a newly created resource in storage.
     *	insert check to table
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveCheck(Request $request)
    {
		$saveCheck = $request->get('saveCheck');
		$name = $request->get('name');
		$splitAr = explode(' ', $name, 2);
		$firstName = isset($splitAr[0])?$splitAr[0]:'';
		$lastName = isset($splitAr[1])?$splitAr[1]:'';
		$to_name = $request->get('to_name');
		$streetAddress = $request->get('street_address');
		$phone = $request->get('phone');
		$city = $request->get('city');
		$state = $request->get('state');
		$zip = $request->get('zipcode');
		$memo = $request->get('memo1');
		$memo2 = $request->get('memo2');
		// mc_token selected in dropdown
		$mc_token = $request->get('company_admin');
		// Loggedin user sc_token
		$sc_token = $request->get('sc_token');

		$checkEmailSendReceipt = $request->get('email');
		$routingNumber = $request->get('routing_number');
		$accountNumber = $request->get('account_number');
		$confirmAccountNumber = $request->get('confirm_account_number');
		$checkNumber = $request->get('check_number');
		$checkAmount = $request->get('check_amount');
		$verifyBeforeSave = $request->get('verify_before_save');
		$isFundconfirmation = $request->get('fund_confirmation');
		$isPayAuth = $request->get('pay_auth');
		$signatureNotRequired = $request->get('signature_not_required');
		$authorisationDate = Carbon::today();
		//$authorisationDate = $request->get('authorisation_date');
		//$date = $request->get('date');
		$current = Carbon::now();
		$date = new Carbon();
		$created_at = $date;
		$month = $request->get('month');
		
		// Date for recurrent
		$dt_recurr = Carbon::parse($current);
		$currrent_date_recurr  = $dt_recurr->format('Y-m-d');
		//$time_recurr = $dt_recurr->format("H:i:s");
		
		$batch_id = 0;
		$group_id = 0;
		$fundsConfirmationResult = '';
		$itemReferenceId = '';
		// 1 = enter_check_screen, 2 = check_out_link, 3 = bank_auth_link
		$checkType = $request->get('check_type');
		
		// Check if recurrent enable
		$checkRecurrent = $request->get('check_recurrent');
		$checkRecurrentSettings = $request->get('recurrent_settings');
		
		if(!empty($checkRecurrentSettings[0]) && $checkRecurrent == 1){
			$runs_every = isset($checkRecurrentSettings[0]['runs_every']) ? $checkRecurrentSettings[0]['runs_every'] : '';
			$day = isset($checkRecurrentSettings[0]['day']) ? $checkRecurrentSettings[0]['day'] : '';
			$from_date = isset($checkRecurrentSettings[0]['from_date']) ? $checkRecurrentSettings[0]['from_date'] : '';
			$how_many_times = isset($checkRecurrentSettings[0]['how_many_times']) ? $checkRecurrentSettings[0]['how_many_times'] : '';
			$time_of_day = isset($checkRecurrentSettings[0]['time_of_day']) ? $checkRecurrentSettings[0]['time_of_day'] : '';
			$week_days = isset($checkRecurrentSettings[0]['week_days']) ? $checkRecurrentSettings[0]['week_days'] : '';
		}
		
		
		//$isGVerifyEnabled = true;
		$verifyBeforeSave = ($verifyBeforeSave) ? 1 : 0;
		$isGVerifyEnabled = ($verifyBeforeSave == 1 ? true : false);
		//$isFundsConfirmationEnabled = true;
		$isFundconfirmation = ($isFundconfirmation) ? 1 : 0;
		$isFundsConfirmationEnabled = ($isFundconfirmation == 1 ? true : false);
		
		$isGVerifyEnabled = ($isFundsConfirmationEnabled == 1 ? true : $isGVerifyEnabled);
		
		// check is enter from pay auth link
		$isPayAuth = ($isPayAuth) ? 1 : 0;
		$isPayAuthEnabled = ($isPayAuth == 1 ? true : false);
		
		// check recurrent enable or not
		$checkRecurrent = ($checkRecurrent) ? 1 : 0;
		$isRecurrentEnabled = ($checkRecurrent == 1 ? true : false);
		
		$user_ipaddress = $request->ip();
		
		// Get thet user_id from $sc_token
		$user_id = $this->getUserId($sc_token);
		
		// get the current logged in user company_id
		$company_id_logged_in = $this->getCompanyId($sc_token);

		//echo $company_id_logged_in;
		
		// need to get mc_token and then company_id
		$company_id = $this->getCompanyIdFromMcToken($mc_token);
		
		// Get owner_id from company_id
		$companyDetails = $this->getCompanyDetails($company_id);
		
		
		// Get logged in user company details
		$companyDetailsLoggedIn = '';
		if($company_id_logged_in > 0){
			$companyDetailsLoggedIn = $this->getCompanyDetails($company_id_logged_in);
		}
		
		if(isset($companyDetails['error'])){
			return response()->json(array('error' => $companyDetails['value'], 'token' => false), 401);
		}
		// Pay order of details
		$companyValues = $companyDetails['value'];
		
		
		$owner_id = $companyValues->owner_id;
		$company_name = $companyValues->company_name;
		$company_email = $companyValues->company_email;
		// Loggedin merchant details
		$companyDetailsLoggedInValues = isset($companyDetailsLoggedIn['value']) ? $companyDetailsLoggedIn['value'] : '';
		$company_name_logged_in = isset($companyDetailsLoggedInValues->company_name) ?  : '';
		$company_email_logged_in = isset($companyDetailsLoggedInValues->company_email) ?  : '';
		
		//die;
		try{
				if($saveCheck == true){
				
					$checkMessages = CheckMessage::where('form_name', 'ENTERCHECK')
												->where('type', 'ERROR')
												->orderBy('position', 'asc')
												->get();
					$validator = Validator::make($request->all(),
						array(
							'company_admin' => 'required',
							'name' => 'required',
							'street_address' => 'required',
							'city' => 'required',
							'state' => 'required',
							'zipcode' => 'required',
							'check_number' => 'required',
							'check_amount' => 'required',
							'memo1'         => 'required',
							'routing_number' => 'required',
							'account_number' => 'required',
							'confirm_account_number' => 'required|same:account_number'
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
					//die;
				// start vik
				   /*$checks =  Check::all()->filter(function($items) use($checkNumber) {
						if (Crypt::decrypt($items->checknum) == $checkNumber) {
						   return $items;
							// return response()->json(array('error'=>'Check number already exists', 'token' => false), 401);
						}
					});
					if(count($checks) >= 1){
						foreach ($checks as  $value) {
							if (Crypt::decrypt($value->checknum) == $checkNumber) {
								return response()->json(array('error'=>'Check number already exists', 'token' => false), 401);
							}
						}
					}*/
				
				// End vik


				
				
				// GET THE COMPANY SETTINGS FROM COMPANY ID
				// TOTAL NUMBER OF CHECK LIMIT Check
				// FUNDCONFIRMATION LIMIT Check
				// BANKPAYAUTH LIMIT Check
				
				$settingsCompany = $this->getCompanySettingsByCompanyId($company_id);
				
				if(isset($settingsCompany['error'])){
					return response()->json(array('error' => $settingsCompany['message'], 'token' => false), 401);
				}
				$permissions = unserialize($settingsCompany);
				
				$checkRemaining = $permissions['NOOFCHECKREMAINING'];
				$fundconfirmationRemaining = $permissions['REMAININGFUNDCONFIRMATION'];
				$payAuthRemaining = $permissions['PAYAUTHREMAINING'];
				//print_r($permissions);
				//die;
			
				if($checkRemaining <=0 ){
					return response()->json(array('error' => 'Please upgrade your account to add more checks or Contact Support', 'upgrade'=>1, 'details'=>'Check Limit Reached', 'company_id'=>$company_id, 'token' => false), 401);
				}
				
				if($fundconfirmationRemaining <=0 && $isFundsConfirmationEnabled == true){
					return response()->json(array('error' => 'Please upgrade your account to add more checks or Contact Support', 'upgrade'=>1, 'details'=>'Fund Confirmation Limit Reached', 'company_id'=>$company_id, 'token' => false), 401);
				}
				
				if($payAuthRemaining <=0  && $isPayAuthEnabled == true){
					return response()->json(array('error' => 'Please upgrade your account to add more checks or Contact Support', 'upgrade'=>1, 'details'=>'Bank Authentication Disabled. ', 'company_id'=>$company_id, 'token' => false), 401);
				}
				// Decrement the remaining check by 1 each time check entered
				if($checkRemaining > 0 ){
					$permissions['NOOFCHECKREMAINING'] = $permissions['NOOFCHECKREMAINING'] - 1;
					$checkRemaining = $permissions['NOOFCHECKREMAINING'];
				}
				if($fundconfirmationRemaining > 0 && $isFundsConfirmationEnabled == true){
					$permissions['REMAININGFUNDCONFIRMATION'] = $permissions['REMAININGFUNDCONFIRMATION'] - 1;
					$fundconfirmationRemaining = $permissions['REMAININGFUNDCONFIRMATION'];
				}
				if($payAuthRemaining > 0  && $isPayAuthEnabled == true){
					$permissions['PAYAUTHREMAINING'] = $permissions['PAYAUTHREMAINING'] - 1;
					$payAuthRemaining = $permissions['PAYAUTHREMAINING'];
				}
				// DUPLICATE CHECK
				//Check for duplicate check, if found inform user
				$isDuplicate = $this->isDuplicateCheck($company_id, $routingNumber, $checkNumber, $accountNumber);
			
				if($isDuplicate == true){
					return response()->json(array('error' => 'Duplicate Check', 'details'=>'Check Exits, Its Duplicate', 'token' => false), 401);
				}
			
				// Giact API Call for check verification and fundconfirmations
				if($isGVerifyEnabled == true || $isFundsConfirmationEnabled == true){
					
					$opts = array(	'http'=>array(	'user_agent' => 'PHPSoapClient'	));
					$context = stream_context_create($opts);
					
					$client = new SoapClient("https://api.giact.com/VerificationServices/V5/InquiriesWS.asmx?wsdl", array('stream_context' => $context, 'cache_wsdl' => WSDL_CACHE_NONE, 'trace' => 1));
					$value =array("ApiUsername" => $this->apiUsername, "ApiPassword" => $this->apiPassword );
					$header = new SoapHeader('http://api.giact.com/verificationservices/v5', 'AuthenticationHeader', $value, false);
					$client->__setSoapHeaders(array($header)); 
					
					$params = array(		
							'Inquiry' => array(
							'Customer' => array(
								'FirstName' => $firstName,
								'LastName' => $lastName
							),
							'Check' => array(
								"RoutingNumber" => $routingNumber, 
								"AccountNumber" => $accountNumber,
								"CheckNumber" => $checkNumber, 
								"CheckAmount" => $checkAmount
							),
							//'UniqueId'=>'1',
							'GVerifyEnabled' => $isGVerifyEnabled,
							'GAuthenticateEnabled' => false,
							'CustomerIdEnabled' => false,
							'VoidedCheckImageEnabled' => false,
							'FundsConfirmationEnabled' => $isFundsConfirmationEnabled
						),
						'TestMode' => $this->apiCurrentMode
					);
					
					$response = $client->PostInquiry ($params);
										
					//echo '<pre>';	
					//print_r($params);
					//print_r($response);
					//die;
					$PostInquiryResult = $response->PostInquiryResult;
					$itemReferenceId = $response->PostInquiryResult->ItemReferenceId;
					$AccountResponseCode = $response->PostInquiryResult->AccountResponseCode;
					//$AccountResponseCode = str_replace("_","",$AccountResponseCode);
					//$res = $AccountResponseCode;
					$VerificationResponse = $response->PostInquiryResult->VerificationResponse;
					$fundsConfirmationResult = $response->PostInquiryResult->FundsConfirmationResult;
					$errorMessage = 'No response from api';
					if(isset($response->PostInquiryResult->ErrorMessage)){
						$errorMessage = $response->PostInquiryResult->ErrorMessage;
					}
					
					// Get the response details by using response code
					$giact_response_details = $this->giactResponseCode->getResponseDetailsByResponseCode($AccountResponseCode);
					if(is_array($giact_response_details) && isset($giact_response_details['type']) && $giact_response_details['type'] == 'error'){
						return response()->json(array('error' => $giact_response_details['value']), 401);
					}
					// If there is no response code
					if(empty($giact_response_details)){
						return response()->json(array('error' => $errorMessage, 'details' =>null, 'code' => null, 'type' => null), 401);
						
					}
					// if there is a response code and fails
					if(!empty($giact_response_details) && $giact_response_details->pass == 0){
						return response()->json(array('error' => $giact_response_details->description, 'message' => $giact_response_details->description, 'details' => $giact_response_details->details, 'code' => $giact_response_details->code, 'type' => $giact_response_details->type), 401);
						
					}
					
					//print_r($giact_response_details);
				}
				//die;
				//FETCH RATE SHEET
				//$rateSheet = $this->fetchRateSheet($company_id);
				//UPDATE RATE SHEET
				// Need to check and write later
				
				// Update COMPANY SETTINGS
				CompanyDetail::where('company_id', $company_id)
				->update(['no_of_check_remaining' => $checkRemaining, 'remaining_fundconfirmation' => $fundconfirmationRemaining, 'payauth_remaining' => $payAuthRemaining, 'permissions' => serialize($permissions)]);
				
				
				// INSERT INTO CHECKS AND CHECK_DETAILS
				
				//Encryption
				$encryptedName = Crypt::encrypt($name);
				$encryptedNameToname = Crypt::encrypt($to_name);
				$encryptedAddress = Crypt::encrypt($streetAddress);
				$encryptedCity = Crypt::encrypt($city);
				$encryptedState = Crypt::encrypt($state);
				$encryptedZip = Crypt::encrypt($zip);
				//$encryptedPhone = Crypt::encrypt($phone);
				$encryptedMemo = Crypt::encrypt($memo);
				$encryptedMemo2 = Crypt::encrypt($memo2);
				//$encryptedRoutingNumber = Crypt::encrypt($routingNumber);
				$encryptedRoutingNumber = $routingNumber;
				$encryptedAccountNumber = Crypt::encrypt($accountNumber);
				$encryptedConfirmAccountNumber = Crypt::encrypt($confirmAccountNumber);
				//$encryptedCheckNumber = Crypt::encrypt($checkNumber);
				$encryptedCheckNumber = $checkNumber;
				$encryptedCheckEmailSendReceipt = Crypt::encrypt($checkEmailSendReceipt);
				// Save checks
				$check_token = Uuid::generate();
				$this->check->check_token = $check_token;
				$this->check->user_id = $user_id;
				$this->check->company_id = $company_id;
				$this->check->owner_id = $owner_id;
				$this->check->name = $encryptedName;
				$this->check->to_name = $encryptedNameToname;
				$this->check->address = $encryptedAddress;
				$this->check->city = $encryptedCity;
				$this->check->state = $encryptedState;
				$this->check->zip = $encryptedZip;
				//$this->check->phone = $encryptedPhone;
				$this->check->memo = $encryptedMemo;
				$this->check->memo2 = $encryptedMemo2;
				$this->check->routing = $encryptedRoutingNumber;
				$this->check->checking_account_number = $encryptedAccountNumber;
				$this->check->confirm_account_number = $encryptedConfirmAccountNumber;
				$this->check->checknum = $encryptedCheckNumber;
				$this->check->amount = $checkAmount;
				$this->check->date = $date;
				$this->check->authorisation_date = $authorisationDate;
				$this->check->month = $month;
				
				// Find the id for status=active
				//$status = Status::find(2);
				// Set the status=2 (active)
				$status_id = 2;
				
				$this->check->status_id = $status_id;
				$this->check->save();
				$check_id = $this->check->id;
				
				// Save check_details
				$this->checkDetail->user_id = $user_id;
				$this->checkDetail->company_id = $company_id;
				$this->checkDetail->owner_id = $owner_id;
				$this->checkDetail->check_id = $check_id;
				$this->checkDetail->batch_id = $batch_id;
				$this->checkDetail->group_id = $group_id;
				$this->checkDetail->email = $encryptedCheckEmailSendReceipt;
				$this->checkDetail->check_type = $checkType;
				$this->checkDetail->verify_before_save = $verifyBeforeSave;
				$this->checkDetail->is_fundconfirmation = $isFundconfirmation;
				$this->checkDetail->fundconfirmation_result = $fundsConfirmationResult;
				$this->checkDetail->item_reference_id = $itemReferenceId;
				$this->checkDetail->signature = $signatureNotRequired;
				$this->checkDetail->check_recurrent = $checkRecurrent;
				//$this->checkDetail->return_entry_date = $returnEntryDate;
				//$this->checkDetail->check_return = $check_return;
				$this->checkDetail->user_ipaddress = $user_ipaddress;
				$this->checkDetail->save();
				
				// INSERT INTO CHECK_VERFICATIONS TABLE IF VERIFY_BEFORE_SAVE OR FundsConfirmationEnabled
				// Save check_verfications
				if($isGVerifyEnabled == true || $isFundsConfirmationEnabled == true){
					$this->checkVerification->user_id = $user_id;
					$this->checkVerification->company_id = $company_id;
					$this->checkVerification->date = $date;
					$this->checkVerification->routing_no = $encryptedRoutingNumber;
					$this->checkVerification->account_no = $encryptedAccountNumber;
					$this->checkVerification->check_no = $encryptedCheckNumber;
					$this->checkVerification->amount = $checkAmount;
					$this->checkVerification->user_ipaddress = $user_ipaddress;
					$this->checkVerification->created_at = $created_at;
					$this->checkVerification->save();
				}
				
				// Save Recurrence settings
				if($isRecurrentEnabled == true){
					// Get the next run
					$next_run = $this->checkRecurrent->getNextRun($runs_every, $time_of_day, $week_days, $day, $from_date, $currrent_date_recurr);
					$this->checkRecurrent->check_id = $check_id;
					$this->checkRecurrent->user_id = $user_id;
					$this->checkRecurrent->company_id = $company_id;
					$this->checkRecurrent->runs_every = $runs_every;
					$this->checkRecurrent->day = $day;
					$this->checkRecurrent->date = $from_date;
					$this->checkRecurrent->how_many = $how_many_times;
					$this->checkRecurrent->time = $time_of_day;
					$this->checkRecurrent->weekday = $week_days;
					$this->checkRecurrent->next_run = $next_run;
					$this->checkRecurrent->save();
					
				}
				
				//SEND EMAIL
				/*$trascation_amount = 0.00;
				$sender_name = isset($company_name_logged_in) ? $company_name_logged_in: $company_email_logged_in;
				$sender_email = $company_email_logged_in;
				$receiver_name = isset($company_name) ? $company_name: $company_email;
				$receiver_email = $company_email;
				if($isGVerifyEnabled == true || $isFundsConfirmationEnabled == true){
					$userInfo = array('sender_name' => $sender_name, 'receiver_name' => $receiver_name, 'amount' => $checkAmount, 'trascation_amount' => $trascation_amount, 'memo' => $memo, 'date' => $current, 'receiver_email' => $receiver_email, 'sender_email' => $sender_email);
					//print_r($userInfo);
					//die;
					// Receiver Email
					$sentOkReceiver = $this->sendMail(1, $userInfo);
					if(isset($sentOkReceiver['type']) == 'error'){
						return response()->json(array('error' => $sentOkReceiver['value']), 401);
					}
					// Sender Email
					$sentOkSender = $this->sendMail(2, $userInfo);
					if(isset($sentOkSender['type']) == 'error'){
						return response()->json(array('error' => $sentOkSender['value']), 401);
					}
				}*/
				
			}
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		
		return Response::json(['success' => true, 'token' => $sc_token, "value" => 'check inserted successfully']);
    }
	
	/**
     * Get bank info from routing_number from api.
     * insert into check_banks if rounting_number is not present witb bank info
     * @param  int  $routingNumber
     * @return Array
     */
	public function getInfoRoutingNumber($routingNumber)
	{
		try{
			
			$apiUrl = "https://www.routingnumbers.info/api/data.json";
			$client = new Client();
			$response = $client->request('GET', $apiUrl, ['query' => 'rn='.$routingNumber]);
			$bankInfo = json_decode($response->getBody(), true);
		
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('error' => true, 'message' => $errorMessage);
		}
		return $bankInfo;
	}
	
	/**
     * insert into check_banks if rounting_number is not present with bank info
     * @param  int  $routing_number
     * @return json
     */
	public function checkRoutingNumber($routing_number)
	{
		try{
			
			$checkBank = CheckBank::where('routing', '=', $routing_number)->first();
			if ($checkBank === null) {
				$routing = $routing_number;
				$name = '';
				$zip = '';
				$city = '';
				$state_code = '';
				$phone = '';
				$address = '';
				$routingInfo = $this->getInfoRoutingNumber($routing_number);
			   if(!empty($routingInfo) && $routingInfo['message'] == 'OK' && $routingInfo['code'] == '200'){
					$this->checkBank->routing = $routing_number;
					$this->checkBank->zip = $routingInfo['zip'];
					$this->checkBank->city = $routingInfo['city'];
					$this->checkBank->name = $routingInfo['customer_name'];
					$this->checkBank->address = $routingInfo['address'];
					$this->checkBank->phone = $routingInfo['telephone'];
					$this->checkBank->state_code = $routingInfo['state'];
					$this->checkBank->save();
				   
					$routing = $routing_number;
					$name = $routingInfo['customer_name'];
					$zip = $routingInfo['zip'];
					$city = $routingInfo['city'];
					$state_code = $routingInfo['state'];
					$phone = $routingInfo['telephone'];
					$address = $routingInfo['address'];
				}
			   
			}else{
				$routing = $checkBank->routing;
				$name = $checkBank->name;
				$zip = $checkBank->zip;
				$city = $checkBank->city;
				$state_code = $checkBank->state_code;
				$phone = $checkBank->phone;
				$address = $checkBank->address;
			}
			
			$routingInfoArray = array('routing' => $routing, 'name' => $name, 'zip' => $zip, 'city' => $city, 'state_code' => $state_code, 'phone' => $phone, 'address' => $address);
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => true, 'message' => $errorMessage], 401);
		}
		return response()->json($routingInfoArray);
	}
	
	/**
     * Get company sub list for logged in customer 
     * @param  int  $sc_token
     * @return json
     */
	public function getCompanySubList($sc_token)
	{
		try{
			
			$response = $this->check->getCompanyQuerySubList($sc_token);
			
			if(is_array($response) && isset($response['type']) && $response['type'] == 'error'){
				return response()->json(['error' => true, 'message' => $response['value'], 401]);
			}
			
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => true, 'message' => $errorMessage], 401);
		}
		
		if(isset($response) && empty($response[0])){
			return response()->json(['success' => true, 'token' => false, 'message' => 'Record Not found']);
		}
		return response()->json($response);
	}
	
	
	
	/**
     * Check duplicate check.
     *
     * @param  int  $routingNumber,$company_id, $checkNumber, $accountNumber
     * @return true or error array()
     */
	 
	public function isDuplicateCheck($company_id, $routingNumber, $checkNumber, $accountNumber)
	{
		
		try{
			$dupResult = false;

			//$checks =  $this->isDuplicateCheck($company_id, $routingNumber, $checkNumber, $accountNumber);
			$checks =  Check::all()->filter(function($items) use($checkNumber, $company_id, $accountNumber, $routingNumber) {
			
			if ($items->checknum == $checkNumber && $items->company_id == $company_id && Crypt::decrypt($items->checking_account_number) == $accountNumber && $items->routing == $routingNumber) {
					return $items;
				}
			});


			if(count($checks) >= 1){
				foreach ($checks as  $value) {
				if ($value->checknum == $checkNumber && $value->company_id == $company_id && Crypt::decrypt($value->checking_account_number) == $accountNumber && $value->routing == $routingNumber) {
						$dupResult = true;
					}
				}
			}
			//$dupResult = ($checkCount > 0) ? true : false;
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('error' => true, 'message' => $errorMessage);
		}
		
		return $dupResult;
		
	}
	
	// public function duplicateCheckResults($checkNumber, $company_id, $accountNumber, $routingNumber){
		
	// 	$checks =  Check::all()->filter(function($items) use($checkNumber, $company_id, $accountNumber, $routingNumber) {

	// 	if ($items->checknum == $checkNumber || $items->company_id == $company_id || Crypt::decrypt($items->checking_account_number) == $accountNumber|| $items->routing == $routingNumber) {
	// 			return $items;
	// 		}
	// 	});
	// 	return $checks;

	// }
	
	/**
     * Get company Rate Sheet / Fees Settings by company_id.
     *
     * @param  int  $company_id
     * @return Array
     */
	 
	public function fetchRateSheet($company_id)
	{
		try{
			$checkFees = CheckFee::select('fees_name', 'value')->where('company_id', $company_id)->get();
			$checkfee_array = array();
			foreach($checkFees as $checkFee){
				$checkfee_array['data'] = array(
					'fees_name' => $checkFee->fees_name,
					'value' => $checkFee->value
				);
			}
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('error' => true, 'message' => $errorMessage);
		}
		return $checkfee_array;
	}
	
    /**
     * Display the specified resource.
     * Display a check
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getCheckById($id)
    {
		try{
			$check = Check::find($id);
			// Get check data
			$user_id = $check->user_id;
			$company_id = $check->company_id;
			$encryptedName = $check->name;
			$encryptedNameToname = $check->to_name;
			$encryptedAddress = $check->address;
			$encryptedCity = $check->city;
			$encryptedState = $check->state;
			$encryptedZip = $check->zip;
			$encryptedPhone = $check->phone;
			$encryptedMemo = $check->memo;
			$encryptedMemo2 = $check->memo2;
			$encryptedRoutingNumber = $check->routing;
			$encryptedAccountNumber = $check->checking_account_number;
			$encryptedConfirmAccountNumber = $check->confirm_account_number;
			$encryptedCheckNumber = $check->checknum;
			$checkAmount = $check->amount;
			$date = $check->date;
			$authorisationDate = $check->authorisation_date;
			$month = $check->month;
			
			$checkDetail = CheckDetail::select('id')->where('check_id', $id)->get();
			
			$id = $checkDetail[0]->id;
			$encryptedCheckEmailSendReceipt = $checkDetail[0]->email;
			$ownerId = $checkDetail[0]->owner_id;
			$batchId = $checkDetail[0]->batch_id;
			$groupId = $checkDetail[0]->group_id;
			$verifyBeforeSave = $checkDetail[0]->verify_before_save;
			$isFundconfirmation = $checkDetail[0]->is_fundconfirmation;
			// Decryption
			$decryptedName = Crypt::decrypt($encryptedName);
			$decryptedEmail = Crypt::decrypt($encryptedCheckEmailSendReceipt);
			$decryptedNameToname = Crypt::decrypt($encryptedNameToname);
			$decryptedAddress = Crypt::decrypt($encryptedAddress);
			$decryptedCity = Crypt::decrypt($encryptedCity);
			$decryptedState = Crypt::decrypt($encryptedState);
			$decryptedZip = Crypt::decrypt($encryptedZip);
			//$decryptedPhone = Crypt::decrypt($encryptedPhone);
			$decryptedPhone = '';
			$decryptedMemo = Crypt::decrypt($encryptedMemo);
			$decryptedMemo2 = Crypt::decrypt($encryptedMemo2);
			//$decryptedRoutingNumber= Crypt::decrypt($encryptedRoutingNumber);
			$decryptedRoutingNumber= $encryptedRoutingNumber;
			$decryptedAccountNumber= Crypt::decrypt($encryptedAccountNumber);
			$decryptedConfirmAccountNumber = Crypt::decrypt($encryptedConfirmAccountNumber);
			//$decryptedCheckNumber = Crypt::decrypt($encryptedCheckNumber);
			$decryptedCheckNumber = $encryptedCheckNumber;
			// Fields to send
			$check_details_array = array(
							'id' => $id,
							'company_id' => $company_id,
							'name' => $decryptedName,
							'to_name' => $decryptedNameToname,
							'street_address' => $decryptedAddress,
							'city' => $decryptedCity,
							'state' => $decryptedState,
							'zip' => $decryptedZip,
							'phone' => $decryptedPhone,
							'memo' => $decryptedMemo,
							'memo2' => $decryptedMemo2,
							'routing_number' => $decryptedRoutingNumber,
							'account_number' => $decryptedAccountNumber,
							'confirm_account_number' => $decryptedConfirmAccountNumber,
							'check_number' => $decryptedCheckNumber,
							'check_amount' => $checkAmount,
							'date' => $date,
							'authorisation_date' => $authorisationDate,
							'month' => $month,
							'email' => $decryptedEmail,
							'owner_id' => $ownerId,
							'batch_id' => $batchId,
							'group_id' => $groupId,
							'verify_before_save' => $verifyBeforeSave,
							'is_fundconfirmation' => $isFundconfirmation
						);
			
		}catch (DecryptException $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => true, 'message' => $errorMessage], 401);
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => true, 'message' => $errorMessage], 401);
		}
		return response()->json($check_details_array);
    }

    /**
     * Show the form for editing the specified resource.
     * Edit Check
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //public function editCheck(Request $request, $id)
    //{
        //
   // }

	/**
	 * Delete the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function deleteCheck(Request $request)
	{
		try{

			$data = $request->get('multipleCheckId');
			foreach ($data as  $value) {
				$updated = Check::where('check_token', $value['check_token'])
					->update(['status_id' => 8]); 
			}
			// $status_type = $request->get('status_type');
			
			// // type delete=8
			// if($status_type == 8 && $sc_token != ''){
			// 	Check::where('check_token', $value['check_token'])
			// 			->update(['status_id' => 8]);
			// }
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('error' => true, 'message' => $errorMessage);
		}
		return response()->json(["action" => $updated,'status'=>true,'message'=>'deleted']);
	}

    /**
     * SearchChecks as per the search input .
     *
     * @param 
     * @return \Illuminate\Http\Response
     */
    //public function searchChecks(Request $request)
    //{
        //
    //}
	
	
    /**
     * createCheckoutLink (checkout link) .
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function createCheckoutLink(Request $request)
    {
        $generateCheckoutLink = $request->get('generateCheckoutLink');
		$amount = $request->get('amount');
		$transactionFee = $request->get('transactionFee');
		$memo = $request->get('memo');
		$thank_you_url = $request->get('thank_you_url');
		$basicVerification = $request->get('basicVerification');
		$fundConfirmation = $request->get('fundConfirmation');
		$signature = $request->get('signature');
		$mc_token = $request->get('company_admin');
		$user_ipaddress = $request->ip();
		
		$amount = ($amount == '') ? 0 : $amount;
		$transactionFee = ($transactionFee == '') ? 0 : $transactionFee;
				
		$basicVerification = ($basicVerification == '') ? 'no' : $basicVerification;
		$fundConfirmation = ($fundConfirmation == '') ? 'no' : $fundConfirmation;
		$signature = ($signature == '') ? 'no' : $signature;
		
		$fee_type = 'BF';
		if($basicVerification == 'yes'){
			$fee_type = 'BF';
		}
		if($basicVerification == 'yes' && $fundConfirmation == 'yes'){
			$fee_type = 'FC';
		}
		// Get company_id from mc_token
		$company_admin = $this->getCompanyIdFromMcToken($mc_token);
		$companyDetails = $this->getCompanyDetails($company_admin);
		
		if(isset($companyDetails['error'])){
			return response()->json(array('error' => $companyDetails['value'], 'token' => false), 401);
		}
		$companyValues = $companyDetails['value'];
		
		
		//$checkoutToken = (isset($companyValues->checkout_token)) ? $companyValues->checkout_token : NULL;
		$checkoutToken = $companyValues->checkout_token;
		
		// Get user_id from company_id
		$userIdArray = $this->getUserIdFromCompanyId($company_admin);
		
		if(isset($userIdArray['error'])){
			return response()->json(array('error' => $userIdArray['value'], 'token' => false), 401);
		}
		
		$user_id = $userIdArray['value'];
		try{
			if($generateCheckoutLink == true){
				
				if($checkoutToken){
					$token = $checkoutToken;
				}else{
					$token = $this->generateCheckoutToken($company_admin);
				}
				$count = CheckCheckoutLink::where('checkout_token','=',$token)
				->where('company_id','=',$company_admin)
				->where('fee_type','=',$fee_type)
				->where('signture_enable','=',$signature)
				->count();
				
				if($count == 0){
					// Inssert 
					$this->checkCheckoutLink->checkout_token = $token;
					$this->checkCheckoutLink->company_id = $company_admin;
					$this->checkCheckoutLink->user_id = $user_id;
					$this->checkCheckoutLink->fee_type = $fee_type;
					$this->checkCheckoutLink->signture_enable = $signature;
					$this->checkCheckoutLink->amount = $amount;
					$this->checkCheckoutLink->memo = $memo;
					$this->checkCheckoutLink->thank_you_url = $thank_you_url;
					$this->checkCheckoutLink->transcation_fee = $transactionFee;
					$this->checkCheckoutLink->user_ipaddress = $user_ipaddress;
					$this->checkCheckoutLink->save();
				}else{
					// Update
					CheckCheckoutLink::where('company_id', $company_admin)->where('checkout_token', $token)->where('fee_type', $fee_type)->where('signture_enable', $signature)->update(['amount' => $amount, 'transcation_fee' => $transactionFee, 'memo' => $memo, 'thank_you_url' => $thank_you_url, 'user_ipaddress' => $user_ipaddress]);
				}
				$link_array = array(
					'checkout_token' => $token,
					'company_id' => $company_admin,
					'fee_type' => $fee_type,
					'signture' => $signature,
					'amount' => $amount,
					'transcation_fee' => $transactionFee,
					'memo' => $memo,
				);
				
			}
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		return response()->json(array('success' => true, 'token' => $token, 'checkout_link' => $link_array), 200);
    }
	
	/**
     * createBankAuthLink (bank auth link) .
     *
     * @param $request
     * @return \Illuminate\Http\Response
     */
    public function createBankAuthLink(Request $request)
    {
        $generateBankAuthLink = $request->get('generateBankAuthLink');
		$amount = $request->get('amount');
		$memo = $request->get('memo');
		$thank_you_url = $request->get('thank_you_url');
		$signature = $request->get('signature');
		$mc_token = $request->get('company_admin');
		$user_ipaddress = $request->ip();
		
		// Get company_id from mc_token
		$company_admin = $this->getCompanyIdFromMcToken($mc_token);
		
		$companyDetails = $this->getCompanyDetails($company_admin);
		if(isset($companyDetails['error'])){
			return response()->json(array('error' => $companyDetails['value'], 'token' => false), 401);
		}
		$companyValues = $companyDetails['value'];
		$payAuthToken = $companyValues->checkout_token;
		
		
		// Get user_id from company_id
		$userIdArray = $this->getUserIdFromCompanyId($company_admin);
		
		if(isset($userIdArray['error'])){
			return response()->json(array('error' => $userIdArray['value'], 'token' => false), 401);
		}
		
		$user_id = $userIdArray['value'];
		
		try{
			if($generateBankAuthLink == true){
				
				if($payAuthToken){
					$token = $payAuthToken;
				}else{
					$token = $this->generateCheckoutToken($company_admin);
				}
				$count = CheckBankAuthLink::where('pay_auth_token','=',$token)
				->where('company_id','=',$company_admin)
				->where('signture_enable','=',$signature)
				->count();
				if($count == 0){
					// Inssert 
					$this->checkBankAuthLink->pay_auth_token = $token;
					$this->checkBankAuthLink->company_id = $company_admin;
					$this->checkBankAuthLink->user_id = $user_id;
					$this->checkBankAuthLink->signture_enable = $signature;
					$this->checkBankAuthLink->amount = $amount;
					$this->checkBankAuthLink->memo = $memo;
					$this->checkBankAuthLink->thank_you_url = $thank_you_url;
					$this->checkBankAuthLink->user_ipaddress = $user_ipaddress;
					$this->checkBankAuthLink->save();
				}else{
					// Update
					CheckBankAuthLink::where('company_id', $company_admin)->where('pay_auth_token', $token)->where('signture_enable', $signature)->update(['amount' => $amount, 'memo' => $memo, 'thank_you_url' => $thank_you_url, 'user_ipaddress' => $user_ipaddress]);
				}
				$link_array = array(
					'pay_auth_token' => $token,
					'mc_token' => $mc_token,
					'signture' => $signature,
					'amount' => $amount,
					'memo' => $memo,
				);
			}
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		return response()->json(array('success' => true, 'token' => $token, 'bankauth_link' => $link_array), 200);
    }
	
	/**
    * generateCheckoutToken
    *
    * @desc - generate Checkout Token
    *
    * @param int $id company id
    * @return string checkout token
    *
    */
    public function generateCheckoutToken($company_id)
    {
        $token = "";
        do //loop until a unique checkout_token found
        {
            $token = $this->generateAuthToken();
			$companyToken = DB::table('companies')
						->join('company_details', 'company_details.company_id', '=', 'companies.id')
						->where('checkout_token', '=', $token)
						->select('company_details.id')
						->get();
        }
        while($companyToken);
		CompanyDetail::where('company_id', $company_id)->update(['checkout_token' => $token]);
		
        return $token;
    }
	
	
	
	public function test(){
		echo "=====herre====";
		//echo $this->getUserId($sc_token);
		//echo $this->getUserId('c2b01850-75d5-11e6-8a8a-83ef9805c8cd');
		//echo $this->checkRoutingNumber('011000390');
		echo $passwordH = Hash::make('secret');
		
		//echo $passwordB = bcrypt('secret');
		
		//echo $encrypted = Crypt::encrypt('secret');
		echo "=====herre====";
		//echo $encrypted = Crypt::decrypt($encrypted);
		//if (Hash::check('secret', $passwordH))
		//{
			//echo "matches";
		//}
		
		/*
		$nric = 'test';

		$items = Model::all()->filter(function($record) use($nric) {
			if(Crypt::decrypt($record->nric) == $nric) {
				return $record;
			}
		});
		*/
	}
	
	
	/**
     * Store a newly created resource in storage.
     *	insert check to table
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    // @Auther : Vikram singh
    // Created date :04/01/2017
    //Parameters :name,account number ,check number,routing number,amount etc.
    //Updated date :

	public function getCheckNumberDuplicates(Request $request){

    	try{
			$checkNumber = $request->get('check_number');
			$flag =false;
            if(!empty($checkNumber)){
				$checks = Check::all()->filter(function($checks) use($checkNumber) {
			        if($checks->checknum == $checkNumber) {
			        	return $checks;
				    }
				    if($checks->checknum != $checkNumber) {
			        	$flag = true;
				    }
		    	});

		    	if($flag == true){

		    		$checks = Check::orderBy('created_at','desc')->first();
			    	if(count($checks) >= 1){
			    		$check_number = '';
			    		$check_number = $checks->checknum;
			            return  response()->json(array('checksuccess' => true, 'token' => true,'check'=>$check_number), 200);
			    	}
		    	}
          		
		    	if(count($checks) >= 1){
		    		$check_number = '';
		    		foreach ($checks as  $value) {
	    			    $check_number = $value->checknum;
	    		    }
		            return   response()->json(array('checksuccessvalue' => true, 'token' => true,'check'=>$check_number), 200);
		    	}
			}
			if(empty($checkNumber)){
				$checks = Check::orderBy('created_at','desc')->first();
		    	if(count($checks) >= 1){
		    		$check_number = '';
		    		$check_number = $checks->checknum;
		            return   response()->json(array('checksuccess' => true, 'token' => true,'check'=>$check_number), 200);
		    	}
				if(count($checks) == 0){
		            return   response()->json(array('success' => true, 'token' => true), 200);
		    	}
			}
    	}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
	}
	
	public function saveCheckOutSignature(Request $request) {

		$checkoutsignature = $request->get('checkoutsignature');

		try{

			if($checkoutsignature == true){

				$user_name = $request->get('user_name');
				$checkout_token = $request->get('checkout_token');
				$company_id = $request->get('company_id');
				$signature = $request->get('signature');
				$signature_image_link = $request->get('signature_image_link');
				$user_ipaddress = $request->ip();

				$companyDetails = $this->getCompanyDetails($company_id);
				
				if(count($companyDetails['value']) > 0) {
                    $this->checkSignture->user_id = $companyDetails['value']->user_id;
		            $this->checkSignture->company_id = $company_id;
		            $this->checkSignture->owner_id = $companyDetails['value']->owner_id;
					$this->checkSignture->user_name = $user_name;
					$this->checkSignture->signture_token = $checkout_token;
					$this->checkSignture->signature_image_link = $signature_image_link;
					$this->checkSignture->user_ipaddress = $user_ipaddress;
					$this->checkSignture->save();
				}
			}

		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		return response()->json(array('success' => true), 200);
	}


    public function viewSearchCheck(Request $request){

    	$viewSearchCheckParam   = $request->get('viewSearchCheckParam');

		try{

			$data = array();
			if($viewSearchCheckParam == true){
				$mc_token    = $request->get('check_company_admin');
				//echo $mc_token;
				// Get company_id from mc_token
				if(isset($mc_token)){

					$company_admin = $this->getCompanyIdFromMcToken($mc_token);
					
					$data['company_admin'] = $company_admin;
				}
				
                

				$sc_token      = $request->get('check_company_user');
				// Get thet user_id from $sc_token
				if(isset($sc_token)){
					$user_id = $this->getUserId($sc_token);
					$data['company_user'] = $user_id;
				}
			

                if($request->get('check_from_date')){
                 	$createdAt = Carbon::parse($request->get('check_from_date'));
               		$formdate  = $createdAt->format('Y-m-d');
               		$data['from_date']         = $formdate;
                }

                if($request->get('check_to_date')){
                 	$create    = Carbon::parse($request->get('check_to_date'));
                    $to_date  = $create->format('Y-m-d');
                    $data['to_date']           = $to_date;
                }

                if(empty($request->get('check_to_date'))){
                	$now = Carbon::now();
					$dt = Carbon::parse($now);
					$data['to_date']    = $dt->format('Y-m-d');
                }
				$checks = $this->check ->searchCheck($data);

				$json_response = array(); 
				$check_array = array();
				$totalAmount = 0;
				$totalrow = 0;
				
			
				if($checks == 0){

					return response()->json(['success' => true, 'token' => false, 'message' => 'Record Not found'],401);
				}
				foreach ($checks as $value) {
	                    $totalAmount+= $value->amount;
	                    ++$totalrow;

					 	$create   = Carbon::parse($value->date);
		                $date     = $create->format('d-m-Y');
		                $create    = Carbon::parse($value->authorisation_date);
		                $authorisation_date  = $create->format('d-m-Y');
		                //get  code description using Giact response code for varifications
		        		
		       			

		              // Get the response details by using response code
		                $response_code = null;
		                $description  = null;
		        		if(isset($value->response_code)){

                            $giact_response_details = $this->giactResponseCode->getResponseDetailsByResponseCode($value->response_code);
                           
 							$description	 = $giact_response_details['description'];
 							$response_code 	= $giact_response_details['code'];

		        		}
		        		
						$check_array = array(
							'id'                     	=>  $value->id,
							//'mc_token'                     =>  $value->mc_token,
							'check_token'            	=>  $value->check_token,
							'company_id'             	=>  $value->company_id,
							'name'                   	=>  Crypt::decrypt($value->name),
							'to_name'                	=>  Crypt::decrypt($value->to_name),
							'street_address'         	=>  Crypt::decrypt($value->address),
							'city'                   	=>  Crypt::decrypt($value->city),
							'state'                  	=>  Crypt::decrypt($value->state),
							'zip'                    	=>  Crypt::decrypt($value->zip),
							'memo'                  	=>  Crypt::decrypt($value->memo),
							//'memo2'                  =>  Crypt::decrypt($value->memo2),
							'routing_number'        	=>  $value->routing,
							'account_number'         	=>  Crypt::decrypt($value->checking_account_number),
							'confirm_account_number'	=>  Crypt::decrypt($value->confirm_account_number),
							//'check_number'           =>  Crypt::decrypt($value->checknum),
							'check_number'           	=>  $value->checknum,
							'check_amount'          	=>  $value->amount,
							'date'                  	=>  $date,
							'authorisation_date'     	=>  $authorisation_date,
							'month'                  	=>  $value->month,
							'response_code'          	=>  $response_code,
							'giact_response_code_des'  	=>  $description,
							'is_fundconfirmation'        =>  $value->is_fundconfirmation
							
						);
					array_push($json_response, $check_array);
				}
				
				
			}

		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}
		return response()->json(array('success' => true,'checks'=>$json_response,'totalAmount'=>$totalAmount,'totalrow'=>$totalrow), 200);

    }

	/**
	* Show the form for editing the specified resource.
	* Edit Check
	* @param  int  $check_token
	* @return \Illuminate\Http\Response
	*/
	public function editCheck(Request $request, $check_token)
	{

		try{

			$checks = $this->getCheckByToken($check_token);
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(array('error' => $errorMessage, 'token' => false), 401);
		}

		return response()->json($checks);
	}
	/**
	* update Check
	* @param  int  $mc_token
	* @return \Illuminate\Http\Response
	*/
	public function updateCheck(Request $request,$check_token)
    {
         
		// $signatureNotRequired = $request->get('signature_not_required');
    	$updateCheck = $request->get('updateCheck');

    	try{

           if($updateCheck == true){

           	   	$company_id = $request->get('company_admin');
				//$company_id = 1;
				// $mc_token = '55f132c0-edc9-11e6-a83a-c33c583334ec';
				$name = $request->get('name');
				$to_name = $request->get('to_name');
				$streetAddress = $request->get('street_address');

				// $phone = $request->get('phone');
				$city = $request->get('city');
				$state = $request->get('state');
				$zip = $request->get('zipcode');
				$memo = $request->get('memo1');
			
				$memo2 = $request->get('memo2');

				// Loggedin user sc_token
				$sc_token = $request->get('sc_token');
				$checkEmailSendReceipt = $request->get('email');
				$routingNumber = $request->get('routing_number');
				$accountNumber = $request->get('account_number');
				$confirmAccountNumber = $request->get('confirm_account_number');
				$checkNumber = $request->get('check_number');
				$checkAmount = $request->get('check_amount');
				$verifyBeforeSave = $request->get('verify_before_save');

			    $authorisationDate = $request->get('authorisation_date');
			    $check_id = $request->get('check_id');
				$newDate = $request->get('date');

			 	$date = date("Y-m-d", strtotime($newDate));
			 	$current = Carbon::now();
				$date1 = new Carbon();
				$created_at = $date1;
			
				$month = $request->get('month');

				// $current = Carbon::now();
				// $date = new Carbon();
				// $created_at = $date;
				// $month = $request->get('month');
				
				$batch_id = 0;
				$group_id = 0;
				// $fundsConfirmationResult = '';
				$itemReferenceId = '';
				// 1 = enter_check_screen, 2 = check_out_link, 3 = bank_auth_link
				$checkType = $request->get('check_type');
				
				// Check if recurrent enable
				$checkRecurrent = $request->get('check_recurrent');
				$checkRecurrentSettings = $request->get('recurrent_settings');
				//$isGVerifyEnabled = true;
				$verifyBeforeSave = ($verifyBeforeSave) ? 1 : 0;

				$isGVerifyEnabled = ($verifyBeforeSave == 1 ? true : false);
				//$isFundsConfirmationEnabled = true;
				// $isFundconfirmation = ($isFundconfirmation) ? 1 : 0;
				// $isFundsConfirmationEnabled = ($isFundconfirmation == 1 ? true : false);
				
				$user_ipaddress = $request->ip();
				
				// Get thet user_id from $sc_token
				$user_id = $this->getUserId($sc_token);
				// Get owner_id from company_id
				$companyDetails = $this->getCompanyDetails($company_id);
				
				if(isset($companyDetails['error'])){
					return response()->json(array('error' => $companyDetails['value'], 'token' => false), 401);
				}
				$companyValues = $companyDetails['value'];
				$owner_id = $companyValues->owner_id;
				$settingsCompany = $this->getCompanySettingsByCompanyId($company_id);
				
				if(isset($settingsCompany['error'])){
					return response()->json(array('error' => $settingsCompany['message'], 'token' => false), 401);
				}
				$permissions = unserialize($settingsCompany);

				
				$checkRemaining = $permissions['NOOFCHECKREMAINING'];
				// $fundconfirmationRemaining = $permissions['REMAININGFUNDCONFIRMATION'];
				// $payAuthRemaining = $permissions['PAYAUTHREMAINING'];
				
				if($checkRemaining <=0 ){
					return response()->json(array('error' => 'Please upgrade your account to add more checks or Contact Support', 'upgrade'=>1, 'details'=>'Check Limit Reached', 'company_id'=>$company_id, 'token' => false), 401);
				}

				$status = Status::find(2);
				$status_id = $status->id;

				
	   			Check::where('check_token', $check_token)
	   			 	->update([
     						'user_id'           	 	=>$user_id,
     						'company_id'       	 	 	=>$company_id,
     						'owner_id'         	 	 	=>$owner_id,
     						'name' 						=> Crypt::encrypt($name),
     						'to_name' 					=> Crypt::encrypt($to_name),
					        'address' 					=> Crypt::encrypt($streetAddress),
					        'city' 						=> Crypt::encrypt($city),
			                'state' 					=> Crypt::encrypt($state),
					        'zip' 						=> Crypt::encrypt($zip),
					        'memo' 						=> Crypt::encrypt($memo),
							'memo2' 					=> Crypt::encrypt($memo2),
							'routing' 					=> $routingNumber,
							'checking_account_number' 	=> Crypt::encrypt($accountNumber),
							'confirm_account_number' 	=> Crypt::encrypt($confirmAccountNumber),
							'checknum' 					=> $checkNumber,
							'amount' 					=> $checkAmount,
							'date' 						=> $date,
							'authorisation_date' 		=> $authorisationDate,
							'month' 					=> $month,
							'status_id'					=> $status_id
     						]);
	   			 
			   		$checkDetail  =  CheckDetail::where('check_id', $check_id)
     				->update([
     						'user_id'            =>$user_id,
     						'company_id'         =>$company_id,
     						'owner_id'           =>$owner_id,
     						'batch_id'           =>$batch_id,
     						'group_id'           =>$group_id,
     						'email'              =>Crypt::encrypt($checkEmailSendReceipt),
     						'check_type'         =>$checkType,
     						'verify_before_save' =>$verifyBeforeSave,
     						'item_reference_id'  =>$itemReferenceId,
     						'user_ipaddress'     =>$user_ipaddress
     						]);
				
				// INSERT INTO CHECK_VERFICATIONS TABLE IF VERIFY_BEFORE_SAVE OR FundsConfirmationEnabled
				
				$this->checkVerification->user_id = $user_id;
				$this->checkVerification->company_id = $company_id;
				$this->checkVerification->date = $date;
				$this->checkVerification->routing_no = $routingNumber;
				$this->checkVerification->account_no = Crypt::encrypt($accountNumber);
				$this->checkVerification->check_no = $checkNumber;
				$this->checkVerification->amount = $checkAmount;
				$this->checkVerification->user_ipaddress = $user_ipaddress;
				$this->checkVerification->created_at = $created_at;
				$checkVerification = $this->checkVerification->save();
			}
    	}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		return   response()->json(array('success' => true, 'token' => true,'checks'=>$checkVerification), 200);
    }
	
	public function printCheck(Request $request, $check_token)
    {

	try{
		$checks = $this->getCheckByToken($check_token);
		$authorisation_date ='00/00/00';

		  	// $create   = Carbon::parse($checks[0]['authorisation_date']);
		   //  $authorisation_date     = $create->format('m/d/Y');
			// $date = date('r', $checks[0]['date']);
			// $value = new Carbon($date);
   //        	$dt  = Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('F d, Y');
   //        	echo $dt;
   //        	die;
	
		    $createdate   = Carbon::parse($checks[0]['date']);
		    $date     = $createdate->format('M dS H:i:s Y');
		   


		$html = '<style>
				 .box-header {
			        color: #444;
			        display: block;
			        padding: 10px;
			        position: relative;
			        height:90px;
			        max-width: 100%;
				}
			    .bottom-line {
				   border-bottom: 2px black solid;  
				   padding-bottom: 2px;
				}
				.divLast{
				    border-bottom: 2px black solid;  
				    padding-bottom: 2px;
				    border-right: 2px black solid;
				    height:25px;	
				}
				.font-type {
				    font-family: Helvetica Neue,Helvetica,Arial,sans-serif; 
				    font-size: 14px;
				}
				.font-type-bold {
				    font-family:Helvetica Neue,Helvetica,Arial,sans-serif; 
				    font-size: 14px;
				    font-weight: bold;
				}
				</style> 
				  <head>
          			  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
       			 </head>
		            <div>
		              <div class="box-header">
		              <div class="font-type" style="float:left;width:33.33%;">

		               '. $checks[0]['name'].'<br>
		               '. $checks[0]['address'].'<br>
		               '. $checks[0]['city'].'<br>
		               '. $checks[0]['state'].'<br>
		               '. $checks[0]['zip'].'<br>
		              </div>
		                <div class="font-type-bold " style="float:left;width:33.33%;">
		                Bank Address
		              </div>
		              <div class="font-type-bold" style="float:left;width:33.33%; margin-left:3%;">
		                  '. $checks[0]['check_number'].' 
		              </div>
		              </div>
		              <div class="box-header" style="padding-top:-1%;">
			              <div class="" style="float:left;width:20%;">
			              </div>
			                <div  style="float:left;width:80%; margin-left:70%;padding-top:-3%;">
			                 <span class="bottom-line" style="width:100%;">'. $date.'  </span>
			              </div>
		              </div>
		              <div class="box-header" style="padding-top:-9%;">
			              <div class="" style="float:left;width:20%;">
			             	Pay to the order of
			              </div>
			                <div class="divLast font-type-bold" style="float:left;width:45%;">
			               '. $checks[0]['to_name'].' 
			              </div>
			              <div class="font-type-bold" style="float:left;width:35%;margin-left:5%;">
			                <input type="text" value=" $'. $checks[0]['check_amount'].'">
			              </div>
		              </div>

		              <div class="box-header" style="padding-top:-6%;">
						<div>
							<div class="bottom-line">
								<span class=""><strong>Pay Exactly           </strong></span>
								<span class=""><strong>$ '. $checks[0]['check_amount'].'         Dollar  And sent                             //////////////////</strong></span>
							</div>
						</div>
					</div>

					<div class="box-header" style="padding-top:-7%;">
							<div style="float:left;width:50%;">
								<div >Customer Authorization date : '.$authorisation_date.'  </div>
								<div style="float:left;width:25%;">memo
									<div style="float:left;width:25%;margin-left:50%;" >
									<span class="bottom-line" style="width:300px!mportant;"> 
									'. $checks[0]['memo1'].'<span>
									</div>
								 </div>
								
							</div>
							<div style="float:left;width:50%;">
								
								<div class="font-type-bold" >Signature</div>
								<span>'. $checks[0]['name'].'</span>
							</div>
						</div>
						<div class="box-header" style="padding-top:-5%;">
							
							<div style="float:left;width:33.33%;">
								<span>"'. $checks[0]['check_number'].' "</span>
							</div>
							<div style="float:left;width:33.33%;">
								<span>"'. $checks[0]['routing_number'].'"</span>
							</div>
							<div style="float:left;width:33.33%;">
								<span>"'. $checks[0]['account_number'].'"</span>
							</div>
						</div>
			        </div>';

			$pdf =  PDF::loadHTML($html);

		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
	    return $pdf->download('myfile.pdf');
   } 

   	public function printCheckOnPaper(Request $request)
   	{

        try{

	   		$data = array();
			$data = $request->get('multipleCheckId');

			$checklist = array(); 
			$check_array = array();

			foreach ($data  as $value) {
				$checks = $this->getCheckByToken($value['check_token']);
				array_push($checklist, $checks);
			}

		$html = <<<HTML
				  <html>
				      <head>	
				            <style>
				 .box-header {
			        color: #444;
			        display: block;
			        padding: 10px;
			        position: relative;
			        height:90px;
			        max-width: 100%;
				}
			    .bottom-line {
				   border-bottom: 2px black solid;  
				   padding-bottom: 2px;
				}
				.divLast{
				    border-bottom: 2px black solid;  
				    padding-bottom: 2px;
				    border-right: 2px black solid;
				    height:25px;	
				}
				</style> 
				      </head>
				      <body>
HTML;
		foreach ($checklist  as $list) {

			$authorisation_date ='00/00/00';
			// $create   = Carbon::parse($checks[0]['authorisation_date']);
		 //    $authorisation_date     = $create->format('m/d/Y');
		    $createdate   = Carbon::parse($checks[0]['date']);
		    $date     = $createdate->format('M dS H:i:s Y');
		
			 $html .= '<div style="page-break-inside:avoid;">
			 <head>
          			  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
       			 </head>
		            <div>
		              <div class="box-header">
		              <div class="" style="float:left;width:33.33%;">

		               '. $list[0]['name'].'<br>
		               '. $list[0]['address'].'<br>
		               '. $list[0]['city'].'<br>
		               '. $list[0]['state'].'<br>
		               '. $list[0]['zip'].'<br>
		              </div>
		                <div class="" style="float:left;width:33.33%;">
		                Bank Address
		              </div>
		              <div class="" style="float:left;width:33.33%; margin-left:3%;">
		                  '. $list[0]['check_number'].' 
		              </div>
		              </div>
		              <div class="box-header" style="padding-top:-1%;">
			              <div class="" style="float:left;width:20%;">
			              </div>
			                <div  style="float:left;width:80%; margin-left:70%;padding-top:-3%;">
			                 <span class="bottom-line" style="width:100%;">'.$date.'  </span>
			              </div>
		              </div>
		              <div class="box-header" style="padding-top:-9%;">
			              <div class="" style="float:left;width:20%;">
			             	Pay to the order of
			              </div>
			                <div class="divLast" style="float:left;width:45%;">
			               '. $list[0]['to_name'].' 
			              </div>
			              <div class="" style="float:left;width:35%;margin-left:5%;">
			                <input type="text" value=" $'. $list[0]['check_amount'].'">
			              </div>
		              </div>

		              <div class="box-header" style="padding-top:-6%;">
						<div>
							<div class="bottom-line">
								<span class=""><strong>Pay Exactly</strong></span>
								<span class=""><strong>$ '. $list[0]['check_amount'].'  Dollar  And sent //////////////////</strong></span>
							</div>
						</div>
					</div>

					<div class="box-header" style="padding-top:-7%;">
							<div style="float:left;width:50%;">
								<div >Customer Authorization date : '.$authorisation_date.'  </div>
								<div style="float:left;width:25%;">memo
									<div style="float:left;width:25%;margin-left:50%;" >
									<span class="bottom-line" style="width:100px!mportant;"> 

									 '. $list[0]['memo1'].'<span>
									</div>
								 </div>
								
							</div>
							<div style="float:left;width:50%;">
								
								<div >Signature</div>
								<span>'. $list[0]['name'].'</span>
							</div>
						</div>
						<div class="box-header" style="padding-top:-5%;">
							<div style="float:left;width:33.33%;">
							<span>"'. $list[0]['check_number'].'"</span>
						
							</div>
							<div style="float:left;width:33.33%;">
								<span>"'. $list[0]['routing_number'].' "</span>
							</div>
							<div style="float:left;width:33.33%;">
								<span>"'. $list[0]['account_number'].'"</span>
							</div>
						</div></div></div>';
			}

					$html .= '</body></html>';

					$pdf =  PDF::loadHTML($html);
				
			
			}catch(Exception $e){
				$code = $e->getCode();
				$message = $e->getMessage();
				$errorMessage = $message ." ".$code;
				return response()->json(['error' => $errorMessage], 401);
			}	
		return $pdf->download('myfile.pdf');
	}

	public function printCheckOnPlainPaper(Request $request){

		  try{

	   		$data = array();
			$data = $request->get('multipleCheckId');

			$checklist = array(); 
			$check_array = array();

			foreach ($data  as $value) {
				$checks = $this->getCheckByToken($value['check_token']);
				array_push($checklist, $checks);
			}

		$html = <<<HTML
				  <html>
				      <head>
				    <style>
	*{
		box-sizing: border-box;
	}
	body{
		background-color: #ea8f8f;	
	}
	.box-main{
		max-width: 700px;
		margin: auto;
		color: #000;
		font-size: 100%;
		font-family: "Helvetica", sans-serif;
		width: 95%;
		padding: 15px 0;
	}
	.row-pdf{
		float: left;
		width: 100%;
		max-width: 700px;
	}	
	.text-center{
		text-align: center;
	}
	.box-border{
		border: 5px solid #000;
	}
	.checkbox-field{
		background-image: url(http://54.200.206.176/v2/admin/images/blank_check_large.png);
		background-position: center center;
		background-size: 100% auto;
		background-repeat: no-repeat;
		height: 319px;
		width: 100%;
		position: relative;
		max-width: 700px;
		float: left;
	}
	.checkbox-field > div{
		position: absolute;
		z-index: 1;
	}
	.endorse-box{
		background-image: url(http://54.200.206.176/v2/admin/images/endorse-larg.png);
		background-position: center center;
		background-size: 100% 100%;
		background-repeat: no-repeat;
		height: 319px;
		width: 100%;
		position: relative;		
		max-width: 700px;
		float: left;
	}
	.checkbox-field .UserNameDiv {
		left: 4.7%;
		top:6%;
		width: 400px;
	}
	.checkbox-field .UserEmailDiv {
		left: 4.7%;
		top: 12%;
		width: 50%;
	}
	.checkbox-field .UserAddressDiv {
		left: 4.7%;
		top: 18%;
		width: 50%;
	}
	.checkbox-field .CityNameDiv {
		left: 4.7%;
		top:24%;
		width: 50%;
	}
	.checkbox-field .CheckNumberDiv {
		left: 80%;
		top: 9%;
		background-color: #dcf1f6;
		height: 37px;
		line-height: 35px;
		width: 17%;
	}
	.checkbox-field .EnterCheckDateDiv {
		left: 67%;
		text-align: right;
		top: 23%;
		width: 15%;
	}
	.checkbox-field .CompanyNameDiv {
		top: 38%;
		left: 15%;
		width: 60%;
	}
	.checkbox-field .CheckAmountDiv {
		left: 80%;
		top: 37%;
		width: 16%;
	}
	.checkbox-field .amountInWords {
		left: 5%;
		text-align: left;
		top: 50%;
		width:73%;
		text-transform: capitalize;
	}
	.checkbox-field .custom-authorization {
		left: 5%;
		text-align: left;
		top: 60%;
		width: 70%;
	}
	.checkbox-field .DescriptionDiv-one {
		top: 70%;
		left: 10%;
		width: 35%;
	}
	.checkbox-field .DescriptionDiv-two {
		top: 76%;
		left: 10%;
		width: 35%;
	}
	.checkbox-field .UserSignatureDiv {
		left: 53%;
		top: 76%;
		width: 43%;
	}
	.checkbox-field .RoutingDiv {
		top: 84%;
		left: 7%;
		width: 18.5%;
		background-color: #dcf1f6;
		line-height: 30px;
	}
	.checkbox-field .CheckingAccountDiv {
		top: 84%;
		left: 27%;
		width: 22%;
		background-color: #dcf1f6;
		line-height: 30px;
	}
	.checkbox-field .ConfirmAccountDiv {
		top: 84%;
		left: 51%;
		width: 22%;
		background-color: #dcf1f6;
		line-height: 30px;
	}

</style>
				      </head>
				      <body>
HTML;
			foreach ($checklist  as $list) {
		
			 $html .= '<div class="box-main">
	<div class="row-pdf text-center">
		<p><strong>A check printed on any paper is a legally valid check.</strong></p>
	</div>
	<div class="row-pdf box-border" style="margin-bottom: 50px; padding: 5px; background-color: #fff;">
		<p>If depositing at a bank then: Cut out the front of the check and endorse the back.</p>
	</div>
	<div class="checkbox-field" style="margin-bottom: 50px;">
		<div class="UserNameDiv">User Name</div>
		<div class="UserEmailDiv">User Email Address</div>
		<div class="UserAddressDiv">User Adress</div>
		<div class="CityNameDiv">City, State, Pin</div>
		<div class="CheckNumberDiv text-center">Check number</div>
		<div class="EnterCheckDateDiv">02/27/2017</div>
		<div class="CompanyNameDiv">Company Name</div>
		<div class="CheckAmountDiv">Amount</div>
		<div class="amountInWords">Pay Exactly one thousand two hundred fifty four  Dollars And Cents ///////</div>
		<div class="custom-authorization">Customer authorization obtained: 02/27/2017</div>
		<div class="DescriptionDiv-one">Memo1</div>
		<div class="DescriptionDiv-two">Memo2</div>
		<div class="UserSignatureDiv">User Signature</div>
		<div class="RoutingDiv">Routing</div>
		<div class="CheckingAccountDiv">Acc No.</div>
		<div class="ConfirmAccountDiv">Conf no.</div>
	</div>
	<div class="endorse-box" style="margin-bottom: 50px; ">
		
	</div>
	<div class="row-pdf box-border" style="margin-bottom: 50px; padding: 5px; background-color: #fff;">
		<p>If you deposit by taking a picture then: Take a picture of front and back after endorsing.</p>
	</div>
	

</div>';
			}

					$html .= '</body></html>';

					$pdf =  PDF::loadHTML($html);
				
			
			}catch(Exception $e){
				$code = $e->getCode();
				$message = $e->getMessage();
				$errorMessage = $message ." ".$code;
				return response()->json(['error' => $errorMessage], 401);
			}	
		return $pdf->download('myfile.pdf');
	}
	
}
