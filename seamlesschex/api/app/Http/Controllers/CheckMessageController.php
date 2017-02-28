<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use DB;
use App\Http\Controllers\Controller;
use App\CheckMessage as CheckMessage;

class CheckMessageController extends Controller
{
	private $checkMessage;
    
	public function __construct(CheckMessage $checkMessage)
	{
		$this->checkMessage = $checkMessage;
	}
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMessages()
    {
        //$messages = DB::table('check_messages')->get();
		
		try{
			
			$messages = CheckMessage::all();
			$json_response = array(); 
			$message_array = array();
			foreach ($messages as $message) {
					$message_array['data'][] = array(
						'id' => $message->id,
						'field_label' => $message->field_label,
						'field_name' => $message->field_name,
						'form_name' => $message->form_name,
						'message' => $message->message,
						'type' => $message->type,
						'position' => $message->position,
						'updated_at' => $message->updated_at
					);
			}
			array_push($json_response, $message_array);
			
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
		return response()->json($json_response);
    }
	
    /**
     * creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createMessage(Request $request)
    {
		try{
			// Get all post values
			$field_label = $request->get('field_label');
			$field_name = $request->get('field_name');
			$form_name = $request->get('form_name');
			$message = $request->get('message');
			$type = $request->get('type');
			$position = $request->get('position');
			
			$this->checkMessage->field_label = $field_label;
			$this->checkMessage->field_name = $field_name;
			$this->checkMessage->form_name = $form_name;
			$this->checkMessage->message = $message;
			$this->checkMessage->type = $type;
			$this->checkMessage->position = $position;
			$save = $this->checkMessage->save();
			
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
    public function updateMessage(Request $request, $id)
    {
		try{
			// Get all post values
			$field_label = $request->get('field_label');
			$field_name = $request->get('field_name');
			$form_name = $request->get('form_name');
			$messageValue = $request->get('message');
			$type = $request->get('type');
			$position = $request->get('position');
			
			$message = CheckMessage::find($id);
			
			$message->field_label = $field_label;
			$message->field_name = $field_name;
			$message->form_name = $form_name;
			$message->message = $messageValue;
			$message->type = $type;
			$message->position = $position;
			$save = $message->save();
			
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
     * Remove the specified resource from Message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteMessage($id)
    {
        try{
			$message = CheckMessage::find($id);
			$message->delete();
				
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
		return response()->json(["id" => $id, "action" => 'deleted']);
    }
	
	/**
     * Get the data for specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getMessageById($id)
    {
        try{
			$message = CheckMessage::find($id);
			$json_response = array(); 
			$message_array = array();
			
			$message_array['data'] = array(
				'id' => $message->id,
				'field_label' => $message->field_label,
				'field_name' => $message->field_name,
				'form_name' => $message->form_name,
				'message' => $message->message,
				'type' => $message->type,
				'position' => $message->position,
				'updated_at' => $message->updated_at
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
		return response()->json($message_array);
    }
}
