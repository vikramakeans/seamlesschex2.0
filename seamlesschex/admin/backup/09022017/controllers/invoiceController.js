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

		//view Invoice
		if($rootScope.authenticated == true && $rootScope.authSuAdmin == true && $scope.action == 'invoice-view'){
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
            $scope.loader.loading = true ;
			$http.get( API_URL + 'api/authenticate/downloadinvoiceaspdf/'+stripe_id, { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf;'}).success(function(data,headers,status) {
			
				$scope.loader.loading = false ;
		        var filename,
		            octetStreamMime = "application/octet-stream",
		            contentType;
				if (!filename) {
		            filename = headers["x-filename"] || 'invoice.pdf';
		        }
		        contentType = headers["content-type"] || octetStreamMime;
            // Determine the content type from the header or default to "application/octet-stream"
          
	            if (navigator.msSaveBlob) {
	                var blob = new Blob([data], { type: contentType });
	                navigator.msSaveBlob(blob, filename);
	            } else {
	                var urlCreator = window.URL || window.webkitURL || window.mozURL || window.msURL;

	                if (urlCreator) {
	                   
	                    var link = document.createElement("a");

	                    if ("download" in link) {
	                        // Prepare a blob URL
	                        var blob = new Blob([data], { type: contentType });
	                        var url = urlCreator.createObjectURL(blob);

	                        link.setAttribute("href", url);
	                        link.setAttribute("download", filename);
	                        var event = document.createEvent('MouseEvents');
	                        event.initMouseEvent('click', true, true, window, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
	                        link.dispatchEvent(event);
	                    } else {
	                        var blob = new Blob([data], { type: octetStreamMime });
	                        var url = urlCreator.createObjectURL(blob);
	                        $window.location = url;
	                    }
	                }
	            }
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

				$http.post( API_URL + 'api/authenticate/downloadmultipleinvoiceaspdf',$scope.invoiceMultiple , { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf;'}).success(function(data,headers,status) {

					$scope.loader.loading = false ;
		            var filename,
		                octetStreamMime = "application/octet-stream",
		                contentType;
					if (!filename) {
	                    filename = headers["x-filename"] || 'invoice.pdf';
	                }
                	contentType = headers["content-type"] || octetStreamMime;
            // Determine the content type from the header or default to "application/octet-stream"

		            if (navigator.msSaveBlob) {
		                var blob = new Blob([data], { type: contentType });
		                navigator.msSaveBlob(blob, filename);
		            } else {

		                var urlCreator = window.URL || window.webkitURL || window.mozURL || window.msURL;
		                if (urlCreator) {

		                    var link = document.createElement("a");
		                    if ("download" in link) {
		                        // Prepare a blob URL
		                        var blob = new Blob([data], { type: contentType });
		                        var url = urlCreator.createObjectURL(blob);

		                        link.setAttribute("href", url);
		                        link.setAttribute("download", filename);
		                        var event = document.createEvent('MouseEvents');
		                        event.initMouseEvent('click', true, true, window, 1, 0, 0, 0, 0, false, false, false, false, 0, null);
		                        link.dispatchEvent(event);
		                    } else {
		                        var blob = new Blob([data], { type: octetStreamMime });
		                        var url = urlCreator.createObjectURL(blob);
		                        $window.location = url;
		                    }
		                }
		            }
			    }).error(function(error) {

				});
		   	}
		   	else{
		   		var message = '<strong>Seamlesschex !</strong> Please Select invoice.';
                Flash.create('success', message, 2000, {class: 'custom-class'}, true);
		   	}
	    }
	}
	
})();
