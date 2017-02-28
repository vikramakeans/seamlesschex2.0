var app = angular.module('flashq', []);
	/*app.factory("flash", function($rootScope) {
	  var queue = [];
	  var currentMessage = "";

	  $rootScope.$on("$routeChangeSuccess", function() {
		currentMessage = queue.shift() || "";
	  });

	  return {
		setMessage: function(message) {
		  queue.push(message);
		},
		getMessage: function() {
		  return currentMessage;
		}
	  };
	});*/
	app.factory('AlertService', function () {
	  var success = {},
		  error = {},
		  alert = false;
	  return {
		getSuccess: function () {
		  return success;
		},
		setSuccess: function (value) {
		  success = value;
		  alert = true;
		},
		getError: function () {
		  return error;
		},
		setError: function (value) {
		  error = value;
		  alert = true;
		},
		reset: function () {
		  success = {};
		  error = {};
		  alert = false;
		},
		hasAlert: function () {
		  return alert;
		}
	  }
	});