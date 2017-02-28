(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('RegisterController', RegisterController);
	

	function RegisterController($scope, $auth, $state, $http, $rootScope, API_URL, $payments, $timeout) {
		
		var vm = this;
		vm.user = {};
		vm.registerError = false;
		vm.registerSuccess = false;
		vm.registerErrorText;
		vm.registerSuccessText;
		var stripeToken;
		$scope.isDisabled = false;
		$scope.value= '24.99';
		$scope.planAmount={};
		
		// Set the amount value as per plan selected
		$scope.setAmount = function(value) {
		   vm.user.amount = value;
		}
		
		// Get the default image for credit card icon if empty
		$scope.checkEmpty = function(value){
			if(vm.user.number == ""){
				vm.user.type = '';
			}
		}
		//$scope.myModel = {};
		$scope.myPrompt = "Phone Number* Required to Activate Account ";
		//$scope.message = "Credit card validation with ngPayments";

		//$scope.verified = function () {
			//return $payments.verified();
			
		//}
		//$scope.register.phone = undefined;
		
		vm.register = function() {
			//console.log(vm.user.agree);
			//console.log(vm.user.number);
			//console.log(vm.user.number.split(' ').join(''));
			//console.log(vm.user.phone);
			//console.log($scope.inputValue);
			//console.log($scope.form.userRegister);
			$scope.isDisabled = true;
			// For validation purpose sending all details
			var userCardDetails = {
				name: vm.user.name,
				cname: vm.user.cname,
				saddress: vm.user.saddress,
				city: vm.user.city,
				state: vm.user.state,
				zip: vm.user.zip,
				business_type: vm.user.business_type,
				phone: vm.user.phone,							
				email: vm.user.email,
				password: vm.user.password,
				number: vm.user.number,
				exp_month: vm.user.exp_month,
				exp_year: vm.user.exp_year,
				cvc: vm.user.cvc,
				plan: vm.user.plan,
				amount: vm.user.amount,
				agree: vm.user.agree
			}
			//console.log(vm.user.agree);
			if(vm.user.agree == 'yes'){
				// Get Token from stripe using laravel/php api call
				$http.post( API_URL + 'api/register/token', userCardDetails).then(function(response) {
					//console.log(response);
					
					if(response['data']['error']){
						vm.registerError = true;
						vm.registerErrorText = response['data']['error'];
						$scope.isDisabled = false;
						return false;
					}
					// Token contains id, last4, and card type:
					var stripeToken = response['data']['id'];
					var card_type = response['data']['card']['funding'];
					// If card type is prepaid not allow to register
					if(card_type=='prepaid'){
						vm.registerError = true;
						vm.registerErrorText = 'Unfortunately we do not accept prepaid cards';
						$scope.isDisabled = false;
						return false;
					}
					// If stripe token is there send all user details for registration
					// registration includes check in 1.mailchimp(if not insert it), 2.create customer in stripe and 14 days trial, 3. register in system
					if((stripeToken !='' && card_type !='prepaid')  || (stripeToken != null && card_type !='prepaid')){
							var userDetails = {
								name: vm.user.name,
								cname: vm.user.cname,
								saddress: vm.user.saddress,
								city: vm.user.city,
								state: vm.user.state,
								zip: vm.user.zip,
								business_type: vm.user.business_type,
								phone: vm.user.phone,							
								email: vm.user.email,
								password: vm.user.password,
								number: vm.user.number,
								exp_month: vm.user.exp_month,
								exp_year: vm.user.exp_year,
								cvc: vm.user.cvc,
								plan: vm.user.plan,
								amount: vm.user.amount,
								agree: vm.user.agree,
								stripeToken: stripeToken
							}
							// Http post for registartion in system and stripe
							$auth.signup(userDetails).then(function(resposeRe) {
								//console.log("success");
								if(resposeRe.data.success === true){
									vm.user = {};
									vm.registerSuccess = true;
									vm.registerSuccessText = 'Account Created Succesfully';
									$scope.isDisabled = false;
									$timeout(function () { vm.registerSuccess = false; }, 5000);
								}
								
							}, function(error) {
								vm.registerError = true;
								vm.registerErrorText = error.data.error;
								$scope.isDisabled = false;
								$timeout(function () { vm.registerError = false; }, 5000);
							});
					}
				}, function(error) {
					vm.registerError = true;
					vm.registerErrorText = error.data.error;
					$scope.isDisabled = false;
					$timeout(function () { vm.registerError = false; }, 5000);
				});
				
			}else{
				vm.registerError = true;
				vm.registerErrorText = 'Please agree terms and conditions';
				$scope.isDisabled = false;
			}
		}
	}
	
})();