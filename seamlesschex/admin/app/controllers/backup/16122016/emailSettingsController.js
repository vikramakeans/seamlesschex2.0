(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('EmailSettingsController', EmailSettingsController);

	function EmailSettingsController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.emailsettings;
		vm.emailsettings = {};
		vm.emailsettingsError = false;
		vm.emailsettingsSuccess = false;
		vm.emailsettingsErrorText;
		vm.emailsettingsSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		var id = $stateParams.id;
		
		// List the Email	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of email from the API
			$http.get( API_URL + 'api/authenticate/getEmail').success(function(emailSettings) {
				
				for(var key in emailSettings){
					if(emailSettings.hasOwnProperty(key)){
						var emailSettings = JSON.stringify(emailSettings[key].data);
						$scope.emailsettings.emailSettings = JSON.parse(emailSettings);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the email by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editEmailSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'editEmailSettings' && id != '')){
			$http.get( API_URL + 'api/authenticate/email/'+id).success(function(email) {
				var response = JSON.stringify(email);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(emailValue, key){
					 $scope.emailsettings = emailValue;
					 
				 });
				
			}).error(function(error) {
				vm.error = error;
			});
			
			// Edit Email Settings
			$scope.updateEmailSettings = function() {
				
				var emailSettings = {
					settings_type: $scope.emailsettings.settings_type,
					settings_name: $scope.emailsettings.settings_name,
					value: $scope.emailsettings.value
				}
				$http.post( API_URL + 'api/authenticate/updateEmail/'+id, emailSettings).then(function(response) {
					if(response.data.success === true){
						$scope.emailsettings.emailsettingsError = false;
						$scope.emailsettings.emailsettingsSuccess = true;
						$scope.emailsettings.emailsettingsSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.emailsettings.emailsettingsSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.emailsettings.emailsettingsError = true;
					$scope.emailsettings.emailsettingsErrorText = error.data.error;
				});
			};
			
		}
		
		// Create Email Settings
		if(($rootScope.authSuAdmin == true && $scope.action == 'addEmailSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'addEmailSettings')){
			$scope.addEmailSettings = function() {
				
				var emailSettings = {
					settings_type: $scope.emailsettings.settings_type,
					settings_name: $scope.emailsettings.settings_name,
					value: $scope.emailsettings.value
				}
				$http.post( API_URL + 'api/authenticate/createEmail', emailSettings).then(function(response) {
					if(response.data.success === true){
						$scope.emailsettings.emailsettingsError = false;
						$scope.emailsettings.emailsettingsSuccess = true;
						$scope.emailsettings.emailsettingsSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.emailsettings.emailsettingsSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.emailsettings.emailsettingsError = true;
					$scope.emailsettings.emailsettingsErrorText = error.data.error;
				});
			};
		}
		
		// Delete email
		
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteEmail = function(ev,id) {
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
				  $http.post( API_URL + 'api/authenticate/deleteEmail/'+id).then(function(response) {
					$scope.emailsettings.emailsettingsSuccess = true;
					$scope.emailsettings.emailsettingsSuccessText = 'Deleted Sucessfully';
				}, function(error) {
					$scope.emailsettings.emailsettingsError = true;
					$scope.emailsettings.emailsettingsErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
	  };
			
	}
	
})();
