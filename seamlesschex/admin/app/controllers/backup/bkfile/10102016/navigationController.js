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
			{ name : 'Dashboard', iconClasses: 'fa fa-dashboard',
					menu : [
							{ 
								name: 'Dashboard', 
								iconClasses: 'fa fa-dashboard',     
								sref: 'superAdminDashboard',
							}
							] 
			},
			{ name : 'Seamlesschex Admin', iconClasses: 'fa  fa-user-md', 
					menu : [
					{ 
						name: 'Seamlesschex Admin', 
						iconClasses: 'fa fa-home',     
						sref: 'seamlesschex-admins'
					},
					{ 
						name: 'Add New', 
						iconClasses: 'fa fa-home',     
						sref: 'seamlesschex-admin-new'
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
			{ name : 'Multiple Subscriptions', iconClasses: 'fa  fa-user-md', 
					menu : [
					{ 
						name: 'Subscriptions', 
						iconClasses: 'fa fa-home',     
						sref: 'subscriptions'
					},
					{ 
						name: 'Add New Subscription', 
						iconClasses: 'fa fa-home',     
						sref: 'subscription-new'
					}
					] 
			},
			{ name : 'Add Companies', iconClasses: 'fa fa-building-o', 
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
			{ name : 'Search Checks', iconClasses: 'fa fa-search',
					menu : [
							{ 
								name: 'Search Checks',
								iconClasses: '',
								sref: 'view-print-check'
							}
							] 
			},
			{ name : 'Invoices', iconClasses: 'fa fa-file-text-o', 
					menu : [
							{ 
								name: 'Invoices',
								iconClasses: '',
								sref: '#/'
							}
							] 
			},
			{ name : 'Batch', iconClasses: 'fa fa-exchange', 
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
			{ name : 'Settings', iconClasses: 'fa fa-gears', 
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
			{ name : 'Email Templates', iconClasses: 'fa fa-gears', 
					menu : [
							{
								name: 'Email Templates',
								iconClasses: '',
								sref: 'email-template'
							}, 
							] 
			},
			{ name : 'Plans', iconClasses: 'fa fa-gears', 
					menu : [
							{
								name: 'Plans',
								iconClasses: '',
								sref: 'plan-list'
							}, 
							{
								name: 'Add Plan',
								iconClasses: '',
								sref: 'plan-new'
							},
							] 
			},
			{ name : 'Acitivity Logs', iconClasses: 'fa fa-history', 
					menu : [
							{
								name: 'Logs',
								iconClasses: '',
								sref: 'activity-logs'
							}
							]
			},
			{ name : 'Profile', iconClasses: 'fa fa-history', 
					menu : [
							{
								name: 'Profile',
								iconClasses: '',
								sref: 'profile-details'
							}
							]
			}
		  ];
		 
		//seamlesschecx admin menu
		this.adminmenu =  [
		{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', permissionValue:'', 
				menu : [
						{ 
							name: 'Dashboard', 
							iconClasses: 'fa fa-dashboard',     
							sref: 'scxAdminDashboard',
							permissionValue: ''
						}
						] 
		}
		];
		 
		 //company admin menu
		this.companyadminmenu = [
		{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', permissionValue: '', 
				menu : [
						{ 
							name: 'Dashboard', 
							iconClasses: 'fa fa-dashboard',     
							sref: 'companyAdminDashboard',
							permissionValue: ''
						}
						] 
		},
		
		{ name : 'Enter Check', iconClasses: 'fa fa-users', permissionValue: $rootScope.currentUser.permissions.ADDCHECK, 
				menu : [
						{ 
							name: 'Entered Checks',
							iconClasses: '',
							sref: 'enter-check',
							permissionValue: $rootScope.currentUser.permissions.ADDCHECK
						}						
						] 
		},
		{ name : 'View/Print Checks', iconClasses: 'fa fa-search', permissionValue: $rootScope.currentUser.permissions.VIEWCHECK,
				menu : [
						{ 
							name: 'View/Print Checks',
							iconClasses: '',
							sref: 'view-print-check',
							permissionValue: $rootScope.currentUser.permissions.ADDCHECK
						}
						] 
		},
		{ name : 'Create Payment Link', iconClasses: 'fa fa-file-text-o', permissionValue: $rootScope.currentUser.permissions.CHECKOUTLINK,
				menu : [
						{ 
							name: 'Create Payment Link',
							iconClasses: '',
							sref: 'create-payment-link',
							permissionValue: $rootScope.currentUser.permissions.CHECKOUTLINK
						}
						] 
		},
		{ name : 'Create Bank Auth Link', iconClasses: 'fa fa-exchange', permissionValue: $rootScope.currentUser.permissions.BANKAUTHLINK,
				menu : [
						{	name: 'Create Bank Auth Link',
							iconClasses: '',
							sref: 'create-bank-auth-link',
							permissionValue: $rootScope.currentUser.permissions.BANKAUTHLINK
						}
						] 
		},
		{ name : 'Companies', iconClasses: 'fa fa-gears', permissionValue: $rootScope.currentUser.permissions.COMPANY,
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

		//company user menu
		this.companyusermenu =  [
		{ name : 'Dashboard', iconClasses: 'fa fa-dashboard', permissionValue: $rootScope.currentUser.permissions.DASHBOARD, 
				menu : [
						{ 
							name: 'Dashboard', 
							iconClasses: 'fa fa-dashboard',     
							sref: 'companyAdminDashboard',
							permissionValue: ''
						}
						] 
		},
		
		{ name : 'Enter Check', iconClasses: 'fa fa-users', permissionValue: $rootScope.currentUser.permissions.ADDCHECK, 
				menu : [
						{ 
							name: 'Entered Checks',
							iconClasses: '',
							sref: 'enter-check',
							permissionValue: $rootScope.currentUser.permissions.ADDCHECK
						}						
						] 
		},
		{ name : 'View/Print Checks', iconClasses: 'fa fa-search', permissionValue: $rootScope.currentUser.permissions.VIEWCHECK,
				menu : [
						{ 
							name: 'View/Print Checks',
							iconClasses: '',
							sref: 'view-print-check',
							permissionValue: $rootScope.currentUser.permissions.ADDCHECK
						}
						] 
		},
		{ name : 'Create Payment Link', iconClasses: 'fa fa-file-text-o', permissionValue: $rootScope.currentUser.permissions.CHECKOUTLINK,
				menu : [
						{ 
							name: 'Create Payment Link',
							iconClasses: '',
							sref: 'create-payment-link',
							permissionValue: $rootScope.currentUser.permissions.CHECKOUTLINK
						}
						] 
		},
		{ name : 'Create Bank Auth Link', iconClasses: 'fa fa-exchange', permissionValue: $rootScope.currentUser.permissions.BANKAUTHLINK,
				menu : [
						{	name: 'Create Bank Auth Link',
							iconClasses: '',
							sref: 'create-bank-auth-link',
							permissionValue: $rootScope.currentUser.permissions.BANKAUTHLINK
						}
						] 
		},
		{ name : 'Companies', iconClasses: 'fa fa-gears', permissionValue: $rootScope.currentUser.permissions.COMPANY,
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
