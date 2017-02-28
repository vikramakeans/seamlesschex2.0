<?php

namespace App;

use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Model;
use DB;

class Check extends Model
{
	use UserTrait;
	

    public function searchCheck($data)
    {

    	try{
           
			DB::enableQueryLog();

	    	$query = DB::table('checks')
	    		->join('check_details', 'check_details.check_id', '=', 'checks.id')
	    		->select('checks.*','check_details.response_code','check_details.is_fundconfirmation');

			if (!empty($data["company_admin"]) && $data["company_admin"] != -1) {
			
			    $query = $query->where('checks.company_id','=',$data['company_admin']);
			    $query = $query->where('checks.status_id','=',2);

			}
	   		if (!empty($data["company_user"]) && $data["company_user"] != -1) {

			    $query = $query->where('checks.user_id','=',$data['company_user']);
			    $query = $query->where('checks.status_id','=',2);
			}
			
			
			if (!empty($data["from_date"])) {
				
				
				//$query = $query->whereBetween('checks.date', array($data['from_date'],$data['to_date']));
				$query = $query->wheredate('checks.date','>=',$data['from_date']);
				$query = $query->where('checks.status_id','=',2);
			
			}
			if (!empty($data["to_date"])) {
				
				$query = $query->wheredate('checks.date', '<=',$data['to_date']);
				$query = $query->where('checks.status_id','=',2);
			
			}

		  	$checks  = $query->get();
		 
            
    	}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
 
	    return count($checks) >=1 ? $checks  : 0;
    }


    	
    public function editCheck($check_token){

    	try{

    		$data = DB::table('checks')
           	 	->join('check_details', 'checks.id', '=', 'check_details.check_id')
		    	->where('checks.check_token','=',$check_token)
		   		->select('checks.*','check_details.email')->get();
            
    	}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
   
	    return count($data) >=1 ? $data : 0;
    }

    
	
	/**
     * Get company sub list query for logged in customer 
     * @param  string  $sc_token
     * @return array
     */
	 
	public function getCompanyQuerySubList($sc_token)
	{
		try{
			
			if ($sc_token !== null) {
				$company_admin = Check::getCompanyId($sc_token);
				if($company_admin['type'] == 'error'){
					return response()->json(['error' => true, 'message' => $company_admin['value']], 401);
				}
				
				$companyAdmin = DB::table('companies')
				->join('company_details', 'companies.id', '=', 'company_details.company_id')
				->whereIn('company_details.status_id', [1,2])
				->where(function($q) use($company_admin){
					  $q->where('company_details.company_id', '=', $company_admin)
						->orWhere('companies.owner_id', '=', $company_admin);
				})
				->orderBy('companies.id', 'DESC')
				->select('companies.mc_token', 'companies.company_name', 'companies.company_email')
				->get();
				
				$response = array(); 
				$merchant_array = array();
				foreach ($companyAdmin as $company) {
						$merchant_array['data'][] = array(
							'sc_token' => $sc_token,
							'mc_token' => $company->mc_token,
							'name' => $company->company_name,
							'email' => $company->company_email
						);
				}
				array_push($response, $merchant_array);
			}
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errormsg = $message ." ".$code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
	}
	

}
