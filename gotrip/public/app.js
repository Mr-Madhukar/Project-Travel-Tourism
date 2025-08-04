// Angular.js application
angular.module('gotripApp', ['ngRoute', 'ngAnimate', 'ngStorage'])
  .config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    // Configure routes
    $routeProvider
      .when('/', {
        templateUrl: 'views/home.html',
        controller: 'HomeController'
      })
      .when('/login', {
        templateUrl: 'views/login.html',
        controller: 'AuthController'
      })
      .when('/register', {
        templateUrl: 'views/register.html',
        controller: 'AuthController'
      })
      .when('/book', {
        templateUrl: 'views/book.html',
        controller: 'BookingController',
        resolve: {
          auth: ['AuthService', function(AuthService) {
            return AuthService.requireAuth();
          }]
        }
      })
      .when('/my-bookings', {
        templateUrl: 'views/my-bookings.html',
        controller: 'BookingsController',
        resolve: {
          auth: ['AuthService', function(AuthService) {
            return AuthService.requireAuth();
          }]
        }
      })
      .when('/destinations', {
        templateUrl: 'views/destinations.html',
        controller: 'DestinationsController'
      })
      .when('/profile', {
        templateUrl: 'views/profile.html',
        controller: 'ProfileController',
        resolve: {
          auth: ['AuthService', function(AuthService) {
            return AuthService.requireAuth();
          }]
        }
      })
      .when('/about', {
        templateUrl: 'views/about.html'
      })
      .when('/contact', {
        templateUrl: 'views/contact.html',
        controller: 'ContactController'
      })
      .otherwise({
        redirectTo: '/'
      });
      
    // Use HTML5 History API
    $locationProvider.html5Mode(true);
  }])
  
  // Run block to handle authentication state on app startup
  .run(['$rootScope', '$location', 'AuthService', function($rootScope, $location, AuthService) {
    // Check authentication on app startup
    AuthService.checkAuth();
    
    // Listen for route changes to handle authentication
    $rootScope.$on('$routeChangeStart', function(event, next, current) {
      if (next.resolve) {
        $rootScope.isLoading = true;
      }
    });
    
    $rootScope.$on('$routeChangeSuccess', function() {
      $rootScope.isLoading = false;
    });
    
    $rootScope.$on('$routeChangeError', function(event, next, previous, error) {
      $rootScope.isLoading = false;
      if (error === 'AUTH_REQUIRED') {
        $location.path('/login');
      }
    });
  }]); 