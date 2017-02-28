<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;
use App\UserSubscription;
use App\UserSubscriptionOther;
use App\CompanyDetail;
use App\CheckMessage as CheckMessage;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;
use Response;
use Illuminate\Database\Eloquent\Model;
use Config;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Token; 
use Stripe\Error as StripeError;
use Stripe\Customer as StripeCustomer;
use Stripe\Charge as StripeCharge;
use Stripe\Error\Card as StripeErrorCard;
use Stripe\Error\Authentication as StripeErrorAuthentication;
use Stripe\Error\ApiConnection as StripeErrorApiConnection;
use Stripe\Error\Base as StripeErrorBase;
use Stripe\Error\InvalidRequest as StripeErrorInvalidRequest;
use PDF;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    use UserController;
	public function __construct(UserSubscriptionOther $userSubscriptionOther)
	{
		Stripe::setApiKey(Config::get('services.stripe.secret'));
		Stripe::setApiVersion("2014-08-20"); // use your Stripe API version
		$this->fundconfirmationPlanId = Config::get('services.multisubscription.fundconfirmationPlanId');
		$this->signturePlanId = Config::get('services.multisubscription.signturePlanId');
		$this->checkoutlinkPlanId = Config::get('services.multisubscription.checkoutlinkPlanId');
		$this->bankauthlinkPlanId = Config::get('services.multisubscription.bankauthlinkPlanId');
		$this->userSubscriptionOther = $userSubscriptionOther;
	}
	/* @Auther Vikram singh
	*	Updated date :19/01/17
	*	Listing All charges from stripe 
	*
	*/
	public function listStripeCharges(){
		try{
			
			$stripeCharges = StripeCharge::all(array("limit" => 50));
			$customer      = StripeCustomer::all(array("limit" => 50));
			$charge_response = array(); 
			$charge_array = array();
			foreach ($stripeCharges->data as $key => $charge) {

				foreach ($customer->data as $key => $custmer) {

			 	    if($charge->customer == $custmer->id ){
                      
                       	$amount = (($charge->amount)/(100));
						$charge_array['charges'][] = array(
							'id' => $charge->id,
							'amount' => $amount,
							'currency' => $charge->currency,
							'customer' => $charge->customer,
							'email' => $custmer->email,
							 'invoice' => $charge->invoice,
							'status' => $charge->status,
						);
			 	    }
			    }
			}
			 array_push($charge_response, $charge_array);
			
		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return $charge_response;
	}
	/* @Auther Vikram singh
	*	method scope :view invoice as per invoice id
	*	date :20/01/17
	*   parameter :invoice id
	*/
	public function viewInvoice(Request $request){
		
       try{
		
			$stripeCharges = StripeCharge::retrieve($request->get('id'));
			$date = date('r', $stripeCharges->created);
			$value = new Carbon($date);
          	$dt  = Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('F d, Y');
			$charge_array = array(
				'id' => $stripeCharges->id,
				'amount' => (($stripeCharges->amount)/(100)),
				'date' =>$dt,
				'card_type'=>$stripeCharges->card['brand'],
				'last4' =>$stripeCharges->card['last4']
			);
			//print_r($charge_array);

		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
		return $charge_array;
	}

	/* @Auther Vikram singh
	*	method scope : Download  multiple Invoice as pdf format
	*	date :23/01/17
	*   parameter : invoice id
	*/
    public function downloadMultipleInvoiceAsPdf(Request $request){

    	try {

    	$html = <<<HTML
				  <html>
				      <head>
				            <style type="text/css">
				               .box-header {
										color: #444;
									    display: block;
									    padding: 10px;
									    position: relative;
									    height:90px;
									    background : #008cdd none repeat scroll 0 0 !important;
									}
									h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
									    font-weight: 500;
									    line-height: 1.1;
									    color: inherit;
									    font-size: 20px;
									    font-family: Source Sans Pro, sans-serif;
									}
									.bg-light-blue, .label-primary, .modal-primary .modal-body {
									    background-color: #3c8dbc !important;
									}
									 .bg-light-blue, .font-color,.label-primary, .modal-primary .modal-body  {
									  	color: #fff !important;
									  }
									  th {
									    text-align: left;
									}
									.table {
									    width: 100%;
									    margin-bottom: 20px;
									}
									.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
								    padding: 8px;
								    line-height: 1.428571429;
								    vertical-align: top;
								    border-
								    top: 1px solid #ddd;
									}
									.color-anchor {
											color: #3c8dbc;
									}
									.text-center{
										text-align:center;
									}
									</style>
				      </head>
				      <body>
HTML;

			$data = $request->get('multipleInvoiceId');
			
			foreach ($data  as $key=>$value) {
			
		
			    $invoiceData = StripeCharge::retrieve($value['invoice_id']);
		
	            $amount     = (($invoiceData->amount)/(100));
	            $date1 = date('r', $invoiceData->created);
				$value = new Carbon($date1);
		        $date  = Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('F d, Y');
				
				$card_type 	 = $invoiceData->card['brand'];
				$last4       =   $invoiceData->card['last4'];
				
			    $html .= '<div style="page-break-after: always;">
	<div class="box-header ">
		<center class="font-color">
			<h3 class="ng-binding"> $  ' . $amount.' at Seamless Checks, LLC</h3>
			<hr style="width: 25%;opacity:0.5 ">
			' . $card_type.'   <span>'.$last4 .'</span>
		</center>
	</div><br>
	<div class="box-body Reciept-table table ">
		<table class="table">
			<tbody>
				<tr class="bg-light-blue">
					<th class="ng-binding"> ' . $date.'</th>
					<th>#2659-3479</th>
				</tr>
				<tr class=".Reciept_row_two">
					<td class="">Description </td>
					<td class="" "="">Amount </td>
				</tr>
				<tr class="">
					<td class="">Subscription to SeamlessChex Pro Plan </td>
					<td class="ng-binding"> ' .$amount.' $</td>
				</tr>
				<tr class="">
					<td style="padding-left:15%" class="">Total</td>
					<td class="ng-binding"> ' .$amount .' $</td>
				</tr>
				<tr class="">
					<td style="padding-left:15%;font-weight:bold;font-size:15px;" class="">Paid</td>
					<td class="ng-binding" style="font-weight:bold;font-size:15px;"> ' .$amount.' $</td>
				</tr>
			</tbody>
		</table>
	</div>
	<div class="box-footer fotter text-center">

		<p class="text-center">
		Have a question or need help? <a>Send us an email </a> or <a class="color-anchor">give us a call at (888) 998-2439.</a>
		</p>
		<hr style="height: 1px;width: 45%;">	
		<p class="text-center">
		You are receiving this email because you made a purchase at <a class="color-anchor">Seamless Checks, LLC.</a>
		</p>
		

	</div></div>';
				
			}

			$html .= '</body></html>';

			$pdf =  PDF::loadHTML($html);

			}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}	
		return $pdf->download('myfile.pdf');
	
    }
    /* @Auther Vikram singh
	*	method scope : Download  invoice as pdf format
	*	date :23/01/17
	*   parameter : invoice id
	*/
	public function downloadInvoiceAsPdf(Request $request,$stripe_id)
	{

	   try{
			
			$stripeCharges = StripeCharge::retrieve($stripe_id);
			$date1 = date('r', $stripeCharges->created);
			$value = new Carbon($date1);
			$date  = Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('F d, Y');
			$receiptData = array(
					'id' => $stripeCharges->id,
					'amount' => (($stripeCharges->amount)/(100)),
					'date' =>$date,
			        'card_type'  => $stripeCharges->card['brand'],
				     'last4'       =>   $stripeCharges->card['last4']
				     );

			$html = '<style>
					.box-header {
						color: #444;
					    display: block;
					    padding: 10px;
					    position: relative;
					    height:90px;
					    background : #008cdd none repeat scroll 0 0 !important;
					}
					h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
					    font-weight: 500;
					    line-height: 1.1;
					    color: inherit;
					    font-size: 20px;
					    font-family: Source Sans Pro, sans-serif;
					}
					.bg-light-blue, .label-primary, .modal-primary .modal-body {
					    background-color: #3c8dbc !important;
					}
					 .bg-light-blue, .font-color,.label-primary, .modal-primary .modal-body  {
					  	color: #fff !important;
					  }
					  th {
					    text-align: left;
					}
					.table {
					    width: 100%;
					    margin-bottom: 20px;
					}
					.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
				    padding: 8px;
				    line-height: 1.428571429;
				    vertical-align: top;
				    border-top: 1px solid #ddd;
					}
					.color-anchor {
						color: #3c8dbc;
					}
					.text-center{
						text-align:center;
					}
					</style>
					<head>
				        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				    </head>
				    <div>
					<div class="box-header ">
						<center class="font-color">
							<h3 class="ng-binding"> $  ' .$receiptData['amount'] .'  at Seamless Checks, LLC</h3>
							<hr style="width: 25%;opacity:0.5 ">
							'.$receiptData['card_type'] .' <span> '.$receiptData['last4'].' </span>
						</center>
					</div><br>
					<div class="box-body Reciept-table table ">
					<table class="table">
						<tbody>
							<tr class="bg-light-blue">
								<th class="ng-binding"> ' .$receiptData['date'] .'</th>
								<th>#2659-3479</th>
							</tr>
							<tr class=".Reciept_row_two">
								<td class="">Description </td>
								<td class="" "="">Amount </td>
							</tr>
							<tr class="">
								<td class="">Subscription to SeamlessChex Pro Plan </td>
								<td class="ng-binding"> ' .$receiptData['amount'] .' $</td>
							</tr>
							<tr class="">
								<td style="padding-left:15%" class="">Total</td>
								<td class="ng-binding"> ' .$receiptData['amount'] .' $</td>
							</tr>
							<tr class="">
								<td style="padding-left:15%;font-weight:bold;font-size:15px;" class="">Paid</td>
								<td class="ng-binding" style="font-weight:bold;font-size:15px;"> ' .$receiptData['amount'] .' $</td>
							</tr>
						</tbody>
					</table>
				</div>
					<div class="box-footer fotter text-center">

			<p class="text-center">
				Have a question or need help? <a class="color-anchor">Send us an email </a> or <a class="color-anchor">give us a call at (888) 998-2439.</a>
			</p >
				<hr style="height: 1px;width: 45%;">	
			<p class="text-center">
			You are receiving this email because you made a purchase at <a class="color-anchor">Seamless Checks, LLC.</a>
			</p>
			
		    
		</div>
				</div>';

			$pdf =  PDF::loadHTML($html);

		}catch(Exception $e){
			$code = $e->getCode();
			$message = $e->getMessage();
			$errorMessage = $message ." ".$code;
			return response()->json(['error' => $errorMessage], 401);
		}
	    return $pdf->download('myfile.pdf');
	}
	
}
