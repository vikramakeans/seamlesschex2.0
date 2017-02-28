(function() {

	'use strict';

	angular
		.module('authApp').factory('checkFactory', function($http,API_URL) {
			 return{
			    getDuplicateCheckNumberFromFactory : function() {
			        return $http({
			            url:  API_URL + 'api/authenticate/checkduplicates',
			            method: 'post'
			        })
			    }
			 }
       }).directive('restrictInput', [function(){

        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                var ele = element[0];
                var regex = RegExp(attrs.restrictInput);
                var value = ele.value;

                ele.addEventListener('keyup',function(e){
                    if (regex.test(ele.value)){
                        value = ele.value;
                    }else{
                        ele.value = value;
                    }
                });
            }
        };
    }])
	.controller('CheckController', CheckController);

	function CheckController($scope, $location, $auth, $state, $http, $rootScope, API_URL, $payments, $stateParams, $timeout, $filter, CLIENT_URL, Upload, checkFactory, Flash, $mdDialog, localStorageService, dateFilter) {
		
		$scope.supported = false;



		$scope.checkout_link = {};
		$scope.check = {};
		$scope.company = {};
		$scope.paymentLink = {};
		$scope.bankauthLink = {};
		$scope.companysub = {};
		$scope.sc_token = $rootScope.currentUser.sc_token;
		$scope.authSuAdmin = $rootScope.authSuAdmin;
		$scope.authcompAdmin = $rootScope.authcompAdmin;
		
		$scope.date = new Date();
		$scope.month =  $filter('date')($scope.date, 'MMMM');
		var sc_token = $scope.sc_token;
		var authSuAdmin = $scope.authSuAdmin;
		var authcompAdmin = $scope.authcompAdmin;
		
		$scope.action = $stateParams.action;
        var mc_token = $stateParams.mc_token;
		
		
        $scope.check_token = $stateParams.check_token;
		
		


		// For Table Header Sort
		//$scope.orderByField = 'name';
		//$scope.reverseSort = false;
		
		//$scope.propertyName = 'name';
		//$scope.propertyName = 'date';
		$scope.reverse = true;

		$scope.sortBy = function(propertyName) {
			
		$scope.reverse = ($scope.propertyName === propertyName) ? !$scope.reverse : false;
		$scope.propertyName = propertyName;

		};

        // console.log(mc_token,'hellomc_token');
		// serch checks param
		$scope.check_company_admin = $stateParams.check_company_admin;
		$scope.check_company_user = $stateParams.check_company_user;
		$scope.check_from_date = $stateParams.check_from_date;
		$scope.check_to_date = $stateParams.check_to_date;
		$scope.check_month = $stateParams.check_month;

		$scope.options_list_show = [

			{ plan_name: "SeamlessChex Starter Plan"},
			{ plan_name: "SeamlessChex pro plan"},
			{ plan_name: "SeamlessChex premium "},
        ];
		 //@Auther : Vikram Singh
	    //Created date :10/01/17
	    //get check number 


		if($scope.action == 'enterCheck' && sc_token != ''){

		    checkFactory.getDuplicateCheckNumberFromFactory().success(function(response){
			       	if(response.checksuccess === true){
			       		$scope.data = response;
		                $scope.check.check_number = parseInt($scope.data.check)+ parseInt(1);
			       	}
			       if(response.success === true){
		                $scope.check.check_number =  parseInt(5001);
			       }
			}).error(function(error){
		           
			});
		}
		

	   	$scope.getCheckNumber = function(){
	    	var createcheck = {
				check_number: $scope.check.check_number 
		    }
    		$http.post( API_URL + 'api/authenticate/checkduplicates', createcheck).then(function(response) {
				
				if(response.data.checksuccess === true){
					$scope.checklist = response.data.check;
					$scope.check.check_number = parseInt($scope.checklist)+ parseInt(1);
				}
				if(response.data.checksuccessvalue === true){
					$scope.checklist = response.data.check;
					$scope.check.check_number = parseInt($scope.checklist)+ parseInt(1);
				}
				if(response.data.noduplicates === true){
				    $scope.check.check_number =  parseInt(5001);
				 }

			}, function(error) {
				// 	if(error.data.error === false){
				// 	 $scope.createcheck.checknumber =  parseInt(5001);
				// }
			});
	    }
		
		
		// Routing number check if present nothing to do else insert
		$scope.routingNumberCheck = function() {
			var routing_number = $scope.check.routing_number;
			if(routing_number){
				$http.get( API_URL + 'api/authenticate/checkRoutingNumber/'+routing_number).success(function(response) {
					$scope.check.routingInfo = response;
				 }).error(function(error) {
					$scope.error = error.data.error;
				});
			}				
		};
		
		
		if(sc_token){
			
			// Get merchants(sub account and main merchants) for current logged in users/company-admins/merchants
			$http.get( API_URL + 'api/authenticate/getCompanySubList/'+sc_token).success(function(users) {
				for(var key in users){
					if(users.hasOwnProperty(key)){
						var users = JSON.stringify(users[key].data);
						$scope.companyadmin = JSON.parse(users);
						
					}
				}
				// default selecting the first index
				$scope.check.companyadmin = $scope.companyadmin[0];
				//$scope.paymentLink.companyadmin = $scope.companyadmin[0];
				$scope.paymentLink.company_admin = $scope.companyadmin[0];
				$scope.bankauthLink.company_admin = $scope.companyadmin[0];
				$scope.companysub.companyadmin = $scope.companyadmin[0];
				$scope.company_admin = $scope.companyadmin[0];
			 }).error(function(error) {
				$scope.errorTrue = true;
				//$scope.error = error.data.error;
			});
			if($scope.authSuAdmin === false){
				$http.get( API_URL + 'api/authenticate/getCompanyPermissions/'+sc_token).success(function(permissions) {
					// user_settings permission listing
					var response_permissions = JSON.stringify(permissions);
					var default_sett = JSON.parse(response_permissions);
					$scope.data = default_sett;
					angular.forEach($scope.data, function(permissionValue, key){
					 // user_settings permission listing
					 $scope.company_permissions = permissionValue.company_permissions;
					});
				 }).error(function(error) {
					$scope.error = error.data.error;
				});
			}
		}
		
		
		
			// Recurrent Settings 
			$scope.showAdvanced = function(ev) {
				$mdDialog.show({
				  controller: 'CheckController as check',
				  templateUrl: 'views/template/recurring-payment.html',
				  parent: angular.element(document.body),
				  targetEvent: ev,

				})
				.then(function(saveRecurrent) {
					//alert(saveRecurrent.runs_every.value);
					//console.log(saveRecurrent);
					
					//$scope.error_message_type = true;
					console.log($scope.error_message_type);
					$scope.recurreing_settings = saveRecurrent;
					
					//console.log($scope.recurreing_settings);
					
					//$scope.check.payment = true;
					//$scope.recurreing_settings = saveRecurrent;
					//console.log($scope.recurreing_settings);
				}, function() {
					$scope.check.payment = false;
				});
			};
			
			// Enable fundConfirmation autometically if basic verfication enbale

			$scope.enableBasicVerification = function(ev){
				
				 if($scope.check.fund_confirmation == 0){
					 $scope.check.verify_before_save = 0;
				 }else{
					 $scope.check.verify_before_save = 1;
				 }
			}

			$scope.isDayEnable         = false;
			$scope.isWeekEnable        = false;
			$scope.isTwoWeeksEnable    = false;
			$scope.isMonthEnable       = false;
			$scope.isYearEnable        = false;

			 $scope.typeOptions = [
					{ name: 'Day', value: 'day' }, 
					{ name: 'Week', value: 'week'}, 
					{ name: 'Two Weeks', value: 'twoweek'}, 
					{ name: 'Month', value: 'month'}, 
					{ name: 'Year', value: 'year'}, 
				];
			$scope.weekdays = [
					{ name: 'Sunday', value: 'sunday' }, 
					{ name: 'Monday', value: 'monday'}, 
					{ name: 'Tuesday', value: 'tuesday'}, 
					{ name: 'Wednesday', value: 'wednesday'}, 
					{ name: 'Thursday', value: 'thursday'}, 
					{ name: 'Friday', value: 'friday'}, 
					{ name: 'Saturday', value: 'saturday'}, 
				];
				$scope.monthdate = [];
				for (var i = 1; i <= 31; i++) {
					$scope.monthdate .push( { name: i, value: i });
				}
			$scope.recurrentOptionChange = function(){
				if($scope.runs_every.name == 'Day'){
					$scope.isDayEnable          = true;
					$scope.isWeekEnable        = false;
					$scope.isTwoWeeksEnable    = false;
					$scope.isMonthEnable       = false;
				}
				if($scope.runs_every.name == 'Week'){
					$scope.isDayEnable   = true;
					$scope.isWeekEnable  = true;
					$scope.isMonthEnable = false;
				}
				if($scope.runs_every.name == 'Two Weeks'){
					$scope.isTwoWeeksEnable = true;
					$scope.isDayEnable      = false;
					$scope.isWeekEnable     = false;
					$scope.isMonthEnable    = false;
				}
				if($scope.runs_every.name == 'Month'){
					$scope.isDayEnable         = true;
					$scope.isWeekEnable        = false;
					$scope.isTwoWeeksEnable    = false;
					$scope.isMonthEnable       = true;
				
				}
				if($scope.runs_every.name == 'Year'){
					$scope.isDayEnable         = false;
					$scope.isWeekEnable        = false;
					$scope.isTwoWeeksEnable    = true;
					$scope.isMonthEnable       = false;
				}
				
			}
			$scope.cancelRecurrent = function() {
				 $scope.check.payment = false;
				 $mdDialog.cancel();
			};

			$scope.error_message_type = false;

			$scope.options = {
				step: 15,
				timeFormat: 'H:i A'
			};

			$scope.saveRecurrent = function() {
				//$scope.check.payment = true;
				var runs_every, day, week_days, time_of_day, how_many_times, from_date;
				//angular js validation is start here
			

				if(angular.isUndefined($scope.runs_every)) {
				    $scope.error_message_type ='Please select recurrent type !';
				    alert($scope.error_message_type);
					return false;
	       		}

				if(angular.isUndefined($scope.time_of_day)) {
				    $scope.error_message_type ='Please select time !';
				   	alert($scope.error_message_type);
					return false;
	       		}
	      		
	       		//var timereg = /^[01]\d\:[0-5]\d\s[AP]M$/;
                var timereg = /^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9] [APap][mM]$/;
 				var numreg = /^\d+$/;
 				time_of_day	= moment($scope.time_of_day).format('H:m A');


                if($scope.runs_every.name == 'Day')
                {

                    if(angular.isUndefined($scope.time_of_day)) {
					    $scope.error_message_type ='Please select time !';
					   	alert($scope.error_message_type);
						return false;
			       	}
                    if(!timereg.test(time_of_day))
                    {
                        alert('Time format is not correct');
                        return;
             		}
   			 	}

   			 	if($scope.runs_every.name === 'Week')
                {

                    if(angular.isUndefined($scope.week_days))
                    {
                        alert('Please select weekday');
                        return;
                    }
                    if(angular.isUndefined($scope.time_of_day)) {
					    $scope.error_message_type ='Please select time !';
					   	alert($scope.error_message_type);
						return false;
			       	}
                    if(!timereg.test(time_of_day))
                    {
                        alert('Time format is not correct');
                        return;
                    }
                }

             
                if($scope.runs_every.name === 'Two Weeks')
                {
                   
                    if(angular.isUndefined($scope.from_date))
                    {
                        alert('Please select date and time');
                        return;
                    }
				}
				if($scope.runs_every.name === 'Month')
                {
                   
                    if(angular.isUndefined($scope.day))
                    {
                        alert('Please select day');
                        return;
                    }
				}

				
				if(!numreg.test($scope.how_many_times))
                {
                    alert('Please enter how many times run this check');
                    return;
                }
             
				if($scope.runs_every != undefined){
					runs_every = $scope.runs_every.value;
				}
				if($scope.day != undefined){
					day = $scope.day.value;
				}
				if($scope.week_days != undefined){
					week_days = $scope.week_days.value;
				}
				if($scope.time_of_day != undefined){
					time_of_day = moment($scope.time_of_day).format('H:m A');
				}
				if($scope.how_many_times != undefined){
					how_many_times = $scope.how_many_times;
				}
				if($scope.from_date != undefined){
					from_date = $scope.from_date;
				}
				/*$scope.recurreing_settings = [{'recurreing_settings':
					[{'runs_every':runs_every, 'day':day, 'week_days':week_days, 'time_of_day':time_of_day, 'how_many_times':how_many_times, 'from_date':from_date}]
				}];*/
				$scope.recurreing_settings = [{'runs_every':runs_every, 'day':day, 'week_days':week_days, 'time_of_day':time_of_day, 'how_many_times':how_many_times, 'from_date':from_date}];
				
				//console.log($scope.recurreing_settings);
				
				$mdDialog.hide($scope.recurreing_settings);
				
			};
			
			//Save Check
			$scope.saveCheck = function() {
				
				//console.log($scope.recurreing_settings);
				//console.log($scope.check.payment);
				
			   // check params	
			   var saveCheckParam = {
					saveCheck: true,
					sc_token: sc_token,
					company_admin: $scope.check.companyadmin.mc_token,
					name: $scope.check.name,
					to_name: $scope.check.companyadmin.name,
					email: $scope.check.email,
					street_address: $scope.check.address,
					city: $scope.check.city,
					state: $scope.check.state,
					zipcode: $scope.check.zip,
					//phone: $scope.check.phone,
					check_number: $scope.check.check_number,
					check_amount: $scope.check.check_amount,
					memo1: $scope.check.memo1,
					memo2: $scope.check.memo2,
					routing_number: $scope.check.routing_number,
					account_number: $scope.check.account_number,
					confirm_account_number: $scope.check.confirm_account_number,
					//date: $scope.check.date,
					//authorisation_date: $scope.check.authorisation_date,
					month: $scope.month,
					check_type: 1,
					verify_before_save: $scope.check.verify_before_save,
					fund_confirmation: $scope.check.fund_confirmation,
					check_recurrent: $scope.check.payment,
					recurrent_settings: $scope.recurreing_settings,
					signature_not_required: $scope.check.signature_not_required
				}
				$http.post( API_URL + 'api/authenticate/enter/save/check', saveCheckParam).then(function(response) {
				
					if(response.data.success === true){
						// $scope.check = {};
					    var message = 'Seamlesschex !Check Added Successfully.';
               		    Flash.create('success', message, 5000, {class: 'custom-class'}, true);
               		    $state.go('search-checks');
						// $scope.checkError = false;
						// $scope.checkSuccess = true;
						// $scope.checkSuccessText = 'Check Added Successfully';
						// $timeout(function () { $scope.checkSuccess = false; }, 5000);
					}

				}, function(error) {
					 var message =  error.data.error
               		 Flash.create('danger', message, 5000, {class: 'custom-class'}, true);
					// $scope.checkError = true;
					// $scope.checkErrorText = error.data.error;
			});
		}
			
		

		//update check
		$scope.updateCheck = function(){
			
			var check_token = $scope.check_token;
		    var updateCheckParam = {
				updateCheck: true,
				sc_token: sc_token,
				company_admin: $scope.check.company_id,
		
				name: $scope.check.name,
				to_name: $scope.check.to_name,
				email: $scope.check.email,
				street_address: $scope.check.address,
				city: $scope.check.city,
				state: $scope.check.state,
				zipcode: $scope.check.zip,
				//phone: $scope.check.phone,
				check_number: $scope.check.check_number,
				check_amount: $scope.check.check_amount,
				memo1: $scope.check.memo1,
				memo2: $scope.check.memo2,
				authorisation_date: $scope.check.authorisation_date,
				date: $scope.check.date,
				routing_number: $scope.check.routing_number,
				account_number: $scope.check.account_number,
				confirm_account_number: $scope.check.confirm_account_number,
			
				month: $scope.month,
				check_type: 1,
				verify_before_save: $scope.check.verify_before_save,
				// fund_confirmation: $scope.check.fund_confirmation,
				check_recurrent: $scope.check.recurreing_payments,
				// signature_not_required: $scope.check.signature_not_required
			}
			$http.post( API_URL + 'api/authenticate/enter/update/check/'+check_token, updateCheckParam).then(function(response) {
			
				if(response.data.success === true){
				    var message = 'Seamlesschex !Check updated Successfully.';
           		    Flash.create('success', message, 2000, {class: 'custom-class'}, true);
           		    $state.go('search-checks');
				}

			}, function(error) {
				var message =  error.data.error
           		Flash.create('danger', message, 5000, {class: 'custom-class'}, true);
		});

		}
		
		//$scope.select2Options = {
			//allowClear:true,
			
		//};
		//  Get Company Users, selected company admin/merchants
		    
		$scope.$watch('check.company_admin', function(company_admin) {
            $scope.getCompanyUsers = function(company_admin) {
			
			//console.log($scope.viewprintcheck.company_admin);
		   if (company_admin == "") {return;}
			//var companyadminid = $scope.$eval(company_admin);
			var companyadminid = company_admin;
			
			var paramSettings = { 
				getCompanyUsers: true, 
				sc_token:sc_token, 
				company_admin: companyadminid 
			}
		
			var config = { 
				params: paramSettings, 
				headers : {'Accept' : 'application/json'} 
			};

		   $http.get( API_URL + 'api/authenticate/getCompanyUsers', config).success(function(users) {
			   if(users['token'] === false){
				   $scope.companyusers = '';
				   return false;
			   }
				// company_users populate 
				for(var key in users){
					if(users.hasOwnProperty(key)){
						//console.log(users[key]);
						var users = JSON.stringify(users[key].data);
						$scope.companyusers = JSON.parse(users);
						
					}
					
				}
				    
				//return $scope.companyusers;
				
			 }).error(function(error) {
				$scope.error = error.data.error;
			});
		};
       });
		
		// Serch checks Merchants (both company-admin and super admin)
		if($scope.action == 'viewPrintCheck' && sc_token != ''){
            localStorageService.clearAll();
           
		}
		
		if($scope.action == 'viewChecks' && sc_token != ''){
			
			$scope.getCheckList = function(action){

				   console.log(action);

					var viewSearchCheckParam = {
					viewSearchCheckParam: true,
					check_company_admin:  $scope.check_company_admin,
					check_company_user: $scope.check_company_user,
					check_from_date: $scope.check_from_date,
					check_to_date: $stateParams.check_to_date,
					check_month:$scope.check_month 
				}
				$http.post( API_URL + 'api/authenticate/viewsearchcheck', viewSearchCheckParam).then(function(response) {
					if(response.data.success === true){
						$scope.checklist = response.data.checks;
						$scope.totalchecks = response.data.totalrow;
						$scope.totalRow = $scope.totalchecks ? $scope.totalchecks: $scope.totalchecks = 0;
						$scope.total = response.data.totalAmount;
						$scope.totalAmount = $scope.total ? $scope.total: $scope.total = 0;
						// var toggleStatus = $scope.selectAll = true;
						// console.log(action);
						// angular.forEach($scope.checklist, function(check){ 
					 //        check.multiple = toggleStatus;
					 //    });

					    if(action =='viewChecks' && action !=''){
					       var toggleStatus = $scope.selectAll = true;
							angular.forEach($scope.checklist, function(check){ 
						        check.multiple = toggleStatus;
						        
						        
						    });
					    }
					    if(action =='deleteChecks' && action !=''){
					   	var toggleStatus = $scope.selectAll = false;
						angular.forEach($scope.checklist, function(check){ 
					        check.multiple = toggleStatus;
					    });

					    }
					}
					
				}, function(error) {
					$scope.message = error.data.message;
			
	              	//Flash.create('success', message, 2000, {class: 'custom-class'}, true);
					
			});

			}

			$scope.getCheckList($scope.action);
		}
		
		//	Vikram code	
		$scope.selectAllCheck = function() {

		    var toggleStatus = !$scope.selectAll;
		    angular.forEach($scope.checklist, function(check){ 
		         check.multiple = toggleStatus;
		    });

		}
		$scope.unChecked = function (){
			$scope.selectAll = false;
		}

		// delete selected check 

        $scope.deleteCheck = function(ev){
			var deleteCheckArr = [];
	        angular.forEach($scope.checklist, function(check){ 
		     	if(check.multiple == true){
		           deleteCheckArr.push({check_token : check.check_token});
		        }
		    });

		   	if(deleteCheckArr.length > 0){
		   		// Appending dialog to document.body to cover sidenav in docs app
			var confirm = $mdDialog.confirm()
				  .title('Confirm')
				  .textContent('Are you sure you want to delete the check?')
				  //.ariaLabel('Lucky day')
				  .targetEvent(ev)
				  .ok('Yes')
				  .cancel('No');
			$mdDialog.show(confirm).then(function() {
			  $scope.status = 'yes';
			 	$scope.deleteMultipleCheck = {
						multipleCheckId :deleteCheckArr
				};
			  if($scope.status == 'yes'){
				 $http.post( API_URL + 'api/authenticate/check/deletecheck',$scope.deleteMultipleCheck).success(function(data,headers,status) {
					if(data.status === true){
						angular.forEach($scope.checklist, function(check){ 
					     	if(check.multiple == true){
					          $scope.checklist.splice(check.check_token, 1);
					           check.multiple = false;
					           var action = 'deleteChecks';
					           $scope.getCheckList(action);
					        }
					    })
					    var message = '<strong>Seamlesschex !</strong>Check Deleted successfully.';
              		 	Flash.create('danger', message, 2000, {class: 'custom-class'}, true);
					}
			    }).error(function(error) {

				});
			  }
			}, function() {
			  $scope.status = 'no';
			});
				
		   	}
		   	else{
		   		var message = '<strong>Seamlesschex !</strong> Please select atleast one check to delete.';
                Flash.create('success', message, 2000, {class: 'custom-class'}, true);
		   	}
		}
		//print selected checks on check paper Print_CheckPaper
		$scope.printCheckOnPaper = function(ev){
			var printCheckOnpaperArr = [];
	        angular.forEach($scope.checklist, function(check){ 
		     	if(check.multiple == true){
		           printCheckOnpaperArr.push({check_token : check.check_token});
		        }
		    });

		   	if(printCheckOnpaperArr.length > 0){
		   		// Appending dialog to document.body to cover sidenav in docs app
			var confirm = $mdDialog.confirm()
				  .title('Confirm')
				  .textContent('Are you sure you want to print the check?')
				  //.ariaLabel('Lucky day')
				  .targetEvent(ev)
				  .ok('Yes')
				  .cancel('No');
			$mdDialog.show(confirm).then(function() {
				$scope.loader.loading = true; 
			  $scope.status = 'yes';
			 	$scope.printCheckOnPaperParam = {
						multipleCheckId :printCheckOnpaperArr
				};
			  if($scope.status == 'yes'){
				 $http.post( API_URL + 'api/authenticate/check/printcheckonpaper',$scope.printCheckOnPaperParam, { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf;'}).success(function(data,headers,status) {
					$scope.loader.loading = false ;
		        var filename,
		            octetStreamMime = "application/octet-stream",
		            contentType;
				if (!filename) {
		            filename = headers["x-filename"] || 'check.pdf';
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
			}, function() {
			  $scope.status = 'no';
			});
				
		   	}
		   	else{
		   		var message = '<strong>Seamlesschex !</strong> Please select atleast one check to print on paper.';
                Flash.create('success', message, 2000, {class: 'custom-class'}, true);
		   	}
		}

		//
		//print selected checks on plain paper Print_CheckPaper
		$scope.printCheckOnPlainPaper = function(ev){

			var printCheckArr = [];
	        angular.forEach($scope.checklist, function(check){ 
		     	if(check.multiple == true){
		           printCheckArr.push({check_token : check.check_token});
		        }
		    });

		   	if(printCheckArr.length > 0){
		   		// Appending dialog to document.body to cover sidenav in docs app
			var confirm = $mdDialog.confirm()
				  .title('Confirm')
				  .textContent('Are you sure you want to print the check?')
				  //.ariaLabel('Lucky day')
				  .targetEvent(ev)
				  .ok('Yes')
				  .cancel('No');
			$mdDialog.show(confirm).then(function() {
				$scope.loader.loading = true; 
			  $scope.status = 'yes';
			 	$scope.printCheckOnPlainPaperParam = {
						multipleCheckId :printCheckArr
				};
			  if($scope.status == 'yes'){
				 $http.post( API_URL + 'api/authenticate/check/printcheckonplainpaper',$scope.printCheckOnPlainPaperParam, { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf;'}).success(function(data,headers,status) {
					$scope.loader.loading = false ;
		        var filename,
		            octetStreamMime = "application/octet-stream",
		            contentType;
				if (!filename) {
		            filename = headers["x-filename"] || 'check.pdf';
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
			}, function() {
			  $scope.status = 'no';
			});
				
		   	}
		   	else{
		   		var message = '<strong>Seamlesschex !</strong> Please select atleast one check to print on paper.';
                Flash.create('success', message, 2000, {class: 'custom-class'}, true);
		   	}
		}
		
		
		$scope.pay_exactly = 0;
		$scope.getdataForEdit = function(check_token){
			$http.get( API_URL + 'api/authenticate/editcheck/'+check_token).then(function(response) {
				console.log(response);
				$scope.checklistArray = response.data;
				angular.forEach($scope.checklistArray, function(value, key){
				 	$scope.check = value ;
					var dateStr = value.date;

      				$scope.check.date= $filter('date')(new Date(dateStr.split('-').join('/')), 'MM-d-yyyy');
				   
					if(value.check_amount){
						$scope.pay_exactly = $scope.numberInWords(value.check_amount);
					}
			
			   });

			}, function(error) {
				$scope.bankauthLinkError = true;
				$scope.bankauthLinkErrorText = error.data.error;
			});
		}

		if($scope.action == 'editCheck' && $stateParams.check_token != ''){
			 var check_token = $stateParams.check_token;
			$scope.getdataForEdit(check_token);
		}

		//get data for duplicate check
		if($scope.action == 'duplicateCheck' && sc_token != ''){
			var check_token = $stateParams.check_token;
			$scope.getdataForEdit(check_token);
			
		}
		
		// Serch recurrent checks Merchants (both company-admin and super admin)
		if($scope.action == 'viewRecurrentChecks' && sc_token != ''){
			
		}

		//get check amount in words for edit
		$scope.getNumberInWords = function(amount){
			$scope.pay_exactly  =  $scope.numberInWords(amount);
		}
		
		
		// Copy the Url and select the value in textbox
		$scope.selectAllContent = function($event) {
		   $event.target.select();
		};
        $scope.copyButton = 'Copy';
		$scope.success = function () {
			$scope.copyButton = 'Copied!';
			angular.element('#generateUrl').triggerHandler('click');
		};
		$scope.fail = function (err) {
			console.error('Error!', err);
		};
		$scope.paymentLink = {};
		
		// Genrate Payment Link
		$scope.generateCheckoutLink = function() {
		   // Payment Link param
			var paymentLinkParam = {
				generateCheckoutLink: true,
				amount: $scope.paymentLink.amount,
				transactionFee: $scope.paymentLink.transactionFee,
				memo: $scope.paymentLink.memo,
				basicVerification: $scope.paymentLink.BASICVERIFICATION,
				fundConfirmation: $scope.paymentLink.FUNDCONFIRMATION,
				signature: $scope.paymentLink.SIGNATURE,
				thank_you_url: $scope.paymentLink.thank_you_url,
				company_admin: $scope.paymentLink.company_admin.mc_token
			}
			// Post to generate url and return the url
			$http.post( API_URL + 'api/authenticate/generate/checkout', paymentLinkParam).then(function(response) {
				//$scope.paymentLink.payLinkUrl = response.url;
				//console.log(response);
				if(response.data.success === true){
					var response = JSON.stringify(response.data);
					var data = JSON.parse(response);
					//$scope.data = data;
					 $scope.checkout_link = data.checkout_link;
					 $scope.checkout_token = $scope.checkout_link.checkout_token;
					 $scope.company_id = $scope.checkout_link.company_id;
					 
					 $scope.fee_type = ($scope.checkout_link.fee_type === 'BF')? 1 : 2;
					 $scope.signture = ($scope.checkout_link.signture === 'yes')? 1 : 0;
					 
					 $scope.paymentLink.payLinkUrl = CLIENT_URL+'checkout/'+$scope.checkout_token+'/'+$scope.company_id+'/'+$scope.fee_type+'/'+$scope.signture;
				}
				
			}, function(error) {
				$scope.paymentLinkError = true;
				$scope.paymentLinkErrorText = error.data.error;
			});
		};
		// Genrate Bank Auth Link
		$scope.generateBankAuthLink = function() {
		   // Bank Auth Link param
		   var signatureEnable = ($scope.bankauthLink.SIGNATURE) ? 1 : 0;
			var bankauthLinkParam = {
				generateBankAuthLink: true,
				amount: $scope.bankauthLink.amount,
				memo: $scope.bankauthLink.memo,
				signature: signatureEnable,
				thank_you_url: $scope.bankauthLink.thank_you_url,
				company_admin: $scope.bankauthLink.company_admin.mc_token
			}
			// Post to generate url and return the url
			$http.post( API_URL + 'api/authenticate/generate/bankauth', bankauthLinkParam).then(function(response) {
				if(response.data.success === true){
					var response = JSON.stringify(response.data);
					
					var data = JSON.parse(response);
					//$scope.data = data;
					 $scope.bankauth_link = data.bankauth_link;
					 $scope.pay_auth_token = $scope.bankauth_link.pay_auth_token;
					 $scope.company_id = $scope.bankauth_link.company_id;
					 
					 $scope.signture = ($scope.bankauth_link.signture === 'yes')? 1 : 0;
					 
					 $scope.bankauthLink.bankLinkUrl = CLIENT_URL+'payauth/'+$scope.pay_auth_token+'/'+$scope.company_id+'/'+$scope.signture;
				}
				//$scope.bankauthLink.bankLinkUrl = API_URL;
			}, function(error) {
				$scope.bankauthLinkError = true;
				$scope.bankauthLinkErrorText = error.data.error;
			});
		};
		// Convert amount number in to words
		$scope.numberInWords = function(num) {
		  $scope.amountWords = toWords(num);
		  return $scope.amountWords;
		};
		
		var th = ['', 'thousand', 'million', 'billion', 'trillion'];
		var dg = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
		var tn = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
		var tw = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

		function toWords(s) {
		  s = s.toString();
		  s = s.replace(/[\, ]/g, '');
		  if (s != parseFloat(s)) return 'not a number';
		  var x = s.indexOf('.');
		  if (x == -1) x = s.length;
		  if (x > 15) return 'too big';
		  var n = s.split('');
		  var str = '';
		  var sk = 0;
		  for (var i = 0; i < x; i++) {
			if ((x - i) % 3 == 2) {
			  if (n[i] == '1') {
				str += tn[Number(n[i + 1])] + ' ';
				i++;
				sk = 1;
			  } else if (n[i] != 0) {
				str += tw[n[i] - 2] + ' ';
				sk = 1;
			  }
			} else if (n[i] != 0) {
			  str += dg[n[i]] + ' ';
			  if ((x - i) % 3 == 0) str += 'hundred ';
			  sk = 1;
			}


			if ((x - i) % 3 == 1) {
			  if (sk) str += th[(x - i - 1) / 3] + ' ';
			  sk = 0;
			}
		  }
		  if (x != s.length) {
			var y = s.length;
			str += 'point ';
			for (var i = x + 1; i < y; i++) str += dg[n[i]] + ' ';
		  }
		  return str.replace(/\s+/g, ' ');
		};
	
	
		//@Auther : Vikram Singh 
    //Created date :29/12/1/2016
	   $scope.importbatch = {};
       var  file
       $scope.upload = function (files) {
            if (files && files.length){
                for (var i = files.length - 1; i >= 0; i--) {
                    file = files[i];
                } 
            }
        }
        $scope.showMsgs = false;
	   /* $scope.uploadFile = function(form) {

	    	 if ($scope.myform.$valid) {
          	  	  Upload.upload({
			            url: API_URL + 'api/authenticate/uploadcsv',
			            data: {file: file, companyadmin: $scope.importbatch.companyadmin}
			        }).then(function (resp) {
			        	if(resp.data.success === true){
							$scope.scxadminError = false;
							$scope.scxImportcsvSuccess = true;
							$scope.scxImportcsvSuccessText = 'Seamlesschex ! Csv File uploaded successfully';
						    $timeout(function () { $scope.scxImportcsvSuccess = false; }, 5000);
					    }
			        }, function (resp) {
			              $scope.scxadminError = true;
			              // $scope.importbatch = {};	
			              $scope.scxImportcsvErrorText = 'Error in csv file';
					      $timeout(function () {  $scope.scxadminError = false; }, 5000);
			        }, function (evt) {
			            var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
			            console.log('progress: ' + progressPercentage + '% ' + evt.config.data.file.name);
			       });
	        } else {
	            $scope.showMsgs = true;
	        }
	        
	    }	*/
 		
		$scope.Clear = function (){
			$scope.clear();
		}


		//pay check
		$scope.signatueData = {};
		$scope.payCheck = function(){
			var signature = $scope.accept();
			var checkOutSignatueParam = {
				checkoutsignature: true,
				user_name:$scope.check_out.name,
				checkout_token: $stateParams.checkout_token,
				company_id:  $stateParams.company_id,
				fee_type: $stateParams.fee_type,
				signture: $stateParams.signture,
				signature_image_link:signature.dataUrl
			}
			// Post to generate url and return the url
			$http.post( API_URL + 'api/authenticate/savecheckoutsignature', checkOutSignatueParam).then(function(response) {

				if(response.data.success === true){
				   
				}
				
			}, function(error) {
				$scope.bankauthLinkError = true;
				$scope.bankauthLinkErrorText = error.data.error;
			});
		}
     
    
		
	
	
	// Downloading PDF Section
    $scope.loader = {
			loading: false,
	};
    $scope.printCheck = function($event,check_token){
    	$event.preventDefault();
    	var check_token = check_token;
    	 $scope.loader.loading = true ;
			$http.get( API_URL + 'api/authenticate/check/printcheck/'+check_token, { responseType: 'arraybuffer' },{'Content-Type': 'application/application/pdf;'}).success(function(data,headers,status) {
			
				$scope.loader.loading = false ;
		        var filename,
		            octetStreamMime = "application/octet-stream",
		            contentType;
				if (!filename) {
		            filename = headers["x-filename"] || 'check.pdf';
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
	

}
	
})();
