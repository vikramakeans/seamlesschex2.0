<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $fillable = array('id', 'user_id', 'company_id','status_id','role_id');
	
	/**
     * Get the user that owns the user_details.
    */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
	
	/**
     * Get the user_details record associated with the company.
    */
    public function userDetailCompany()
    {
        return $this->hasOne('App\Company');
    }
	
	/**
     * Get the user_details record associated with the company.
    */
    public function userDetailCompanyDetail()
    {
        return $this->hasOne('App\CompanyDetail');
    }
}
