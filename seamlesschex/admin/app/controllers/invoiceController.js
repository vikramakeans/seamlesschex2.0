(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('InvoiceController', InvoiceController);

	function InvoiceController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL,CLIENT_URL, $mdDialog, $timeout, $window, $sce,Flash) {
		
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
		// For Table Header Sort
		//$scope.orderByField = 'name';
		//$scope.reverseSort = false;
		
		$scope.propertyName = 'name';
		$scope.reverse = true;

		$scope.sortBy = function(propertyName) {
		$scope.reverse = ($scope.propertyName === propertyName) ? !$scope.reverse : false;
		$scope.propertyName = propertyName;
		};
		
		// List the Invoices	
		$scope.invoice ="";
		if(($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.fetch == true) || ($rootScope.authenticated == true && $rootScope.authScxAdmin == true && $scope.fetch == true)){
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

		//view Invoice
		if(($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.action == 'invoice-view') ||($rootScope.authenticated == true && $rootScope.authScxAdmin == true && $scope.action == 'invoice-view')){
				$scope.invoice = {
					id :$stateParams.id
				};

		    //var id = $stateParams.id;
			$http.post( API_URL + 'api/authenticate/viewinvoice',$scope.invoice).success(function(getInvoice) {

                $scope.amount      = getInvoice.amount;
				$scope.date        = getInvoice.date;
				$scope.stripe_id   = getInvoice.id;
				$scope.card_type   = getInvoice.card_type;
				$scope.last4       = getInvoice.last4;
				$scope.currencySign = '$';
			}).error(function(error) {
				vm.error = error;
			});
		}

		$scope.loader = {
			loading: false,
		};

		$scope.downloadInvoice = function($event,stripe_id){
		     $event.preventDefault();
			//$window.location.href=API_URL + 'api/authenticate/downloadinvoiceaspdf/'+stripe_id;
	 	  	 var a = document.createElement("a");
            document.body.appendChild(a);
            $scope.loader.loading = true ;
			 $http.get( API_URL + 'api/authenticate/downloadinvoiceaspdf/'+stripe_id, { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf; charset=UTF-8'}).success(function(binary) {
			$scope.loader.loading = false ;
				  var file = new Blob([binary], {type: 'application/pdf'});
				 
				  var fileURL = URL.createObjectURL(file);
				  	a.href = fileURL;
	                a.download = fileURL;
	                a.click()
	                $sce.trustAsResourceUrl(fileURL);
				 }).error(function(error) {
				vm.error = error;
			});
	    }

	    $scope.toggleAll = function() {
		     var toggleStatus = !$scope.selectAll;
		    angular.forEach($scope.invoice.getInvoice, function(invoice){ 
		    	//invoice.multiple == true
		         invoice.multiple = toggleStatus;
		    });

		}
		$scope.unChecked = function (){
			$scope.selectAll = false;
		}
	
	    $scope.downloadMultipleInvoice = function(data){
              
	        var arr = [];
	        angular.forEach(data.getInvoice, function(invoice){ 
		     	if(invoice.multiple == true){
		           arr.push({invoice_id : invoice.id});
		        }

		    });
		   	if(arr.length > 0){
		   		 $scope.loader.loading = true ;
	                $scope.invoiceMultiple = {
						multipleInvoiceId :arr
					};

				 var a = document.createElement("a");
	             document.body.appendChild(a);
				 $http.post( API_URL + 'api/authenticate/downloadmultipleinvoiceaspdf',$scope.invoiceMultiple , { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf; charset=UTF-8'}).success(function(binary) {
					 	 $scope.loader.loading = false ;
					  var file = new Blob([binary], {type: 'application/pdf'});
					  var fileURL = URL.createObjectURL(file);
					  	a.href = fileURL;
		                a.download = fileURL;
		                a.click()
		                
		                $sce.trustAsResourceUrl(fileURL);
					 }).error(function(error) {
					

				});
		   	}
		   	else{
		   		//alert('please select invoice !!');
		   		var message = '<strong>Seamlesschex !</strong> Please Select invoice.';
                Flash.create('success', message, 2000, {class: 'custom-class'}, true);
		   	}
	       
	    }
		
		//merchant invoice
		$scope.invoice = {};
		if( ($stateParams.action == 'merchantAccount' && $rootScope.currentUser !='') || ($stateParams.action == 'editCompanyAdmin' && $rootScope.currentUser !='')){
			var sc_token = $rootScope.currentUser.sc_token;
			if($stateParams.action == 'editCompanyAdmin'){
				sc_token = $stateParams.sc_token;
			}
			$http.get( API_URL + 'api/authenticate/merchantinvoice/'+sc_token).success(function(getInvoice) {
				for(var key in getInvoice){
					if(getInvoice.hasOwnProperty(key)){
						var getInvoice = JSON.stringify(getInvoice[key].charges);
						
						$scope.invoice.getInvoice = JSON.parse(getInvoice);
						//console.log($scope.invoice.getInvoice);
						
					}
				}
				//console.log(getInvoice);
				
			}).error(function(error) {
				vm.error = error;
			});
		}
	}
	
})();
