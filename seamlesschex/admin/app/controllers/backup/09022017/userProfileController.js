(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('UserProfileController', UserProfileController);

	function UserProfileController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout, CLIENT_URL, Flash) {
		var vm = this;
		$scope.company = {};
		
		$scope.sc_token = $stateParams.sc_token;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		$scope.role_id = $stateParams.role_id;
		$scope.company.settings = {};
		$scope.company.all_plan = {};
		$scope.companyuser = {};
		$scope.companysub = {};
		
		$scope.authSuAdmin = $rootScope.authSuAdmin;
		$scope.authScxAdmin = $rootScope.authScxAdmin;
		$scope.authcompAdmin = $rootScope.authcompAdmin;
		
		$scope.company.default_settings = [];
		vm.companyError = false;
		vm.companySuccess = false;
		vm.companyErrorText;
		vm.companySuccessText;
		var sc_token = $scope.sc_token;
		
		if($scope.action == 'merchantAccount'){
			var sc_token = $rootScope.currentUser.sc_token;
			$scope.sc_token = $rootScope.currentUser.sc_token;
		}
	
		// Edit Profile
		// Get the one company details as per the sc_token
		if(($scope.sc_token && $scope.sc_token != null && $scope.action == 'profileEdit' && $scope.authcompAdmin == true) ||($scope.sc_token && $scope.sc_token != null && $scope.action == 'merchantAccount' && $scope.authcompAdmin == true)){
			
			
			//Grab the company details from the API
			$http.get( API_URL + 'api/authenticate/user/'+sc_token).success(function(response) {
				
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(companyValue, key){
					 $scope.company = companyValue;
				 });
				
				 
				
				// For All Plan Drop down
				 var response_all_plan = JSON.stringify($scope.company.all_plan);
				 var plans = JSON.parse(response_all_plan);
					$scope.plans = plans;
				 angular.forEach($scope.plans, function(planValue, key){
					 $scope.company.all_plan = planValue.plans;
				 });
				 
				 // Create Company User
				 $scope.saveCompanyUser = function() {
					var merchantUserDetails = {
						createCompanyUser: true,
						email: $scope.companyuser.email,
						set_url: CLIENT_URL,
						company_admin: $scope.company.mc_token,
						created_by: sc_token,
						user_settings: $scope.companyuser.user_settings
					}
					
					$http.post( API_URL + 'api/authenticate/createCompanyUser', merchantUserDetails).then(function(response) {
						if(response.data.success === true){
							 // Add to subscription list
							$scope.companyuser.users.push({
								email: response.data.email,
								status: response.data.status,
								company_name: response.data.company_name,
								user_settings: response.data.user_settings,
								invited_date: response.data.invited_date,
								time: response.data.time
							});
							var message = '<strong>Seamlesschex !</strong>Merchant User added successfully and send email to the user.';
							$scope.companyuserError = false;
							$scope.companyuserSuccess = true;
							$scope.companyuserSuccessText = message;
							$timeout(function () { $scope.companySuccess = false; }, 5000);
						}
						
					}, function(error) {
						$scope.companyuserError = true;
						$scope.companyuserErrorText = error.data.error;
						
					});
		 
			};
			
			//For revoke/invoke Access
			$scope.actionAccess =function(status_id, sc_token){
			   
				var actionAccess = {
					status_id: status_id,
					sc_token :sc_token
				}
				
				$http.post( API_URL + 'api/authenticate/actionAccess', actionAccess).then(function(response) {
					
					if(response.data.invoke=== 1){
						var message = '<strong>Seamlesschex !</strong> Invoke Access Sucessfully.';
						var id = Flash.create('success', message, 2000, {class: 'custom-class', id: 'merchantUser'}, true);
						$state.go($state.current, {}, {reload: true});
					    
						$timeout(function () { angular.element('#tab_2').triggerHandler('click');}, 10);
					}
					if(response.data.revoke === 1){
						var message = '<strong>Seamlesschex !</strong> Revoke Access Sucessfully.';
						var id = Flash.create('success', message, 2000, {class: 'custom-class', id: 'merchantUser'}, true);
						$state.go($state.current, {}, {reload: true});
						
						$timeout(function () { angular.element('#tab_2').triggerHandler('click');}, 10);
					}
				}, function(error) {
						$scope.companyuserError = true;
						$scope.companyuserErrorText = error.data.error;
				});
			}
			
			// Create company sub
			$scope.saveCompanySub = function() {
				var companySubDetails = {
					createCompanySub: true,
					name: $scope.companysub.name,
					email: $scope.companysub.email,
					created_by: $scope.sc_token,
					company_admin: $scope.company.mc_token
				}
				
				$http.post( API_URL + 'api/authenticate/createCompanySub', companySubDetails).then(function(response) {
					
					// hide the message after 5 sec
					if(response.data.success === true){
						// Add to company-sub to list
						$scope.companysub.users.push({
							email: response.data.email,
							name: response.data.name
						});
					   var message = 'Seamlesschex !Merchant Created Successfully.';
						$scope.companysubError = false;
						$scope.companysubSuccess = true;
						$scope.companysubSuccessText = message;
						$timeout(function () { $scope.companysubSuccess = false; }, 5000);
						
					}
					
				}, function(error) {
					$scope.companysubError = true;
					$scope.companysubErrorText = error.data.error;
				});
	 
			};
				
				 
			}).error(function(error) {
				vm.error = error;
			});
			
			
			// Get the multiple subscriptions for the merchant
			$http.get( API_URL + 'api/authenticate/subscription/lists/'+sc_token).success(function(subscriptions) {
				
				if( subscriptions.message ){
					$scope.companysubscriptionsMessage = true;
					$scope.companysubscriptionsMessageText = subscriptions.message;
				}else{
					for(var key in subscriptions){
						if(subscriptions.hasOwnProperty(key)){
							
							var subscriptions = JSON.stringify(subscriptions[key].data);
							$scope.subscriptions = JSON.parse(subscriptions);
							
						}
					}
				}
				
				//console.log($scope.subscriptions);
				 
			}).error(function(error) {
				vm.error = error;
			});
			
			// Add Multiple subscription to merchant
			$scope.saveMultipleSubscription = function() {
				var subscription;
				if($scope.company_settings.subscription == 'Payment Link'){
					subscription = 'CHECKOUTLINK';
				}
				if($scope.company_settings.subscription == 'Fundconfirmation'){
					subscription = 'FUNDCONFIRMATION';
				}
				if($scope.company_settings.subscription == 'Signature'){
					subscription = 'SIGNTURE';
				}
				if($scope.company_settings.subscription == 'Bank Auth Link'){
					subscription = 'BANKAUTHLINK';
				}
				var subcriptionDetails = {
					addSubscriptionMutiple: true,
					plan_type: $scope.company.stripe_plan_multiple,
					subscription: subscription,
					sc_token: $scope.company.sc_token
				}
				
				$http.post( API_URL + 'api/authenticate/subscription/multiple', subcriptionDetails).then(function(response) {
					
					// hide the message after 5 sec
					if(response.data.success === true){
						 // Add to subscription list
						$scope.subscriptions.push({
							stripe_plan_type: response.data.stripe_plan_type,
							subscription_starts_at: response.data.subscription_starts_at,
							subscription_ends_at: response.data.subscription_ends_at
						});
						$scope.companysubscriptionsError = false;
						$scope.companysubscriptionsSuccess = true;
						$scope.companysubscriptionsSuccessText = 'Subscription Added Successfully';
						$timeout(function () { $scope.companysubscriptionsSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.companysubscriptionsError = true;
					$scope.companysubscriptionsErrorText = error.data.error;
				});

			};
			
			// Cancel the subscription
			$scope.status = '  ';
			$scope.customFullscreen = false;
			$scope.cancelSubscriptions = function(ev,stripe_subscription, company_admin, stripe_plan_type, index) {
			
			// Appending dialog to document.body to cover sidenav in docs app
			var confirm = $mdDialog.confirm()
				  .title('Confirm')
				  .textContent('Are you sure you want to cancel the subscription?')
				  //.ariaLabel('Lucky day')
				  .targetEvent(ev)
				  .ok('Yes')
				  .cancel('No');
			$mdDialog.show(confirm).then(function() {
			  $scope.status = 'yes';
			  var cancelSubscription = {
				  cancelSubscription: true,
				  stripe_subscription: stripe_subscription,
				  company_admin: company_admin,
				  stripe_plan_type: stripe_plan_type
			  }
			  
			  if($scope.status == 'yes'){
				  $http.post( API_URL + 'api/authenticate/subscription/cancel', cancelSubscription).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						// to remove the clicked index
						$scope.subscriptions.splice(index, 1);
						$scope.companysubscriptionsError = false;
						$scope.companysubscriptionsSuccess = true;
						$scope.companysubscriptionsSuccessText = 'Subscription Canceled Successfully';
						$timeout(function () { $scope.companysubscriptionsSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companysubscriptionsError = true;
					$scope.companysubscriptionsErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
		  };
			
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
			
			// If merchant in trialing and want activate the plan 
			$scope.activateSubscriptionNow = function() {
				var activateNowDetails = {
					activateNowDetails: true
				}
				
				$http.post( API_URL + 'api/authenticate/subscription/active/'+sc_token, activateNowDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Plan activated successfully';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			};
			// If merchant in active and try to upgrade or change billing cycle
			$scope.updateSubscriptionNow = function() {
				var upgradeNowDetails = {
					upgradeNowDetails: true,
					stripe_plan_new:$scope.company.stripe_plan
				}
				//console.log(upgradeNowDetails);
				$http.post( API_URL + 'api/authenticate/subscription/upgrade/'+sc_token, upgradeNowDetails).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Plan upgraded successfully';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			}
			//Show or hide credit card details
			$scope.IsVisible = false;
			$scope.toggleCrediCardDetails = function() {
				$scope.IsVisible = $scope.IsVisible ? false : true;
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
			
			
			// List the company-user by sc_token (for company-admin)	
			if($rootScope.authenticated == true && $rootScope.authcompAdmin == true && $scope.action == 'merchantAccount'){
				
				var sc_token = $rootScope.currentUser.sc_token;
				//Grab the list of users from the API
				$http.get( API_URL + 'api/authenticate/companyUsers/'+sc_token).success(function(users) {
					//console.log(users);
					if( users.message ){
						$scope.companyuserMessage = true;
						$scope.companyuserMessageText = users.message;
					}else{
						for(var key in users){
							if(users.hasOwnProperty(key)){
								var users = JSON.stringify(users[key].data);
								$scope.companyuser.users = JSON.parse(users);
							}
						}
					}
					
					
				}).error(function(error) {
					vm.error = error;
				});
				
				// List the company-sub	as per sc_token
					
				$http.get( API_URL + 'api/authenticate/business/'+sc_token).success(function(users) {
					
					if( users.message ){
						$scope.companysubMessage = true;
						$scope.companysubMessageText = users.message;
					}else{
						for(var key in users){
							if(users.hasOwnProperty(key)){
								//console.log(users[key]);
								var users = JSON.stringify(users[key].data);
								$scope.companysub.users = JSON.parse(users);
								//vm.users = users;
							}
						}
					}
					
				}).error(function(error) {
					vm.error = error;
				});
				
				
			}

			// get plan as per check box selected
			/*$scope.getFilterPlans = function() {
				
				var getFilterPlans = {
					activateNowDetails: true,
					number: $scope.company.number
				}
				console.log("coming");
				console.log($scope.company.all_plan);
				$http.post( API_URL + 'api/authenticate/merchant/getFilterPlans/'+sc_token, getFilterPlans).then(function(response) {
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Plan activated successfully';
						$timeout(function () { $scope.companySuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			};*/
			
		 
			
		}
		
		if(($scope.sc_token && $scope.sc_token != null && $scope.action == 'profileEdit' && $scope.authSuAdmin == true) || ($scope.sc_token && $scope.sc_token != null && $scope.action == 'profileEdit' && $scope.authScxAdmin == true) ){
			//Grab the super admin/ seamlesschex admin details from the API
			$http.get( API_URL + 'api/authenticate/admin/'+sc_token).success(function(response) {
				
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(adminValue, key){
					 $scope.company = adminValue;
				 });
				 
			}).error(function(error) {
				$scope.companyError = true;
				$scope.companyErrorText = error.data.error;
			});
			
			// Update admin Details
			$scope.adminDetails = function() {
				//console.log($scope.company.name);
				var adminDetails = {
					updateAdminDetails: true,
					name: $scope.company.name,
					password: $scope.company.password
				}
				//console.log(sc_token);
				$http.post( API_URL + 'api/authenticate/updateAdmin/'+sc_token, adminDetails).then(function(response) {
					
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyError = false;
						$scope.companySuccess = true;
						$scope.companySuccessText = 'Updated Admin Details';
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