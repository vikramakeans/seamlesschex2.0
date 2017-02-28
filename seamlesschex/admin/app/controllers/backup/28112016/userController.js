(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('UserController', UserController);

	function UserController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog) {

		var vm = this;

		vm.users;
		vm.error;

		$scope.sc_token = $stateParams.sc_token;
		$scope.action = $stateParams.action;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.fetch = $stateParams.fetch;
		$scope.status = '';
		$scope.customFullscreen = false;
		
		
		//console.log($scope.action);
		/*$scope.ghostEnable = function(sc_token) {

			if($rootScope.authenticated == true && $rootScope.authSuAdmin == true  && $scope.ghost_mode == true){
				//Grab the list of users from the API
				$http.post( API_URL + 'api/authenticate/user/ghlo/'+sc_token).success(function(response) {
					var user_admin = JSON.stringify(localStorage.getItem('user'));
					localStorage.setItem('user_admin', user_admin);
					localStorage.removeItem(user);
					var user = JSON.stringify(response.user);

					// Set the stringified user data into local storage
					localStorage.setItem('user', user);
					$rootScope.authenticated = true;
					$rootScope.currentUser = response.user;

					// For company admin
					if($rootScope.currentUser.role == 3){
						$rootScope.authcompAdmin = true;
						$state.go('companyAdminDashboard');
					}
					
				}).error(function(error) {
					vm.error = error;
				});
			}
		}*/
		
		//vm.getCompanyAdmin = function() {
			/*if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.action == 'companies'){
				//Grab the list of users from the API
				$http.get( API_URL + 'api/authenticate').success(function(users) {
					//console.log(users);
					//vm.users = users;
					
					//vm.users = angular.fromJson(users);
					//console.log(JSON.stringify(users.data));
					//var users = JSON.stringify(users);
					//vm.users = JSON.parse(users);
					
					for(var key in users){
						if(users.hasOwnProperty(key)){
							//console.log(users[key]);
							var users = JSON.stringify(users[key].data);
							vm.users = JSON.parse(users);
							//vm.users = users;
						}
					}
					
					
				}).error(function(error) {
					vm.error = error;
				});
			}*/
		//}
		
		
		// Back to super admin
		vm.backToSA = function() {
			// Remove the authenticated user from local storage
			localStorage.removeItem('user');
			$rootScope.authenticated = false;
			// Remove the current user info from rootscope
			$rootScope.currentUser = null;
			
			// Set the storage item
			var user_admin = JSON.parse(localStorage.getItem('user_admin'));
			localStorage.setItem('user', user_admin);
			$rootScope.authenticated = true;
			$rootScope.currentUser = JSON.parse(user_admin);
			
			// For Super admin
			if($rootScope.currentUser.role == 1){
				$rootScope.authSuAdmin = true;
				$state.go('superAdminDashboard');
			}
			// For Super admin
			if($rootScope.currentUser.role == 2){
				$rootScope.authSuAdmin = true;
				$state.go('superAdminDashboard');
			}
			$rootScope.ghostLoginEnabled = false;
			localStorage.removeItem('user_admin');
			// Flip authenticated to false so that we no longer
			// show UI elements dependant on the user being logged in
			
		}
		// Delete Company
		/*$scope.deleteCompany = function(ev, sc_token) {
		// Appending dialog to document.body to cover sidenav in docs app
		var confirm = $mdDialog.confirm()
			  .title('Confirm')
			  .textContent('Are you sure you want to delete the company?')
			  //.ariaLabel('Lucky day')
			  .targetEvent(ev)
			  .ok('Yes')
			  .cancel('No');
		$mdDialog.show(confirm).then(function() {
		  $scope.status = 'yes';
		  var companyUserStatus = {
			  deleteCompany: true,
			  status_type: 3,
		  }
		  if($scope.status == 'yes'){
			  $http.post( API_URL + 'api/authenticate/user/deleteCompany/'+sc_token, companyUserStatus).then(function(response) {
				vm.users.companySuccess = true;
				vm.users.companySuccessText = 'Company Updated Successfully';
			}, function(error) {
				vm.users.companyError = true;
				vm.users.companyErrorText = error.data.error;
			});
		  }
		}, function() {
		  $scope.status = 'no';
		});
	  };*/
		
		// toggle the sidebar navigation
		$scope.toggleNavigation = function() {
			
			 var myEl = angular.element( document.querySelector( '#mainContainer' ) );
				if(myEl.hasClass('sidebar-collapse')) {
				  myEl.removeClass('sidebar-collapse');  
				} else{
					myEl.addClass('sidebar-collapse');
				}
				
				if(myEl.hasClass('sidebar-open')) {
				  myEl.removeClass('sidebar-open');  
				} else{
					myEl.addClass('sidebar-open');
				}
		};
		
		// We would normally put the logout method in the same
		// spot as the login method, ideally extracted out into
		// a service. For this simpler example we'll leave it here
		vm.logout = function() {
			
			$auth.logout().then(function() {

				// Remove the authenticated user from local storage
				localStorage.removeItem('user');

				// Flip authenticated to false so that we no longer
				// show UI elements dependant on the user being logged in
				$rootScope.authenticated = false;

				// Remove the current user info from rootscope
				$rootScope.currentUser = null;

				// Redirect to auth (necessary for Satellizer 0.12.5+)
				$state.go('login');
			});
		}
		
		
	}
	
})();