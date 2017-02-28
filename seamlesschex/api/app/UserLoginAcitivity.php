<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;
class UserLoginAcitivity extends Model
{
    public $table = "user_login_activities";
	public $users = "users";
	public $companies = "companies";

    protected $fillable = array('id', 'user_id', 'company_id', 'content', 'last_change_at', 'last_login_at','ip_address');

    public function getActivityList(){
        $response = DB::table('user_login_activities')
			->leftJoin('users','user_login_activities.user_id', '=', 'users.id')
			->leftJoin('user_details','users.id', '=', 'user_details.user_id')
         	->leftJoin('companies','user_details.user_id', '=','companies.user_id')
         	->select('users.id','users.name','users.email','users.username','companies.company_name','user_details.status_id','user_login_activities.ip_address','user_login_activities.created_at')->get();
		return $response;
    }
}
