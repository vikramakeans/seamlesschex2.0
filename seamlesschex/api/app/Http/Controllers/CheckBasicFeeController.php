<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use DB;
use App\Http\Controllers\Controller;
use App\CheckBasicFee as CheckBasicFee;

class CheckBasicFeeController extends Controller
{
	private $checkBasicFee;
    
	public function __construct(CheckBasicFee $checkBasicFee)
	{
		$this->checkBasicFee = $checkBasicFee;
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
    public function getFee()
    {
        //$fees = DB::table('check_basic_fees')->get();
		
		try{
			
			$fees = CheckBasicFee::all();
			$json_response = array(); 
			$fee_array = array();
			foreach ($fees as $fee) {
					$fee_array['data'][] = array(
						'id' => $fee->id,
						'fees_name' => $fee->fees_name,
						'value' => $fee->value,
						'updated_at' => $fee->updated_at
						
					);
			}
			array_push($json_response, $fee_array);
			
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
    public function createFee(Request $request)
    {
		try{
			// Get all post values
			$fees_name = $request->get('fees_name');
			$value = $request->get('value');
					
			$this->checkBasicFee->fees_name = $fees_name;
			$this->checkBasicFee->value = $value;
			$save = $this->checkBasicFee->save();
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
    public function updateFee(Request $request, $id)
    {
		try{
			// Get all post values
			$fees_name = $request->get('fees_name');
			$value = $request->get('value');
			
			$fee = CheckBasicFee::find($id);
			
			$fee->fees_name = $fees_name;
			$fee->value = $value;
			$save = $fee->save();
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
     * Remove the specified resource from Fee.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteFee($id)
    {
        try{
			$fee = CheckBasicFee::find($id);
			$fee->delete();
				
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
    public function getFeeById($id)
    {
        try{
			$fee = CheckBasicFee::find($id);
			
			$fee_array = array();
			
			$fee_array['data'] = array(
						'id' => $fee->id,
						'fees_name' => $fee->fees_name,
						'value' => $fee->value,
						'updated_at' => $fee->updated_at
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
		return response()->json($fee_array);
    }
}
