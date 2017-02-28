<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyDetail extends Model
{
    protected $fillable = array('id', 'user_id', 'company_id','total_no_check','no_of_check_remaining', 'total_fundconfirmation', 'remaining_fundconfirmation', 'total_payauth', 'payauth_remaining', 'owner_id', 'token', 'checkout_token', 'pay_auth_token', 'companies_permission', 'payment_link_permission', 'signture_permission', 'pay_auth_permission', 'status_id');
	
	
	/**
     * Get the company that owns the company_details.
    */
    public function company()
    {
        return $this->belongsTo('App\Company');
    }
	
	/**
     * Get the company that owns the users.
    */
    public function companyDetailUser()
    {
        return $this->belongsTo('App\User');
    }
	
	/**
     * Get the company that owns the user_details.
    */
    public function companyDetailUserDetail()
    {
        return $this->belongsTo('App\UserDetail');
    }
}
