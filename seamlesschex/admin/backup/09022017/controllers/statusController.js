(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('StatusController', StatusController);

	function StatusController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.status;
		vm.status = {};
		vm.statusError = false;
		vm.statusSuccess = false;
		vm.statusErrorText;
		vm.statusSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		//console.log($scope.action);
		
		var id = $stateParams.id;
		
		
		// List the Status	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of status from the API
			$http.get( API_URL + 'api/authenticate/getStatus').success(function(userStatus) {
				for(var key in userStatus){
					if(userStatus.hasOwnProperty(key)){
						var userStatus = JSON.stringify(userStatus[key].data);
						$scope.status.userStatus = JSON.parse(userStatus);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the status by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editStatus') || ($rootScope.authSuAdmin == true && $scope.action == 'editStatus' && id != '')){
			$http.get( API_URL + 'api/authenticate/status/'+id).success(function(status) {
				
				var response = JSON.stringify(status);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(statusValue, key){
				$scope.status = statusValue;
					 
				 });
				//console.log($scope.status);
				
			}).error(function(error) {
				vm.error = error;
			});
		
			// Edit Status
			$scope.updateStatus = function() {
				
				var userStatus = {
					status: $scope.status.status,
					status_name: $scope.status.status_name,
					color: $scope.status.color
					
				}
				$http.post( API_URL + 'api/authenticate/updateStatus/'+id, userStatus).then(function(response) {
					if(response.data.success === true){
						$scope.status.statusError = false;
						$scope.status.statusSuccess = true;
						$scope.status.statusSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.status.statusSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.status.statusError = true;
					$scope.status.statusErrorText = error.data.error;
				});
			};
			
		}
		// Create Status
		if(($rootScope.authSuAdmin == true && $scope.action == 'addStatus') || ($rootScope.authSuAdmin == true && $scope.action == 'addStatus')){
			$scope.addStatus = function() {
				
				var userStatus = {
					status: $scope.status.status,
					status_name: $scope.status.status_name,
					color: $scope.status.color
					
				}
				$http.post( API_URL + 'api/authenticate/createStatus', userStatus).then(function(response) {
					if(response.data.success === true){
						$scope.status.statusError = false;
						$scope.status.statusSuccess = true;
						$scope.status.statusSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.status.statusSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.status.statusError = true;
					$scope.status.statusErrorText = error.data.error;
				});
			};
		}
		
		
			
	}
	
})();
