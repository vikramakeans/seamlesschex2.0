<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Exception;
use Response;
Use DB;
use App\Http\Controllers\Controller;
use App\UserStatus as UserStatus;

class UserStatusController extends Controller
{
   private $userStatus;
   
   public function __construct(UserStatus $userStatus)
	{
		$this->userStatus = $userStatus;
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
      public function getStatus()
    {
        //$Status = DB::table('user_statuses')->get();
		
		try{
			
			$statuses = UserStatus::all();
			$json_response = array(); 
			$status_array = array();
			foreach ($statuses as $status) {
					$status_array['data'][] = array(
						'id' => $status->id,
						'status' => $status->status,
						'status_name' => $status->status_name,
						'color' => $status->color
												
					);
			}
			array_push($json_response, $status_array);
			
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
    public function createStatus(Request $request)
    {
		
		try{
			// Get all post values
			$status = $request->get('status');
			$status_name = $request->get('status_name');
			$color = $request->get('color');
			
			$this->userStatus->status = $status;		
			$this->userStatus->status_name = $status_name;
			$this->userStatus->color = $color;
			$save = $this->userStatus->save();
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
    public function updateStatus(Request $request, $id)
    {
		try{
			// Get all post values
			$statuse = $request->get('status');
			$status_name = $request->get('status_name');
			$color = $request->get('color');
			
			$status = UserStatus::find($id);
			
			$status->status = $statuse;
			$status->status_name = $status_name;
			$status->color = $color;
			$save = $status->save();
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
     * Get the data for specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getStatusById($id)
    {
        try{
			$status = UserStatus::find($id);
			$json_response = array(); 
			$status_array = array();
			
			$status_array['data'] = array(
						'id' => $status->id,
						'status' => $status->status,
						'status_name' => $status->status_name,
						'color' => $status->color
						
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
		return response()->json($status_array);
    }
     
     
     
     
}
