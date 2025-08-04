// Main controller for GoTrip AngularJS Application
angular.module('gotripApp')
  .controller('MainController', ['$scope', '$rootScope', '$location', 'AuthService', function($scope, $rootScope, $location, AuthService) {
    // Controller variables
    $scope.menuOpen = false;
    $scope.currentYear = new Date().getFullYear();
    
    // Authentication check
    $scope.isAuthenticated = AuthService.isAuthenticated;
    $scope.currentUser = AuthService.getCurrentUser();
    
    // Toggle mobile menu
    $scope.toggleMenu = function() {
      $scope.menuOpen = !$scope.menuOpen;
    };
    
    // Close menu on navigation
    $rootScope.$on('$routeChangeSuccess', function() {
      $scope.menuOpen = false;
    });
    
    // Logout function
    $scope.logout = function() {
      AuthService.logout();
    };
    
    // Listen for authentication changes
    $rootScope.$on('auth:login', function(event, user) {
      $scope.currentUser = user;
    });
    
    $rootScope.$on('auth:logout', function() {
      $scope.currentUser = null;
    });
  }]); 