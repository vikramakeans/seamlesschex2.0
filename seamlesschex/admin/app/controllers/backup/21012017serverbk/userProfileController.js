(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('UserProfileController', UserProfileController);

	function UserProfileController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog) {
		var vm = this;
		$scope.company = {};
		
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
		// Edit Profile
		// Get the one company details as per the sc_token
		if($scope.sc_token && $scope.sc_token != null && $scope.action == 'profileEdit'){
			
			var sc_token = $scope.sc_token;
			//Grab the company details from the API
			$http.get( API_URL + 'api/authenticate/user/'+sc_token).success(function(response) {
				
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(companyValue, key){
					 $scope.company = companyValue;
				 });
				 
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
			
			
		}
	}
	
})();