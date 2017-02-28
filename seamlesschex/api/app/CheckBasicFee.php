<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CheckBasicFee extends Model
{
    protected $fillable = array('id', 'fees_name', 'value');
	
	/**
     * Get Default fee Settings.
     *
     * 
     * @return array
     */
	
    public function getDefaultFeeSetting()
    {
		try{
			$fees_array = DB::table('check_basic_fees')
					->select('fees_name', 'value')
					->get();
			$fees = array();
			foreach($fees_array as $fee){
				$fees[$fee->fees_name] = $fee->value;
			}
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		return $fees;
	}
}
