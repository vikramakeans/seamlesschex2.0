<?php

namespace App;

use App\Traits\UserTrait;
use Illuminate\Database\Eloquent\Model;
use App\UserStatus as Status;
use App\UserDetail as UserDetail;

use DB;

class Company extends Model
{
	use UserTrait;
    protected $fillable = array('id', 'user_id', 'owner_id','company_name','cname', 'business_type', 'company_email', 'address', 'city', 'state', 'zip', 'settings');
	
	/**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
	/**
     * Get the company_details record associated with the company.
    */
    public function companyDetail()
    {
        return $this->hasOne('App\CompanyDetail');
    }
	
	/**
     * Get the user record associated with the company.
    */
    public function companyUser()
    {
        return $this->belongsTo('App\User');
		
    }
	
	/**
     * Get the user_details record associated with the user.
    */
    public function companyUserDetail()
    {
        return $this->belongsTo('App\UserDetail');
    }
	
	/**
     * List all merchant
    */
	public function getAllMerchant()
	{
		try{
			
			$companyAdmin = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->join('companies', 'user_details.company_id', '=', 'companies.id')
				->join('company_details', 'companies.id', '=', 'company_details.company_id')
				->whereNotIn('user_details.status_id', [0, 8])
				->whereIn('user_details.role_id', [3,5])
				->orderBy('users.id', 'DESC')
				//->limit(50)
				->select('users.sc_token', 'users.name', 'users.email', 'users.created_at', 'user_details.company_id', 'user_details.role_id', 'user_details.permission_settings',  'user_details.stripe_plan', 'user_details.amount', 'user_details.lastloggedin_at', 'user_details.last_invoice_at', 'companies.mc_token', 'companies.cname', 'companies.business_type', 'company_details.status_id', 'company_details.permissions')
				->get();
			
			$response = array(); 
			$user_array = array();
			$status_array = array();
			foreach ($companyAdmin as $company) {
					$user_settings = unserialize($company->permission_settings);
					$company_settings = unserialize($company->permissions);
					$status_id = $company->status_id;
					if($status_id){
						$status = Status::find($status_id);
						$status_array = array('status_name' => $status->status_name, 'status_code' => $status->status, 'color' => $status->color, 'id' => $status_id);
					}
					
					$user_array['data'][] = array(
						'sc_token' => $company->sc_token,
						'name' => $company->name,
						'business_type' => $company->business_type,
						'cname' => $company->cname,
						'email' => $company->email,
						'stripe_plan' => $company->stripe_plan,
						'amount' => $company->amount,
						'user_settings' => $user_settings,
						'company_settings' => $company_settings,
						'status' => $status_array,
						'created_at' => date("m/d/Y", strtotime($company->created_at)),
						'last_invoice_at' => $company->last_invoice_at,
						'lastloggedin_at' => $company->lastloggedin_at
					);
			}
			array_push($response, $user_array);
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
		
	}
	
	
	
	/**
     * List Company sub (business)
    */
	public function getCompanySub()
	{
		try{
			
			$companySub = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->join('companies', 'user_details.company_id', '=', 'companies.id')
				->join('company_details', 'companies.id', '=', 'company_details.company_id')
				->where('user_details.role_id', '=', 5)
			    ->where('user_details.status_id', '!=', 8)
				->select('users.sc_token', 'users.name', 'users.email',  'companies.company_name','companies.mc_token','user_details.status_id', 'companies.owner_id')
				->get();
				
			$response = array(); 
			$user_array = array();

			foreach ($companySub as $subcomp) {

					$status_id = $subcomp->status_id;
					if($status_id){
						$status = Status::find($status_id);
						$status_array = array('status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
					}
					$ownerCompanyName = '';
					if($subcomp->owner_id != '' && $subcomp->owner_id != 0){
						$companyOwnerDetails = $this->company->getCompanyDetails($subcomp->owner_id);
						$companyValues = $companyOwnerDetails['value'][0];
						$ownerCompanyName = $companyValues->company_name;
					}
					$user_array['data'][] = array(
						'sc_token' => $subcomp->sc_token,
						'mc_token' => $subcomp->mc_token,
						'name' => $subcomp->name,
						'email' => $subcomp->email,
						'company_name' => $subcomp->company_name,
						'status_id'=>$status_array,
						'owner_name'=>$ownerCompanyName
					);
			
			}
			array_push($response, $user_array);
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
		
	}
	
	
	
	/**
     * Get merchant details by sc_token
    */
	public function getMerchantQueryByScToken($sc_token)
	{
		try{
			
			/*$companyAdmin = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
			->join('user_subscriptions', 'users.id', '=', 'user_subscriptions.user_id')
            ->join('companies', 'user_details.company_id', '=', 'companies.id')
            ->join('company_details', 'companies.id', '=', 'company_details.company_id')
			->where('users.sc_token', '=', $sc_token)
            ->select('users.sc_token', 'users.name', 'users.email', 'user_details.company_id', 'user_details.role_id', 'user_details.permission_settings', 'user_details.status_id', 'companies.mc_token', 'companies.cname', 'companies.address', 'companies.city', 'companies.state', 'companies.zip', 'companies.phone', 'companies.website', 'companies.taxid', 'companies.business_type', 'companies.bank_name', 'companies.authorised_signer', 'companies.bank_account_no','companies.bank_routing', 'companies.settings','company_details.status_id', 'company_details.total_no_check', 
			'company_details.permissions', 'user_subscriptions.stripe_id', 'user_subscriptions.stripe_plan', 'user_subscriptions.stripe_subscription', 'user_subscriptions.amount', 'user_subscriptions.stripe_active', 'user_subscriptions.trial_ends_at', 'user_subscriptions.subscription_ends_at')
            ->first();*/
			
			$companyAdmin = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->join('companies', 'user_details.company_id', '=', 'companies.id')
            ->join('company_details', 'companies.id', '=', 'company_details.company_id')
			->where('users.sc_token', '=', $sc_token)
            ->select('users.sc_token', 'users.name', 'users.email', 'user_details.company_id', 'user_details.role_id', 'user_details.permission_settings', 'user_details.status_id', 'companies.mc_token', 'companies.cname', 'companies.address', 'companies.city', 'companies.state', 'companies.zip', 'companies.phone', 'companies.website', 'companies.taxid', 'companies.business_type', 'companies.bank_name', 'companies.authorised_signer', 'companies.bank_account_no','companies.bank_routing', 'companies.settings','company_details.status_id', 'company_details.total_no_check', 
			'company_details.permissions')
            ->first();
		
		
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $companyAdmin;
	}
	
	/**
     * Get business by sc_token
    */
	public function getBusinessQueryByScToken($sc_token)
	{
		try{
			
			$company_admin = Company::getCompanyId($sc_token);
			
			if($company_admin['type'] == 'error'){
				return array('type' => 'error', 'value' => $company_admin['value']);
			}
			if($sc_token != '' && $company_admin != ''){
			
				$companyUsers = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->join('companies', 'user_details.company_id', '=', 'companies.id')
				->where('user_details.role_id', '=', 5)
				->where('user_details.status_id', '!=', 8)
				->where(function($q) use($company_admin){
					  $q->where('user_details.company_id', '=', $company_admin)
						->orWhere('companies.owner_id', '=', $company_admin);
				})
				->select('users.sc_token', 'users.id', 'users.name', 'users.email', 'users.created_at', 'user_details.status_id', 'user_details.permission_settings', 'companies.company_email', 'companies.company_name')
				->get();
				
			$json_response = array(); 
			$user_array = array();
			foreach ($companyUsers as $user) {
					$status_id = $user->status_id;
					if($status_id){
						$status = Status::find($status_id);
						$status_array = array('status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
					}
					$user_array['data'][] = array(
						'sc_token' => $user->sc_token,						
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email,
						'company_email' => $user->company_email,
						'company_name' => $user->company_name,
						'status' => $status_array,
						'user_settings' => unserialize($user->permission_settings),
						'invited_date' => date("m/d/Y", strtotime($user->created_at)),
						'time' => date("H:i:s", strtotime($user->created_at))
					);
			}
			array_push($json_response, $user_array);
			
		}
		
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $json_response;
	}
	
	/**
     * Export merchant Details Quesry
    */
	public function getMerchantForExport()
	{
		try{
			
			$merchant  =  DB::table('users')
	        ->join('user_details', 'users.id', '=', 'user_details.user_id')
	        ->join('companies', 'user_details.company_id', '=', 'companies.id')
	        ->join('company_details', 'companies.id', '=', 'company_details.company_id')
	        ->join('user_subscriptions', 'users.id', '=', 'user_subscriptions.user_id')
	        ->whereIn('user_details.role_id', [3,5])
			->where('user_details.status_id', '!=', 8)
			->orderBy('users.id', 'DESC')
	        ->select('users.sc_token', 'users.name', 'users.email', 'user_details.status_id','users.created_at',  'user_details.permission_settings','user_details.lastloggedin_at', 'user_details.last_invoice_at', 
	        	'companies.address','companies.city','companies.state','companies.zip','companies.phone',
	        	'companies.bank_name','companies.bank_account_no','companies.authorised_signer','companies.bank_routing',
	        	'companies.cname','user_details.amount',
	        	'company_details.total_no_check','company_details.no_of_check_remaining','company_details.total_fundconfirmation','company_details.remaining_fundconfirmation',
	        	'company_details.total_payauth','company_details.payauth_remaining','company_details.token','company_details.checkout_token','company_details.pay_auth_token',
	        	'company_details.companies_permission','company_details.fundconfirmation_permission','company_details.payment_link_permission','company_details.signture_permission',
	        	'company_details.pay_auth_permission','company_details.permissions',
	        	'user_details.stripe_plan','user_details.company_id', 'companies.company_name','companies.business_type','companies.id', 'companies.company_email','companies.owner_id')->get();
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $merchant;
		
	}
	
	/**
     * Get All company admin
     *
     * @param 
     * @return \Illuminate\Http\Response
     */
	
	public function getCompanyAdminQuery(){
		try{
			$companyAdmin = DB::table('companies')
				->join('company_details', 'companies.id', '=', 'company_details.company_id')
				->where('company_details.status_id', '=', 2)
				->select('companies.mc_token', 'companies.company_name', 'companies.company_email')
				->get();
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $companyAdmin;
	}
	
	
}
