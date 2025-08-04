// Authentication Service for GoTrip AngularJS Application
angular.module('gotripApp')
  .service('AuthService', ['$http', '$q', '$localStorage', '$location', function($http, $q, $localStorage, $location) {
    // Service variables
    var currentUser = null;
    
    // Initialize authentication state from localStorage
    if ($localStorage.gotripToken) {
      // Set Authorization header for all requests if token exists
      $http.defaults.headers.common.Authorization = 'Bearer ' + $localStorage.gotripToken;
    }
    
    // Service methods
    return {
      // Register a new user
      register: function(userData) {
        return $http.post('/api/auth/register', userData)
          .then(function(response) {
            // Store JWT token in localStorage
            $localStorage.gotripToken = response.data.token;
            $localStorage.gotripUser = response.data.user;
            
            // Set Authorization header for all requests
            $http.defaults.headers.common.Authorization = 'Bearer ' + response.data.token;
            
            // Set current user
            currentUser = response.data.user;
            
            return response.data;
          });
      },
      
      // Login a user
      login: function(credentials) {
        return $http.post('/api/auth/login', credentials)
          .then(function(response) {
            // Store JWT token in localStorage
            $localStorage.gotripToken = response.data.token;
            $localStorage.gotripUser = response.data.user;
            
            // Set Authorization header for all requests
            $http.defaults.headers.common.Authorization = 'Bearer ' + response.data.token;
            
            // Set current user
            currentUser = response.data.user;
            
            return response.data;
          });
      },
      
      // Logout the current user
      logout: function() {
        // Remove token from localStorage
        delete $localStorage.gotripToken;
        delete $localStorage.gotripUser;
        
        // Remove Authorization header
        delete $http.defaults.headers.common.Authorization;
        
        // Clear current user
        currentUser = null;
        
        // Redirect to login page
        $location.path('/login');
        
        // Send logout request to server (for logging purposes only)
        return $http.post('/api/auth/logout');
      },
      
      // Check if user is authenticated
      isAuthenticated: function() {
        return !!$localStorage.gotripToken;
      },
      
      // Get current user
      getCurrentUser: function() {
        return currentUser || $localStorage.gotripUser;
      },
      
      // Check if token is valid
      checkAuth: function() {
        if (this.isAuthenticated()) {
          return $http.get('/api/auth/check')
            .then(function(response) {
              // Token is valid
              currentUser = response.data.user;
              return true;
            })
            .catch(function(error) {
              // Token is invalid or expired
              console.error('Authentication error:', error);
              this.logout();
              return false;
            }.bind(this));
        }
        return $q.resolve(false);
      },
      
      // Require authentication for route resolvers
      requireAuth: function() {
        return this.checkAuth()
          .then(function(isAuthenticated) {
            if (!isAuthenticated) {
              return $q.reject('AUTH_REQUIRED');
            }
            return true;
          });
      }
    };
  }]); 