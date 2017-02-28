<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CheckGiactResponseCode extends Model
{
    /**
     * get Response Details by response_code
     *
     * @param  string  $response_code
     * @return \Illuminate\Http\Response
     */
	
    public function getResponseDetailsByResponseCode($response_code)
    {
		try{
			
			$checkGiactResponseCode = CheckGiactResponseCode::where('code', $response_code)->first();
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errormsg = $message ." ".$code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $checkGiactResponseCode;
    }
}
