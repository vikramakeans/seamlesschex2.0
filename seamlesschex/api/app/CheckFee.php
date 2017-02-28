<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class CheckFee extends Model
{
    protected $fillable = array('id', 'user_id', 'company_id','fees_name','value');
	
	/**
	* Get the Fee
	*
	* @param  string  $sc_token
	* @return \Illuminate\Http\Response
	*/
	
	public function getCompanyFee($sc_token)
	{
		
		try {
			$checkFees = DB::table('users')
				->rightJoin('check_fees', 'check_fees.user_id', '=', 'users.id')
				->where('sc_token', '=', $sc_token)
				->select('check_fees.fees_name', 'check_fees.value')
				->get();
			$feeDetail = array();
			foreach($checkFees as $fees){
				$feeDetail[$fees->fees_name] = $fees->value;
			}
		
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $feeDetail;	
	}
}
