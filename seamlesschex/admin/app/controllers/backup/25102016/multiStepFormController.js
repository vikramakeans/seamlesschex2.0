(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('MultiStepFormController', MultiStepFormController);
		//.directive('jqtimepicker', jqtimepicker)
		//.directive('somedirective', somedirective);
	
	
	function MultiStepFormController($scope, $location, $auth, $state, $http, $rootScope, API_URL, $payments, $stateParams, $timeout) {
		
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
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		$scope.role_id = $stateParams.role_id;
		$scope.company.settings = {};
		
		$scope.company.default_settings = [];
		vm.companyError = false;
		vm.companySuccess = false;
		vm.companyErrorText;
		vm.companySuccessText;
		$('input.timepicker').timepicker({});
		
		
		
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
		//$scope.myModel = {};
		$scope.myPrompt = "Phone Number* Required to Activate Account ";
		//$scope.message = "Credit card validation with ngPayments";
		
		$scope.steps = [
		'Company Details',
		'Bank Details',
		'Fee Settings',
		'Plan Details',
		'Credit Card',
		'Permissions',
		'Processing Cutoff'
		];
		//Check which page tab
		if($scope.action == 'addCompanySub' || $scope.action == 'editCompanySub'){
			$scope.steps = [
			'Company Details',
			'Plan Details'
			];
		}
		if($scope.action == 'addCompanyUser' || $scope.action == 'editCompanyUser'){
			$scope.steps = [
			'User Details'
			];
		}
		if($scope.action == 'profileEdit'){
			$scope.steps = [
			'Company Details',
			'Credit Card'
			];
		}
		if($scope.action == 'viewPrintCheck'){
			$scope.steps = [
			'Entered checks',
			'Recurring Payments'
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
		
		$scope.models = {
			time: new Date(),
			format: 'h:mm a',
			minTime: '9:00 am',
			maxTime: '9:00 pm',
			step: '30'
		};
				
		// Get the default settings for company-admin
		if($scope.action == 'addCompanyAdmin'){
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
			
			// Create Company
			$scope.createCompany = function() {
				// console.log($scope.company.name);
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
					is_mailchimp_update: $scope.company.mailchimp
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
				// Processing Cutoff
				var batchSettings = {
					saveBatchSettings: true,
					batch_settings: $scope.company.batch_settings
				}
				
				var companyAdminDetails = angular.extend({}, companyDetails, bankDetails, feeSettings, planDetails, creditCardDetails, permissionSettings, batchSettings);
				//console.log(angular.extend({}, companyDetails, bankDetails, feeSettings, planDetails, creditCardDetails, permissionSettings, batchSettings));
				$http.post( API_URL + 'api/authenticate/user/createCompany', companyAdminDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Company Admin Created Successfully';
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
				 
				 
			}).error(function(error) {
				vm.error = error;
			});
			
			// Update Company Details
			$scope.companyDetails = function() {
				//console.log($scope.company.name);
				var companyDetails = {
					updateCompanyDetails: true,
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
					password: $scope.company.password
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
					stripe_plan: $scope.company.stripe_plan,
					trial_ends_at: $scope.company.trial_ends_at,
					subscription_ends_at: $scope.company.subscription_ends_at,
					status: $scope.company.status,
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
			}
			
			// Update Batch Settings
			
			$scope.batchSettings = function() {
				
				//console.log($scope.company.settings);
				var batch_settings = $scope.company.batch_settings;
				var permissionSettings = {
					batchSettings: true,
					batch_settings: batch_settings
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, permissionSettings).then(function(response) {
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
		// Get all comany admin in dropdown
		if($scope.action == 'addCompanyUser' || $scope.action == 'editCompanyUser' || $scope.action == 'editCompanySub' || $scope.action == 'addCompanySub' || $scope.action == 'viewPrintCheck' || $scope.action == 'addSubscription'){
			//$scope.selectedItem =  {id:501,company_name:"Test again"} ;
			$http.get( API_URL + 'api/authenticate/companyAdmin').success(function(response) {
				//console.log(response);
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(companyAdmin, key){
					 $scope.company.company_admin = companyAdmin;
				 });
				 //console.log($scope.company.company_admin);
				
				 }).error(function(error) {
				vm.error = error;
			});
			
		}
		
	}
	
})();