(function() {

	'use strict';

	angular
		.module('authApp', ['ngRoute','ui.materialize'])
		.controller('PayController', PayController)
		.config(['$routeProvider', function($routeProvider) {
        $routeProvider.when('/register/:user_id', {
            templateUrl: '../admin/views/userRegister.html',
            controller: 'PayController'
        });
    }]);

	function PayController($scope,$http,reservationData,$location,$routeParams){
		$scope.res_id = $routeParams.reserv_id;
        $scope.paid = false;

        $scope.handleStripe = function(status, response){
            if(response.error) {
                $scope.paid= false;
                $scope.message = "Error from Stripe.com"
            } else {
                var $payInfo = {
                    'token' : response.id,
                    'customer_id' : $scope.reservation_info.customer_id,
                    'total':$scope.reservation_info.total_price
                };

                $http.post('/api/register', $payInfo).success(function(data){
                    if(data.status=="OK"){
                        $scope.paid= true;
                        $scope.message = data.message;
                    }else{
                        $scope.paid= false;
                        $scope.message = data.message;
                    }
                });

            }
        };

        $scope.init = function(){
            $scope.loaded = false;
            
            $http.get('/api/reservation/'+$scope.res_id).success(function(data){
                $scope.reservation_info = data;
                $scope.loaded=true;
            });
        };

        $scope.init();
	}
    
})();