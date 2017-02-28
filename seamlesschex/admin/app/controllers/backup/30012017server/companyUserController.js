(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('CompanyUserController', CompanyUserController);
	function CompanyUserController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout, $q, $log, Flash, $location, CLIENT_URL) {
		
		var vm = this;
		vm.error;
		vm.users;
		vm.companyuser;
		vm.companyuser = {};
		$scope.companyuser = {};
		$scope.companysub = {};
		$scope.companysubscription = {};
		$scope.companysubscriptions = {};
		$scope.company_settings = {};
		$scope.scxadmin = {};
		$scope.company = {};
		$scope.companyuser.settings = {};
		vm.companyuserError = false;
		vm.companyuserSuccess = false;
		vm.companyuserErrorText;
		vm.companyuserSuccessText;
		$scope.sc_token = $stateParams.sc_token;
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		$scope.role_id = $stateParams.role_id;
		$scope.current_role_id = $rootScope.currentUser.role;
		//console.log($scope.action);
		
		var sc_token = $scope.sc_token;
		
		
		// For Table Header Sort
		//$scope.orderByField = 'name';
		//$scope.reverseSort = false;
		
		$scope.propertyName = 'name';
		$scope.reverse = true;

		$scope.sortBy = function(propertyName) {
		$scope.reverse = ($scope.propertyName === propertyName) ? !$scope.reverse : false;
		$scope.propertyName = propertyName;
		};
				
		// Company User Statuses
		$scope.statusUser = [
		'active',
		'inactive',
		'delete'
		];
		// List company-admin
		if( ($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.action == 'companies') || ($rootScope.authenticated == true && $rootScope.authScxAdmin == true && $scope.action == 'companies')){
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate').success(function(users) {
								
				for(var key in users){
					if(users.hasOwnProperty(key)){
						//console.log(users[key]);
						var users = JSON.stringify(users[key].data);
						$scope.companyuser.users = JSON.parse(users);
						//vm.users = users;
					}
				}
				
				
			}).error(function(error) {
				$scope.companyuser.error = error;
			});
		}
		// Delete Company-admin
		$scope.deleteCompany = function(ev, sc_token) {
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
				  status_type: 8,
			  }
			  if($scope.status == 'yes'){
				  $http.post( API_URL + 'api/authenticate/user/deleteCompany/'+sc_token, companyUserStatus).then(function(response) {
					$scope.companySuccess = true;
					$scope.companySuccessText = 'Company deleted Successfully';
				}, function(error) {
					$scope.companyError = true;
					$scope.companyErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
		};
		
		// List the company-user	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == 'companyUsers' || ($rootScope.authenticated == true && $rootScope.authScxAdmin == true && $scope.fetch == 'companyUsers') ){
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate/companyUsers').success(function(users) {
				//console.log(users);
				if( users.message ){
					$scope.companyuserMessage = true;
					$scope.companyuserMessageText = users.message;
				}else{
					for(var key in users){
						if(users.hasOwnProperty(key)){
							//console.log(users[key]);
							var users = JSON.stringify(users[key].data);
							$scope.companyuser.users = JSON.parse(users);
							//vm.users = users;
						}
					}
				}
				
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the company-user by sc_token (for company-admin)	
		if($rootScope.authenticated == true && $rootScope.authcompAdmin == true && $scope.fetch == 'companyUsers'){
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
							//console.log(users[key]);
							var users = JSON.stringify(users[key].data);
							$scope.companyuser.users = JSON.parse(users);
							//vm.users = users;
						}
					}
				}
				
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		$scope.saveCompanyUser = function() {
			var merchantUserDetails = {
			    createCompanyUser: true,
				email: $scope.companyuser.email,
				set_url: CLIENT_URL,
				company_admin: $scope.companyuser.company_admin,
				user_settings: $scope.companyuser.user_settings
			}
			
			$http.post( API_URL + 'api/authenticate/createCompanyUser', merchantUserDetails).then(function(response) {
				if(response.data.success === true){
					var message = '<strong>Seamlesschex !</strong>Merchant User added successfully and send email to the user.';
           		    var id = Flash.create('success', message, 5000, {class: 'custom-class', id: 'merchantUser'}, true);
					$state.go('company-users');
				}
				
			}, function(error) {
				$scope.companyuserError = true;
				$scope.companyuserErrorText = error.data.error;
				
			});
 
		};
		
		// No need for merchant user edit
		
		// Get the company-user data as per sc_token
		if($scope.sc_token && $scope.sc_token != null && $scope.action == 'editCompanyUser'){
			$http.get( API_URL + 'api/authenticate/companyUser/'+sc_token).success(function(response) {
			
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				//console.log($scope.data);
				angular.forEach($scope.data, function(companyValue, key){
						$scope.companyuser = companyValue;
					});
					// For default select company_admin
					//$scope.selectedItem = {id: $scope.companyuser.company_admin, company_name: $scope.companyuser.company_name, company_email: $scope.companyuser.company_email};
					$scope.companyuser.company_admin = $scope.companyuser.company_admin;
					
					//console.log($scope.selectedItem);
				 }).error(function(error) {
					$scope.error = error.data.error;
					});			
		}
		
		
		// Ghost Login for company-user
		$scope.ghostEnable = function(sc_token) {
			
			if($rootScope.authenticated == true && $rootScope.authSuAdmin == true  && $scope.ghost_mode == true){
				//Grab the list of users from the API
				$http.post( API_URL + 'api/authenticate/user/ghlo/'+sc_token).success(function(response) {
					var user_admin = JSON.stringify(localStorage.getItem('user'));
					localStorage.setItem('user_admin', user_admin);
					localStorage.removeItem('user');
					var user = JSON.stringify(response.user);

					// Set the stringified user data into local storage
					localStorage.setItem('user', user);
					$rootScope.authenticated = true;
					$rootScope.currentUser = response.user;
					$rootScope.ghostLoginEnabled = true;
					
					// For seamlesschex admin
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
					
				}).error(function(error) {
					vm.error = error;
				});
			}
		}
		
		// Delete company-user
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteCompanyUser = function(ev,sc_token) {
		
		// Appending dialog to document.body to cover sidenav in docs app
		var confirm = $mdDialog.confirm()
			  .title('Confirm')
			  .textContent('Are you sure you want to delete the company user?')
			  //.ariaLabel('Lucky day')
			  .targetEvent(ev)
			  .ok('Yes')
			  .cancel('No');
		$mdDialog.show(confirm).then(function() {
		  $scope.status = 'yes';
		  var companyUserStatus = {
			  deleteCompanyUser: true,
			  status_type: 8,
		  }
		  if($scope.status == 'yes'){
			  $http.post( API_URL + 'api/authenticate/deleteCompanyUser/'+sc_token, companyUserStatus).then(function(response) {
				if(response.data.success === 'delete'){  
					var message = '<strong>Seamlesschex !</strong> Merchant User deleted Successfully.';
					var id = Flash.create('success', message, 2000, {class: 'custom-class', id: id}, true);
					$state.go($state.current, {}, {reload: true});
				  }
			}, function(error) {
				$scope.companyuser.companyuserError = true;
				$scope.companyuser.companyuserErrorText = error.data.error;
			});
		  }
		}, function() {
		  $scope.status = 'no';
		});
	  };
	  
	 
		
		
		// List the company-sub	
		if(($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == 'companySub') || ($rootScope.authenticated == true && $rootScope.authScxAdmin == true && $scope.fetch == 'companySub')){
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate/companies-sub').success(function(users) {
				//console.log(users);
				
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
		
		// Get the company-sub data as per sc_token
		if($scope.sc_token && $scope.sc_token != null && $scope.action == 'editCompanySub'){
			$http.get( API_URL + 'api/authenticate/company-sub/'+sc_token).success(function(response) {
			
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				//console.log($scope.data);
				angular.forEach($scope.data, function(companyValue, key){
						$scope.companysub = companyValue;
						$scope.companysub.userstatus = companyValue.status;
					});
					// For default select company_admin
					//$scope.selectedItem = {id: $scope.companysub.company_admin_id, company_name: $scope.companysub.company_admin_name, company_email: $scope.companysub.company_admin_email};
					
					//$scope.selectedId = $scope.companysub.company_admin_id;
					//$scope.companysub.company_admin = $scope.companysub.company_admin_id;
					$scope.company.company_admin = $scope.companysub.mc_token;
					$scope.companysub.company_admin = $scope.companysub.mc_token;
					//console.log($scope.selectedItem);
					//console.log($scope.companysub.company_admin);
					//console.log($scope.company.company_admin);
				 }).error(function(error) {
					$scope.error = error.data.error;
				});			
		};
		
		// Add the company-sub new
		// Create company sub
		$scope.saveCompanySub = function() {
			var companySubDetails = {
				createCompanySub: true,
				name: $scope.companysub.name,
				email: $scope.companysub.email,
				created_by: $scope.current_role_id,
				company_admin: $scope.companysub.company_admin
			}
			
			$http.post( API_URL + 'api/authenticate/createCompanySub', companySubDetails).then(function(response) {
				
				// hide the message after 5 sec
				if(response.data.success === true){
				   var message = 'Seamlesschex !Merchant Created Successfully.';
                   var id = Flash.create('success', message, 2000, {class: 'custom-class', id: 'merchantSub'}, true);
                   $state.go('company-sub');
					
				}
				
			}, function(error) {
				$scope.companysubError = true;
				$scope.companysubErrorText = error.data.error;
			});
 
		};			
		
		// Update Company Sub
		$scope.updateCompanySub = function() {
			
			var companySubDetails = {
				updateCompanySub: true,
				name: $scope.companysub.name,
				email: $scope.companysub.email,
				created_by: $scope.current_role_id,
				company_admin: $scope.companysub.company_admin,
				status: $scope.companysub.status.status_code	
			}
			//console.log(sc_token);
			$http.post( API_URL + 'api/authenticate/updateCompanySub/'+sc_token, companySubDetails).then(function(response) {
				// hide the message after 5 sec
				if(response.data.success === true){
					var message = 'Seamlesschex !Merchant Updated Successfully.';
                    Flash.create('success', message, 2000, {class: 'custom-class'}, true);
                    $state.go('company-sub');
				}
			}, function(error) {
				//$scope.companysubError = true;
				//$scope.companysubErrorText = error.data.error;
				var message =  error.data.error;
                var id = Flash.create('danger', message, 2000, {class: 'custom-class', id: id}, true);
			});
		};
		
		// Delete the company-sub
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteCompanySub = function(ev,sc_token) {
		// Appending dialog to document.body to cover sidenav in docs app
		var confirm = $mdDialog.confirm()
			  .title('Confirm')
			  .textContent('Are you sure you want to delete the merchant?')
			  .targetEvent(ev)
			  .ok('Yes')
			  .cancel('No');
		$mdDialog.show(confirm).then(function() {
		  $scope.status = 'yes';
		  var companySubStatus = {
			  deleteCompanySub: true,
			  status_type: 8,
		  }
		  if($scope.status == 'yes'){
			  $http.post( API_URL + 'api/authenticate/deleteCompanySub/'+sc_token, companySubStatus).then(function(response) {
				// hide the message after 5 sec
				if(response.data.success === true){
				    var message = 'Seamlesschex !Merchant deleted Successfully';
                    Flash.create('success', message, 2000, {class: 'custom-class'}, true);
                    $state.go($state.current, {}, {reload: true});
				}
			}, function(error) {
				var message = error.data.error;
                Flash.create('warning', message, 2000, {class: 'custom-class', id: id}, true);
				
			});
		  }
		}, function() {
		  $scope.status = 'no';
		});
	  };
	  
	  	// List the seamlesschex admin	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == 'scxAdmins'){
			
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate/scxAdmin').success(function(users) {
				//console.log(users);
				if( users.message ){
					$scope.scxadminMessage = true;
					$scope.scxadminMessageText = users.message;
				}else{
						for(var key in users){
						if(users.hasOwnProperty(key)){
						
							var users = JSON.stringify(users[key].data);
							$scope.scxadmin.users = JSON.parse(users);
						}
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
			
		}
		
		
		// Create seamlesschex admin
		$scope.saveScxAdmin = function() {
				var scxAdminDetails = {
					createScxAdmin: true,
					name: $scope.scxadmin.name,
					email: $scope.scxadmin.email,
					set_url: CLIENT_URL,
					user_role: $scope.scxadmin.user_role,
					created_by: $scope.current_role_id
				}
				
				$http.post( API_URL + 'api/authenticate/createScxAdmin', scxAdminDetails).then(function(response) {
					
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.scxadminError = false;
						var message = 'Seamlesschex Admin Created Successfully.';
						var id = Flash.create('success', message, 5000, {class: 'custom-class', id: 'scxAdmin'}, true);
						$state.go('seamlesschex-admins');
					}
					
				}, function(error) {
					$scope.scxadminError = true;
					$scope.scxadminErrorText = error.data.error;
				});
     
			};
			
		// Edit Seamlesschex-Admin
		//console.log(vm);
		/*$scope.updateScxAdmin = function() {
			
			var scxAdminDetails = {
				updateScxAdmin: true,
				name: $scope.scxadmin.name,
				username: $scope.scxadmin.username,
				password: $scope.scxadmin.password,
				cpassword: $scope.scxadmin.cpassword,
				status: $scope.scxadmin.status,
				created_by: $scope.current_role_id
				
			}
			//console.log(sc_token);
			
			$http.post( API_URL + 'api/authenticate/updateScxAdmin/'+sc_token, scxAdminDetails).then(function(response) {
				
				if(response.data.success === true){
					
					var message = 'Seamlesschex Admin Updated Successfully.';
					var id = Flash.create('success', message, 5000, {class: 'custom-class', id: 'scxAdmin'}, true);
					$state.go('seamlesschex-admins');
					
				}
			}, function(error) {
				$scope.scxadminError = true;
				$scope.scxadminErrorText = error.data.error;
			});
			
			
			
		};*/
		
		//console.log($scope.action);
		// Get the scxadmin data as per sc_token
		/*if($scope.sc_token && $scope.sc_token != null && $scope.action == 'editScxAdmin'){
			$http.get( API_URL + 'api/authenticate/scxAdmin/'+sc_token).success(function(response) {
			
				var response = JSON.stringify(response);
				var data = JSON.parse(response);
				$scope.data = data;
				//console.log($scope.data);
				angular.forEach($scope.data, function(companyValue, key){
						$scope.scxadmin = companyValue;
					});
					// For default select company_admin
					$scope.selectedItem = {id: $scope.scxadmin.company_admin, company_name: $scope.scxadmin.company_name, company_email: $scope.scxadmin.company_email};
					
					//console.log($scope.selectedItem);
				 }).error(function(error) {
					$scope.error = error.data.error;
				});			
		}*/
		
		
		// Delete seamlesschex-admin
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteScxAdmin = function(ev,sc_token) {
		// Appending dialog to document.body to cover sidenav in docs app
		var confirm = $mdDialog.confirm()
			  .title('Confirm')
			  .textContent('Are you sure you want to delete the seamlesschex admin?')
			  //.ariaLabel('Lucky day')
			  .targetEvent(ev)
			  .ok('Yes')
			  .cancel('No');
		$mdDialog.show(confirm).then(function() {
		  $scope.status = 'yes';
		  var scxAdminStatus = {
			  deleteScxAdmin: true,
			  status_type: 8,
		  }
		  if($scope.status == 'yes'){
			  $http.post( API_URL + 'api/authenticate/deleteScxAdmin/'+sc_token, scxAdminStatus).then(function(response) {
				  if(response.data.action == 'delete'){
					var message = 'Seamlesschex Admin Deleted Successfully.';
					var id = Flash.create('success', message, 5000, {class: 'custom-class', id: 'scxAdmin'}, true);
					$state.go($state.current, {}, {reload: true});
				  }
			}, function(error) {
				$scope.scxadmin.scxadminError = true;
				$scope.scxadmin.scxadminErrorText = error.data.error;
			});
		  }
		}, function() {
		  $scope.status = 'no';
		});
	  };

		// Add Multiple subscription to company admin
		$scope.saveCompanySubscription = function() {
			var companySubcriptionDetails = {
				addCompanySubscription: true,
				plan_type: $scope.company_settings,
				number: $scope.companysubscription.number,
				exp_month: $scope.companysubscription.exp_month,
				exp_year: $scope.companysubscription.exp_year,
				cvc: $scope.companysubscription.cvc,
				company_admin: $scope.companysubscription.company_admin
			}
			
			$http.post( API_URL + 'api/authenticate/subscription/multiple', companySubcriptionDetails).then(function(response) {
				
				// hide the message after 5 sec
				if(response.data.success === true){
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
		// List the multiple subscription	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == 'multiplesubscriptions'){
			//Grab the list of subscriptions from the API
			$http.get( API_URL + 'api/authenticate/subscription/lists').success(function(subscriptions) {
				
				if( subscriptions.message ){
					$scope.companysubscriptionsMessage = true;
					$scope.companysubscriptionsMessageText = subscriptions.message;
				}else{
					for(var key in subscriptions){
						if(subscriptions.hasOwnProperty(key)){
							
							var subscriptions = JSON.stringify(subscriptions[key].data);
							$scope.companysubscriptions.subscriptions = JSON.parse(subscriptions);
							
						}
					}
				}
				
				
			}).error(function(error) {
				
				$scope.companysubscriptionsError = true;
				$scope.companysubscriptionsErrorText = error;
			});
		};
		
		// Delete seamlesschex-admin
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.cancelSubscriptions = function(ev,stripe_subscription, company_admin, stripe_plan_type) {
		
		// Appending dialog to document.body to cover sidenav in docs app
		var confirm = $mdDialog.confirm()
			  .title('Confirm')
			  .textContent('Are you sure you want to delete the company user?')
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
	  
		//Export xl sheet
		$scope.exportCustomer = function() {
			$http.get(API_URL + 'api/authenticate').success(function(response){
			var response = JSON.stringify(response);
			var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(companyValue, key){
					$scope.export = companyValue.data;
					// trigger the click after get the values
					$timeout(function() {
						angular.element('#exportCustomer').triggerHandler('click');
					});
					
				});
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
				}
				if(response.data.revoke === 1){
              	   var message = '<strong>Seamlesschex !</strong> Revoke Access Sucessfully.';
                   var id = Flash.create('success', message, 2000, {class: 'custom-class', id: 'merchantUser'}, true);
                   $state.go($state.current, {}, {reload: true});
				}
			}, function(error) {
				var message =  error.data.error;
                var id = Flash.create('danger', message, 2000, {class: 'custom-class', id: 'merchantUser'}, true);
			});
		}
		
		//@Auther Vikram
		//Add merchant while user edit the mechant details if user click add merchant link

		/*if($scope.sc_token && $scope.sc_token != null  && $stateParams.action === 'addCompanySub'){
			var sc_token = $stateParams.sc_token;
			$http.post( API_URL + 'api/authenticate/addMerchantFromLink/'+sc_token).then(function(response) {
				// hide the message after 5 sec
				if(response.status === 200){
			        $scope.data = response.data;
					angular.forEach($scope.data, function(companyValue, key){
						
						$scope.companysub.company_admin = companyValue.mc_token;
				 	});
					console.log($scope.companysub.company_admin);

				}
			}, function(error) {
				var message = '<strong>Seamlesschex !</strong> Data is not available.';
                Flash.create('danger', message, 2000, {class: 'custom-class', id: id}, true);
			});
		}

		//Add merchant user while user edit the mechant details if user click add merchant link
		if($scope.sc_token && $scope.sc_token != null&& $stateParams.action === 'addCompanyUser'){
			var sc_token = $stateParams.sc_token;
			$http.post( API_URL + 'api/authenticate/addMerchantFromLink/'+sc_token).then(function(response) {
				// hide the message after 5 sec
				if(response.status === 200){

			        $scope.data = response.data;
					angular.forEach($scope.data, function(companyValue, key){
						$scope.companyuser.company_admin = companyValue.mc_token ;
				 	});
					console.log($scope.companyuser.company_admin);
				    
				}
			}, function(error) {
				var message = '<strong>Seamlesschex !</strong> Data is not available.';
                Flash.create('danger', message, 2000, {class: 'custom-class'}, true);
			});
		}*/
		// Get access type as per company
		/*$scope.$watch('companyuser.company_admin', function(company_admin) {
			$scope.getCompanyPermission = function(company_admin) {
				if (!company_admin) return;
				var companyadminid = $scope.$eval(company_admin);
				
				var paramSettings = { 
					getCompanyPermission: true, 
					sc_token:sc_token, 
					company_admin: companyadminid 
				}

				var config = { 
					params: paramSettings, 
					headers : {'Accept' : 'application/json'} 
				};
					$http.get( API_URL + 'api/authenticate/getCompanyPermissionBy', config).success(function(users) {
				   if(users['token'] === false){
					   $scope.companyusers = '';
					   return false;
				   }
					// company_users populate 
					for(var key in users){
						if(users.hasOwnProperty(key)){
							//console.log(users[key]);
							var users = JSON.stringify(users[key].data);
							$scope.companyusers = JSON.parse(users);
							
						}
						
					}
						
					//return $scope.companyusers;
					
				 }).error(function(error) {
					$scope.error = error.data.error;
				});
				console.log("coming");
				console.log(company_admin);
			};
		});*/
		
		    
			
	}
	
})();