<?php

namespace App;
use DB;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
   
   protected $fillable = [
        'user_id', 'email', 'token'
    ];
	public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->freshTimestamp();
        });
    }

   /**
     * Check set pass url valid or invalid
    */
	public function setForgetPassURLCheck($token)
	{
		try{
			
			$response = DB::table('users')
				->join('password_resets', 'users.id', '=', 'password_resets.user_id')
				->where('password_resets.token', '=', $token)
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
