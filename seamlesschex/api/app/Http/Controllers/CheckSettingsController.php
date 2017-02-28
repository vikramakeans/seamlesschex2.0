<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use DB;
use App\Http\Controllers\Controller;
use App\CheckSetting as CheckSetting;

class CheckSettingsController extends Controller
{
	private $checkSetting;
    
	public function __construct(CheckSetting $checkSetting)
	{
		$this->checkSetting = $checkSetting;
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
    public function getEmail()
    {
 
		try{
			
			$emails = CheckSetting::all();
			
			$json_response = array(); 
			$email_array = array();
			foreach ($emails as $email) {
					$email_array['data'][] = array(
						'id' => $email->id,
						'settings_type' => $email->settings_type,
						'settings_name' => $email->settings_name,
						'value' => $email->value,
						'updated_at' => $email->updated_at
						
					);
			}
			array_push($json_response, $email_array);
			
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
     * creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createEmail(Request $request)
    {
		try{
			// Get all post values
			$settings_type = $request->get('settings_type');
			$settings_name = $request->get('settings_name');
			$value = $request->get('value');
			
			
			$this->checkSetting->settings_type = $settings_type;
			$this->checkSetting->settings_name = $settings_name;
			$this->checkSetting->value = $value;
			$save = $this->checkSetting->save();
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
     * Editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEmail(Request $request, $id)
    {
		try{
			// Get all post values
			$settings_type = $request->get('settings_type');
			$settings_name = $request->get('settings_name');
			$value = $request->get('value');
					
			$email = CheckSetting::find($id);
			
			$email->settings_type = $settings_type;
			$email->settings_name = $settings_name;
			$email->value = $value;
			$save = $email->save();
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
     * Remove the specified resource from Email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteEmail($id)
    {
        try{
			$email = CheckSetting::find($id);
			$email->delete();
				
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
		return response()->json(["id" => $id, "action" => 'deleted']);
    }
	
	/**
     * Get the data for specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getEmailById($id)
    {
		
        try{
			$email = CheckSetting::find($id);
			$json_response = array(); 
			$email_array = array();
			
			$email_array['data'] = array(
				'id' => $email->id,
				'settings_type' => $email->settings_type,
				'settings_name' => $email->settings_name,
				'value' => $email->value
				//'updated_at' => $email->updated_at
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
		return response()->json($email_array);
    }
}

