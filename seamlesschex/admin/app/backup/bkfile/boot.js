requirejs(["lib/angular/angular.min"], function(angular) {
    require.config({
		baseUrl: "app",
		paths: {
			'angular': 'lib/angular/angular.min',
			'angular-ui-router': 'lib/angular-ui-router/build/angular-ui-router',
			//'satellizer': 'lib/satellizer/satellizer',
			'angularAMD': 'lib/angularAMD.min'
			
		},
		shim: {
			"angularAMD": ["angular"],
			"angular-route": ["angular"]
		},    
		deps: ['app']
	});
});



/*require.config({
    baseUrl: 'app'
    //urlArgs: 'v=1.0'
});

require(
    [
        'lib/angular/angular.min',
        'lib/angular/angularAMD.min',
        'lib/angular-ui-router/build/angular-ui-router',
        'lib/satellizer/satellizer',
		'app',
        'services/routeResolver',
        'services/config',
        'services/customersBreezeService',
        'services/customersService',
        'services/dataService',
        'controllers/navbarController',
        'controllers/orderChildController'
    ],
    function () {
        angular.bootstrap(document, ['authApp']);
    });
	*/
