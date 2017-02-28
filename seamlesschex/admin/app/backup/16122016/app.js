(function() {

	'use strict';

	angular
		.module('authApp', ['ui.router', 'satellizer', 'phonenumberModule','ngPayments','chieffancypants.loadingBar', 'ngAnimate', 'ui.bootstrap', 'angularjs-datetime-picker', 'ui.select2', 'dnTimepicker','ngMaterial', 'textAngular', 'angularSlideables', 'jqtimepickerModule', 'angular-clipboard','colorpicker.module', 'export.csv'])
		//.constant('API_URL', 'http://localhost:8080/laravel/seamlesschex/api/public/')
		//.constant('CLIENT_URL', 'http://localhost:8080/laravel/seamlesschex/admin/#/')
                .constant('CLIENT_URL', 'http://54.200.206.176/v2/admin/#/')
		//.constant('API_URL', 'http://localhost:8080/laravel/seamlesschex/api/public/')
		.constant('API_URL', 'http://54.200.206.176/v2/api/public/')
		.config(function($stateProvider,  $urlRouterProvider, $authProvider, $httpProvider, $provide, cfpLoadingBarProvider, API_URL) {
			cfpLoadingBarProvider.includeSpinner = true;
			
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
				.state('scxAdminDashboard', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/dashboard-admin',
					templateUrl: '../admin/views/dashboard-admin.html',
					//controller: 'DashboardController as superadmin'
					
				})
				.state('seamlesschex-admins', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/seamlesschex-admins',
					params: {
						sc_token: null,
						ghost_mode: true,
						fetch: 'scxAdmins',
					},
					templateUrl: '../admin/views/seamlesschex-admins.html',
					//controller: 'SuperAdminController as superadmin'
					//controller: 'CompanyUserController as companyuser'
					
				})
				.state('seamlesschex-admin-new', {
					needLogin: true,
					url: '/seamlesschex-admin-new',
					params: {
						sc_token: null,
						action: 'addScxAdmin',
						role_id: 2,
					},
					templateUrl: '../admin/views/seamlesschex-admin-form.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('seamlesschex-admin-edit', {
					needLogin: true,
					url: '/seamlesschex-admin-edit/:sc_token',
					params: {
						action: 'editScxAdmin'
					},
					templateUrl: '../admin/views/seamlesschex-admin-form.html',
					controller: 'CompanyUserController as companyuser'
					
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
						fetch: true,
						action: 'companies',
					},
					templateUrl: '../admin/views/companies.html',
					//controller: 'SuperAdminController as superadmin'
					//controller: 'UserController as user'
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-edit', {
					needLogin: true,
					url: '/companies/:sc_token',
					params: {
						action: 'editCompanyAdmin'
					},
					//templateUrl: '../admin/views/company-edit.html',
					templateUrl: '../admin/views/company-form.html',
					controller: 'MultiStepFormController as companyAdmin'
					//controller: 'UserController as user'
					//controller: 'TabController as panel'
					
				})
				.state('company-new', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					//url: '/companies',
					url: '/company-new',
					params: {
						sc_token: null,
						action: 'addCompanyAdmin',
						role_id: 3,
					},
					templateUrl: '../admin/views/company-form.html',
					controller: 'MultiStepFormController as companyAdmin'
					
				})
				.state('subscriptions', {
					needLogin: true,
					url: '/subscriptions',
					params: {
						sc_token: null,
						ghost_mode: true,
						fetch: 'multiplesubscriptions'
					},
					templateUrl: '../admin/views/subscriptions.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('subscription-new', {
					needLogin: true,
					url: '/subscription-new',
					params: {
						sc_token: null,
						action: 'addSubscription',
						//role_id: 3,
					},
					templateUrl: '../admin/views/subscription-form.html',
					//controller: 'MultiStepFormController as companyAdmin'
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-users', {
					needLogin: true,
					//superAdmin: true,
					//companyAdmin: false,
					//companyUser: false,
					//ghostMode: false,
					url: '/company-users',
					params: {
						sc_token: null,
						ghost_mode: true,
						fetch: 'companyUsers',
					},
					templateUrl: '../admin/views/company-users.html',
					//controller: 'SuperAdminController as superadmin'
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-user-new', {
					needLogin: true,
					url: '/company-user-new',
					params: {
						sc_token: null,
						action: 'addCompanyUser',
						role_id: 4,
					},
					templateUrl: '../admin/views/company-user-form.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-user-edit', {
					needLogin: true,
					url: '/company-user-edit/:sc_token',
					params: {
						action: 'editCompanyUser'
					},
					templateUrl: '../admin/views/company-user-form.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-sub', {
					needLogin: true,
					url: '/company-sub',
					params: {
						sc_token: null,
						fetch: 'companySub',
					},
					templateUrl: '../admin/views/company-sub.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-sub-new', {
					needLogin: true,
					url: '/company-sub-new',
					params: {
						sc_token: null,
						action: 'addCompanySub',
						role_id: 5,
					},
					templateUrl: '../admin/views/company-sub-form.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('company-sub-edit', {
					needLogin: true,
					url: '/company-sub-edit/:sc_token',
					params: {
						action: 'editCompanySub'
					},
					templateUrl: '../admin/views/company-sub-form.html',
					controller: 'CompanyUserController as companyuser'
					
				})
				.state('fee-settings', {
					needLogin: true,
					url: '/fee-settings',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/fee-settings.html',
					controller: 'FeeSettingsController as feesettings'
					
				})
				.state('fee-settings-add', {
					needLogin: true,
					url: '/fee-settings-add',
					params: {
						action: 'addFeeSettings'
					},
					templateUrl: '../admin/views/fee-settings-form.html',
					controller: 'FeeSettingsController as feesettings'
					
				})
				.state('fee-settings-edit', {
					needLogin: true,
					url: '/fee-settings-edit/:id',
					params: {
						action: 'editFeeSettings'
					},
					templateUrl: '../admin/views/fee-settings-form.html',
					controller: 'FeeSettingsController as feesettings'
					
				})
				.state('permission-settings', {
					needLogin: true,
					url: '/permission-settings',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/permission-settings.html',
					controller: 'PermissionSettingsController as permissionsettings'
					
				})
				.state('permission-settings-add', {
					needLogin: true,
					url: '/permission-settings-add',
					params: {
						action: 'addPermissionSettings'
					},
					templateUrl: '../admin/views/permission-settings-form.html',
					controller: 'PermissionSettingsController as permissionsettings'
					
				})
				.state('permission-settings-edit', {
					needLogin: true,
					url: '/permission-settings-edit/:id',
					params: {
						action: 'editPermissionSettings'
					},
					templateUrl: '../admin/views/permission-settings-form.html',
					controller: 'PermissionSettingsController as permissionsettings'
					
				})
				.state('message-settings', {
					needLogin: true,
					url: '/message-settings',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/message-settings.html',
					controller: 'MessageSettingsController as messagesettings'
					
				})
				.state('message-settings-edit', {
					needLogin: true,
					url: '/message-settings-edit/:id',
					params: {
						action: 'editMessageSettings'
					},
					templateUrl: '../admin/views/message-settings-form.html',
					controller: 'MessageSettingsController as messagesettings'
					
				})
				.state('message-settings-add', {
					needLogin: true,
					url: '/message-settings-add',
					params: {
						action: 'addMessageSettings'
					},
					templateUrl: '../admin/views/message-settings-form.html',
					controller: 'MessageSettingsController as messagesettings'
					
				})
				.state('email-settings', {
					needLogin: true,
					url: '/email-settings',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/email-settings.html',
					controller: 'EmailSettingsController as emailsettings'
					
				})
				.state('email-settings-add', {
					needLogin: true,
					url: '/email-settings-add',
					params: {
						action: 'addEmailSettings'
					},
					templateUrl: '../admin/views/email-settings-form.html',
					controller: 'EmailSettingsController as emailsettings'
					
				})
				.state('email-settings-edit', {
					needLogin: true,
					url: '/email-settings-edit/:id',
					params: {
						action: 'editEmailSettings'
					},
					templateUrl: '../admin/views/email-settings-form.html',
					controller: 'EmailSettingsController as emailsettings'
					
				}).state('status', {
					needLogin: true,
					url: '/status',
					params: {
						fetch: true,
						action: 'status'
					},
					templateUrl: '../admin/views/status.html',
					controller: 'StatusController as status'
					
				})
				.state('status-add', {
					needLogin: true,
					url: '/status-add',
					params: {
						action: 'addStatus'
					},
					templateUrl: '../admin/views/status-form.html',
					controller: 'StatusController as status'
					
				})
				.state('status-edit', {
					needLogin: true,
					url: '/status-edit/:id',
					params: {
						action: 'editStatus'
					},
					templateUrl: '../admin/views/status-form.html',
					controller: 'StatusController as status'
					
				})
				.state('invoice', {
					needLogin: true,
					url: '/invoice',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/invoice.html',
					controller: 'InvoiceController as invoice'
					
				})
				.state('batch-settings', {
					needLogin: true,
					url: '/batch-settings',
					params: {
						action: 'batchSettings'
					},
					templateUrl: '../admin/views/batch-settings.html',
					//controller: 'BatchSettingsController as batchsettings'
					
				})
.state('import-checks', {
					needLogin: true,
					url: '/import-checks',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/import-checks.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('imported-checks', {
					needLogin: true,
					url: '/imported-checks',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/imported-checks.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('print-checks', {
					needLogin: true,
					url: '/print-checks',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/print-checks.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('email-template', {
					needLogin: true,
					url: '/email-template',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/email-template.html',
					controller: 'EmailTemplateController as emailtemplate'
					
				})
				.state('email-template-add', {
					needLogin: true,
					url: '/email-template-add',
					params: {
						action: 'addEmailTemplate'
					},
					templateUrl: '../admin/views/email-template-form.html',
					controller: 'EmailTemplateController as emailtemplate'
					
				})
				.state('email-template-edit', {
					needLogin: true,
					url: '/email-template-edit/:id',
					params: {
						action: 'editEmailTemplate'
					},
					templateUrl: '../admin/views/email-template-form.html',
					controller: 'EmailTemplateController as emailtemplate'
					
				})
				.state('plan-list', {
					needLogin: true,
					url: '/plan-list',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/plan-list.html',
					controller: 'PlanDetailsController as plandetails'
					
				})
				.state('plan-new', {
					needLogin: true,
					url: '/plan-new',
					params: {
						action: 'addPlanDetails'
					},
					templateUrl: '../admin/views/plan-form.html',
					controller: 'PlanDetailsController as plandetails'
					
				})
				.state('plan-edit', {
					needLogin: true,
					url: '/plan-edit/:id',
					params: {
						action: 'editPlanDetails'
					},
					templateUrl: '../admin/views/plan-form.html',
					controller: 'PlanDetailsController as plandetails'
					
				})
				.state('activity-logs', {
					needLogin: true,
					url: '/activity-logs',
					params: {
						action: 'activityLogs'
					},
					templateUrl: '../admin/views/activity-logs.html',
					//controller: 'CompanyUserController as companyuser'
					
				})
				.state('profile-edit', {
					needLogin: true,
					url: '/profile-edit/:sc_token',
					params: {
						action: 'profileEdit'
					},
					templateUrl: '../admin/views/profile-form.html',
					//controller: 'UserProfileController as userprofile'
					
				})
				.state('user-permissions', {
					needLogin: true,
					url: '/user-permissions/:sc_token',
					params: {
						action: 'permissions',
						permission: true,
					},
					templateUrl: '../admin/views/common/permissions.html',
					//controller: 'PermissionController as permission'
					
				})
				.state('companyAdminDashboard', {
					needLogin: true,
					//superAdmin: false,
					companyAdmin: true,
					//companyUser: false,
					//ghostMode: false,
					url: '/dashboard-ca',
					templateUrl: '../admin/views/dashboard-ca.html',
					//controller: 'CompanyAdminController as companyadmin'
					controller: 'UserController as user'
					
				})
				.state('checkout', {
					needLogin: false,
					url: '/checkout/:checkout_token/:company_id/:fee_type/:signture',
					templateUrl: '../admin/views/checkout.html',
					controller: 'CheckController as check'
					
				})
				.state('payauth', {
					needLogin: false,
					url: '/payauth/:pay_auth_token/:company_id/:signture',
					templateUrl: '../admin/views/bank_auth.html',
					controller: 'CheckController as check'
					
				})
				.state('enter-check', {
					needLogin: true,
					url: '/enter-check',
					templateUrl: '../admin/views/enter-check.html',
					controller: 'CheckController as check'
					
				})
				.state('view-print-check', {
					needLogin: true,
					url: '/view-print-check',
					params: {
						fetch: true,
						action: 'viewPrintCheck',
						sc_token: null
					},
					templateUrl: '../admin/views/view-print-check.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('create-payment-link', {
					needLogin: true,
					url: '/create-payment-link',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/create-payment-link.html',
					controller: 'CheckController as check'
					
				})
				.state('create-bank-auth-link', {
					needLogin: true,
					url: '/create-bank-auth-link',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/create-bank-auth-link.html',
					controller: 'CheckController as check'
					
				})
				.state('companies-company', {
					needLogin: true,
					url: '/companies-company',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/company-account.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('users', {
					needLogin: true,
					url: '/users',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/user-account.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('export-check', {
					needLogin: true,
					url: '/export-check',
					params: {
						fetch: true,
						sc_token: null
					},
					templateUrl: '../admin/views/export-check.html',
					//controller: 'CompanyAdminController as companyadmin'
					
				})
				.state('companyUserDashboard', {
					needLogin: true,
					//superAdmin: false,
					//companyAdmin: false,
					//companyUser: true,
					//ghostMode: false,
					url: '/dashboard-cu',
					templateUrl: '../admin/views/dashboard-cu.html',
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
				$rootScope.authScxAdmin = false;
				//$rootScope.ghostLoginEnabled = false;
				
				// Grab the user from local storage and parse it to an object
				var user = JSON.parse(localStorage.getItem('user'));
				
				
				// If there is any user data in local storage then the user is quite
				// likely authenticated. If their token is expired, or if they are
				// otherwise not actually authenticated, they will be redirected to
				// the auth state because of the rejected request anyway
				if(user && user!=null) {
					// For Ghost Login check and enable the button
					var user_admin = JSON.parse(localStorage.getItem('user_admin'));
					if(user_admin && user_admin != null){
						$rootScope.ghostLoginEnabled = true;
					}
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
						$rootScope.authScxAdmin = true;
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
							$state.go('scxAdminDashboard');
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
