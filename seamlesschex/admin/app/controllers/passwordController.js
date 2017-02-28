(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('PasswordController', PasswordController);
	function PasswordController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout, $q, $log, Flash, $location, CLIENT_URL) {
		
		$scope.setpass = {} ;
		$scope.forgetpass = {};
		// set password for invite user
		$scope.confirm_token = $stateParams.confirm_token;
		// set password for active/trialing user
		$scope.token = $stateParams.token;
		$scope.action = $stateParams.action;
		
		var confirm_token = $scope.confirm_token;
		var token = $scope.token;
		$scope.setpassError = false;
		$scope.setpassFormError = false;
		var flag;
		//alert(confirm_token);
		//alert(token);
		
		//$scope.setpassErrorText = false;
		if((confirm_token == '' && token == '')||(confirm_token == undefined && token == undefined)){
			flag = true;
		}else{
			flag = false;
		}
		
		if(flag == true){
			$scope.setpassError = true;
			$scope.setpassErrorText = "Invalid Token";
		}else{
			var setpasslink = {
				confirm_token: confirm_token 
		    }
			if(confirm_token != undefined ){
				var setpasslink = {
					confirm_token: confirm_token 
				}
			}
			if(token != undefined){
				var setpasslink = {
					token: token 
				}
			}
			
	    	$http.post( API_URL + 'api/authenticate/setpasslink', setpasslink).then(function(response) {
				if(response.data.error === true){
					$scope.setpassError = true;
					$scope.setpassErrorText = response.data.message;
				}
			}, function(error) {
				$scope.setpassError = true;
				$scope.setpassErrorText = "Your password link has expired.";
			});
		}
	  
	    //set password for company users
	    $scope.setPassword = function(){
	    	
			var setPassword = {
				password: $scope.setpass.password,
				cpassword: $scope.setpass.cpassword,
				sc_token:confirm_token,
				token:token,
			}
			$http.post( API_URL + 'api/authenticate/setPassword', setPassword).then(function(response) {
			
				if(response.data.success === true){
					$scope.setpassFormError = false;
					$scope.setpassSuccess = true;
					if(confirm_token){
						$scope.setpassSuccessText = 'Seamlesschex ! Password  created successfully';
					}
					if(token){
						$scope.setpassSuccessText = 'Seamlesschex ! Password  reset successfully';
					}
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
		
		//Check email is exits or not for forgetpassword
	    $scope.forgetPassword = function(){
	    	
			var forgetPassword = {
				email: $scope.forgetpass.email,
				set_url: CLIENT_URL
			}
			$http.post( API_URL + 'api/authenticate/checkEmailForgetPassword', forgetPassword).then(function(response) {
			
				if(response.data.success === true){
					$scope.forgetpassFormError = false;
					$scope.forgetpassSuccess = true;
					$scope.forgetpassSuccessText = 'Please Check Your Email. Check your SPAM folder if you do not see in your inbox. ';
					$timeout(function () { 
						$scope.forgetpassFormError = false;
						$scope.forgetpassSuccess = false;
						$state.go('login');
					 }, 5000);
					 
				}
				
				if(response.data.error){
					$scope.forgetpassFormError = true;
					$scope.forgetpassFormErrorText = error.data.error;
				}
				
			}, function(error) {
				$scope.forgetpassFormError = true;
				$scope.forgetpassFormErrorText = error.data.error;
			});
		
	
	    }
		  
			
	}
	
})();