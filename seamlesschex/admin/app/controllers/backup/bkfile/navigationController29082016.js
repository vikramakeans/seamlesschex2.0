(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('NavigationController', NavigationController);
		//.directive('leftMenu', leftMenu)
		//.directive('leftMenuItem', leftMenuItem);

	function NavigationController($rootScope, $scope, $state) {
		  // to get current state
		  $rootScope.$state = $state;
		 
		  this.menu = [
			{ name : 'Dashboard', iconClasses: 'fa fa-dashboard',
					menu : [
							{ 
								name: 'Dashboard', 
								iconClasses: 'fa fa-dashboard',     
								sref: 'superAdminDashboard',
							}
							] 
			},
			{ name : 'Company Admin', iconClasses: 'fa  fa-user-md', 
					menu : [
					{ 
						name: 'Company Admin', 
						iconClasses: 'fa fa-home',     
						sref: 'companies'
					},
					{ 
						name: 'Add New', 
						iconClasses: 'fa fa-home',     
						sref: 'company-new'
					}
					] 
			},
			{ name : 'Company', iconClasses: 'fa  fa-user-md', 
					menu : [
					{ 
						name: 'Company', 
						iconClasses: 'fa fa-home',     
						sref: 'company-sub'
					},
					{ 
						name: 'Add New', 
						iconClasses: 'fa fa-home',     
						sref: 'company-sub-new'
					}
					] 
			},
			{ name : 'Company User', iconClasses: 'fa fa-users', 
					menu : [
							{ 
								name: 'Company User',
								iconClasses: '',
								sref: 'company-users'
							},
							{ 
								name: 'Add New',
								iconClasses: '',
								sref: 'company-user-new'
							}
							] 
			},
			{ name : 'Search Checks', iconClasses: 'fa fa-dashboard',
					menu : [
							{ 
								name: 'Search Checks',
								iconClasses: '',
								sref: '#/'
							}
							] 
			},
			{ name : 'Invoices', iconClasses: 'fa fa-dashboard', 
					menu : [
							{ 
								name: 'Invoices',
								iconClasses: '',
								sref: '#/'
							}
							] 
			},
			{ name : 'Batch', iconClasses: 'fa fa-dashboard', 
					menu : [
							{
								name: 'Import checks',
								iconClasses: '',
								sref: '#/'
							}, 
							{
								name: 'Imported checks',
								iconClasses: '',
								sref: '#/'
							}, 
							{
								name: 'Print checks',
								iconClasses: '',
								sref: '#/'
							}
							] 
			},
			{ name : 'Settings', iconClasses: 'fa fa-dashboard', 
					menu : [
							{
								name: 'Fees settings',
								iconClasses: '',
								sref: '#/'
							}, 
							{
								name: 'Message settings',
								iconClasses: '',
								sref: '#/'
							}, 
							{
								name: 'Email settings',
								iconClasses: '',
								sref: '#/'
							}, 
							{
								name: 'Batch Settings',
								iconClasses: '',
								sref: '#/'
							}
							] 
			},
			{ name : 'Acitivity Logs', iconClasses: 'fa fa-dashboard', 
					menu : [
							{
								name: 'Logs',
								iconClasses: '',
								sref: '#/'
							}
							]
			}
		  ];
		  
		  $scope.showChilds = function(index){
				$scope.navigation.menu[index].active = !$scope.navigation.menu[index].active;
				collapseAnother(index);
			};
			
			var collapseAnother = function(index){
				for(var i=0; i<$scope.navigation.menu.length; i++){
					if(i!=index){
						$scope.navigation.menu[i].active = false;
					}
				}
		};
		
	}
	
	
	
})();