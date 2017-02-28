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