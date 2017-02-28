<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckVerification extends Model
{
   public function updateCheckVerications($data){


   	$checkVerification = new CheckVerification;

		$checkVerification->user_id        = $data['user_id'];
		$checkVerification->company_id     = $data['company_id'];
		$checkVerification->date           = $data['date'];
		$checkVerification->routing_no     = $data['routing_no'];
		$checkVerification->account_no     = $data['account_no'];
		$checkVerification->check_no       = $data['check_no'];
		$checkVerification->amount         = $data['amount'];
		$checkVerification->user_ipaddress = $data['user_ipaddress'];
	
	$created = $checkVerification->save();
	return $created;
   }
}
