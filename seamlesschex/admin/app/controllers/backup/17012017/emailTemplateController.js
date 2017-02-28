(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('EmailTemplateController', EmailTemplateController);

	function EmailTemplateController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout, textAngularManager) {
		
		var vm = this;
		vm.error;
		vm.emailtemplate;
		vm.emailtemplate = {};
		vm.emailtemplateError = false;
		vm.emailtemplateSuccess = false;
		vm.emailtemplateErrorText;
		vm.emailtemplateSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		//console.log($scope.action);
		
		var id = $stateParams.id;
		
		$scope.version = textAngularManager.getVersion();
		$scope.versionNumber = $scope.version.substring(1);
		
		
		// List the emailtemplate	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of emailtemplate from the API
			$http.get( API_URL + 'api/authenticate/getEmailTemplate').success(function(emailTemplate) {
				for(var key in emailTemplate){
					if(emailTemplate.hasOwnProperty(key)){
						var emailTemplate = JSON.stringify(emailTemplate[key].data);
						$scope.emailtemplate.emailTemplate = JSON.parse(emailTemplate);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the EmailTemplate by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editEmailTemplate') || ($rootScope.authSuAdmin == true && $scope.action == 'editEmailTemplate' && id != '')){
			$http.get( API_URL + 'api/authenticate/emailtemplate/'+id).success(function(email) {
				
				var response = JSON.stringify(email);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(emailValue, key){
					 $scope.emailtemplate = emailValue;
					 
				 });
				//console.log($scope.emailtemplate);
				
			}).error(function(error) {
				vm.error = error;
			});
		
			// Edit Email Template 
			$scope.updateEmailTemplate = function() {
				
				var emailTemplate = {
					template_name: $scope.emailtemplate.template_name,
					from: $scope.emailtemplate.from,
					subject: $scope.emailtemplate.subject,
					template_value: $scope.emailtemplate.template_value
					
				}
				$http.post( API_URL + 'api/authenticate/updateEmailTemplate/'+id, emailTemplate).then(function(response) {
					if(response.data.success === true){
						$scope.emailtemplate.emailtemplateError = false;
						$scope.emailtemplate.emailtemplateSuccess = true;
						$scope.emailtemplate.emailtemplateSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.emailtemplate.emailtemplateSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.emailtemplate.emailtemplateError = true;
					$scope.emailtemplate.emailtemplateErrorText = error.data.error;
				});
			};
			
		}
		
		// Create Email Template 
		if(($rootScope.authSuAdmin == true && $scope.action == 'addEmailTemplate') || ($rootScope.authSuAdmin == true && $scope.action == 'addEmailTemplate')){
			$scope.addEmailTemplate = function() {
				
				var emailTemplate = {
					template_name: $scope.emailtemplate.template_name,
					from: $scope.emailtemplate.from,
					subject: $scope.emailtemplate.subject,
					template_value: $scope.emailtemplate.template_value
					
				}
				$http.post( API_URL + 'api/authenticate/createEmailTemplate', emailTemplate).then(function(response) {
					if(response.data.success === true){
						$scope.emailtemplate.emailtemplateError = false;
						$scope.emailtemplate.emailtemplateSuccess = true;
						$scope.emailtemplate.emailtemplateSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.emailtemplate.emailtemplateSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.emailtemplate.emailtemplateError = true;
					$scope.emailtemplate.emailtemplateErrorText = error.data.error;
				});
			};
		}
		// Delete emailtemplate
		
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deleteEmailTemplate = function(ev,id) {
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
				  $http.post( API_URL + 'api/authenticate/deleteEmailTemplate/'+id).then(function(response) {
					$scope.emailtemplate.emailtemplateSuccess = true;
					$scope.emailtemplate.emailtemplateSuccessText = 'Deleted Sucessfully';
				}, function(error) {
					$scope.emailtemplate.emailtemplateError = true;
					$scope.emailtemplate.emailtemplateErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
	  };
			
	}
	
})();
