(function() {

	'use strict';

	angular
		.module('authApp', ['ui.router', 'satellizer', 'phonenumberModule','ngPayments','chieffancypants.loadingBar', 'ngAnimate', 'theme.services', 'theme.pages-controllers'])
		.constant('API_URL', 'http://localhost:8080/laravel/seamlesschex/api/public/')
		.config(function($stateProvider,  $urlRouterProvider, $authProvider, $httpProvider, $provide, cfpLoadingBarProvider, API_URL) {
			//cfpLoadingBarProvider.includeSpinner = true;
			
			function redirectWhenLoggedOut($q, $injector) {

				return {

					responseError: function(rejection) {

						// Need to use $injector.get to bring in $state or else we get
						// a circular dependency error
						var $state = $injector.get('$state');

						// Instead of checking for a status code of 400 which might be used
						// for other reasons in Laravel, we check for the specific rejection
						// reasons to tell us if we need to redirect to the login state
						var rejectionReasons = ['token_not_provided', 'token_expired', 'token_absent', 'token_invalid'];

						// Loop through each rejection reason and redirect to the login
						// state if one is encountered
						angular.forEach(rejectionReasons, function(value, key) {

							if(rejection.data.error === value) {
								
								// If we get a rejection corresponding to one of the reasons
								// in our array, we know we need to authenticate the user so 
								// we can remove the current user from local storage
								localStorage.removeItem('user');

								// Send the user to the auth state so they can login
								$state.go('login');
							}
						});

						return $q.reject(rejection);
					}
				}
			}

			// Setup for the $httpInterceptor
			$provide.factory('redirectWhenLoggedOut', redirectWhenLoggedOut);

			// Push the new factory onto the $http interceptor array
			$httpProvider.interceptors.push('redirectWhenLoggedOut');
			
			//$authProvider.loginUrl = '/api/authenticate';
			$authProvider.loginUrl = API_URL + 'api/authenticate';
			
			$authProvider.signupUrl = API_URL + 'api/register';

			$urlRouterProvider.otherwise('/login');
			
			
			
			$stateProvider
				.state('login', {
					url: '/login',
					templateUrl: '../admin/views/authView.html',
					controller: 'AuthController as auth'
				})
				.state('register', {
				  url: '/register',
				  templateUrl: '../admin/views/userRegister.html',
				  controller: 'RegisterController as register'
				})
				.state('superAdminDashboard', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/dashboard-sa',
					templateUrl: '../admin/views/dashboard-sa.html',
					//controller: 'DashboardController as superadmin'
					
				})
				.state('companies', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/companies',
					params: {
						sc_token: null,
						ghost_mode: true,
					},
					templateUrl: '../admin/views/companies.html',
					//controller: 'SuperAdminController as superadmin'
					controller: 'UserController as user'
					
				})
				.state('companies-edit', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/companies',
					//url: '/companies/:sc_token',
					params: {
						sc_token: null,
					},
					templateUrl: '../admin/views/companies-edit.html',
					//controller: 'SuperAdminController as superadmin'
					controller: 'UserController as user'
					
				})
				.state('companies-new', {
					needLogin: false,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/companies-new',
					//url: '/companies/:sc_token',
					templateUrl: '../admin/views/companies-new.html',
					//controller: 'SuperAdminController as superadmin'
					controller: 'UserController as user'
					
				})
				.state('companyAdminDashboard', {
					needLogin: true,
					//superAdmin: false,
					companyAdmin: true,
					//companyUser: false,
					//ghostMode: false,
					url: '/dashboard-ca',
					templateUrl: '../admin/views/companyAdminView.html',
					//controller: 'CompanyAdminController as companyadmin'
					controller: 'UserController as user'
					
				})
				.state('companyUserDashboard', {
					needLogin: true,
					//superAdmin: false,
					//companyAdmin: false,
					//companyUser: true,
					//ghostMode: false,
					url: '/dashboard-cu',
					templateUrl: '../admin/views/companyUserView.html',
					//controller: 'CompanyUserController as companyuser'
					controller: 'UserController as user'
					
				});
				
				
		})
		.run(function($rootScope, $state, $timeout, $document) {
			//console.log('starting run');
			
			/* Start Autometic logout */
			// Automatic logout after idle for sometime
			// Timeout timer value
			/*var TimeOutTimerValue = 10000;

			// Start a timeout
			if($rootScope.authenticated == true){
			var TimeOut_Thread = $timeout(function(){ LogoutByTimer() } , TimeOutTimerValue);
			}
			var bodyElement = angular.element($document);

			angular.forEach(['keydown', 'keyup', 'click', 'mousemove', 'DOMMouseScroll', 'mousewheel', 'mousedown', 'touchstart', 'touchmove', 'scroll', 'focus'], 
			function(EventName) {
				 bodyElement.bind(EventName, function (e) { TimeOut_Resetter(e) });  
			});

			function LogoutByTimer(){
				//console.log('Logout');
				localStorage.removeItem('user');
				$rootScope.authenticated = false;
				$rootScope.currentUser = null;
				$state.go('login');
			}

			function TimeOut_Resetter(e){
				//console.log(' ' + e);
				// Stop the pending timeout
				$timeout.cancel(TimeOut_Thread);

				// Reset the timeout
				TimeOut_Thread = $timeout(function(){ LogoutByTimer() } , TimeOutTimerValue);
			}*/
			/* End Autometic logout */
			
			// $stateChangeStart is fired whenever the state changes. We can use some parameters
			// such as toState to hook into details about the state as it is changing
			$rootScope.$on('$stateChangeStart', function(event, toState) {
				
				// Default is authentication is false
				$rootScope.authenticated = false;
				$rootScope.authSuAdmin = false;
				$rootScope.authcompAdmin = false;
				$rootScope.authcompUser = false;
				
				// Grab the user from local storage and parse it to an object
				var user = JSON.parse(localStorage.getItem('user'));
				
				
				// If there is any user data in local storage then the user is quite
				// likely authenticated. If their token is expired, or if they are
				// otherwise not actually authenticated, they will be redirected to
				// the auth state because of the rejected request anyway
				if(user && user!=null) {

					// The user's authenticated state gets flipped to
					// true so we can now show parts of the UI that rely
					// on the user being logged in
					// Putting the user's data on $rootScope allows
					// us to access it anywhere across the app. Here
					// we are grabbing what is in local storage
					$rootScope.authenticated = true;
					$rootScope.currentUser = user;
					// For Super admin
					if($rootScope.currentUser.role == 1){
						$rootScope.authSuAdmin = true;
					}
					// For Super admin
					if($rootScope.currentUser.role == 2){
						$rootScope.authSuAdmin = true;
					}
					// For company admin
					if($rootScope.currentUser.role == 3){
						$rootScope.authcompAdmin = true;
					}
					// For company user
					if($rootScope.currentUser.role == 4){
						$rootScope.authcompUser = true;
					}
					
					// If the user is logged in and we hit the auth route we don't need
					// to stay there and can send the user to the main state
					if(toState.name === "login") {
						// Preventing the default behavior allows us to use $state.go
						// to change states
						event.preventDefault();

						// go to the "main" state as per role
						// For Super admin
						if($rootScope.currentUser.role == 1){
							$state.go('superAdminDashboard');
						}
						// For Super admin
						if($rootScope.currentUser.role == 2){
							$state.go('superAdminDashboard');
						}
						// For company admin
						if($rootScope.currentUser.role == 3){
							$state.go('companyAdminDashboard');
						}
						// For company user
						if($rootScope.currentUser.role == 4){
							$state.go('companyUserDashboard');
						}
					}					
				}
				
				// Need Login to access the page else redirect to login page
				if(toState.needLogin == true){
					if($rootScope.authenticated == false || $rootScope.authenticated == undefined){
					  event.preventDefault();
					  $state.go('login');
					}
				  }
				
				/*if(toState.needLogin == true && toState.superAdmin == true){
					  if($rootScope.authenticated == false || $rootScope.authenticated == undefined || ){
						event.preventDefault();
						$state.go('login');
					}
				}*/
				//console.log($rootScope.authenticated);
				//console.log($rootScope.authSuAdmin);
				
			});
				
		});
		
})();