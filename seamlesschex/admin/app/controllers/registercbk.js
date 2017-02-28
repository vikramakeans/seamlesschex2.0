(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('RegisterController', RegisterController);
	

	function RegisterController($scope, $auth, $state, $http, $rootScope,$stateParams,  API_URL, $payments, $timeout) {
		
		var vm = this;
		vm.user = {};
		vm.registerError = false;
		vm.registerSuccess = false;
		vm.registerErrorText;
		vm.registerSuccessText;
		var stripeToken;
		$scope.isDisabled = false;
		$scope.value= 'SeamlessChex Starter Plan';
		$scope.planAmount={};
		
		// Set the amount value as per plan selected
		$scope.setAmount = function(value) {
			vm.user.amount = 24.99;
			if(value == 'SeamlessChex Starter Plan'){
				vm.user.amount = 24.99;
			}
			if(value == 'SeamlessChex Pro Plan'){
				vm.user.amount = 49.99;
			}
			if(value == 'SeamlessChex Premium Plan'){
				vm.user.amount = 99.99;
			}
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
		
				
			//console.log(vm.user.number.split(' ').join(''));
			//console.log(vm.user.phone);
			//console.log($scope.inputValue);
			//console.log($scope.form.userRegister);
			var expire = vm.user.expiration;
			var exp_month = '';
			var exp_year = '';
			if(expire != undefined){
				exp_month = expire.substring(0,2);
				exp_year = expire.slice(-2);

				$scope.exp_year = exp_year;
				$scope.exp_month = exp_month;
			}
			
		

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
				website: vm.user.website,							
				email: vm.user.email,
				password: vm.user.password,
				number: vm.user.number,
				exp_month: exp_month,
				exp_year: exp_year,
				cvc: vm.user.cvc,
				plan: vm.user.plan,
				amount: vm.user.amount,
				agree: vm.user.agree,
				user_token: vm.user.user_token
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
								website: vm.user.website,							
								email: vm.user.email,
								password: vm.user.password,
								number: vm.user.number,
								exp_month: exp_month,
								exp_year: exp_year,
								cvc: vm.user.cvc,
								plan: vm.user.plan,
								amount: vm.user.amount,
								agree: vm.user.agree,
								stripeToken: stripeToken,
								user_token: vm.user.user_token
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
				vm.registerErrorText = 'Please agree terms and conditionsasdasd';
				$scope.isDisabled = false;
			}
		}
		
		// Regiter using step1 and step2

		$scope.steps = [
		'Sign Up Now',
		'Activate Free Trial Now',
		
		];
		if($stateParams.action == 'stepUserDetails'){
			$scope.steps = [
				'Activate Free Trial Now',
			];
		}
		
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

		
		function ValidateEmail(email) {
			var expr = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
			return expr.test(email);
		}

		$scope.incrementStep = function() {

			// if(angular.isUndefined(vm.user.name)) {
			// 	$scope.error_message_name ='Enter Business Name !';
			// 	return false;
   //     		}
       	
			// if(angular.isUndefined(vm.user.cname) || vm.user.cname.length <= 1){
			// 	$scope.error_message_cname = 'Enter First and Last Name!!';
			// 	return false;
			// }
			// if(angular.isUndefined(vm.user.email)) {
			// 	$scope.error_message_email = 'Enter Business email !';
			// 	return false;
   //     		}
			// else if(vm.user.email != ''){
			// 	if(!ValidateEmail(vm.user.email)) {
			// 		$scope.error_message_email = 'Invalid Email!';
			// 		return false;
			// 	}
			// }
			
			// if(angular.isUndefined( vm.user.website) ||  vm.user.website.length <= 1){
			// 	$scope.error_message_website = 'Enter Website!';
			// 	return false;
			// }
			// if(angular.isUndefined(vm.user.saddress)){
			// 	$scope.error_message_saddress = "Enter Street Address!";
			// 	return false;
			// }
			// if(angular.isUndefined(vm.user.city)){
			// 	$scope.error_message_city = "Enter City!";
			// 	return false;
			// }

			// if(angular.isUndefined(vm.user.state) || vm.user.state.lenght != 2 || !isNaN(state)){
			// 	$scope.error_message_state = "Enter State!";
			// 	return false;
			// }
		
			// if(angular.isUndefined(vm.user.zip) || (vm.user.zip.length > 10)){
			// 	$scope.error_message_zip = "Enter Valid Zip!";
			// 	return false;
			// }
			// if(angular.isUndefined(vm.user.business_type) == ''){
			// 	$scope.error_message_business_type = "Select Industry type!";
			// 	return false;
			// }
			// if(angular.isUndefined(vm.user.password)){
			//  	$scope.error_message_password = "Enter Password!";
			// 	return false;
			// }
			
			// if(vm.user.password.length < 8){
			// 	$scope.error_message_password = "Password must be at least 8 digits!";
			// 	return false;
			// }
			// if (angular.isUndefined(vm.user.privacypolicy)){
			// 	$scope.error_message_privacypolicy = "Please agree to privacy policy!";
			// 	return false;
			// }
			// return true;


			var submitAction = (vm.user.user_token == '' || vm.user.user_token == undefined) ? 'create' : 'update';
			
			// Step 1 data insertion to api and local table
			var basicDetails = {
				action: submitAction,
				name: vm.user.name,
				cname: vm.user.cname,
				email: vm.user.email,
				website: vm.user.website,
				saddress: vm.user.saddress,
				city: vm.user.city,
				state: vm.user.state,
				zip: vm.user.zip,
				business_type: vm.user.business_type,
				phone: vm.user.phone,							
				password: vm.user.password,
				privacypolicy: vm.user.privacypolicy,
				user_token: vm.user.user_token
			}
			if(vm.user.privacypolicy == 'yes'){
				// Get Token from stripe using laravel/php api call
				$http.post( API_URL + 'api/register/basic', basicDetails).then(function(response) {
					//console.log(response);
					
					if(response.data.error){
						vm.registerError = true;
						vm.registerErrorText = response.data.error;
						//$scope.isDisabled = false;
						return false;
					}
					
					if(response.data.success == true){
						// assign the return token to hidden input
						vm.user.user_token = response.data.token;
						if($scope.hasNextStep() )
						{
						  var stepIndex = $scope.getCurrentStepIndex();
						  var nextStep = stepIndex + 1;
						  $scope.selection = $scope.steps[nextStep];
						}
					}
					
					
					
				}, function(error) {
					vm.registerError = true;
					vm.registerErrorText = error.data.error;
					//$scope.isDisabled = false;
					$timeout(function () { vm.registerError = false; }, 5000);
				});
				
			}else{
				vm.registerError = true;
				vm.registerErrorText = 'Please agree seamlesschex privacy policy';
				//$scope.isDisabled = false;
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
		

		$scope.plan_list_show = [
		    {opt: 'Starter Plan (10 Checks) - $24.99/mo', plan: 'SeamlessChex Starter Plan'},
			{opt: 'Pro Plan (25 Checks) - $49.99/mo', plan: 'SeamlessChex Pro Plan'},
			{opt: 'Premium Plan (75 Checks) - $99.99/mo', plan: 'SeamlessChex Premium Plan'}
		];
		// Get details for user_steps, for comple the sign up
		if($stateParams.action == 'stepUserDetails'){
			
			$http.get( API_URL + 'api/authenticate/user/step/'+sc_token).then(function(response) {
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				//console.log($scope.data);
				angular.forEach($scope.data, function(userValue, key){
						vm.user = userValue;
					});
				
			}, function(error) {
				vm.registerError = true;
				vm.registerErrorText = error.data.error;
				//$scope.isDisabled = false;
				$timeout(function () { vm.registerError = false; }, 5000);
			});
		}
	}
	
	
	
})();