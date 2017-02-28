(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('CompanyUserController', CompanyUserController);

	function CompanyUserController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.users;
		vm.companyuser;
		vm.companyuser = {};
		$scope.companyuser = {};
		$scope.companysub = {};
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
		
		// Company User Statuses
		$scope.statusUser = [
		'active',
		'inactive',
		'delete'
		];
		// List company-admin
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.action == 'companies'){
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
				  status_type: 3,
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
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == 'companyUsers'){
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate/companyUsers').success(function(users) {
				//console.log(users);
				
				for(var key in users){
					if(users.hasOwnProperty(key)){
						//console.log(users[key]);
						var users = JSON.stringify(users[key].data);
						$scope.companyuser.users = JSON.parse(users);
						//vm.users = users;
					}
				}
				
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		// Create company user
		$scope.saveCompanyUser = function() {
				
				var companyUserDetails = {
					createCompanyUser: true,
					name: $scope.companyuser.name,
					username: $scope.companyuser.username,
					password: $scope.companyuser.password,
					cpassword: $scope.companyuser.cpassword,
					created_by: $scope.current_role_id,
					company_admin: $scope.companyuser.company_admin
				}
				
				$http.post( API_URL + 'api/authenticate/createCompanyUser', companyUserDetails).then(function(response) {
					
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.companyuserError = false;
						$scope.companyuserSuccess = true;
						$scope.companyuserSuccessText = 'Company User Created Successfully';
						$timeout(function () { $scope.companyuserSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.companyuserError = true;
					$scope.companyuserErrorText = error.data.error;
				});
     
			};
		// Edit company-user
		//console.log(vm);
		$scope.updateCompanyUser = function() {
			
			var companyUserDetails = {
				updateCompanyUser: true,
				name: $scope.companyuser.name,
				username: $scope.companyuser.username,
				password: $scope.companyuser.password,
				cpassword: $scope.companyuser.cpassword,
				created_by: $scope.current_role_id,
				user_settings: $scope.companyuser.user_settings,
				company_admin: $scope.companyuser.company_admin
			}
			//console.log(sc_token);
			$http.post( API_URL + 'api/authenticate/updateCompanyUser/'+sc_token, companyUserDetails).then(function(response) {
				// hide the message after 5 sec
				if(response.data.success === true){
					$scope.companyuserError = false;
					$scope.companyuserSuccess = true;
					$scope.companyuserSuccessText = 'Company User Updated Successfully';
					$timeout(function () { $scope.companyuserSuccess = false; }, 5000);
				}
			}, function(error) {
				$scope.companyuserError = true;
				$scope.companyuserErrorText = error.data.error;
			});
		};
		//console.log($scope.action);
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
					$scope.selectedItem = {id: $scope.companyuser.company_admin, company_name: $scope.companyuser.company_name, company_email: $scope.companyuser.company_email};
					
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
				$scope.companyuser.companyuserSuccess = true;
				$scope.companyuser.companyuserSuccessText = 'Company User Updated Successfully';
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
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == 'companySub'){
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
					});
					// For default select company_admin
					$scope.selectedItem = {id: $scope.companysub.company_admin_id, company_name: $scope.companysub.company_admin_name, company_email: $scope.companysub.company_admin_email};
					
					//console.log($scope.selectedItem);
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
					$scope.companysubError = false;
					$scope.companysubSuccess = true;
					$scope.companysubSuccessText = 'Company Created Successfully';
					$timeout(function () { $scope.companysubSuccess = false; }, 5000);
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
				status: $scope.companysub.status
			}
			//console.log(sc_token);
			$http.post( API_URL + 'api/authenticate/updateCompanySub/'+sc_token, companySubDetails).then(function(response) {
				// hide the message after 5 sec
				if(response.data.success === true){
					$scope.companysubError = false;
					$scope.companysubSuccess = true;
					$scope.companysubSuccessText = 'Company Updated Successfully';
					$timeout(function () { $scope.companysubSuccess = false; }, 5000);
				}
			}, function(error) {
				$scope.companysubError = true;
				$scope.companysubErrorText = error.data.error;
			});
		};
		
		// Delete the company-sub
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteCompanySub = function(ev,sc_token) {
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
		  var companySubStatus = {
			  deleteCompanySub: true,
			  status_type: 8,
		  }
		  if($scope.status == 'yes'){
			  $http.post( API_URL + 'api/authenticate/deleteCompanySub/'+sc_token, companySubStatus).then(function(response) {
				// hide the message after 5 sec
				if(response.data.success === true){
					$scope.companysubError = false;
					$scope.companysubSuccess = true;
					$scope.companysubSuccessText = 'Company deleted Successfully';
					$timeout(function () { $scope.companysubSuccess = false; }, 5000);
				}
			}, function(error) {
				$scope.companysubError = true;
				$scope.companysubErrorText = error.data.error;
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
						if(users.hasOwnProperty(key)){418
						
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
					username: $scope.scxadmin.username,
					password: $scope.scxadmin.password,
					cpassword: $scope.scxadmin.cpassword,
					role_id: $scope.role_id,
					created_by: $scope.current_role_id
				}
				
				$http.post( API_URL + 'api/authenticate/createScxAdmin', scxAdminDetails).then(function(response) {
					
					// hide the message after 5 sec
					if(response.data.success === true){
						$scope.scxadminError = false;
						$scope.scxadminSuccess = true;
						$scope.scxadminSuccessText = 'Seamlesschex Admin Created Successfully';
						$timeout(function () { $scope.scxadminSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.scxadminError = true;
					$scope.scxadminErrorText = error.data.error;
				});
     
			};
			
		// Edit Seamlesschex-Admin
		//console.log(vm);
		$scope.updateScxAdmin = function() {
			
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
				// hide the message after 5 sec
				if(response.data.success === true){
					$scope.scxadminError = false;
					$scope.scxadminSuccess = true;
					$scope.scxadminSuccessText = 'Seamlesschex Admin Updated Successfully';
					$timeout(function () { $scope.scxadminSuccess = false; }, 5000);
				}
			}, function(error) {
				$scope.scxadminError = true;
				$scope.scxadminErrorText = error.data.error;
			});
		};
		//console.log($scope.action);
		// Get the scxadmin data as per sc_token
		if($scope.sc_token && $scope.sc_token != null && $scope.action == 'editScxAdmin'){
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
		}
		
		
		// Delete seamlesschex-admin
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteScxAdmin = function(ev,sc_token) {
		console.log(sc_token);
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
		  var scxAdminStatus = {
			  deleteScxAdmin: true,
			  status_type: 8,
		  }
		  if($scope.status == 'yes'){
			  $http.post( API_URL + 'api/authenticate/deleteScxAdmin/'+sc_token, scxAdminStatus).then(function(response) {
				$scope.scxadmin.scxadminSuccess = true;
				$scope.scxadmin.scxadminSuccessText = 'Seamlesschex Admin Updated Successfully';
			}, function(error) {
				$scope.scxadmin.scxadminError = true;
				$scope.scxadmin.scxadminErrorText = error.data.error;
			});
		  }
		}, function() {
		  $scope.status = 'no';
		});
	  }; 
			
	}
	
})();