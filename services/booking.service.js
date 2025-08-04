// Booking Service for GoTrip AngularJS Application
angular.module('gotripApp')
  .service('BookingService', ['$http', function($http) {
    // Service methods
    return {
      // Get all bookings for the current user
      getBookings: function() {
        return $http.get('/api/bookings')
          .then(function(response) {
            return response.data.bookings;
          });
      },
      
      // Get a specific booking by reference
      getBooking: function(bookingRef) {
        return $http.get('/api/bookings/' + bookingRef)
          .then(function(response) {
            return response.data.booking;
          });
      },
      
      // Create a new booking
      createBooking: function(bookingData) {
        return $http.post('/api/bookings', bookingData)
          .then(function(response) {
            return response.data;
          });
      },
      
      // Cancel a booking
      cancelBooking: function(bookingRef) {
        return $http.put('/api/bookings/' + bookingRef + '/cancel')
          .then(function(response) {
            return response.data;
          });
      },
      
      // Calculate booking price
      calculatePrice: function(destination, travelers, roomType, options) {
        // First try to get dynamic pricing from server
        return $http.get('/api/destinations/prices/all')
          .then(function(response) {
            const prices = response.data.prices;
            
            // Find price for the selected destination
            const destinationPrice = prices.find(price => 
              price.name.toLowerCase() === destination.toLowerCase()
            );
            
            if (destinationPrice) {
              return calculateTotalPrice(destinationPrice.dynamic_price, travelers, roomType, options);
            } else {
              // Fallback to local calculation if destination not found
              return calculatePriceFallback(destination, travelers, roomType, options);
            }
          })
          .catch(function() {
            // Fallback to local calculation if API call fails
            return calculatePriceFallback(destination, travelers, roomType, options);
          });
          
        // Local function to calculate total price with all factors
        function calculateTotalPrice(basePrice, travelers, roomType, options) {
          // Apply room type factors
          let roomFactor = 1.0;
          if (roomType === 'deluxe') {
            roomFactor = 1.25;
          } else if (roomType === 'suite') {
            roomFactor = 1.5;
          } else if (roomType === 'family') {
            roomFactor = 1.3;
          }
          
          // Apply additional options
          let additionalCost = 0;
          if (options) {
            if (options.airportPickup) additionalCost += 2500; // ₹2,500
            if (options.tourGuide) additionalCost += 8000; // ₹8,000
            if (options.insurance) additionalCost += 5000; // ₹5,000
          }
          
          // Apply group discount
          let groupFactor = 1.0;
          if (travelers >= 5) {
            groupFactor = 0.95; // 5% group discount for 5+ travelers
          } else if (travelers >= 3) {
            groupFactor = 0.98; // 2% group discount for 3-4 travelers
          }
          
          // Calculate total price with all factors
          const roomPrice = basePrice * roomFactor;
          const groupPrice = roomPrice * groupFactor;
          const totalPrice = (groupPrice * travelers) + additionalCost;
          
          return {
            basePrice: basePrice,
            roomPrice: roomPrice,
            totalPrice: totalPrice,
            formattedTotalPrice: '₹' + Math.round(totalPrice).toLocaleString('en-IN'),
            factors: {
              room: roomFactor,
              group: groupFactor,
              additionalCost: additionalCost
            }
          };
        }
        
        // Fallback pricing calculation if API is unavailable
        function calculatePriceFallback(destination, travelers, roomType, options) {
          let basePrice = 0;
          
          // Set base price based on destination
          if (destination.toLowerCase().includes('london')) {
            basePrice = 150000; // ₹1,50,000
          } else if (destination.toLowerCase().includes('vancouver') || destination.toLowerCase().includes('canada')) {
            basePrice = 199999; // ₹1,99,999
          } else if (destination.toLowerCase().includes('monaco')) {
            basePrice = 139999; // ₹1,39,999
          } else if (destination.toLowerCase().includes('paris') || destination.toLowerCase().includes('france')) {
            basePrice = 149999; // ₹1,49,999
          } else if (destination.toLowerCase().includes('tokyo') || destination.toLowerCase().includes('japan')) {
            basePrice = 169999; // ₹1,69,999
          } else if (destination.toLowerCase().includes('zurich') || destination.toLowerCase().includes('switzerland')) {
            basePrice = 159999; // ₹1,59,999
          } else {
            basePrice = 100000; // Default price: ₹1,00,000
          }
          
          return calculateTotalPrice(basePrice, travelers, roomType, options);
        }
      }
    };
  }]); 