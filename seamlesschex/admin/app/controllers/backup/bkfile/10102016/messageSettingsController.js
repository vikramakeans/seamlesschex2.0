(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('MessageSettingsController', MessageSettingsController);

	function MessageSettingsController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.messagesettings;
		vm.messagesettings = {};
		vm.messagesettingsError = false;
		vm.messagesettingsSuccess = false;
		vm.messagesettingsErrorText;
		vm.messagesettingsSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		//console.log($scope.action);
		
		var id = $stateParams.id;
		
		
		// List the Messages	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of messages from the API
			$http.get( API_URL + 'api/authenticate/getMessages').success(function(messageSettings) {
				for(var key in messageSettings){
					if(messageSettings.hasOwnProperty(key)){
						var messageSettings = JSON.stringify(messageSettings[key].data);
						$scope.messagesettings.messageSettings = JSON.parse(messageSettings);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the message by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editMessageSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'editMessageSettings' && id != '')){
			$http.get( API_URL + 'api/authenticate/message/'+id).success(function(message) {
				
				var response = JSON.stringify(message);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(messageValue, key){
					 $scope.messagesettings = messageValue;
					 
				 });
				//console.log($scope.messagesettings);
				
				
			}).error(function(error) {
				vm.error = error;
			});
			
			// Edit Message Settings
			$scope.updateMessageSettings = function() {
				
				var messageSettings = {
					field_label: $scope.messagesettings.field_label,
					field_name: $scope.messagesettings.field_name,
					form_name: $scope.messagesettings.form_name,
					message: $scope.messagesettings.message,
					type: $scope.messagesettings.type,
					position: $scope.messagesettings.position
				}
				$http.post( API_URL + 'api/authenticate/updateMessage/'+id, messageSettings).then(function(response) {
					if(response.data.success === true){
						$scope.messagesettings.messagesettingsError = false;
						$scope.messagesettings.messagesettingsSuccess = true;
						$scope.messagesettings.messagesettingsSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.messagesettings.messagesettingsSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.messagesettings.messagesettingsError = true;
					$scope.messagesettings.messagesettingsErrorText = error.data.error;
				});
			};
			
		}
		
		// Create Message Settings
		if(($rootScope.authSuAdmin == true && $scope.action == 'addMessageSettings') || ($rootScope.authSuAdmin == true && $scope.action == 'addMessageSettings')){
			$scope.addMessageSettings = function() {
				
				var messageSettings = {
					field_label: $scope.messagesettings.field_label,
					field_name: $scope.messagesettings.field_name,
					form_name: $scope.messagesettings.form_name,
					message: $scope.messagesettings.message,
					type: $scope.messagesettings.type,
					position: $scope.messagesettings.position
				}
				$http.post( API_URL + 'api/authenticate/createMessage', messageSettings).then(function(response) {
					if(response.data.success === true){
						$scope.messagesettings.messagesettingsError = false;
						$scope.messagesettings.messagesettingsSuccess = true;
						$scope.messagesettings.messagesettingsSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.messagesettings.messagesettingsSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.messagesettings.messagesettingsError = true;
					$scope.messagesettings.messagesettingsErrorText = error.data.error;
				});
			};
		}
		// Delete message
		
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteMessage = function(ev,id) {
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
				  $http.post( API_URL + 'api/authenticate/deleteMessage/'+id).then(function(response) {
					$scope.messagesettings.messagesettingsSuccess = true;
					$scope.messagesettings.messagesettingsSuccessText = 'Deleted Sucessfully';
				}, function(error) {
					$scope.messagesettings.messagesettingsError = true;
					$scope.messagesettings.messagesettingsErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
	  };
			
	}
	
})();