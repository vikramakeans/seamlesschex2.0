<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use DB;
use App\Http\Controllers\Controller;
use App\EmailTemplate as EmailTemplate;


class EmailTemplateController extends Controller
{
	
	private $emailTemplate;
    
	public function __construct(EmailTemplate $emailTemplate)
	{
		$this->emailTemplate = $emailTemplate;
	}
	
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getEmailTemplate()
    {
        //$emailtemplate = DB::table('email_templates')->get();
		
		try{
			
			$emailtemplates = EmailTemplate::all();
			$json_response = array(); 
			$emailtemplate_array = array();
			foreach ($emailtemplates as $emailtemplate) {
					$emailtemplate_array['data'][] = array(
						'id' => $emailtemplate->id,
						'template_name' => $emailtemplate->template_name,
						'from_name' => $emailtemplate->from_name,
						'subject' => $emailtemplate->subject,
						'template_value' => $emailtemplate->template_value,
						'created_at' => $emailtemplate->created_at,
						'updated_at' => $emailtemplate->updated_at
						
					);
			}
			array_push($json_response, $emailtemplate_array);
			
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}
		if(empty($json_response[0])){
			return response()->json(['success' => true, 'token' => false, 'message' => 'Record Not found']);
		}
		return response()->json($json_response);
    }

    /**
     * creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createEmailTemplate(Request $request)
    {
		try{
			// Get all post values
			$template_name = $request->get('template_name');
			$from_name = $request->get('from_name');
			$from_email = $request->get('from_email');
			$cc_email = $request->get('cc_email');
			$bcc_email = $request->get('bcc_email');
			$subject = $request->get('subject');
			$template_value = $request->get('template_value');
					
			$this->emailTemplate->template_name = $template_name;
			$this->emailTemplate->from_name = $from_name;
			$this->emailTemplate->from_email = $from_email;
			$this->emailTemplate->cc_email = $cc_email;
			$this->emailTemplate->bcc_email = $bcc_email;
			$this->emailTemplate->subject = $subject;
			$this->emailTemplate->template_value = $template_value;
			$save = $this->emailTemplate->save();
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}
		return response()->json(array('success' => true, 'token' => true), 200);
    }
    
    /**
     * Editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateEmailTemplate(Request $request, $id)
    {
		try{
			// Get all post values
			$template_name = $request->get('template_name');
			$from_name = $request->get('from_name');
			$from_email = $request->get('from_email');
			$cc_email = $request->get('cc_email');
			$bcc_email = $request->get('bcc_email');
			$subject = $request->get('subject');
			$template_value = $request->get('template_value');
			
			$emailtemplate = EmailTemplate::find($id);
			
			$emailtemplate->template_name = $template_name;
			$emailtemplate->from_name = $from_name;
			$emailtemplate->from_email = $from_email;
			$emailtemplate->cc_email = $cc_email;
			$emailtemplate->bcc_email = $bcc_email;
			$emailtemplate->subject = $subject;
			//$emailtemplate->template_value = HTML::encode($template_value);
			$emailtemplate->template_value = $template_value;
			$save = $emailtemplate->save();
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}
		return response()->json(array('success' => true, 'token' => true), 200);
    }
    
    /**
     * Remove the specified resource from emailtemplate.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteEmailTemplate($id)
    {
		try{
			$emailtemplate = EmailTemplate::find($id);
			$emailtemplate->delete();
				
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return Response::json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}
		return response()->json(["id" => $id, "action" => 'delete']);
    }
    
    /**
     * Get the data for specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getEmailTemplateById($id)
    {
        try{
			$emailtemplate = EmailTemplate::find($id);
			
			$emailtemplate_array = array();
			$emailtemplate_array['data'] = array(
						'id' => $emailtemplate->id,
						'template_name' => $emailtemplate->template_name,
						'from_name' => $emailtemplate->from_name,
						'from_email' => $emailtemplate->from_email,
						'cc_email' => $emailtemplate->cc_email,
						'bcc_email' => $emailtemplate->bcc_email,
						'subject' => $emailtemplate->subject,
						'template_value' => $emailtemplate->template_value,
						'created_at' => $emailtemplate->created_at,
						'updated_at' => $emailtemplate->updated_at
			);
		}catch (Exception $e) {
			// something went wrong
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMesssage = $message ." ". $code;
			return response()->json(['error' => $errorMesssage, 'token' => false], 401);
		}catch (JWTException $e) {
			// something went wrong
			return Response::json(['error' => 'Connection Error'], HttpResponse::HTTP_CONFLICT);
		}
		return response()->json($emailtemplate_array);
    }
}
