<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckSetting extends Model
{
    protected $fillable = array('id', 'settings_name', 'value');
}
