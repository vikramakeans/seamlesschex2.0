<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMailchimpInfo extends Model
{
    protected $fillable = array('id', 'user_id', 'company_id','list_id');
}
