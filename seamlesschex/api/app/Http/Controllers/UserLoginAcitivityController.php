<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\UserLoginAcitivity;

class UserLoginAcitivityController extends Controller
{

    public function activityList(){

		try{

			$userLoginAcitivity = new UserLoginAcitivity();

			$response = $userLoginAcitivity->getActivityList();
			$json_response = array(); 
			$user_array = array();
			
          	foreach ($response as $res) {
				 $user_array = array(
					'name' => $res->name,
					'status_id' => $res->status_id,
					'username' => $res->username,
					'email' => $res->email,
					'ip_address' => $res->ip_address,
					'company_name' => $res->company_name,
					'in_time' =>date("H:i:s", strtotime($res->created_at)),
					'out_time' =>date("H:i:s", strtotime($res->created_at))
				);
				  array_push($json_response, $user_array);
			}
             
             // array_push($json_response, $user_array);

		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return response()->json(array('success' => true,'data'=> $json_response),200);
    }
}
