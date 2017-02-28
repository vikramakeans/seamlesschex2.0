<?php

namespace App\Traits;

use Illuminate\Http\Request;
use App;
use Validator;
use App\Http\Requests;
use App\User;
use App\UserDetail;
use App\Company;
use App\CompanyDetail;
use App\CheckMessage;
use App\EmailTemplate;
use App\UserStatus as Status;
use DB;

use Exception;
use Response;
use Stripe\Stripe;
use Stripe\Token; 
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Plan as StripePlan;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use Mail;

trait UserTrait
{
	
	
	/**
	 * Get user_id from $sc_token.
	 *
	 * @return \Illuminate\Http\Response
	 */
    public function getUserId($sc_token)
    {
		try{
			$user = User::select('id')->where('sc_token', $sc_token)->first();
			$user_id = $user->id;
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return $user_id;
    }
	
	/**
	 * Get user status by user id from user_details.
	 *
	 * @param  string  $id
	 * @return $status_id
	*/

	public function getUserStatus($id)
	{
		$status = UserDetail::select('status_id')->where('user_id', $id)->first();
		return $status->status_id;
	}
	
	/**
     * Get company_id from $sc_token.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCompanyId($sc_token)
    {
		try{
			$company = DB::table('users')
						->join('user_details', 'user_details.user_id', '=', 'users.id')
						->where('sc_token', '=', $sc_token)
						->select('user_details.company_id')
						->first();
			$company_id = $company->company_id;
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return $company_id;
    }
	
	/**
     * Get company_id from $mc_token.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCompanyIdFromMcToken($mc_token)
    {
		try{
			
			$company = DB::table('companies')
						->where('mc_token', '=', $mc_token)
						->select('companies.id')
						->first();

			$company_id = $company->id;
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return $company_id;
    }
	
	/**
     * Get role_id from $sc_token.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRoleId($sc_token)
    {
		try{
			$user_details = DB::table('users')
						->join('user_details', 'user_details.user_id', '=', 'users.id')
						->where('sc_token', '=', $sc_token)
						->select('user_details.role_id')
						->first();
			$role_id = $user_details->role_id;
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return $role_id;
    }
	
	/**
     * Get user_id from $company_id.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserIdFromCompanyId($company_admin)
    {
		try{
			$company = Company::select('user_id')->where('id', $company_admin)->first();
			$user_id = $company->user_id;
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return array('type' => 'success', 'value' => $user_id);
    }
	
	
	/**
	 * Get status_id from status name (like active, trialing, canceled).
	 *
	 * @param  string  $status
	 * @return $status_id
	*/

	public function getStatusId($status_name)
	{
		try {
			$status = Status::select('id')->where('status', $status_name)->first();
			$status_id = $status->id;
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;

			return array('type' => 'error', 'value' => $errorMessage);
		}
		return array('type' => 'success', 'value' => $status_id);
	}
	
	/**
	 * Get sc_token from user_id.
	 *
	 * @param  int  $user_id
	 * @return \Illuminate\Http\Response else null
	*/

	/*public function getSctoken($user_id)
	{
		try{
		$user = User::select('sc_token')->where('id', $user_id)->get();
		$sc_token = $user[0]->sc_token;
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return array('type' => 'success', 'value' => $sc_token);
		
	}*/
	
	/**
     * Get stripe_id from $user_id.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserSubscriptionByUserId($user_id)
    {
		try{
			$user_subscriptions = DB::table('users')
						->join('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
						->where('users.id', '=', $user_id)
						->select('user_subscriptions.id', 'user_subscriptions.stripe_id', 'user_subscriptions.stripe_subscription', 'user_subscriptions.stripe_plan')
						->first();
			
			//echo $userSubscriptionId = $user_subscriptions[0]->id;
			//echo $userSubscriptionStripeId = $user_subscriptions[0]->stripe_id;
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return array('type' => 'success', 'value' => $user_subscriptions);
    }
	
	
	/**
     * Get permissions from $user_id.
     * retun array $company_permissions
     * @return \Illuminate\Http\Response
     */
    public function getCompanyPermissionByCompanyId($company_id)
    {
		try{
			$company_permissions = DB::table('company_details')
						->where('company_id', '=', $company_id)
						->select('permissions')
						->first();
			$company_permissions = unserialize($company_permissions->permissions);
			}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return array('type' => 'success', 'value' => $company_permissions);
    }
	
	/**
     * Get company details from company_id.
     *
     * @return array
     */
    public function getCompanyDetails($company_id)
    {
		try{
			$companyDetails = DB::table('companies')
						->join('company_details', 'company_details.company_id', '=', 'companies.id')
						->where('companies.id', '=', $company_id)
						->select('companies.mc_token', 'companies.owner_id', 'companies.company_name', 'companies.company_email', 'company_details.checkout_token', 'company_details.pay_auth_token')
						->first();
			
		}catch (Exception $e) {
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('type' => 'error', 'value' => $errorMessage);
		}
		return array('type' => 'success', 'value' => $companyDetails);
    }
	
	/**
     * Get company settings by company_id.
     *
     * @param  int  $company_id
     * @return Array
     */
	 
	public function getCompanySettingsByCompanyId($company_id)
	{
		try{
			$companyDetail = CompanyDetail::select('permissions')->where('company_id', $company_id)->first();
			$permissions = $companyDetail->permissions;
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return array('error' => true, 'message' => $errorMessage);
		}
		return $permissions;
	}
	
	
	
	/**
    * send_email
    *
    * @desc - send email
    * @param - string $templateId, array $userInfo
    * @return array if fails else true
    *
    */
	public function sendMail($templateId, $userInfo)
    {
		try{
			$emailTemplate = EmailTemplate::find($templateId);
			$templateValue = isset($emailTemplate->template_value) ? $emailTemplate->template_value : '';
			$fromName = isset($emailTemplate->from_name) ? $emailTemplate->from_name  : '';
			$fromEmail = isset($emailTemplate->from_email) ? $emailTemplate->from_email  : '';
			$ccEmail = isset($emailTemplate->cc_email) ? $emailTemplate->cc_email  : '';
			$bccEmail = isset($emailTemplate->bcc_email) ? $emailTemplate->bcc_email  : '';
			$subject = isset($emailTemplate->subject) ? $emailTemplate->subject  : '';
			$statusId = isset($emailTemplate->status_id) ? $emailTemplate->status_id  : '';
			$templateName = isset($emailTemplate->template_name) ? $emailTemplate->template_name  : '';
			
			// Custom Template variable
			$name = isset($userInfo['name']) ? $userInfo['name'] : '';
			$email = isset($userInfo['email']) ? $userInfo['email'] : '';
			$merchant = isset($userInfo['merchant']) ? $userInfo['merchant'] : '';
			$set_pass_url = isset($userInfo['set_pass_url']) ? $userInfo['set_pass_url'] : '';
			$receiver_name = isset($userInfo['receiver_name']) ? $userInfo['receiver_name'] : '';
			$sender_name = isset($userInfo['sender_name']) ? $userInfo['sender_name'] : '';
			$receiver_email = isset($userInfo['receiver_email']) ? $userInfo['receiver_email'] : '';
			$sender_email = isset($userInfo['sender_email']) ? $userInfo['sender_email'] : '';
			$amount = isset($userInfo['amount']) ? $userInfo['amount'] : '';
			$trascation_amount = isset($userInfo['trascation_amount']) ? $userInfo['trascation_amount'] : '';
			$memo = isset($userInfo['memo']) ? $userInfo['memo'] : '';
			$date = isset($userInfo['date']) ? $userInfo['date'] : '';
			
			/*$email = (isset($receiver_email)) ? $receiver_email : $email;
			$name = (isset($receiver_name)) ? $receiver_name : $name;
			
			$fromEmail = (isset($sender_email)) ? $sender_email : $email;
			$fromName = (isset($sender_name)) ? $sender_name : $name;*/
			
			$data = array('from_name' => $fromName, 'from_email' => $fromEmail, 'from_name' => $fromName, 'subject' => $subject, 'to_email' => $email, 'to_name' => $name);
			
			$bcc_emails = explode(",", $bccEmail);
			$cc_emails = explode(",", $ccEmail);
			
			
			$templateValue = str_replace(['{{name}}', '{{email}}', '{{merchant}}', '{{set_pass_url}}', '{{receiver_name}}', '{{sender_name}}', '{{receiver_email}}', '{{sender_email}}', '{{amount}}', '{{trascation_amount}}', '{{memo}}', '{{date}}'], [$name, $email, $merchant, $set_pass_url, $receiver_name, $sender_name, $receiver_email, $sender_email, $amount, $trascation_amount, $memo, $date], $templateValue);
			
			$sent = Mail::send([], [], function ($message) use($templateValue, $bcc_emails, $cc_emails, $data){
					$message->from($data['from_email'], $data['from_name']);
					$message->setBody($templateValue, 'text/html');
					$to_name = isset($data['to_name']) ? $data['to_name'] : null;
					$message->to($data['to_email'], $to_name);
					$message->bcc($bcc_emails, null);
					$message->cc($cc_emails, null);
					$message->subject($data['subject']);
				});
			   
		}catch (Exception $exception){
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$errormsg = $message ." ". $code;
			return array('type' => 'error', 'value' => $errormsg);
		}
		return true;
	}
}
