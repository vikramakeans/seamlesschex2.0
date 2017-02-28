(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('CheckController', CheckController);

	function CheckController($scope, $location, $auth, $state, $http, $rootScope, API_URL, $payments, $stateParams, $timeout, $filter, CLIENT_URL) {
		
		$scope.supported = false;
		$scope.checkout_link = {};
		$scope.check = {};
		$scope.company = {};
		$scope.sc_token = $rootScope.currentUser.sc_token;
		$scope.authSuAdmin = $rootScope.authSuAdmin;
		$scope.date = new Date();
		$scope.month =  $filter('date')($scope.date, 'MMMM');
		var sc_token = $scope.sc_token;
		
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
			 }).error(function(error) {
				$scope.error = error.data.error;
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
			
			//Save Check
			$scope.saveCheck = function() {
			   // check params	
			   var saveCheckParam = {
					saveCheck: true,
					sc_token: sc_token,
					company_admin: $scope.check.companyadmin.id,
					name: $scope.check.name,
					to_name: $scope.check.companyadmin.name,
					email: $scope.check.email,
					street_address: $scope.check.address,
					city: $scope.check.city,
					state: $scope.check.state,
					zipcode: $scope.check.zip,
					//phone: $scope.check.phone,
					check_number: $scope.check.check_number,
					check_amount: $scope.check.amount,
					memo: $scope.check.memo,
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
					check_recurrent: $scope.check.recurreing_payments,
					signature_not_required: $scope.check.signature_not_required
				}
				$http.post( API_URL + 'api/authenticate/enter/save/check', saveCheckParam).then(function(response) {
				
					if(response.data.success === true){
						$scope.checkError = false;
						$scope.checkSuccess = true;
						$scope.checkSuccessText = 'Check Added Successfully';
						$timeout(function () { $scope.checkSuccess = false; }, 5000);
					}

				}, function(error) {
					$scope.checkError = true;
					$scope.checkErrorText = error.data.error;
				});
			};
			
		}
		
		//$scope.select2Options = {
			//allowClear:true,
			
		//};
		//  Get Company Users, selected company admin/merchants
		    
		$scope.$watch('check.company_admin', function(company_admin) {
            $scope.getCompanyUsers = function(company_admin) {
		   //console.log($scope.viewprintcheck.company_admin);
		   if (!company_admin) return;
			var companyadminid = $scope.$eval(company_admin);
			
			/* if (company_admin ==='508')
				$scope.states = ['BeiJing', 'ShangHai'];
			if (company_admin ==='509')
				$scope.states = ['California', 'Mississippi'];*/
			
			//return item.id;
		   //console.log(companyadminid);
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
				company_admin: $scope.paymentLink.company_admin
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
				$scope.paymentLinkText = error.data.error;
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
				company_admin: $scope.bankauthLink.company_admin
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
				$scope.bankauthLinkText = error.data.error;
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
	
	}
	
})();
