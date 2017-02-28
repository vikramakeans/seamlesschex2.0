(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('PlanDetailsController', PlanDetailsController);

	function PlanDetailsController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.plandetails;
		vm.plandetails = {};
		vm.plandetailsError = false;
		vm.plandetailsSuccess = false;
		vm.plandetailsErrorText;
		vm.plandetailsSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		//console.log($scope.action);
		
		var id = $stateParams.id;
		
		
		// List the Plans	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of plans from the API
			$http.get( API_URL + 'api/authenticate/getPlans').success(function(planDetails) {
				for(var key in planDetails){
					if(planDetails.hasOwnProperty(key)){
						var planDetails = JSON.stringify(planDetails[key].data);
						$scope.plandetails.planDetails = JSON.parse(planDetails);
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
		
		// List the plan by id
		if(($rootScope.authSuAdmin == true && $scope.action == 'editPlanDetails') || ($rootScope.authSuAdmin == true && $scope.action == 'editPlanDetails' && id != '')){
			$http.get( API_URL + 'api/authenticate/getPlanById/'+id).success(function(plan) {
				
				var response = JSON.stringify(plan);
				var data = JSON.parse(response);
				$scope.data = data;
				angular.forEach($scope.data, function(planValue, key){
					 $scope.plandetails = planValue;
					 
				 });
				console.log($scope.plandetails);
				
				
			}).error(function(error) {
				vm.error = error;
			});
			
			// Edit Plan Detais
			$scope.updatePlanDetails = function() {
				
				var planDetails = {
					plan_name: $scope.plandetails.plan_name,
					plan_name_in_stripe: $scope.plandetails.plan_name_in_stripe,
					amount: $scope.plandetails.amount,
					no_of_check: $scope.plandetails.no_of_check,
					no_of_users: $scope.plandetails.no_of_users,
					no_of_companies: $scope.plandetails.no_of_companies,
					settings: $scope.plandetails.settings
				}
				$http.post( API_URL + 'api/authenticate/updatePlan/'+id, planDetails).then(function(response) {
					if(response.data.success === true){
						$scope.plandetails.plandetailsError = false;
						$scope.plandetails.plandetailsSuccess = true;
						$scope.plandetails.plandetailsSuccessText = 'Record Updated Sucessfully';
						$timeout(function () { $scope.plandetails.plandetailsSuccess = false; }, 5000);
					}
				}, function(error) {
					$scope.plandetails.plandetailsError = true;
					$scope.plandetails.plandetailsErrorText = error.data.error;
				});
			};
			
		}
		
		// Create Plan Detais
		if(($rootScope.authSuAdmin == true && $scope.action == 'addPlanDetails') || ($rootScope.authSuAdmin == true && $scope.action == 'addPlanDetails')){
			$scope.addPlanDetails = function() {
				
				var planDetails = {
					plan_name: $scope.plandetails.plan_name,
					plan_name_in_stripe: $scope.plandetails.plan_name_in_stripe,
					amount: $scope.plandetails.amount,
					no_of_check: $scope.plandetails.no_of_check,
					no_of_users: $scope.plandetails.no_of_users,
					no_of_companies: $scope.plandetails.no_of_companies,
					settings: $scope.plandetails.settings
				}
				$http.post( API_URL + 'api/authenticate/createPlan', planDetails).then(function(response) {
					if(response.data.success === true){
						$scope.plandetails.plandetailsError = false;
						$scope.plandetails.plandetailsSuccess = true;
						$scope.plandetails.plandetailsSuccessText = 'Record Added Sucessfully';
						$timeout(function () { $scope.plandetails.plandetailsSuccess = false; }, 5000);
					}
					
				}, function(error) {
					$scope.plandetails.plandetailsError = true;
					$scope.plandetails.plandetailsErrorText = error.data.error;
				});
			};
		}
		// Delete plan
		
		$scope.status = '  ';
		$scope.customFullscreen = false;
		$scope.deletePlan = function(ev,id) {
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
				  $http.post( API_URL + 'api/authenticate/deletePlan/'+id).then(function(response) {
					$scope.plandetails.plandetailsSuccess = true;
					$scope.plandetails.plandetailsSuccessText = 'Deleted Sucessfully';
				}, function(error) {
					$scope.plandetails.plandetailsError = true;
					$scope.plandetails.plandetailsErrorText = error.data.error;
				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
	  };
			
	}
	
})();
