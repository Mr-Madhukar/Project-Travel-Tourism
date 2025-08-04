// Booking Controller for GoTrip AngularJS Application
angular.module('gotripApp')
  .controller('BookingController', ['$scope', '$location', '$window', 'BookingService', 'DestinationService', 'AuthService', 
    function($scope, $location, $window, BookingService, DestinationService, AuthService) {
      // Controller variables
      $scope.booking = {
        fullName: '',
        email: '',
        phone: '',
        destination: '',
        travelDate: '',
        travelers: 1,
        roomType: 'standard',
        airportPickup: false,
        tourGuide: false,
        insurance: false,
        specialRequests: ''
      };
      $scope.destinations = [];
      $scope.prices = {};
      $scope.isLoading = true;
      $scope.bookingError = null;
      $scope.currentUser = AuthService.getCurrentUser();
      
      // Initialize traveler name and email from current user if available
      if ($scope.currentUser) {
        $scope.booking.fullName = $scope.currentUser.fullName || '';
        $scope.booking.email = $scope.currentUser.email || '';
      }
      
      // Initialize booking form
      function initBookingForm() {
        $scope.isLoading = true;
        
        // Get today's date in YYYY-MM-DD format for min date
        var today = new Date();
        var year = today.getFullYear();
        var month = (today.getMonth() + 1).toString().padStart(2, '0');
        var day = today.getDate().toString().padStart(2, '0');
        $scope.minDate = year + '-' + month + '-' + day;
        
        // Load destinations
        DestinationService.getDestinations()
          .then(function(destinations) {
            $scope.destinations = destinations;
            
            // Check if there's a destination in query params
            var urlParams = new URLSearchParams($window.location.search);
            var destParam = urlParams.get('destination');
            var dateParam = urlParams.get('date');
            
            if (destParam) {
              // Find matching destination
              var matchingDest = destinations.find(function(dest) {
                return dest.name.toLowerCase().includes(destParam.toLowerCase());
              });
              
              if (matchingDest) {
                $scope.booking.destination = matchingDest.name + ', ' + matchingDest.country;
                
                // Update summary image if available
                if (matchingDest.image_url) {
                  $scope.summaryImage = matchingDest.image_url;
                }
              }
            }
            
            // Set travel date if provided
            if (dateParam) {
              $scope.booking.travelDate = dateParam;
              
              // Calculate checkout date (4-5 days later depending on destination)
              var checkInDate = new Date(dateParam);
              var checkOutDate = new Date(checkInDate);
              
              // Determine the duration based on destination
              let duration = 4; // Default duration
              if (destParam) {
                if (destParam.toLowerCase().includes('canada') || 
                    destParam.toLowerCase().includes('tokyo') || 
                    destParam.toLowerCase().includes('switzerland')) {
                  duration = 5;
                }
              }
              
              checkOutDate.setDate(checkInDate.getDate() + duration);
              $scope.duration = duration;
              
              // Set checkout date in model if needed
              // $scope.booking.checkOutDate = ...
            }
            
            // Update price
            updatePrice();
          })
          .catch(function(error) {
            console.error('Error loading destinations:', error);
          })
          .finally(function() {
            $scope.isLoading = false;
          });
      }
      
      // Update booking price
      function updatePrice() {
        if (!$scope.booking.destination) {
          $scope.prices = {
            totalPrice: 0,
            formattedTotalPrice: '₹0'
          };
          return;
        }
        
        var destination = $scope.booking.destination.split(',')[0].trim();
        var travelers = $scope.booking.travelers || 1;
        var roomType = $scope.booking.roomType || 'standard';
        var options = {
          airportPickup: $scope.booking.airportPickup,
          tourGuide: $scope.booking.tourGuide,
          insurance: $scope.booking.insurance
        };
        
        BookingService.calculatePrice(destination, travelers, roomType, options)
          .then(function(prices) {
            $scope.prices = prices;
          })
          .catch(function(error) {
            console.error('Error calculating price:', error);
          });
      }
      
      // Watch for form changes to update price
      $scope.$watch('booking.destination', updatePrice);
      $scope.$watch('booking.travelers', updatePrice);
      $scope.$watch('booking.roomType', updatePrice);
      $scope.$watch('booking.airportPickup', updatePrice);
      $scope.$watch('booking.tourGuide', updatePrice);
      $scope.$watch('booking.insurance', updatePrice);
      
      // Submit booking
      $scope.submitBooking = function() {
        $scope.isLoading = true;
        $scope.bookingError = null;
        
        // Add totalAmount to booking data
        var bookingData = Object.assign({}, $scope.booking, {
          totalAmount: $scope.prices.totalPrice
        });
        
        BookingService.createBooking(bookingData)
          .then(function(response) {
            // Redirect to booking confirmation page or my bookings
            $location.path('/my-bookings');
          })
          .catch(function(error) {
            $scope.bookingError = error.data && error.data.message ? 
              error.data.message : 
              'An error occurred while processing your booking';
            console.error('Booking error:', error);
          })
          .finally(function() {
            $scope.isLoading = false;
          });
      };
      
      // Initialize the form
      initBookingForm();
    }
  ]); 