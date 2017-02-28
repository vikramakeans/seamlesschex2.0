<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::get('/', function () {
    return view('index');
});
//Route::when('*', 'csrf', array('post', 'put', 'delete'));
Route::group(['prefix' => 'api'], function()
{
	//Route::resource('authenticate', 'AuthenticateController', ['only' => ['index']]);
	// For test
	Route::post('test', 'AuthenticateController@test');
	
	// register front end
	Route::post('authenticate', 'AuthenticateController@authenticate');
	Route::post('register', 'AuthenticateController@register');
	Route::get('authenticate/user', 'AuthenticateController@getAuthenticatedUser');
	Route::get('authenticate/admin/{sc_token}', 'AuthenticateController@getAdminByToken');
	Route::post('authenticate/updateAdmin/{sc_token}', 'AuthenticateController@updateAdminDetails');
	
	// get stripe token
	Route::post('register/token', 'UserSubscriptionController@getStripeToken');
	Route::post('authenticate/subscription/multiple', 'UserSubscriptionOtherController@addAnotherSubscription');
	Route::get('authenticate/subscription/lists', 'UserSubscriptionOtherController@listMultipleSubscriptions');
	Route::post('authenticate/subscription/cancel', 'UserSubscriptionOtherController@cancelSubscriptionMutiple');
	Route::get('authenticate/subscription/lists/{sc_token}', 'UserSubscriptionOtherController@listMultipleSubscriptionsByCompany');
	
	Route::post('authenticate/subscription/active/{sc_token}', 'UserSubscriptionController@activateSubscription');
	Route::post('authenticate/subscription/upgrade/{sc_token}', 'UserSubscriptionController@upgradeSubscription');
	
	Route::post('authenticate/testget', 'UserSubscriptionOtherController@test');
	
	
	// company-admin
	Route::resource('authenticate', 'CompanyController', ['only' => ['index']]);
	Route::get('authenticate/user/{sc_token}', 'CompanyController@getCompanyByToken');
	Route::post('authenticate/user/deleteCompany/{sc_token}', 'CompanyController@deleteCompany');
	Route::post('authenticate/user/updateCompany/{sc_token}', 'CompanyController@updateCompany');
	Route::post('authenticate/user/createCompany', 'CompanyController@createCompany');
	//Route::post('user/getStripeTokenFromCardDetails', 'CompanyController@getStripeTokenFromCardDetails');
	Route::post('authenticate/user/ghlo/{sc_token}', 'CompanyController@ghostLogin');
	Route::get('authenticate/settings', 'CompanyController@getAllDefaultCompanySetting');
	Route::get('authenticate/companyAdmin', 'CompanyController@getCompanyAdmin');
	
	Route::get('authenticate/getCompanyPermissions/{sc_token}', 'CompanyController@getCompanyPermissionByToken');
	
	Route::get('authenticate/getCompanyUsers', 'CompanyController@getCompanyUsersByCompany');
	
	//For admin
	Route::post('authenticate/createScxAdmin', 'CompanyController@createScxAdmin');
	Route::get('authenticate/scxAdmin/{sc_token}', 'CompanyController@getScxAdminByToken');
	Route::get('authenticate/scxAdmin', 'CompanyController@listScxAdmin');
	Route::post('authenticate/updateScxAdmin/{sc_token}', 'CompanyController@updateScxAdmin');
	Route::post('authenticate/deleteScxAdmin/{sc_token}', 'CompanyController@deleteScxAdmin');
	
	//For company-user
	Route::post('authenticate/createCompanyUser', 'CompanyController@createCompanyUser');
	Route::get('authenticate/companyUser/{sc_token}', 'CompanyController@getCompanyUserByToken');
	Route::get('authenticate/companyUsers', 'CompanyController@listCompanyUsers');
	Route::post('authenticate/updateCompanyUser/{sc_token}', 'CompanyController@updateCompanyUser');
	Route::post('authenticate/deleteCompanyUser/{sc_token}', 'CompanyController@deleteCompanyUser');
	
	// List the company-user by sc_token
	Route::get('authenticate/companyUsers/{sc_token}', 'CompanyController@getCompanyUsersByToken');
	// List company-sub by sc_token
	Route::get('authenticate/business/{sc_token}', 'CompanyController@getBusinessByToken');
	
	//@vikram
	Route::post('authenticate/setpasslink','AuthenticateController@checkPasswordLink');
	Route::post('authenticate/setPassword','AuthenticateController@setPassword');
	Route::post('authenticate/actionAccess', 'CompanyController@actionAccess');
	
	Route::post('authenticate/checkEmailForgetPassword','AuthenticateController@checkEmailForgetPassword');
	
	// For Company Sub
	Route::post('authenticate/createCompanySub', 'CompanyController@createCompanySub');
	Route::get('authenticate/company-sub/{sc_token}', 'CompanyController@getCompanySubByToken');
	Route::get('authenticate/companies-sub', 'CompanyController@listCompanySub');
	Route::post('authenticate/updateCompanySub/{sc_token}', 'CompanyController@updateCompanySub');
	Route::post('authenticate/deleteCompanySub/{sc_token}', 'CompanyController@deleteCompanySub');
	
	// testing email
	//Route::post('authenticate/testmail', 'CompanyController@sendMail');
	Route::post('authenticate/addMerchantFromLink/{sc_token}', 'CompanyController@addMerchantFromLink');
	//export merchants details
	Route::get('authenticate/exportmerchantDetails', 'CompanyController@exportMerchantDetails');
	
	// Check
	Route::post('authenticate/saveCheck', 'CheckController@saveCheck');
	Route::get('authenticate/getCheckDetails', 'CheckController@getCheckById');
	Route::get('authenticate/searchChecks', 'CheckController@searchChecks');
	Route::post('authenticate/editcheck/{check_token}', 'CheckController@editCheck');
	Route::post('authenticate/deletecheck/{check_token}', 'CheckController@deleteCheck');
	Route::post('authenticate/generate/checkout', 'CheckController@createCheckoutLink');
	Route::post('authenticate/generate/bankauth', 'CheckController@createBankAuthLink');
	
	// Check routing number and get info
	Route::get('authenticate/checkRoutingNumber/{routing_number}', 'CheckController@checkRoutingNumber');
	//Get merchant and sub merchant for loggedin user
	Route::get('authenticate/getCompanySubList/{sc_token}', 'CheckController@getCompanySubList');
	// Enter Check
	Route::post('authenticate/enter/save/check', 'CheckController@saveCheck');
	
	// Enter Check vikram
	Route::post('authenticate/viewsearchcheck', 'CheckController@viewSearchCheck');
	Route::get('authenticate/editcheck/{mc_token}', 'CheckController@editCheck');
	Route::post('authenticate/enter/update/check/{mc_token}', 'CheckController@updateCheck');
	Route::get('authenticate/check/printcheck/{mc_token}', 'CheckController@printCheck');
	
	
	// Link (checkout and bank auth)
	//Route::post('checkout/{checkout_token}/{company_id}/{fee_type}/{signture}', 'CheckController@saveCheck');
	//Route::post('payauth/{checkout_token}/{company_id}/{fee_type}/{signture}', 'CheckController@saveCheck');
	
	Route::get('authenticate/test1', 'CheckController@test');
	Route::post('authenticate/demo', 'CheckController@demo');
	
	
	
	// List Stripe Charges
	//Route::get('authenticate/listcharges', 'UserSubscriptionOtherController@listStripeCharges');
	
	// Permissions
	Route::get('authenticate/permissions/{sc_token}', 'CompanyController@getPermissionByToken');
	Route::get('authenticate/merchant/permissions/{sc_token}', 'CompanyController@getMerchantPermission');
	
	// For Check Message
	Route::get('authenticate/getMessages', 'CheckMessageController@getMessages');
	Route::post('authenticate/deleteMessage/{id}', 'CheckMessageController@deleteMessage');
	Route::get('authenticate/message/{id}', 'CheckMessageController@getMessageById');
	Route::post('authenticate/createMessage', 'CheckMessageController@createMessage');
	Route::post('authenticate/updateMessage/{id}', 'CheckMessageController@updateMessage');
	
	// For Check Fee
	Route::get('authenticate/getFee', 'CheckBasicFeeController@getFee');
	Route::post('authenticate/deleteFee/{id}', 'CheckBasicFeeController@deleteFee');
	Route::get('authenticate/fee/{id}', 'CheckBasicFeeController@getFeeById');
	Route::post('authenticate/createFee', 'CheckBasicFeeController@createFee');
	Route::post('authenticate/updateFee/{id}', 'CheckBasicFeeController@updateFee');
	
	// For Check Permission
	Route::get('authenticate/getPermission', 'UserPermissionController@getPermission');
	Route::post('authenticate/deletePermission/{id}', 'UserPermissionController@deletePermission');
	Route::get('authenticate/permissionById/{id}', 'UserPermissionController@getPermissionById');
	Route::post('authenticate/createPermission', 'UserPermissionController@createPermission');
	Route::post('authenticate/updatePermission/{id}', 'UserPermissionController@updatePermission');
	
	
	// For Check Email
	Route::get('authenticate/getEmail', 'CheckSettingsController@getEmail');
	Route::post('authenticate/deleteEmail/{id}', 'CheckSettingsController@deleteEmail');
	Route::get('authenticate/email/{id}', 'CheckSettingsController@getEmailById');
	Route::post('authenticate/createEmail', 'CheckSettingsController@createEmail');
	Route::post('authenticate/updateEmail/{id}', 'CheckSettingsController@updateEmail');
	
	// For Check Plan
	Route::get('authenticate/getPlans', 'SubscriptionPlanDetailController@getPlans');
	Route::post('authenticate/deletePlan/{id}', 'SubscriptionPlanDetailController@deletePlan');
	Route::get('authenticate/getPlanById/{id}', 'SubscriptionPlanDetailController@getPlanById');
	Route::post('authenticate/createPlan', 'SubscriptionPlanDetailController@createPlan');
	Route::post('authenticate/updatePlan/{id}', 'SubscriptionPlanDetailController@updatePlan');
	
	Route::get('authenticate/planDetails', 'SubscriptionPlanDetailController@getPlanDetailsSettingsByStripePlanID');
	
	// For Check Email Template
	Route::get('authenticate/getEmailTemplate', 'EmailTemplateController@getEmailTemplate');
	Route::post('authenticate/deleteEmailTemplate/{id}', 'EmailTemplateController@deleteEmailTemplate');
	Route::get('authenticate/emailtemplate/{id}', 'EmailTemplateController@getEmailTemplateById');
	Route::post('authenticate/createEmailTemplate', 'EmailTemplateController@createEmailTemplate');
	Route::post('authenticate/updateEmailTemplate/{id}', 'EmailTemplateController@updateEmailTemplate');
	
	// For Status
	Route::get('authenticate/getStatus', 'UserStatusController@getStatus');
	Route::get('authenticate/status/{id}', 'UserStatusController@getStatusById');
	Route::post('authenticate/createStatus', 'UserStatusController@createStatus');
	Route::post('authenticate/updateStatus/{id}', 'UserStatusController@updateStatus');
	
	// List Stripe Charges
	Route::get('authenticate/listcharges', 'InvoiceController@listStripeCharges');
	
	// For Invoice
	Route::post('authenticate/viewinvoice','InvoiceController@viewInvoice');
	Route::get('authenticate/downloadinvoiceaspdf/{stripe_id}','InvoiceController@downloadInvoiceAsPdf');
    Route::post('authenticate/downloadmultipleinvoiceaspdf','InvoiceController@downloadMultipleInvoiceAsPdf');

	
});


