(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('MultiStepFormController', MultiStepFormController);
	
	
	function MultiStepFormController($scope, $location, $auth, $state, $http, $rootScope, API_URL, $payments, $stateParams, $timeout, $filter, $mdDialog) {
		
		var vm = this;
		vm.companyAdmin = {};
		$scope.company = {};
		
		vm.registerError = false;
		vm.registerSuccess = false;
		vm.registerErrorText;
		vm.registerSuccessText;
		var stripeToken;
		$scope.isDisabled = false;
		$scope.value= '24.99';
		$scope.planAmount={};
		
		$scope.sc_token = $stateParams.sc_token;
		$scope.mc_token = $stateParams.mc_token;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		$scope.role_id = $stateParams.role_id;
		$scope.company.settings = {};
		$scope.company_settings = {};
		$scope.company_settings.EXITINGCARD = 'yes';
		
		$scope.authSuAdmin = $rootScope.authSuAdmin;
		$scope.authScxAdmin = $rootScope.authScxAdmin;
		$scope.authcompAdmin = $rootScope.authcompAdmin;
		
		$scope.company.default_settings = [];
		vm.companyError = false;
		vm.companySuccess = false;
		vm.companyErrorText;
		vm.companySuccessText;
		
		
		$scope.options = {
			step: 15,
			timeFormat: 'H:i A'
		};

		$scope.company.time = {};
		// Set the amount value as per plan selected
		$scope.setAmount = function(value) {
		   $scope.amount = value;
		}
		
		// Get the default image for credit card icon if empty
		$scope.checkEmpty = function(value){
			if($scope.number == ""){
				$scope.type = '';
			}
		}
		$scope.steps = [
		'Company Details',
		'Bank Details',
		'Fee Settings',
		'Plan Details',
		'Account',
		'Permissions',
		'Processing Cutoff'
		];
		
		if($scope.action == 'editPermissionSettings' || $scope.action == 'addPermissionSettings'){
			$scope.steps = [
				'Permission Settings'
			];
		}
		
	
		if($scope.action == 'editPlanDetails' || $scope.action == 'addPlanDetails'){
			$scope.steps = [
				'Plan Details'
			];
		}
		//$scope.myModel = {};
		$scope.myPrompt = "Phone Number* Required to Activate Account ";
		//$scope.message = "Credit card validation with ngPayments";
		if($scope.action == 'addCompanyAdmin'){
			$scope.steps = [
			'Company Details',
			'Bank Details',
			'Deposit Services',
			'Plan Details',
			'Account',
			'Permissions'
			];
		}
		if($scope.action == 'editCompanyAdmin'){
			$scope.steps = [
			'Company Details',
			'Bank Details',
			'Deposit Services',
			'Plan Details',
			//'Multiple Subscription',
			'Account',
			'Permissions'
			];
		}
		//Check which page tab
		if($scope.action == 'addCompanySub' || $scope.action == 'editCompanySub'){
			$scope.steps = [
			'Merchant Details',
			//'Plan Details'
			];
		}
		if($scope.action == 'addCompanyUser' || $scope.action == 'editCompanyUser'){
			$scope.steps = [
			'User Details'
			];
		}
		
		// For super admin and seamlesschex admin
		if(($scope.action == 'profileEdit' && $scope.authSuAdmin == true) || ($scope.action == 'profileEdit' && $scope.authScxAdmin == true) ){
			$scope.steps = [
			'Profile'
			];
		}
		// For merchant
		if(($scope.action == 'profileEdit' && $scope.authcompAdmin == true)){
			$scope.steps = [
			'Profile',
			'Account',
			'Credit Card'
			];
		}
		//Merchant Account Link
		if($scope.action == 'merchantAccount'){
			$scope.steps = [
			'Profile',
			'Company & Team',
			'Billing',
			'Invoices'
			];
		}
		
		if($scope.action == 'viewPrintCheck'){
			$scope.steps = [
			'Search checks'
			//'Entered checks',
			//'Recurring Payments'
			];
		}
		
		if($scope.action == 'addSubscription'){
			$scope.steps = [
			'Subscription'
			];
		}
		
		if($scope.action == 'addScxAdmin' || $scope.action == 'editScxAdmin'){
			$scope.steps = [
			'Seamlesschex Admin'
			];
		}
		
		$scope.statuses = [
		'active',
		'inactive',
		'trialing',
		'past_due',
		'canceled',
		'unpaid',
		'unknown',
		'delete'
		];
		
		$scope.timeZone = [
		'EST',
		'CST',
		'MST',
		'PST'
		];
		
		$scope.selection = $scope.steps[0];

		$scope.getCurrentStepIndex = function(){
			// Get the index of the current step given selection
			return _.indexOf($scope.steps, $scope.selection);
		};

		// Go to a defined step index
		$scope.goToStep = function(index) {
			if ( !_.isUndefined($scope.steps[index]) )
			{
			  $scope.selection = $scope.steps[index];
			}
		};

		$scope.hasNextStep = function(){
			var stepIndex = $scope.getCurrentStepIndex();
			var nextStep = stepIndex + 1;
			// Return true if there is a next step, false if not
			return !_.isUndefined($scope.steps[nextStep]);
		};

		$scope.hasPreviousStep = function(){
			var stepIndex = $scope.getCurrentStepIndex();
			var previousStep = stepIndex - 1;
			// Return true if there is a next step, false if not
			return !_.isUndefined($scope.steps[previousStep]);
		};

		$scope.incrementStep = function() {
			if ( $scope.hasNextStep() )
			{
			  var stepIndex = $scope.getCurrentStepIndex();
			  var nextStep = stepIndex + 1;
			  $scope.selection = $scope.steps[nextStep];
			}
		};

		$scope.decrementStep = function() {
			if ( $scope.hasPreviousStep() )
			{
			  var stepIndex = $scope.getCurrentStepIndex();
			  var previousStep = stepIndex - 1;
			  $scope.selection = $scope.steps[previousStep];
			}
		};
		
		
				
		// Get the default settings for company-admin
		if($scope.action == 'addCompanyAdmin'){
			
			getDefaultSettingsMerchant();
	
			
			// Create Company
			$scope.createCompany = function() {
				
				console.log($scope.company.name);
				console.log($scope.company.company_settings);
				// Company Details
				var companyDetails = {
					saveCompany: true,
					name: $scope.company.name,
					cname: $scope.company.cname,
					saddress: $scope.company.saddress,
					city: $scope.company.city,
					state: $scope.company.state,
					zip: $scope.company.zip,
					business_type: $scope.company.business_type,
					phone: $scope.company.phone,							
					email: $scope.company.email,
					password: $scope.company.password,
					website: $scope.company.website,
					taxid: $scope.company.taxid
				}
				// Bank Details
				var bankDetails = {
					saveBankDetails: true,
					bank_name: $scope.company.bank_name,
					bank_routing: $scope.company.bank_routing,
					bank_account_no: $scope.company.bank_account_no,
					authorised_signer: $scope.company.authorised_signer
				}
				// Fee Settings
				var feeSettings = {
					saveFeeSettings: true,
					daily_deposite_fee: $scope.company.fee.DAILY_DEPOSIT_FEE,
					check_verification_fee: $scope.company.fee.CHECK_VERIFICATION_FEE,
					per_check_fee: $scope.company.PER_CHECK_FEE,
					check_processing_fee: $scope.company.CHECK_PROCESSING_FEE
				}
				// Plan Details
				var planDetails = {
					savePlanDetails: true,
					basic_verifications: $scope.company.company_settings.BASICVERFICATIONS,
					total_no_check: $scope.company.company_settings.TOTALNOCHECK,
					no_of_check_remaining: $scope.company.company_settings.NOOFCHECKREMAINING,
					total_fundconfirmation: $scope.company.company_settings.TOTALFUNDCONFIRMATION,
					remaining_fundconfirmation: $scope.company.company_settings.REMAININGFUNDCONFIRMATION,
					total_payauth: $scope.company.company_settings.TOTALPAYAUTH,
					payauth_remaining: $scope.company.company_settings.PAYAUTHREMAINING,
					total_no_of_company: $scope.company.company_settings.TOTAL_COMPANY,
					remaining_no_of_company: $scope.company.company_settings.REMAINING_COMPANY,
					total_no_of_user: $scope.company.company_settings.TOTAL_USER,
					remaining_no_of_user: $scope.company.company_settings.REMAINING_USER,
					companies_permission: $scope.company.company_settings.COMPANY,
					pay_auth_permission: $scope.company.company_settings.BANKAUTHLINK,
					payment_link_permission: $scope.company.company_settings.CHECKOUTLINK,
					signture_permission: $scope.company.company_settings.SIGNTURE,
					fundconfirmation_permission: $scope.company.company_settings.FUNDCONFIRMATION,
					fundconfirmation_fee: $scope.company.company_settings.FUNDCONFIRMATION_FEE,
					stripe_plan: $scope.company.stripe_plan,
					trial_ends_at: $scope.company.trial_ends_at,
					subscription_ends_at: $scope.company.subscription_ends_at,
					status: $scope.company.status,
					//monthly_fee: $scope.company.monthly_user,
					amount: $scope.company.fee.MONTHLY_FEE,
					monthly_fee: $scope.company.fee.MONTHLY_FEE,
					permissions: $scope.company.company_settings
					
				}
				// Credit Card Details
				var creditCardDetails = {
					saveCreditCardDetails: true,
					number: $scope.company.number,
					exp_month: $scope.company.exp_month,
					exp_year: $scope.company.exp_year,
					cvc: $scope.company.cvc
				}
				// Permission Settings
				var permissionSettings = {
					savePermissions: true,
					user_settings: $scope.company.settings
				}
				console.log($scope.company.settings);
				// Processing Cutoff
				$scope.batch_settings = [];
				if($scope.company.time == undefined){
					$scope.batch_settings = [];
				}else{
					
					$scope.batch_settings = [{'same_day_processing_cutoff':
					[{'monday_time':moment($scope.company.time.monday).format('H:m A'), 'monday_timezone':$scope.company.batch_settings.monday_timezone, 'tuesday_time':moment($scope.company.time.tuesday).format('H:m A'), 'tuesday_timezone':$scope.company.batch_settings.tuesday_timezone, 'wednesday_time':moment($scope.company.time.wednesday).format('H:m A'), 'wednesday_timezone':$scope.company.batch_settings.wednesday_timezone, 'thursday_time':moment($scope.company.time.thursday).format('H:m A'), 'thursday_timezone':$scope.company.batch_settings.thursday_timezone, 'friday_time':moment($scope.company.time.friday).format('H:m A'), 'friday_timezone':$scope.company.batch_settings.friday_timezone, 'saturday_time':moment($scope.company.time.saturday).format('H:m A'), 'saturday_timezone':$scope.company.batch_settings.saturday_timezone, 'sunday_time':moment($scope.company.time.sunday).format('H:m A'), 'sunday_timezone':$scope.company.batch_settings.sunday_timezone}]
					}];
				}
				
				var batchSettings = {
					saveBatchSettings: true,
					batch_settings: $scope.batch_settings
				}
				
				var companyAdminDetails = angular.extend({}, companyDetails, bankDetails, feeSettings, planDetails, creditCardDetails, permissionSettings, batchSettings);
				//console.log(angular.extend({}, companyDetails, bankDetails, feeSettings, planDetails, creditCardDetails, permissionSettings, batchSettings));
				$http.post( API_URL + 'api/authenticate/user/createCompany', companyAdminDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Merchant Created Successfully';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			
		}
		// Edit Company
		// Get the one company details as per the sc_token
		if($scope.sc_token && $scope.sc_token != null && $scope.action == 'editCompanyAdmin'){
			
			var sc_token = $scope.sc_token;
			
			//Grab the company details from the API
			getMerchantDetailsByToken(sc_token);
			
			
			// Update Company Details
			$scope.companyDetails = function() {
				//console.log($scope.company.name);
				var companyDetails = {
					updateCompanyDetails: true,
					stripeUpdateEmail: $scope.company.STRIPE_UPDATE_EMAIL,
					name: $scope.company.name,
					cname: $scope.company.cname,
					saddress: $scope.company.saddress,
					city: $scope.company.city,
					state: $scope.company.state,
					zip: $scope.company.zip,
					business_type: $scope.company.business_type,
					phone: $scope.company.phone,							
					email: $scope.company.email,
					stripe_id: $scope.company.stripe_id,
					password: $scope.company.password,
					website: $scope.company.website,
					taxid: $scope.company.taxid
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, companyDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Updated Company Details';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			
			// Update Bank Details
			$scope.bankDetails = function() {
				//console.log($scope.company.name);
				var bankDetails = {
					updateBankDetails: true,
					bank_name: $scope.company.bank_name,
					bank_routing: $scope.company.bank_routing,
					bank_account_no: $scope.company.bank_account_no,
					authorised_signer: $scope.company.authorised_signer
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, bankDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Updated Company Details';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			// Update Fee Settings
			$scope.feeSettings = function() {
				
				var feeSettings = {
					updateFeeSettings: true,
					daily_deposite_fee: $scope.company.fee.DAILY_DEPOSIT_FEE,
					check_verification_fee: $scope.company.fee.CHECK_VERIFICATION_FEE,
					per_check_fee: $scope.company.PER_CHECK_FEE,
					check_processing_fee: $scope.company.CHECK_PROCESSING_FEE
				}
				
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, feeSettings).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Fee Details Updated';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			
			// Update Plan Details
			$scope.planDetails = function() {
				//console.log($scope.company.name);
				var planDetails = {
					updatePlanDetails: true,
					total_no_check: $scope.company.company_settings.TOTALNOCHECK,
					no_of_check_remaining: $scope.company.company_settings.NOOFCHECKREMAINING,
					total_fundconfirmation: $scope.company.company_settings.TOTALFUNDCONFIRMATION,
					remaining_fundconfirmation: $scope.company.company_settings.REMAININGFUNDCONFIRMATION,
					total_payauth: $scope.company.company_settings.TOTALPAYAUTH,
					payauth_remaining: $scope.company.company_settings.PAYAUTHREMAINING,
					companies_permission: $scope.company.company_settings.COMPANY,
					pay_auth_permission: $scope.company.company_settings.BANKAUTHLINK,
					payment_link_permission: $scope.company.company_settings.CHECKOUTLINK,
					signture_permission: $scope.company.company_settings.SIGNTURE,
					fundconfirmation_permission: $scope.company.company_settings.FUNDCONFIRMATION,
					fundconfirmation_fee: $scope.company.company_settings.FUNDCONFIRMATION_FEE,
					stripe_update: $scope.company.company_settings.STRIPE_UPDATE,
					stripe_plan: $scope.company.stripe_plan,
					trial_ends_at: $scope.company.trial_ends_at,
					subscription_ends_at: $scope.company.subscription_ends_at,
					status: $scope.company.status.status_code,
					//monthly_fee: $scope.company.monthly_user,
					amount: $scope.company.fee.MONTHLY_FEE,
					monthly_fee: $scope.company.fee.MONTHLY_FEE,
					permissions: $scope.company.company_settings
					
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, planDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Plan Details Updated';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			
			// Update Credit Card in Stripe
			$scope.creditCardDetails = function() {
				//console.log($scope.company.name);
				var creditCardDetails = {
					updateCreditCardDetails: true,
					number: $scope.company.number,
					exp_month: $scope.company.exp_month,
					exp_year: $scope.company.exp_year,
					cvc: $scope.company.cvc
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, creditCardDetails).then(function(response) {
					//console.log(response);
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Card Details Updated';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			
			// Update Permission Settings
			
			$scope.permissionSettings = function() {
				
				//console.log($scope.company.settings);
				var user_settings = $scope.company.settings;
				var permissionSettings = {
					updatePermissions: true,
					user_settings: user_settings
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, permissionSettings).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Permission settings updated';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			};
			
			
			
			
			// Update Batch Settings
			$scope.batchSettings = function() {
				
				$scope.batch_settings = [{'same_day_processing_cutoff':
				[{'monday_time':moment($scope.company.time.monday).format('H:m A'), 'monday_timezone':$scope.company.batch_settings.monday_timezone, 'tuesday_time':moment($scope.company.time.tuesday).format('H:m A'), 'tuesday_timezone':$scope.company.batch_settings.tuesday_timezone, 'wednesday_time':moment($scope.company.time.wednesday).format('H:m A'), 'wednesday_timezone':$scope.company.batch_settings.wednesday_timezone, 'thursday_time':moment($scope.company.time.thursday).format('H:m A'), 'thursday_timezone':$scope.company.batch_settings.thursday_timezone, 'friday_time':moment($scope.company.time.friday).format('H:m A'), 'friday_timezone':$scope.company.batch_settings.friday_timezone, 'saturday_time':moment($scope.company.time.saturday).format('H:m A'), 'saturday_timezone':$scope.company.batch_settings.saturday_timezone, 'sunday_time':moment($scope.company.time.sunday).format('H:m A'), 'sunday_timezone':$scope.company.batch_settings.sunday_timezone}]
				}];
				//console.log($scope.batch_settings);
				
				//console.log($scope.company.time.monday);
				//console.log($scope.company.timezone.monday);
				//console.log(moment($scope.company.time.monday).format('H:m A'));
				
				var batch_settings = $scope.batch_settings;
				//console.log(batch_settings);
				var batchSettings = {
					batchSettings: true,
					batch_settings: $scope.batch_settings
				}
				
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, batchSettings).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Batch Settings Updated';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			
			
		}
		//console.log($scope.action);
		// Get all comany admin in dropdown
		if( ($scope.action == 'addCompanyUser' || $scope.action == 'editCompanyUser' || $scope.action == 'editCompanySub' || $scope.action == 'addCompanySub' || $scope.action == 'viewPrintCheck' || $scope.action == 'addSubscription' || $scope.action == 'viewPrintCheck' || $scope.action == 'importCheks' || $scope.action == 'importedCheks' || $scope.action == 'printCheks') && ($scope.authSuAdmin == true || $scope.authScxAdmin == true) ){
			//$scope.selectedItem =  {id:501,company_name:"Test again"} ;
			// get merchant dropdown from api
			getMerchantDropdown();
						
		};
		// Get plan details settings
		$scope.getPlanDetailsSettings = function() {
			
			var paramSettings = { getPlanSettings: true, stripe_plan: $scope.company.stripe_plan }
			var config = { params: paramSettings, headers : {'Accept' : 'application/json'} };

			$http.get( API_URL + 'api/authenticate/planDetails',config).success(function(response) {
					
					var response = JSON.stringify(response);
					var data = JSON.parse(response);
					$scope.data = data;
					angular.forEach($scope.data, function(planDetails, key){
						 $scope.company_autopopulate = planDetails;
					 });
					 // auto pulate as per plan details
					 $scope.company.stripe_plan = $scope.company_autopopulate.stripe_plan;
					 $scope.company.fee.MONTHLY_FEE = $scope.company_autopopulate.amount;
					 $scope.company.company_settings.TOTALNOCHECK = $scope.company_autopopulate.no_of_check;
					 $scope.company.company_settings.NOOFCHECKREMAINING = $scope.company_autopopulate.no_of_check;
					 $scope.company.company_settings.TOTALFUNDCONFIRMATION = $scope.company_autopopulate.fundconfirmation_no_check;
					 $scope.company.company_settings.REMAININGFUNDCONFIRMATION = $scope.company_autopopulate.fundconfirmation_no_check;
					 $scope.company.company_settings.TOTALPAYAUTH = $scope.company_autopopulate.bank_auth_link_no_check;
					 $scope.company.company_settings.PAYAUTHREMAINING = $scope.company_autopopulate.bank_auth_link_no_check;
					 $scope.company.company_settings.TOTAL_USER = $scope.company_autopopulate.no_of_users;
					 $scope.company.company_settings.REMAINING_USER = $scope.company_autopopulate.no_of_users;
					 $scope.company.company_settings.TOTAL_COMPANY = $scope.company_autopopulate.no_of_companies;
					 $scope.company.company_settings.REMAINING_COMPANY = $scope.company_autopopulate.no_of_companies;
					 
					 $scope.company.company_settings.BASICVERFICATIONS = $scope.company_autopopulate.settings.BASICVERFICATIONS;
					 $scope.company.company_settings.FUNDCONFIRMATION = $scope.company_autopopulate.settings.FUNDCONFIRMATION;
					 $scope.company.company_settings.BANKAUTHLINK = $scope.company_autopopulate.settings.BANKAUTHLINK;
					 $scope.company.company_settings.SIGNTURE = $scope.company_autopopulate.settings.SIGNTURE;
					 $scope.company.company_settings.COMPANY = $scope.company_autopopulate.settings.COMPANY;
					 $scope.company.company_settings.CHECKOUTLINK = $scope.company_autopopulate.settings.CHECKOUTLINK;
					
				 }).error(function(error) {
				vm.error = error;
			});
			
		};
		
		// Get default settings for merchant
		function getDefaultSettingsMerchant(){
			
			var role_id = $scope.role_id;
			var paramSettings = { getSettings: true, role_id: role_id }
			var config = { params: paramSettings, headers : {'Accept' : 'application/json'} };

			$http.get( API_URL + 'api/authenticate/settings',config).success(function(response) {
					//console.log(response);
					var response = JSON.stringify(response);
					var data = JSON.parse(response);
					$scope.data = data;
					angular.forEach($scope.data, function(companyValue, key){
						 $scope.company = companyValue;
						 $scope.company.fee = companyValue.fees;
						 // Setting fee to 0
						 $scope.company.fee.CHECK_VERIFICATION_FEE = 0;
						 $scope.company.fee.CHECK_PROCESSING_FEE = 0;
						 $scope.company.fee.PER_CHECK_FEE = 0;
						 $scope.company.fee.DAILY_DEPOSIT_FEE = 0;
						 
						 // default fees
						 $scope.company.settings = companyValue.user_settings;
						 $scope.company.def_set = companyValue.default_settings;
						 $scope.company.plans = companyValue.all_paln;
						 //Settings for no_of_check, remaining_check etc (plan details)
						 $scope.company.company_settings = companyValue.plan_settings;
						 $scope.company.check_cutoff_seetings = companyValue.plan_settings.check_cutoff_seetings.same_day_processing_cutoff;
						 //$scope.company.plan_settings = $scope.company.plan_settings;
						 
					 });
					 
					 
					 // All permission listing
					 var response_default_settings = JSON.stringify($scope.company.def_set);
					 var default_sett = JSON.parse(response_default_settings);
					 $scope.per_set = default_sett;
					 angular.forEach($scope.per_set, function(defaultSettValue, key){
						 $scope.company.default_settings = defaultSettValue.per_set;
					 });
					 
					 // For All Plan Drop down
					 var response_all_plan = JSON.stringify($scope.company.plans);
					 var plans = JSON.parse(response_all_plan);
					 $scope.plans = plans;
					 angular.forEach($scope.plans, function(planValue, key){
						 $scope.company.all_plan = planValue.plans;
					 });
					 //console.log($scope.company);
					 //console.log($scope.company.check_cutoff_seetings);
				 }).error(function(error) {
				vm.error = error;
			});
		}
		
		// Grab Company Details from api by sc_token
		function getMerchantDetailsByToken(sc_token){
			
			$http.get( API_URL + 'api/authenticate/user/'+sc_token).success(function(response) {
			
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(companyValue, key){
					 $scope.company = companyValue;
					 //console.log($scope.company.fee);
					 if ("CHECK_VERIFICATION_FEE" in $scope.company.fee){
					 }else{
						 $scope.company.fee.CHECK_VERIFICATION_FEE = 0;
					 }
				 });
				 
				 // For All Plan Drop down
				 var response_all_plan = JSON.stringify($scope.company.all_plan);
				 var plans = JSON.parse(response_all_plan);
				 $scope.plans = plans;
				 angular.forEach($scope.plans, function(planValue, key){
					 $scope.company.all_plan = planValue.plans;
				 });
				
				 $scope.company.stripe_plan = $scope.company.stripe_plan;
				 // For default settings (permission)
				 
				 var response_default_settings = JSON.stringify($scope.company.default_settings);
				 var default_sett = JSON.parse(response_default_settings);
				 $scope.per_set = default_sett;
				 angular.forEach($scope.per_set, function(defaultSettValue, key){
					 $scope.company.default_settings = defaultSettValue.per_set;
					
				 });
				 
				 //$scope.company.settings = {};
				 $scope.company.settings = $scope.company.user_settings;
				 /*angular.forEach($scope.company.user_settings, function(s, key){
					$scope.company.settings[s.permission_name] = s.permission_value; 
				 });*/
				 
				 
				 //console.log($scope.company.batch_settings.same_day_processing_cutoff);
				 //$scope.company.batch_settings =  $scope.company.same_day_processing_cutoff;
				 //console.log($scope.company.batch_settings.same_day_processing_cutoff.time.monday);
				 //console.log($scope.company.batch_settings.time.monday);
				 // For batch_settings
				 //console.log($scope.company.batch_settings);
				 var response_batch_settings = JSON.stringify($scope.company.batch_settings);
				 var batch_settings = JSON.parse(response_batch_settings);
				 $scope.same_day_processing_cutoff = batch_settings;
				 
				 angular.forEach($scope.same_day_processing_cutoff, function(batch_sett, key){
					 
					 $scope.company.batch_settings = batch_sett.same_day_processing_cutoff[0];
				 });
				  //console.log($scope.company.batch_settings);
				 //console.log($scope.company.batch_settings);
				 //console.log($scope.company.batch_settings.monday_time);
				 //$scope.company.timezone = {};
				 //$scope.company.timezone.monday = {};
				 //$scope.company.timezone.monday = $scope.company.batch_settings.monday_timezone;
				 //$scope.company.monday_time = $scope.company.batch_settings.monday_time;
				 
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// Get merchnat dropdown
		function getMerchantDropdown(){

			$http.get( API_URL + 'api/authenticate/companyAdmin').success(function(response) {
				console.log(response);
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(companyAdmin, key){
					 $scope.company.company_admin = companyAdmin;
					 $scope.companysub.company_admin = companyAdmin;
					 // companyadmin.mc_token
				 });
					// $scope.company.company_admin = $scope.company.company_admin.mc_token;
					// console.log($scope.company.company_admin);
					$scope.companysub.company_admin = $scope.companysub.mc_token;
				 if($scope.mc_token && $scope.mc_token != null){
					$scope.companysub.company_admin = $scope.mc_token;
					$scope.companyuser.company_admin = $scope.mc_token;
				 }
				 //console.log($scope.company.company_admin);
				//$scope.company.company_admin = $scope.company.company_admin.mc_token;
				 }).error(function(error) {
				vm.error = error;
			});
		}
		
		
		
	}
	
})();