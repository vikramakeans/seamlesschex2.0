angular.module('jqtimepickerModule', [])
.directive('jqtimepicker', jqtimepicker)
.directive('somedirective', somedirective);

function jqtimepicker() {
	return {
		restrict: 'A',
		require: 'ngModel',
		link: function (scope, element, attrs) {
			element.timepicker({});
		}
	};
}
somedirective.$inject = ['$compile'];
function somedirective($compile) {
    return {	
			restrict: 'E',
			transclude: true,
			scope: {
			   bindModel:'=ngModel'
			},
			template: '<div> <input type="text" ng-model="bindModel" jqtimepicker> </div>'
		}
}
