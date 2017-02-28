(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('FeeSettingsController', FeeSettingsController);

	function FeeSettingsController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.feesettings;
		vm.feesettings = {};
		vm.feesettingsError = false;
		vm.feesettingsSuccess = false;
		vm.feesettingsErrorText;
		vm.feesettingsSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		//console.log($scope.action);
		
		var id = $stateParams.id;
		
		
		// List the Fees	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of fee from the API
			$http.get( API_URL + 'api/authenticate/getFee').success(function(feeSettings) {
				for(var key in feeSettings){
					if(feeSettings.hasOwnProperty(key)){
						var feeSettings = JSON.stringify(feeSettings[key].data);
						$scope.feesettings.feeSettings = JSON.parse(feeSettings);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the fee by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editFeeSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'editFeeSettings' && id != '')){
			$http.get( API_URL + 'api/authenticate/fee/'+id).success(function(fee) {
				
				var response = JSON.stringify(fee);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(feeValue, key){
					 $scope.feesettings = feeValue;
					 
				 });
				//console.log($scope.feesettings);
				
			}).error(function(error) {
				vm.error = error;
			});
		
			// Edit Fee Settings
			$scope.updateFeeSettings = function() {
				
				var feeSettings = {
					fees_name: $scope.feesettings.fees_name,
					value: $scope.feesettings.value
					
				}
				$http.post( API_URL + 'api/authenticate/updateFee/'+id, feeSettings).then(function(response) {
					if(response.data.success === true){
						$scope.feesettings.feesettingsError = false;
						$scope.feesettings.feesettingsSuccess = true;
						$scope.feesettings.feesettingsSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.feesettings.feesettingsSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.feesettings.feesettingsError = true;
					$scope.feesettings.feesettingsErrorText = error.data.error;
				});
			};
			
		}
		
		// Create Fee Settings
		if(($rootScope.authSuAdmin == true && $scope.action == 'addFeeSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'addFeeSettings')){
			$scope.addFeeSettings = function() {
				
				var messageSettings = {
					fees_name: $scope.feesettings.fees_name,
					value: $scope.feesettings.value
					
				}
				$http.post( API_URL + 'api/authenticate/createFee', messageSettings).then(function(response) {
					if(response.data.success === true){
						$scope.feesettings.feesettingsError = false;
						$scope.feesettings.feesettingsSuccess = true;
						$scope.feesettings.feesettingsSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.feesettings.feesettingsSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.feesettings.feesettingsError = true;
					$scope.feesettings.feesettingsErrorText = error.data.error;
				});
			};
		}
		// Delete fee
		
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteFee = function(ev,id) {
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
				  $http.post( API_URL + 'api/authenticate/deleteFee/'+id).then(function(response) {
					$scope.feesettings.feesettingsSuccess = true;
					$scope.feesettings.feesettingsSuccessText = 'Deleted Sucessfully';
				}, function(error) {
					$scope.feesettings.feesettingsError = true;
					$scope.feesettings.feesettingsErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
	  };
			
	}
	
})();
