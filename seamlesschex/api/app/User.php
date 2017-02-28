<?php

namespace App;
use App\Traits\UserTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\UserStatus as Status;
use App\Company as Company;
use DB;

class User extends Authenticatable
{
	use UserTrait;
    
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
	
	
	/**
     * Get the user_details record associated with the user.
    */
    public function userDetail()
    {
        return $this->hasOne('App\UserDetail');
    }
	
	/**
     * Get the company_details record associated with the user.
    */
    public function userCompanyDetail()
    {
        return $this->hasOne('App\CompanyDetail');
    }
	
	/**
     * Get the company record associated with the user.
    */
    public function userCompany()
    {
        return $this->hasOne('App\Company');
    }
	
	
	/**
     * List Seamlesschex Admin
    */
	public function getScxAdmin()
	{
		try{
			
			$scxAdmin = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->where('user_details.status_id', '!=', 8)
				->whereIn('user_details.role_id', [1,2])
				->select('users.sc_token', 'users.name',  'users.email', 'users.created_at', 'user_details.status_id', 'user_details.role_id')
				->get();
				
			$response = array(); 
			$admin_array = array();
			$status_array = array();
			
			foreach ($scxAdmin as $admin) {
					$status_id = $admin->status_id;
					if($status_id){
						$status = Status::find($status_id);
						$status_array = array('status_code' => $status->status, 'status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
					}
					$admin_array['data'][] = array(
						'sc_token' => $admin->sc_token,
						'role_id' => $admin->role_id,
						'name' => $admin->name,
						'email' => $admin->email,
						'status' => $status_array,
						'invited_date' => date("m/d/Y", strtotime($admin->created_at)),
						'time' => date("H:i:s", strtotime($admin->created_at))
					);
					
			}
			array_push($response, $admin_array);
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
		
	}
	
	/**
     * List Company User
    */
	public function getMerchantUser()
	{
		try{
			
			$companyUser = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->join('companies', 'user_details.company_id', '=', 'companies.id')
				->join('company_details', 'companies.id', '=', 'company_details.company_id')
				->where('user_details.role_id', '=', 4)
				->where('user_details.status_id', '!=', 8)
				->select('users.sc_token', 'users.created_at', 'users.name', 'users.email',  'users.username', 'companies.company_name','user_details.status_id','user_details.user_id', 'user_details.permission_settings', 'companies.company_email', 'companies.mc_token')
				->get();
				
			$response = array(); 
			$user_array = array();
			$status_array = array();
			foreach ($companyUser as $company) {
                 $status_id = $company->status_id;
				if($status_id){
						$status = Status::find($status_id);
						$status_array = array('status_code' => $status->status, 'status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
					}
					$user_array['data'][] = array(
						'sc_token' => $company->sc_token,
						'mc_token' => $company->mc_token,
						'name' => $company->name,
						'user_id' => $company->user_id,
						'username' => $company->username,
						'email' => $company->email,
						'company_name' => $company->company_name,
						'company_email' => $company->company_email,
						'user_settings' => unserialize($company->permission_settings),
						'status' =>$status_array,
						'invited_date' => date("m/d/Y", strtotime($company->created_at)),
						'time' => date("H:i:s", strtotime($company->created_at))
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
     * Get company users by sc_token
    */
	public function getCompanyUsersQueryByScToken($sc_token)
	{
		try{
			
			$company_admin = User::getCompanyId($sc_token);
			
			if($company_admin['type'] == 'error'){
				return array('type' => 'error', 'value' => $company_admin['value']);
			}
			if($sc_token != '' && $company_admin != ''){
				
				$companyUsers = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->join('companies', 'user_details.company_id', '=', 'companies.id')
				->where('user_details.role_id', '=', 4)
				->whereIn('user_details.status_id', [2,9])
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
						$status_array = array('status_code' => $status->status, 'status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
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
     * Ghost Login Query
    */
	public function getGhostLoginQuery($sc_token)
	{
		try{
			$user = DB::table('users')
				->join('user_details', 'user_details.user_id', '=', 'users.id')
				->where('sc_token', '=', $sc_token)
				->select('users.name', 'users.email', 'user_details.status_id', 'user_details.role_id', 'user_details.permission_settings')
				->first();
			$name = $user->name;
			$email = $user->email;
			$role_id = $user->role_id;
			$status_id = $user->status_id;
			$permissions = unserialize($user->permission_settings);
			
			$userData = array(
						"sc_token" => $sc_token,
						"name" => $name,
						"email" => $email,
						"status" => $status_id,
						"permissions" => $permissions,
						"role" => $role_id,
						);
		
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $userData;
	}

	
	/**
     * Get specified business(sub company) by sc_token
    */
	public function getScxAdminQuesryByToken($sc_token)
	{
		try{
			
			$admin = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->where('users.sc_token', '=', $sc_token)
            ->select('users.sc_token', 'users.name', 'users.email', 'users.username',  'user_details.status_id', 'user_details.company_id')
            ->first();
			
			$response = array(); 
			$admin_array = array();
			$status_id = $admin->status_id;
			if($status_id){
				$status = Status::find($status_id);
				$status_array = array('status_name' => $status->status_name, 'color' => $status->color, 'id' => $status_id);
			}
			
			$admin_array = array(
				'sc_token' => $admin->sc_token,
				'name' => $admin->name,
				'email' => $admin->email,
				'username' => $admin->username,
				'status' => $status_array
			);
		
			array_push($response, $admin_array);
			
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
	}
	
	/**
     * Get specified company user by sc_token
    */
	public function getCompanyUserQueryByToken($sc_token)
	{
		try{
			
			$companyUser = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->join('companies', 'user_details.company_id', '=', 'companies.id')
            ->join('company_details', 'companies.id', '=', 'company_details.company_id')
			->where('users.sc_token', '=', $sc_token)
            ->select('users.sc_token', 'users.name', 'users.email', 'users.username',  'user_details.status_id', 'user_details.company_id', 'user_details.permission_settings', 'companies.mc_token', 'companies.company_name', 'companies.company_email', 
			'company_details.permissions')
            ->first();
			
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $companyUser;
	}
	
	/**
     * Get all users of merchant by company_admin
    */
	public function getCompanyUsersQueryByCompany($company_admin)
	{
		try{
			
			$companyUsers = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->where('user_details.company_id', '=', $company_admin)
				->where('user_details.role_id', '=', 4)
				->where('user_details.status_id', '=', 2)
				->select('users.sc_token', 'users.id', 'users.name', 'users.email')
				->get();
				
			$response = array(); 
			$user_array = array();
			foreach ($companyUsers as $user) {
					
					$user_array['data'][] = array(
						'sc_token' => $user->sc_token,						
						'id' => $user->id,
						'name' => $user->name,
						'email' => $user->email
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
     * Get specified company sub by sc_token
    */
	public function getCompanySubQueryByToken($sc_token)
	{
		try{
			
			$companyUser = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->join('companies', 'user_details.company_id', '=', 'companies.id')
            ->join('company_details', 'companies.id', '=', 'company_details.company_id')
			->where('users.sc_token', '=', $sc_token)
            ->select('users.sc_token', 'users.name', 'users.email', 'user_details.status_id', 'user_details.company_id', 'companies.mc_token', 'companies.company_name', 'companies.company_email', 'companies.owner_id')
            ->first();
			
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $companyUser;
	}
	
	
	/**
     * Get admin query by sc_token
    */
	public function getAdminQueryByToken($sc_token)
	{
		try{
			$userAdmin = DB::table('users')
			->join('user_details', 'users.id', '=', 'user_details.user_id')
			->where('users.sc_token', '=', $sc_token)
			->select('users.sc_token', 'users.name', 'users.username', 'users.email')
			->first();

			$response = array(); 
			$user_array = array();
			$user_array = array(
				'sc_token' => $userAdmin->sc_token,
				'name' => $userAdmin->name,
				'username' => $userAdmin->username,
				'email' => $userAdmin->email
			);
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
     * Check set pass url valid or invalid
    */
	public function setPassURLCheck($sc_token)
	{
		try{
			
			$response = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->where('user_details.status_id', '=', 9)
				->where('users.sc_token','=',$sc_token)
				->count();
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
		
	}
	
	/**
     * Check email is valid or invalid, with user status is active(2)
    */
	
	public function checkEmailForgetPasswordQuery($email)
	{
		try{
			
			$response = DB::table('users')
				->join('user_details', 'users.id', '=', 'user_details.user_id')
				->whereIn('user_details.status_id', [1,2])
				->where('users.email', '=', $email)
				->count();
			
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return $response;
		
	}
	
	
}
