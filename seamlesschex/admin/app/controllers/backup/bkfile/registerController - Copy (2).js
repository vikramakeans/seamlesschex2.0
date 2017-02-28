(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('RegisterController', RegisterController);

	function RegisterController($scope, $auth, $state, $http, $rootScope, API_URL) {

		var vm = this;
		vm.user = {};
		vm.registerError = false;
		vm.registerErrorText;
		var stripeToken;
		$scope.isDisabled = false;
		$scope.value= '24.99';
		$scope.planAmount={};
		$scope.setAmount = function(value) {
		   //console.log(value);
		   vm.user.amount = value;
		}
		
		vm.register = function() {
			//console.log(vm.user.agree);
			//console.log(vm.user.plan);
			//console.log($scope.form.userRegister);
			/* validation statrt */
			/*if(vm.user.name == '' || vm.user.name == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter Business Name';
				return false;
			}
			if(vm.user.cname == '' || vm.user.cname == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter Contact Name';
				return false;
			}
			if(vm.user.saddress == '' || vm.user.saddress == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter Street Address';
				return false;
			}
			if(vm.user.city == '' || vm.user.city == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter City';
				return false;
			}
			if(vm.user.state == '' || vm.user.state == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter State';
				return false;
			}
			if(vm.user.zip == '' || vm.user.zip == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter Zip';
				return false;
			}
			if(vm.user.business_type == '' || vm.user.business_type == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Select Industry Type';
				return false;
			}
			if(vm.user.email == '' || vm.user.email == undefined){
				vm.registerError = true;
				vm.registerErrorText = 'Please Enter Business Email';
				return false;
			}*/
			/* validation end */
			$scope.isDisabled = true;
			var userCardDetails = {
				number: vm.user.number,
				exp_month: vm.user.exp_month,
				exp_year: vm.user.exp_year,
				cvc: vm.user.cvc
			}
			
				// Get Token from stripe using laravel/php api call
				$http.post( API_URL + 'api/register/token', userCardDetails).then(function(response) {
					//console.log(response);
					/*if(response['data']['message']){
						vm.registerError = true;
						vm.registerErrorText = response['data']['message'];
						$scope.isDisabled = false;
						return false;
					}*/
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
								stripeToken: stripeToken
							}
							// Http post for registartion in system and stripe
							$auth.signup(userDetails).then(function(resposeRe) {
								console.log("success");
								$scope.isDisabled = false;
							}, function(error) {
								vm.registerError = true;
								vm.registerErrorText = error.data.error;
								$scope.isDisabled = false;
							});
					}
				}, function(error) {
					vm.registerError = true;
					vm.registerErrorText = error.data.error;
					$scope.isDisabled = false;
				});
			 
		}
	}
	
	

})();