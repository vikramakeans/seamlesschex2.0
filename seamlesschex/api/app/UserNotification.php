<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $fillable = array('id', 'user_id', 'company_id','notifications');
}
