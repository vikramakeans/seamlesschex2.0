(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('TabController', TabController);

	function TabController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $timeout) {
		
		var vm = this;
		vm.error;
		vm.company = {};
		$scope.company = {};
		$scope.company.settings = {};
		//vm.company.permission_name = [];
		//$scope.permission_name = [];
		//$scope.permission_value = [];
		$scope.company.default_settings = [];
		vm.companyError = false;
		vm.companySuccess = false;
		vm.companyErrorText;
		vm.companySuccessText;
		$scope.sc_token = $stateParams.sc_token;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		//console.log($scope.action);
		// Get the default image for credit card icon if empty
		$scope.checkEmpty = function(value){
			//console.log($scope.company.number);
			if($scope.company.number == ""){
				$scope.company.type = '';
			}
		}
		
		this.tab = 1;
    
		this.selectTab = function (setTab){
			this.tab = setTab;
		};
		this.isSelected = function(checkTab) {
			return this.tab === checkTab;
		};
		
		// Get the one company details as per the sc_token
		if($scope.sc_token && $scope.sc_token != null){
			
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
					monthly_fee: $scope.company.fee.MONTHLY_FEE
					
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
		
	}
	
})();