<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PasswordFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
   
    public function rules()
    {
        $rules = array();
            $rules = [
              'password' => "min:8|required",
              'cpassword'    => "required|min:8|same:password",
              
            ];
        return $rules ;
    }
    
    public function messages()
    {
        $messages = array();
            $messages = [
              'password.required' => "Please enter password",
              'cpassword.required'    => "Please enter confirm password" ,
              'password.min'    => "The password must be at least 8 characters." ,
              'cpassword.min'    => "The confirm password must be at least 8 characters." ,
              'cpassword.same'    => "The confirm password and password must match." ,
            ];
        return $messages ;
    }
}
