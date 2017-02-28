(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('AutocompleteSerchBoxController', AutocompleteSerchBoxController);

	function AutocompleteSerchBoxController($http, $auth, $rootScope, $state, $stateParams, $scope, API_URL, $mdDialog, $timeout, $q, $log) {
		
		var vm = this;
		vm.error;
		vm.users;
		vm.companyuser;
		vm.companyuser = {};
		$scope.companyuser = {};
		$scope.companysub = {};
		$scope.scxadmin = {};
		$scope.company = {};
		$scope.companyuser.settings = {};
		vm.companyuserError = false;
		vm.companyuserSuccess = false;
		vm.companyuserErrorText;
		vm.companyuserSuccessText;
		$scope.sc_token = $stateParams.sc_token;
		$scope.fetch = $stateParams.fetch;
		$scope.ghost_mode = $stateParams.ghost_mode;
		$scope.action = $stateParams.action;
		$scope.role_id = $stateParams.role_id;
		$scope.current_role_id = $rootScope.currentUser.role;
		//console.log($scope.action);
		
		
		var sc_token = $scope.sc_token;
		
		// For Table Header Sort
		//$scope.orderByField = 'name';
		//$scope.reverseSort = false;
		
		// Autocomplete search text box
		var self = this;

		self.simulateQuery = false;
		self.isDisabled    = false;

		self.repos         = loadAll();
		self.querySearch   = querySearch;
		self.selectedItemChange = selectedItemChange;
		self.searchTextChange   = searchTextChange;
		
		// ******************************
		// Internal methods
		// ******************************

		/**
		 * Search for repos... use $timeout to simulate
		 * remote dataservice call.
		 */
		function querySearch (query) {
		  var results = query ? self.repos.filter( createFilterFor(query) ) : self.repos,
			  deferred;
		  if (self.simulateQuery) {
			deferred = $q.defer();
			$timeout(function () { deferred.resolve( results ); }, Math.random() * 1000, false);
			return deferred.promise;
		  } else {
			return results;
		  }
		}
		/*$scope.searchValue = function() {
			//console.log("coming");
			console.log($scope.searchBoxAuto);
			var val = $scope.searchBoxAuto;
			return val+"999";
		};*/
		function searchTextChange(text) {
		  $log.info('Text changed to ' + text);
		  return text;
		}

		function selectedItemChange(item) {
		  $log.info('Item changed to ' + JSON.stringify(item));
		  return item;
		}

		/**
		 * Build `components` list of key/value pairs
		 */
		function loadAll() {
		  var repos = [
			{
			  'name'      : 'Antone Becker'
			},
			{
			  'name'      : 'test'
			},
			{
			  'name'      : 'sipu'
			},
			{
			  'name'      : 'Bower Material'
			},
			{
			  'name'      : 'Material Start'
			}
		  ];
		  return repos.map( function (repo) {
			repo.value = repo.name.toLowerCase();
			return repo;
		  });
		}

		/**
		 * Create filter function for a query string
		 */
		function createFilterFor(query) {
		  var lowercaseQuery = angular.lowercase(query);

		  return function filterFn(item) {
			return (item.value.indexOf(lowercaseQuery) === 0);
		  };

		}
		
		 
			
	}
	
})();