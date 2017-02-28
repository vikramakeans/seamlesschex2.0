<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use GuzzleHttp\Client;

class UserStep extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'cname', 'email', 'token', 'password', 'business_type', 'phone', 'city', 'state', 'zip', 'website'
    ];
	
	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'token',
    ];
	
	public function getUserStepID($user_token){
		try{
			$userStep = UserStep::select('id')->where('token', $user_token)->first();
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $userStep->id;
	}
	
	/**
	* Insert Into Klaviyo API
	* 
	* @param  customer information and $listId, $api_key
	* @return Array
	*/
	public function createUserInKlaviyo($listId, $api_key, $email, $properties = array())
	{
		try{
			
			$params = array(
			   "api_key" => $api_key,
			   "email" => $email,
			   "properties" => json_encode($properties),
			   "confirm_optin" => "false"
			);
			$apiUrl = "https://a.klaviyo.com/api/v1/list/$listId/members"; 
			$client = new Client();
			$response = $client->request('POST', $apiUrl,  array('form_params' => $params));
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('error' => true, 'message' => $errorMessage);
		}
		//return json_decode($response);
		return $response;
	}
}
