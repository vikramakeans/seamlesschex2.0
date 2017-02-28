<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use DB;
use App\Http\Controllers\Controller;
use App\UserPermission as  UserPermission;

class UserPermissionController extends Controller
{
	private $userPermission;
    
	public function __construct(UserPermission $userPermission)
	{
		$this->userPermission = $userPermission;
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
    public function getPermission()
    {
        //$permissions = DB::table('check_basic_permissions')->get();
		
		try{
			$permissions = UserPermission::all();
			$json_response = array(); 
			$permission_array = array();
			foreach ($permissions as $permission) {
					$permission_array['data'][] = array(
						'id' => $permission->id,
						'role_id' => $permission->role_id,
						'sl_no' => $permission->sl_no,
						'permission_label' => $permission->permission_label,
						'permission_type' => $permission->permission_type,
						'permission_name' => $permission->permission_name,
						'permission_value' => $permission->permission_value,
						'updated_at' => $permission->updated_at
						
					);
			}
			array_push($json_response, $permission_array);
			
		}catch (JWTException $e) {
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
    public function createPermission(Request $request)
    {
		try{
			// Get all post values
			$role_id = $request->get('role_id');
			$sl_no = $request->get('sl_no');
			$permission_label = $request->get('permission_label');
			$permission_type = $request->get('permission_type');
			$permission_name = $request->get('permission_name');
			$permission_value = $request->get('permission_value');
			
			$this->userPermission->role_id = $role_id;
			$this->userPermission->sl_no = $sl_no;
			$this->userPermission->permission_label = $permission_label;
			$this->userPermission->permission_type = $permission_type;
			$this->userPermission->permission_name = $permission_name;
			$this->userPermission->permission_value = $permission_value;
			$save = $this->userPermission->save();
			
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
    public function updatePermission(Request $request, $id)
    {
		try{
			// Get all post values
			$role_id = $request->get('role_id');
			$sl_no = $request->get('sl_no');
			$permission_label = $request->get('permission_label');
			$permission_type = $request->get('permission_type');
			$permission_name = $request->get('permission_name');
			$permission_value = $request->get('permission_value');
			
			$permission = UserPermission::find($id);
			
			$permission->role_id = $role_id;
			$permission->sl_no = $sl_no;
			$permission->permission_label = $permission_label;
			$permission->permission_type = $permission_type;
			$permission->permission_name = $permission_name;
			$permission->permission_value = $permission_value;
			$save = $permission->save();
			
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
     * Remove the specified resource from Permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deletePermission($id)
    {
        try{
			$permission = UserPermission::find($id);
			$permission->delete();
				
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
    public function getPermissionById($id)
    {
        try{
			$permission = UserPermission::find($id);
			
			$permission_array = array();
			
			$permission_array['data'] = array(
						'id' => $permission->id,
						'role_id' => $permission->role_id,
						'sl_no' => $permission->sl_no,
						'permission_label' => $permission->permission_label,
						'permission_type' => $permission->permission_type,
						'permission_name' => $permission->permission_name,
						'permission_value' => $permission->permission_value,
						'updated_at' => $permission->updated_at
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
		return response()->json($permission_array);
    }
}
