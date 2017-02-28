var app = angular.module('testApp', []);

app.controller('navCtrl', function() {
  this.menuItems = [];

  this.menuItems = [
  	{ name: 'Home', href: '' },
    { name: 'Products', href: '' },
    { name: 'Overview', href: '' }
  ];

});
