(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('PasswordController', PasswordController);
	function PasswordController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout, $q, $log, Flash, $location, CLIENT_URL) {
		
		$scope.setpass = {} ;

		$scope.confirm_token = $stateParams.confirm_token;
		$scope.action = $stateParams.action;
		
		var confirm_token = $scope.confirm_token;
		$scope.setpassError = false;
		$scope.setpassFormError = false;
		//$scope.setpassErrorText = false;
		
		if(confirm_token == '' || confirm_token == undefined){
			$scope.setpassError = true;
			$scope.setpassErrorText = "Invalid Token";
		}else{
			var setpasslink = {
				confirm_token: confirm_token 
		    }
	    	$http.post( API_URL + 'api/authenticate/setpasslink', setpasslink).then(function(response) {
				if(response.data.error === true){
					$scope.setpassError = true;
					$scope.setpassErrorText = response.data.message;
				}
			}, function(error) {
				$scope.setpassError = true;
				$scope.setpassErrorText = "Invalid Token";
			});
		}
	  
	    //set password for company users
	    $scope.setPassword = function(){
	    	
			var setPassword = {
				password: $scope.setpass.password,
				cpassword: $scope.setpass.cpassword,
				sc_token:confirm_token,
			}
			$http.post( API_URL + 'api/authenticate/setPassword', setPassword).then(function(response) {
			
				if(response.data.success === true){
					$scope.setpassFormError = false;
					$scope.setpassSuccess = true;
					$scope.setpassSuccessText = 'Seamlesschex ! Password  created successfully';
					$timeout(function () { 
						$scope.setpassFormError = false;
						$scope.setpassSuccess = false;
						$state.go('login');
					 }, 5000);
					 
					//$scope.login = function(){
						 //$state.go('login');
					//}
				}
				
				if(response.data.error){
					$scope.setpassFormError = true;
					$scope.setpassFormErrorText = error.data.error;
				}
				
			}, function(error) {
				$scope.setpassFormError = true;
				$scope.setpassFormErrorText = error.data.error;
			});
		
	
	    }
		  
			
	}
	
})();