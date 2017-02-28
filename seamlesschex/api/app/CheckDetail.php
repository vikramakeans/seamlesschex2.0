<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckDetail extends Model
{
    
    public function updateChekDetails($data,$check_id){
        
	    $CheckDetail = CheckDetail::find($check_id);

		$CheckDetail->user_id 			  = $data['user_id'];
		$CheckDetail->company_id 		  = $data['company_id'];
		$CheckDetail->owner_id			  = $data['owner_id'];
	
		$CheckDetail->batch_id 		      = $data['batch_id'];
		$CheckDetail->group_id			  = $data['group_id'];

		$CheckDetail->email 			  = $data['email'];
		$CheckDetail->check_type			  = $data['checkType'];
		$CheckDetail->verify_before_save  = $data['verify_before_save'];

		$CheckDetail->item_reference_id   = $data['itemReferenceId'];
		// $CheckDetail->return_entry_date   = $data['returnEntryDate'];
		// $CheckDetail->check_return		  = $data['check_return'];
		$CheckDetail->user_ipaddress	  = $data['user_ipaddress'];

	    $CheckDetail->save();

	}
}
