<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserStatus extends Model
{
    protected $fillable = array('id', 'status', 'status_name', 'color');
    public $timestamps = false;
}
