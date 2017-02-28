var app = angular.module('plunker', ['ui.select2']);

app.controller('MainCtrl', function($scope) {
  $scope.name = 'World';
  
  $scope.options = [
    {
      name : 'david',
      id : 1
    },
    {
      name : 'hugo',
      id : 2
    }
  ];
  
  $scope.selected = $scope.options[1];
  $scope.selectedId = $scope.selected.id;
  
  
  
  
});

function AjaxCtrl($scope) {
    $scope.countries = ['usa', 'canada', 'mexico', 'france'];
    $scope.$watch('country', function(newVal) {
        if (newVal) $scope.cities = ['Los Angeles', 'San Francisco'];
    });
    $scope.$watch('city', function(newVal) {
        if (newVal) $scope.suburbs = ['SOMA', 'Richmond', 'Sunset'];
    });
}

/*function StaticCtrl($scope) {
    $scope.countries = {
        'usa': {
            'San Francisco': ['SOMA', 'Richmond', 'Sunset'],
            'Los Angeles': ['Burbank', 'Hollywood']
        },
        'canada': {
            'People dont live here': ['igloo', 'cave']
        }
    };
}*/