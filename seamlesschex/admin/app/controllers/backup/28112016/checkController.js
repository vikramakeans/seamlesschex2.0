(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('CheckController', CheckController);

	function CheckController($scope, $location, $auth, $state, $http, $rootScope, API_URL, $payments, $stateParams, $timeout, CLIENT_URL) {
		
		$scope.supported = false;
		$scope.checkout_link = {};
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
		
		// Enter Check Screen
		$scope.name = false;
		$scope.phone = true;
		$scope.email = false;
		$scope.address = true;
		$scope.city = false;
		$scope.state = true;
		$scope.pin = true;
		$scope.pay1 = false;
		$scope.pay2 = true;
		
		// Show or hide the text box as per label clciked
		$scope.placeholder="Name:";
		$scope.placeholderemail="Email:";
		$scope.placeholdercity="City";
		$scope.placeholderpay="Pay to the Order of 1:";

		$scope.buttonCheckedName = function ()
		{
			//$scope.placeholder="Name:";
			//$scope.apply();
			$scope.name = $scope.name === false ? true: false;
			$scope.name = false;
			$scope.phone = true;
			
		};
		
		$scope.buttonunCheckedPhone = function ()
		{
			//$scope.placeholder="Phone:";
			//$scope.apply();
			$scope.phone = $scope.phone === false ? true: false;
			$scope.phone = false;
			$scope.name = true;
			
		};
		$scope.buttonCheckedEmail = function ()
		{
			$scope.email = $scope.email === false ? true: false;
			$scope.email = false;
			$scope.address = true;
			//$scope.placeholderemail="Email:";
			//$scope.apply();
		};
		
		$scope.buttonunCheckedAddress = function ()
		{
			$scope.address = $scope.address === false ? true: false;
			$scope.address = false;
			$scope.email = true;
			
			//$scope.placeholderemail="Address:";
			//$scope.apply();
		};
		$scope.buttonCheckedCity = function ()
		{
			$scope.city = $scope.city === false ? true: false;
			$scope.city = false;
			$scope.state = true;
			$scope.pin = true;
			//$scope.placeholdercity="City:";
			//$scope.apply();
		};
		
		$scope.buttonunCheckedState = function ()
		{
			$scope.state = $scope.state === false ? true: false;
			$scope.state = false;
			$scope.city = true;
			$scope.pin = true;
			//$scope.placeholdercity="State:";
			//$scope.apply();
		};
		$scope.buttonunCheckedPin = function ()
		{
			$scope.pin = $scope.pin === false ? true: false;
			$scope.pin = false;
			$scope.city = true;
			$scope.state = true;
			//$scope.placeholdercity="Pin:";
			//$scope.apply();
		};
		$scope.buttonCheckedPay = function ()
		{
			$scope.pay1 = $scope.pay1 === false ? true: false;
			$scope.pay1 = false;
			$scope.pay2 = true;
			//$scope.apply();
		};
		
		$scope.buttonunCheckedPay = function ()
		{
			$scope.pay2 = $scope.pay2 === false ? true: false;
			$scope.pay2 = false;
			$scope.pay1 = true;
			//$scope.apply();
		};
		
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
				var bankauthLinkParam = {
				generateBankAuthLink: true,
				amount: $scope.bankauthLink.amount,
				memo: $scope.bankauthLink.memo,
				signature: $scope.bankauthLink.SIGNATURE,
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
	}
	
})();
