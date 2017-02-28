// For any third party dependencies, like jQuery, place them in the lib folder.

// Configure loading modules from the lib directory,
// except for 'app' ones, which are in a sibling
// directory.
requirejs.config({
    baseUrl: '.',
    paths: {
        app: 'app',
		angular:'app/lib/angular/angular.min.js',
		angularuirouter:'app/lib/angular-ui-router/build',
		satellizer:'app/lib/satellizer'
    }
});

// Start loading the main app file. Put all of
// your application logic in there.
requirejs(['app/lib/angular/angular','app/lib/angular-ui-router/build/angular-ui-router','app/lib/satellizer/satellizer','app/main'],
function () {
        angular.bootstrap(document, ['authApp']);
    });
