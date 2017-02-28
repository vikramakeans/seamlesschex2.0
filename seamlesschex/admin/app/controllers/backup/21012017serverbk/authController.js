(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('AuthController', AuthController);


	function AuthController($auth, $state, $http, $rootScope, $scope, API_URL) {

		var vm = this;

		vm.loginError = false;
		vm.loginErrorText;

		vm.login = function() {

			var credentials = {
				email: vm.email,
				password: vm.password
			}
			
			// Validate Username and password for blank
			if(vm.email == '' || vm.email == undefined){
				vm.loginError = true;
				vm.loginErrorText = 'Please enter your username';
				return false;
			}
			if(vm.password == '' || vm.password == undefined){
				vm.loginError = true;
				vm.loginErrorText = 'Please enter your password';
				return false;
			}
			
			$auth.login(credentials).then(function() {

				// Return an $http request for the now authenticated
				// user so that we can flatten the promise chain
				//return $http.get('api/authenticate/user').then(function(response) {
				return $http.get( API_URL + 'api/authenticate/user').then(function(response) {
	
					// Stringify the returned data to prepare it
					// to go into local storage
					var user = JSON.stringify(response.data.user);

					// Set the stringified user data into local storage
					localStorage.setItem('user', user);
					
					// The user's authenticated state gets flipped to
					// true so we can now show parts of the UI that rely
					// on the user being logged in
					$rootScope.authenticated = true;

					// Putting the user's data on $rootScope allows
					// us to access it anywhere across the app
					$rootScope.currentUser = response.data.user;
					//console.log(response.data.user);
					//console.log($rootScope.currentUser.role);
					var created_at = $rootScope.currentUser.created_at;
					$rootScope.created_at = new Date();
					
					// For Super admin
					if($rootScope.currentUser.role == 1){
						$rootScope.authSuAdmin = true;
						$state.go('superAdminDashboard');
					}
					// For admin
					if($rootScope.currentUser.role == 2){
						$rootScope.authScxAdmin = true;
						$state.go('scxAdminDashboard');
					}
					// For company admin
					if($rootScope.currentUser.role == 3){
						$rootScope.authcompAdmin = true;
						$state.go('companyAdminDashboard');
					}
					// For company user
					if($rootScope.currentUser.role == 4){
						$rootScope.authcompUser = true;
						$state.go('companyUserDashboard');
					}
					// Default ghost login is false
					$rootScope.ghostLoginEnabled = false;
					
				});

			// Handle errors
			}, function(error) {
				vm.loginError = true;
				vm.loginErrorText = error.data.error;
			});
		}
	}

})();