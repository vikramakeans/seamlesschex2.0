(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('CheckController', CheckController);

	function CheckController($scope, $location, $auth, $state, $http, $rootScope, API_URL, $payments, $stateParams, $timeout) {
		
		$scope.supported = false;
		
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
		// Show or hide the text box as per label clciked
		$scope.placeholder="Name:";
		$scope.placeholderemail="Email:";
		$scope.placeholdercity="City";
		$scope.placeholderpay="Pay to the Order of 1:";

		$scope.buttonCheckedName = function ()
		{
			$scope.placeholder="Name:";
			//$scope.apply();
		}
		
		$scope.buttonunCheckedPhone = function ()
		{
			$scope.placeholder="Phone:";
			//$scope.apply();
		}
		$scope.buttonCheckedEmail = function ()
		{
			$scope.placeholderemail="Email:";
			//$scope.apply();
		}
		
		$scope.buttonunCheckedAddress = function ()
		{
			$scope.placeholderemail="Address:";
			//$scope.apply();
		}
		$scope.buttonCheckedCity = function ()
		{
			$scope.placeholdercity="City:";
			//$scope.apply();
		}
		
		$scope.buttonunCheckedState = function ()
		{
			$scope.placeholdercity="State:";
			//$scope.apply();
		}
		$scope.buttonunCheckedPin = function ()
		{
			$scope.placeholdercity="Pin:";
			//$scope.apply();
		}
		$scope.buttonCheckedPay = function ()
		{
			$scope.placeholderpay="Pay to the Order of 1";
			//$scope.apply();
		}
		
		$scope.buttonunCheckedPay = function ()
		{
			$scope.placeholderpay="Pay to the Order of 2";
			//$scope.apply();
		}
		
		// Genrate Payment Link
		$scope.generateCheckoutLink = function() {
		   // Payment Link param
			var paymentLinkParam = {
				generateCheckoutLink: true,
				amount: $scope.paymentLink.amount,
				transactionFee: $scope.paymentLink.transactionFee,
				memo: $scope.paymentLink.memo,
				basicverification: $scope.paymentLink.BASICVERIFICATION,
				fundconformation: $scope.paymentLink.FUNDCONFIRMATION,
				signature: $scope.paymentLink.SIGNATURE,
				company_admin: $scope.paymentLink.company_admin
			}
			// Post to generate url and return the url
			$http.post( API_URL + 'api/authenticate/generate/checkout', paymentLinkParam).then(function(response) {
				//$scope.paymentLink.payLinkUrl = response.url;
				$scope.paymentLink.payLinkUrl = API_URL;
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
				requiresignature: $scope.bankauthLink.REQUIRESIGNATURE,
				company_admin: $scope.bankauthLink.company_admin
			}
			// Post to generate url and return the url
			$http.post( API_URL + 'api/authenticate/generate/bankauth', bankauthLinkParam).then(function(response) {
				//$scope.paymentLink.payLinkUrl = response.url;
				$scope.bankauthLink.bankLinkUrl = API_URL;
			}, function(error) {
				$scope.bankauthLinkError = true;
				$scope.bankauthLinkText = error.data.error;
			});
		};
	}
	
})();
