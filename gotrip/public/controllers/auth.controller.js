// Authentication Controller for GoTrip AngularJS Application
angular.module('gotripApp')
  .controller('AuthController', ['$scope', '$location', 'AuthService', function($scope, $location, AuthService) {
    // Controller variables
    $scope.user = {};
    $scope.loginError = null;
    $scope.registerError = null;
    $scope.isLoading = false;
    
    // Check if user is already authenticated
    if (AuthService.isAuthenticated()) {
      $location.path('/');
    }
    
    // Login function
    $scope.login = function() {
      $scope.isLoading = true;
      $scope.loginError = null;
      
      AuthService.login($scope.user)
        .then(function(response) {
          // Redirect to home page after successful login
          $location.path('/');
        })
        .catch(function(error) {
          $scope.loginError = error.data && error.data.message ? 
            error.data.message : 
            'An error occurred during login';
        })
        .finally(function() {
          $scope.isLoading = false;
        });
    };
    
    // Register function
    $scope.register = function() {
      $scope.isLoading = true;
      $scope.registerError = null;
      
      AuthService.register($scope.user)
        .then(function(response) {
          // Redirect to home page after successful registration
          $location.path('/');
        })
        .catch(function(error) {
          $scope.registerError = error.data && error.data.message ? 
            error.data.message : 
            'An error occurred during registration';
        })
        .finally(function() {
          $scope.isLoading = false;
        });
    };
  }]); 