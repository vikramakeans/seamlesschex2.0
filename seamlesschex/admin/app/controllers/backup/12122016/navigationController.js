(function() {

	'use strict';

	angular
		.module('authApp')
		.controller('NavigationController', NavigationController);
		//.directive('leftMenu', leftMenu)
		//.directive('leftMenuItem', leftMenuItem);

	function NavigationController($rootScope, $scope, $state, $templateCache, $timeout) {
		  // to get current state
		  $rootScope.$state = $state;
		 //console.log($rootScope.currentUser);
		$scope.clearCache = function() { 
		
			$templateCache.removeAll();
		  }
		 //console.log($rootScope.currentUser.permissions);
		  this.menu = [
			{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', sref: 'superAdminDashboard', subMenu: false,
					menu : [{}] 
			},
			{ name : 'Seamlesschex Admin', iconClasses: 'fa  fa-user-md', sref: 'seamlesschex-admins', subMenu: false,
					menu : [{}] 
			},
			{ name : 'Merchants', iconClasses: 'fa  fa-user-md', sref: 'companies', subMenu: false,
					menu : [{}] 
			},
			{ name : 'Multiple Subscriptions', iconClasses: 'fa  fa-user-md', sref: 'subscriptions', subMenu: false,
					menu : [{}]
			},
			{ name : 'Add Merchants', iconClasses: 'fa fa-building-o', sref: 'company-sub',  subMenu: false,
					menu : [{}]
			},
			{ name : 'User Permissions', iconClasses: 'fa fa-users', sref: 'company-users', subMenu: false,
					menu : [{}]
			},
			{ name : 'Search Checks', iconClasses: 'fa fa-search', sref: 'view-print-check', subMenu: false,
					menu : [{}]
			},
			{ name : 'Invoices', iconClasses: 'fa fa-file-text-o', sref: 'invoice', subMenu: false,
					menu : [{}]
			},
			{ name : 'Batch', iconClasses: 'fa fa-exchange', subMenu: true,
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
			{ name : 'Settings', iconClasses: 'fa fa-gears', subMenu: true,
					menu : [
							{
								name: 'Fees settings',
								iconClasses: '',
								sref: 'fee-settings'
							}, 
							{
								name: 'Permission settings',
								iconClasses: '',
								sref: 'permission-settings'
							},
							{
								name: 'Message settings',
								iconClasses: '',
								sref: 'message-settings'
							}, 
							{
								name: 'Email settings',
								iconClasses: '',
								sref: 'email-settings'
							}, 
							{
								name: 'Batch Settings',
								iconClasses: '',
								sref: 'batch-settings'
							},
							{
								name: 'Status',
								iconClasses: '',
								sref: 'status'
							}
							] 
			},
			{ name : 'Email Templates', iconClasses: 'fa fa-gears', sref: 'email-template', subMenu: false,
					menu : [{}]
			},
			{ name : 'Plans', iconClasses: 'fa fa-gears', sref: 'plan-list', subMenu: false,
					menu : [{}]
			},
			{ name : 'Acitivity Logs', iconClasses: 'fa fa-history', sref: 'activity-logs', subMenu: false,
					menu : [{}]
			},
			{ name : 'Profile', iconClasses: 'fa fa-history', sref: 'profile-details', subMenu: false, 
					menu : [{}]
			}
		  ];
		 
		//seamlesschecx admin menu
		this.adminmenu =  [
		{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', permissionValue:$rootScope.currentUser.permissions.DASHBOARD, sref: 'scxAdminDashboard', subMenu: false,
				menu : [{}]
		},
		{ name : 'Seamlesschex Admin', iconClasses: 'fa  fa-user-md', permissionValue:'', sref: 'seamlesschex-admins', subMenu: false,
					menu : [{}] 
			},
			{ name : 'Companies', iconClasses: 'fa  fa-user-md', permissionValue:'', sref: 'companies', subMenu: false,
					menu : [{}] 
			},
			{ name : 'Multiple Subscriptions', iconClasses: 'fa  fa-user-md', permissionValue:'', sref: 'subscriptions', subMenu: false,
					menu : [{}]
			},
			{ name : 'Add Companies', iconClasses: 'fa fa-building-o', permissionValue:'', sref: 'company-sub',  subMenu: false,
					menu : [{}]
			},
			{ name : 'User Permissions', iconClasses: 'fa fa-users', permissionValue:'', sref: 'company-users', subMenu: false,
					menu : [{}]
			},
			{ name : 'Search Checks', iconClasses: 'fa fa-search', permissionValue:$rootScope.currentUser.permissions.VIEWCHECK, sref: 'view-print-check', subMenu: false,
					menu : [{}]
			},
			{ name : 'Invoices', iconClasses: 'fa fa-file-text-o', permissionValue:'', sref: '#/', subMenu: false,
					menu : [{}]
			},
			{ name : 'Batch', permissionValue:'', iconClasses: 'fa fa-exchange', subMenu: true,
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
			{ name : 'Settings', iconClasses: 'fa fa-gears', permissionValue:'', subMenu: true,
					menu : [
							{
								name: 'Fees settings',
								iconClasses: '',
								sref: 'fee-settings'
							}, 
							{
								name: 'Permission settings',
								iconClasses: '',
								sref: 'permission-settings'
							},
							{
								name: 'Message settings',
								iconClasses: '',
								sref: 'message-settings'
							}, 
							{
								name: 'Email settings',
								iconClasses: '',
								sref: 'email-settings'
							}, 
							{
								name: 'Batch Settings',
								iconClasses: '',
								sref: 'batch-settings'
							}
							] 
			},
			{ name : 'Email Templates', iconClasses: 'fa fa-gears', permissionValue:'', sref: 'email-template', subMenu: false,
					menu : [{}]
			},
			{ name : 'Plans', iconClasses: 'fa fa-gears',  permissionValue:'', sref: 'plan-list', subMenu: false,
					menu : [{}]
			},
			{ name : 'Acitivity Logs', iconClasses: 'fa fa-history', permissionValue:'',  sref: 'activity-logs', subMenu: false,
					menu : [{}]
			},
			{ name : 'Profile', iconClasses: 'fa fa-history',  permissionValue:'', sref: 'profile-details', subMenu: false, 
					menu : [{}]
			}
		];
		 
		 //company admin menu
		this.companyadminmenu = [
		{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', permissionValue: '', sref: 'companyAdminDashboard', subMenu: false,
				menu : [{}]
		},
		
		{ name : 'Enter Check', iconClasses: 'fa fa-users', permissionValue: $rootScope.currentUser.permissions.ADDCHECK, sref: 'enter-check', subMenu: false,
				menu : [{}]
		},
		{ name : 'View/Print Checks', iconClasses: 'fa fa-search', permissionValue: $rootScope.currentUser.permissions.VIEWCHECK, sref: 'view-print-check', subMenu: false,
				menu : [{}]
		},
		{ name : 'Create Payment Link', iconClasses: 'fa fa-file-text-o', permissionValue: $rootScope.currentUser.permissions.CHECKOUTLINK, sref: 'create-payment-link', subMenu: false,
				menu : [{}]
		},
		{ name : 'Create Bank Auth Link', iconClasses: 'fa fa-exchange', permissionValue: $rootScope.currentUser.permissions.BANKAUTHLINK, sref: 'create-bank-auth-link', subMenu: false,
				menu : [{}]
		},
		{ name : 'Companies', iconClasses: 'fa fa-gears', permissionValue: $rootScope.currentUser.permissions.COMPANY, subMenu: true,
				menu : [
						{
							name: 'Companies',
							iconClasses: '',
							sref: 'companies-company',
							permissionValue: $rootScope.currentUser.permissions.COMPANY
						}, 
						{
							name: 'Users',
							iconClasses: '',
							sref: 'users',
							permissionValue: $rootScope.currentUser.permissions.USER
						}
						
						] 
		},
		{ name : 'Reporting', iconClasses: 'fa fa-gears', permissionValue: $rootScope.currentUser.permissions.REPORT, subMenu: true,
				menu : [
						{
							name: 'Download CSV',
							iconClasses: '',
							sref: 'export-check',
							permissionValue: $rootScope.currentUser.permissions.REPORT
						}
						]							
		}
		];

		//company user menu
		this.companyusermenu =  [
		{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', permissionValue: $rootScope.currentUser.permissions.DASHBOARD, sref: 'companyAdminDashboard', subMenu: false,
				menu : [{}]
		},
		
		{ name : 'Enter Check', iconClasses: 'fa fa-users', permissionValue: $rootScope.currentUser.permissions.ADDCHECK, sref: 'enter-check', subMenu: false,
				menu : [{}]
		},
		{ name : 'View/Print Checks', iconClasses: 'fa fa-search', permissionValue: $rootScope.currentUser.permissions.VIEWCHECK, sref: 'view-print-check', subMenu: false,
				menu : [{}]
		},
		{ name : 'Create Payment Link', iconClasses: 'fa fa-file-text-o', permissionValue: $rootScope.currentUser.permissions.CHECKOUTLINK, sref: 'create-payment-link', subMenu: false,
				menu : [{}]
		},
		{ name : 'Create Bank Auth Link', iconClasses: 'fa fa-exchange', permissionValue: $rootScope.currentUser.permissions.BANKAUTHLINK, sref: 'create-bank-auth-link', subMenu: false,
				menu : [{}]
		},
		{ name : 'Merchants', iconClasses: 'fa fa-gears', permissionValue: $rootScope.currentUser.permissions.COMPANY,
				menu : [
						{
							name: 'Merchants',
							iconClasses: '',
							sref: 'companies-company',
							permissionValue: $rootScope.currentUser.permissions.COMPANY
						}, 
						{
							name: 'Users',
							iconClasses: '',
							sref: 'users',
							permissionValue: $rootScope.currentUser.permissions.USER
						}
						
						] 
		},
		{ name : 'Reporting', iconClasses: 'fa fa-gears', permissionValue: $rootScope.currentUser.permissions.REPORT, 
				menu : [
						{
							name: 'Download CSV',
							iconClasses: '',
							sref: 'export-check',
							permissionValue: $rootScope.currentUser.permissions.REPORT
						}
						]							
		}
		];

		
		
		// Menu for Super admin loop
		  $scope.showChilds = function(index){
				// For superadmin menu
				if($rootScope.currentUser.role == 1){
					$scope.navigation.menu[index].active = !$scope.navigation.menu[index].active;
				}
				// For admin menu
				if($rootScope.currentUser.role == 2){
					$scope.navigation.adminmenu[index].active = !$scope.navigation.adminmenu[index].active;
				}
				// For company-admin menu
				if($rootScope.currentUser.role == 3){
					$scope.navigation.companyadminmenu[index].active = !$scope.navigation.companyadminmenu[index].active;
				}
				// For company-user menu
				if($rootScope.currentUser.role == 4){
					$scope.navigation.companyusermenu[index].active = !$scope.navigation.companyusermenu[index].active;
				}
				//$timeout( function(){ collapseAnother(index); }, 500);
				collapseAnother(index);
				
			};
			
			var collapseAnother = function(index){
				// For Super admin menu
				if($rootScope.currentUser.role == 1){
					for(var i=0; i<$scope.navigation.menu.length; i++){
						if(i!=index){
							$scope.navigation.menu[i].active = false;
						}
					}
				}
				// For admin menu
				if($rootScope.currentUser.role == 2){
					for(var i=0; i<$scope.navigation.adminmenu.length; i++){
						if(i!=index){
							$scope.navigation.adminmenu[i].active = false;
						}
					}
				}
				
				// For company-admin menu
				if($rootScope.currentUser.role == 3){
					for(var i=0; i<$scope.navigation.companyadminmenu.length; i++){
						if(i!=index){
							$scope.navigation.companyadminmenu[i].active = false;
						}
					}
				}
				// For company-user menu
				if($rootScope.currentUser.role == 4){
					for(var i=0; i<$scope.navigation.companyusermenu.length; i++){
						if(i!=index){
							$scope.navigation.companyusermenu[i].active = false;
						}
					}
				}
		};
		
	}
	
	
	
})();
