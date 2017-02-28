(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('InvoiceController', InvoiceController);

	function InvoiceController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout) {
		
		var vm = this;
		vm.error;
		vm.invoice;
		vm.invoice = {};
		//vm.invoiceError = false;
		//vm.invoiceSuccess = false;
		//vm.invoiceErrorText;
		//vm.invoiceSuccessText;
		
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		
		//console.log($scope.action);
		
		var id = $stateParams.id;
		
		
		// List the Invoices	
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true){
			//Grab the list of status from the API
			$http.get( API_URL + 'api/authenticate/listcharges').success(function(getInvoice) {
				for(var key in getInvoice){
					if(getInvoice.hasOwnProperty(key)){
						var getInvoice = JSON.stringify(getInvoice[key].charges);
						$scope.invoice.getInvoice = JSON.parse(getInvoice);
						//console.log($scope.invoice.getInvoice);
						
					}
				}
				
			}).error(function(error) {
				vm.error = error;
			});
		}
			
	}
	
})();
