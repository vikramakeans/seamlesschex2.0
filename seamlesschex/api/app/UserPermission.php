<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\UserDetail as UserDetail;
use DB;

class UserPermission extends Model
{
    
	
	/**
     * Get Default Settings for Diffrent role.
     *
     * @param  int  $role_id
     * @return \Illuminate\Http\Response
     */
	
    public function getDefaultCompanySetting($role_id)
    {
		try{
			$user_permissions = DB::table('user_permissions')
					->where('role_id', '=', $role_id)
					->select('permission_name', 'permission_value')
					->get();
			$settings = array();
			foreach($user_permissions as $permission){
				$settings[$permission->permission_name] = $permission->permission_value;
			}
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		
		return serialize($settings);
	}
	
	/**
     * Get Default Settings for Diffrent role.
     *
     * @param  int  $role_id
     * @return \Illuminate\Http\Response
     */
	
    public function getDefaultCompanySettingArray($role_id)
    {
		try{
			$user_permissions = DB::table('user_permissions')
					->where('role_id', '=', $role_id)
					->orderBy('permission_type', 'DESC')
					->orderBy('sl_no', 'ASC')
					->select('sl_no', 'permission_type', 'permission_name', 'permission_value', 'permission_label')
					->get();
			$settings_array = array();
			$response_array = array(); 
			foreach($user_permissions as $permission){
				//$settings[$permission->permission_name] = $permission->permission_value;
				$settings_array['per_set'][] = array(
						'sl_no' => $permission->sl_no,
						'permission_label' => $permission->permission_label,
						'permission_type' => $permission->permission_type,
						'permission_name' => $permission->permission_name,
						'permission_value' => $permission->permission_value
					);
			}
			array_push($response_array, $settings_array);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg], 401);
		}
		return serialize($response_array);
		//return serialize($settings);
	}
	
	/**
     * get Permissions by user_id
     *
     * @param  string  $user_id
     * @return \Illuminate\Http\Response
     */
	
    public function getPermissionByUserId($user_id)
    {
		try{
			$userDetail = UserDetail::select('permission_settings')->where('user_id', $user_id)->first();
			$permission_settings = $userDetail->permission_settings;
			
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}catch (Exception $exception){
			 $code = $exception->getCode();
			 $message = $exception->getMessage();
			 $errormsg = $message ." ". $code;
			 return response()->json(['error' => $errormsg],401);
		}
		return $permission_settings;
    }
}
