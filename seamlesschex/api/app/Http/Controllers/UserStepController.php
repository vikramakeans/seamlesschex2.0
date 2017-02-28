<?php

namespace App\Http\Controllers;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use Exception;
use Response;
use Config;
use Validator;
use App\Http\Requests;
use App\User;
use App\UserDetail;
use App\UserStep;
use App\UserPermission as UserPermission;
use App\CheckMessage as CheckMessage;
use Webpatser\Uuid\Uuid;
use GuzzleHttp\Client;

class UserStepController extends Controller
{
    use UserTrait;
	private $user, $userDetail, $userStep, $userPermission;
	
	public function __construct(User $user, UserDetail $userDetail, UserStep $userStep, UserPermission $userPermission)
	{
		$this->user = $user;
		$this->userDetail = $userDetail;
		$this->userStep = $userStep;
		$this->userPermission = $userPermission;
		$this->listId = Config::get('services.klaviyo.listid1');
		$this->api_key = Config::get('services.klaviyo.apikey');
	}

    /**
     * Inserting basic userdetails from step1
     *
     * @return \Illuminate\Http\Response
     */
	public function registerBasic(Request $request)
	{
        // Get all the input
		$name = $request->get('name');
		$action = $request->get('action');
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
		//$password = $request->get('password');
		$custom_password = $request->get('password');
		$phone = $request->get('phone');
		$website = $request->get('website');
		$privacypolicy = $request->get('privacypolicy');
		$sc_token = $request->get('sc_token');
		$ip = $request->ip();
		
		$status = 0;
		
		try{
			if($action == 'create'){
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
						'email' => 'required|email|unique:users|unique:user_steps',
						//'email' => 'required|email|unique:user_steps',
						'website' => 'required',
						'saddress' => 'required',
						'city' => 'required',
						'state' => 'required',
						'zip' => 'required',
						'business_type' => 'required',
						'phone' => 'required|numeric',
						'password' => 'required|min:8',
						'privacypolicy' => 'required',
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
					//$message = $validator->errors();
					//return response()->json(['error' => $message], 401);
					
				}
				
				// Insert to klaviyo api
				$properties = array('$email' => $email, '$title' => $name, '$phone_number' => $phone, '$organization' => $business_type, 'password' => $custom_password, 'website' => $website, '$city' => $city, '$region' => $state, '$zip' => $zip, '$first_name' => $first_name, '$last_name' => $last_name, '$address1' => $saddress);
				$listId = $this->listId;
				$api_key = $this->api_key;
				
				$response = $this->userStep->createUserInKlaviyo($listId, $api_key, $email, $properties);
				//echo '<pre>';
				//print_r($response);
				//die;
				
				if($response){
					// Save to user_steps
					$token = Uuid::generate();
					$this->userStep->token = $token;
					$this->userStep->name = $name;
					$this->userStep->cname = $cname;
					$this->userStep->business_type = $business_type;
					$this->userStep->email = $email;
					$this->userStep->password = $password;
					$this->userStep->saddress = $saddress;
					$this->userStep->city = $city;
					$this->userStep->state = $state;
					$this->userStep->zip = $zip;
					$this->userStep->phone = $phone;
					$this->userStep->website = $website;
					$this->userStep->ip_address = $ip;
					$this->userStep->status = $status;
					$this->userStep->save();
					
					// Save to user and user_details
					$sc_token = $token;
					$this->user->sc_token = $sc_token;
					$this->user->email = $email;
					$this->user->password = $password;
					$this->user->name = $name;
					$this->user->save();
					// Get last inserted id
					$user_id = $this->user->id;
					
					// Save the user_details
					$this->userDetail->user_id = $user_id;
					$this->userDetail->company_id = 0;
					$this->userDetail->status_id = 0;
					$this->userDetail->role_id = 3;
					
					$user_settings = $this->userPermission->getDefaultCompanySetting(3);
					// else settings from the post need to write code that time
					$this->userDetail->permission_settings = $user_settings;
					
					$this->userDetail->ip_address = $ip;
					$this->userDetail->save();
				}
				$token = (string)$token;
				return response()->json(["success" => true, "token" => $token]);
			}
			if($action == 'update' && $sc_token != ''){
				
				// Validate the input using laravel
				// If try to update exiting email 
				$validator = Validator::make($request->all(),
					array(
						'email' => 'required|email|unique:users'
					)
				);
				if ($validator->fails())
				{
					$messages = $validator->messages();
					if ($messages->has('email') && $email != '') {
						return response()->json(['error' => $messages->first('email')], 401);
					}
				}
				
				$step_user_id = $this->userStep->getUserStepID($sc_token);
				if(is_array($step_user_id) && isset($step_user_id['type']) && $step_user_id['type'] == 'error'){
					return response()->json(['error' => true, 'message' => $step_user_id['value'], 401]);
				}
				// Insert to klaviyo api if email is changed
				$properties = array('$email' => $email, '$title' => $name, '$phone_number' => $phone, '$organization' => $business_type, 'password' => $custom_password, 'website' => $website, '$city' => $city, '$region' => $state, '$zip' => $zip, '$first_name' => $first_name, '$last_name' => $last_name, '$address1' => $saddress);
				$listId = $this->listId;
				$api_key = $this->api_key;
				
				$response = $this->userStep->createUserInKlaviyo($listId, $api_key, $email, $properties);
				if($response){
					$userStep = UserStep::find($step_user_id);
					$userStep->name = $name;
					$userStep->cname = $cname;
					$userStep->business_type = $business_type;
					$userStep->email = $email;
					$userStep->password = $password;
					$userStep->saddress = $saddress;
					$userStep->city = $city;
					$userStep->state = $state;
					$userStep->zip = $zip;
					$userStep->phone = $phone;
					$userStep->website = $website;
					$userStep->ip_address = $ip;
					$userStep->status = $status;
					$userStep->save();
					
					// Get user_id from sc_token and update the details
					$user_id = $this->getUserId($sc_token);
					$user = User::find($user_id);
					$user->name = $name;
					$user->email = $email;
					$user->password = $password;
					$user->save();
					
				}
				$token = (string)$sc_token;
				return response()->json(["success" => true, "token" => $token]);
			}
				
		}catch (Exception $exception){
			 //$errormsg = 'Database error! ' . $exception->getCode();
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errorMessage = $message." ".$code;
			 return response()->json(['error' => $errorMessage, 'token' => false], 401);
		}
		
		
    }
	
	/**
     * Display the specified resource.
     *
     * @param  string  $sc_token
     * @return \Illuminate\Http\Response
     */
    public function getUserByToken($sc_token)
    {
		
		try {
			
			$userStep = UserStep::select('token', 'name', 'email', 'cname', 'saddress', 'city', 'state', 'zip', 'business_type', 'website', 'phone', 'password')->where('token' , $sc_token)->first();
			
			
			$user_array = array();
			if(isset($userStep)){
				$user_array = array(
					'sc_token' => $userStep->token,
					'name' => $userStep->name,
					'cname' => $userStep->cname,
					'email' => $userStep->email,
					'password' => $userStep->password,
					'saddress' => $userStep->saddress,
					'city' => $userStep->city,
					'state' => $userStep->state,
					'zip' => $userStep->zip,
					'phone' => $userStep->phone,
					'website' => $userStep->website,
					'business_type' => $userStep->business_type
				);
			}
		
		
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		
		return response()->json($user_array);
        
    }
	
	

    
}
