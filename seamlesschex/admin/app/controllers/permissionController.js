(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('PermissionController', PermissionController);

	function PermissionController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog) {
		
		var vm = this;
		vm.error;
		vm.permission;
		vm.permission = {};
		//$scope.permission = {};
		//$scope.company = {};
		$scope.permission.settings = {};
		$scope.user_settings = {};
		$scope.default_settings = {};
		$scope.settings = {};
		vm.permissionError = false;
		vm.permissionSuccess = false;
		vm.permissionErrorText;
		vm.permissionSuccessText;
		$scope.sc_token = $stateParams.sc_token;
		$scope.currstate = $stateParams.currstate;
		$scope.fetch = $stateParams.fetch;
		$scope.permission = $stateParams.permission;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		$scope.current_role_id = $rootScope.currentUser.role;
		//console.log($scope.action);
		$rootScope.$state = $state;
		var sc_token = $scope.sc_token;
		var currstate = $scope.currstate;
		if(typeof currstate !== 'undefined' && currstate !== null){
			localStorage.setItem('currstate', currstate);
		}
		
		// get the permssions of the company-user by sc_token
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.permission == true){
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate/permissions/'+sc_token).success(function(permissions) {
				
				// user_settings permission listing
				 var response_default_settings = JSON.stringify(permissions);
				 var default_sett = JSON.parse(response_default_settings);
				 $scope.data = default_sett;
				 angular.forEach($scope.data, function(defaultSettValue, key){
					 // user_settings permission listing
					 $scope.settings.user_settings = defaultSettValue.permission_settings;
					 $scope.default_settings = defaultSettValue.default_settings;
				 });
				
				// All permission listing
				 var response_default_settings = JSON.stringify($scope.default_settings);
				 var default_sett = JSON.parse(response_default_settings);
				 $scope.per_set = default_sett;
				 angular.forEach($scope.per_set, function(defaultSettValue, key){
					 $scope.settings.default_settings = defaultSettValue.per_set;
				 });
				//console.log($scope.settings.default_settings);
			}).error(function(error) {
				vm.error = error;
			});
		}
		// get the permission for merchant by sc_token
		if($rootScope.authenticated == true && $rootScope.authcompAdmin == true && $scope.permission == true){
			//Grab the list of users from the API
			$http.get( API_URL + 'api/authenticate/merchant/permissions/'+sc_token).success(function(permissions) {
				
				// user_settings permission listing
				 var response_default_settings = JSON.stringify(permissions);
				 var default_sett = JSON.parse(response_default_settings);
				 $scope.data = default_sett;
				 angular.forEach($scope.data, function(defaultSettValue, key){
					 // user_settings permission listing
					 $scope.settings.user_settings = defaultSettValue.permission_settings;
					 $scope.default_settings = defaultSettValue.default_settings;
				 });
				 //console.log($scope.default_settings);
				
				// All permission listing
				 var response_default_settings = JSON.stringify($scope.default_settings);
				 var default_sett = JSON.parse(response_default_settings);
				 $scope.per_set = default_sett;
				 angular.forEach($scope.per_set, function(defaultSettValue, key){
					 $scope.settings.default_settings = defaultSettValue.per_set;
				 });
				//console.log($scope.settings.default_settings);
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		
		// Update Permission Settings
		$scope.permissionSettings = function() {
			
			//console.log($scope.company.settings);
			var user_settings = $scope.settings.user_settings;
			
			var permissionSettings = {
				updatePermissions: true,
				user_settings: user_settings
			}
			//console.log(sc_token);
			$http.post( API_URL + 'api/authenticate/user/updateCompany/'+sc_token, permissionSettings).then(function(response) {
				$scope.settings.permissionSuccess = true;
				$scope.settings.permissionSuccessText = 'Permissions Updated Successfully';
			}, function(error) {
				$scope.settings.permissionError = true;
				$scope.settings.permissionErrorText = error.data.error;
			});
		};
		
		$scope.getRouteHistory = function() {
			if(localStorage.currstate){
				var currstate = localStorage.getItem('currstate');
				$state.go(currstate);
			}
			
		}
		 
		
	}
	
})();