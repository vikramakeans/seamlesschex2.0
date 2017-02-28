(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('PermissionSettingsController', PermissionSettingsController);

	function PermissionSettingsController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog) {
		
		var vm = this;
		vm.error;
		vm.permissionsettings;
		vm.permissionsettings = {};
		vm.permissionsettingsError = false;
		vm.permissionsettingsSuccess = false;
		vm.permissionsettingsErrorText;
		vm.permissionsettingsSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		var id = $stateParams.id;
		
		$scope.roles = [
		{id: 1, name: 'Super Admin'},
		{id: 2, name: 'Seamlesschex Admin'},
		{id: 3, name: 'Merchant'},
		{id: 4, name: 'Merchant User'}
		];
		
		// List the Permissions	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of permission from the API
			$http.get( API_URL + 'api/authenticate/getPermission').success(function(permissionSettings) {
				
				for(var key in permissionSettings){
					if(permissionSettings.hasOwnProperty(key)){
						var permissionSettings = JSON.stringify(permissionSettings[key].data);
						$scope.permissionsettings.permissionSettings = JSON.parse(permissionSettings);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the permission by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editPermissionSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'editPermissionSettings' && id != '')){
			$http.get( API_URL + 'api/authenticate/permissionById/'+id).success(function(permission) {
				
				var response = JSON.stringify(permission);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(permissionValue, key){
					 $scope.permissionsettings = permissionValue;
					 
				 });
				
			}).error(function(error) {
				vm.error = error;
			});
			
			// Edit Permission Settings
			$scope.updatePermissionSettings = function() {
				
				var permissionSettings = {
					role_id: $scope.permissionsettings.role_id,
					sl_no: $scope.permissionsettings.sl_no,
					permission_label: $scope.permissionsettings.permission_label,
					permission_type: $scope.permissionsettings.permission_type,
					permission_name: $scope.permissionsettings.permission_name,
					permission_value: $scope.permissionsettings.permission_value
				}
				$http.post( API_URL + 'api/authenticate/updatePermission/'+id, permissionSettings).then(function(response) {
					if(response.data.success === true){
						$scope.permissionsettings.permissionsettingsError = false;
						$scope.permissionsettings.permissionsettingsSuccess = true;
						$scope.permissionsettings.permissionsettingsSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.permissionsettings.permissionsettingsSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.permissionsettings.permissionsettingsError = true;
					$scope.permissionsettings.permissionsettingsErrorText = error.data.error;
				});
			};
			
		}
		
		// Create Permission Settings
		if(($rootScope.authSuAdmin == true && $scope.action == 'addPermissionSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'addPermissionSettings')){
			$scope.addPermissionSettings = function() {
				
				var permissionSettings = {
					role_id: $scope.permissionsettings.role_id,
					sl_no: $scope.permissionsettings.sl_no,
					permission_label: $scope.permissionsettings.permission_label,
					permission_type: $scope.permissionsettings.permission_type,
					permission_name: $scope.permissionsettings.permission_name,
					permission_value: $scope.permissionsettings.permission_value
				}
				$http.post( API_URL + 'api/authenticate/createPermission', permissionSettings).then(function(response) {
					if(response.data.success === true){
						$scope.permissionsettings.permissionsettingsError = false;
						$scope.permissionsettings.permissionsettingsSuccess = true;
						$scope.permissionsettings.permissionsettingsSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.permissionsettings.permissionsettingsSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.permissionsettings.permissionsettingsError = true;
					$scope.permissionsettings.permissionsettingsErrorText = error.data.error;
				});
			};
		}
		
		// Delete permission
		
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deletePermission = function(ev,id) {
			// Appending dialog to document.body to cover sidenav in docs app
			var confirm = $mdDialog.confirm()
				  .title('Confirm')
				  .textContent('Are you sure you want to delete the row?')
				  //.ariaLabel('Lucky day')
				  .targetEvent(ev)
				  .ok('Yes')
				  .cancel('No');
			$mdDialog.show(confirm).then(function() {
			  $scope.status = 'yes';
			  
			  if($scope.status == 'yes'){
				  $http.post( API_URL + 'api/authenticate/deletePermission/'+id).then(function(response) {
					$scope.permissionsettings.permissionsettingsSuccess = true;
					$scope.permissionsettings.permissionsettingsSuccessText = 'Deleted Sucessfully';
				}, function(error) {
					$scope.permissionsettings.permissionsettingsError = true;
					$scope.permissionsettings.permissionsettingsErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
	  };
			
	}
	
})();
