// Destinations Service for GoTrip AngularJS Application
angular.module('gotripApp')
  .service('DestinationService', ['$http', function($http) {
    // Service methods
    return {
      // Get all destinations
      getDestinations: function() {
        return $http.get('/api/destinations')
          .then(function(response) {
            return response.data.destinations;
          });
      },
      
      // Get a specific destination by ID
      getDestination: function(id) {
        return $http.get('/api/destinations/' + id)
          .then(function(response) {
            return response.data.destination;
          });
      },
      
      // Get all destination prices with dynamic pricing
      getPrices: function() {
        return $http.get('/api/destinations/prices/all')
          .then(function(response) {
            return response.data.prices;
          });
      },
      
      // Get price for a specific destination
      getPrice: function(destinationName) {
        return this.getPrices()
          .then(function(prices) {
            return prices.find(function(price) {
              return price.name.toLowerCase() === destinationName.toLowerCase();
            }) || null;
          });
      },
      
      // Get featured destinations (for homepage)
      getFeaturedDestinations: function() {
        return this.getDestinations()
          .then(function(destinations) {
            // Select top destinations (you could implement different logic here)
            return destinations.slice(0, 6);
          });
      },
      
      // Search destinations by name, country, or description
      searchDestinations: function(query) {
        if (!query || query.trim() === '') {
          return this.getDestinations();
        }
        
        query = query.toLowerCase().trim();
        
        return this.getDestinations()
          .then(function(destinations) {
            return destinations.filter(function(destination) {
              return destination.name.toLowerCase().includes(query) ||
                    destination.country.toLowerCase().includes(query) ||
                    (destination.description && destination.description.toLowerCase().includes(query));
            });
          });
      },
      
      // Get destinations by region/country
      getDestinationsByRegion: function(region) {
        if (!region || region.trim() === '') {
          return this.getDestinations();
        }
        
        region = region.toLowerCase().trim();
        
        return this.getDestinations()
          .then(function(destinations) {
            return destinations.filter(function(destination) {
              return destination.country.toLowerCase().includes(region);
            });
          });
      },
      
      // Group destinations by region/continent
      groupDestinationsByRegion: function() {
        return this.getDestinations()
          .then(function(destinations) {
            var regions = {};
            
            destinations.forEach(function(destination) {
              var region = getRegionForCountry(destination.country);
              
              if (!regions[region]) {
                regions[region] = [];
              }
              
              regions[region].push(destination);
            });
            
            return regions;
          });
        
        // Helper function to determine region for a country
        function getRegionForCountry(country) {
          var country = country.toLowerCase();
          
          if (country.includes('united kingdom') || country.includes('france') || 
              country.includes('italy') || country.includes('spain') ||
              country.includes('germany') || country.includes('switzerland') ||
              country.includes('monaco')) {
            return 'Europe';
          } else if (country.includes('japan') || country.includes('china') || 
                    country.includes('india') || country.includes('thailand') ||
                    country.includes('singapore') || country.includes('korea')) {
            return 'Asia';
          } else if (country.includes('canada') || country.includes('united states') || 
                    country.includes('mexico')) {
            return 'North America';
          } else if (country.includes('brazil') || country.includes('argentina') || 
                    country.includes('peru') || country.includes('colombia')) {
            return 'South America';
          } else if (country.includes('australia') || country.includes('new zealand')) {
            return 'Oceania';
          } else if (country.includes('egypt') || country.includes('south africa') || 
                    country.includes('morocco') || country.includes('kenya')) {
            return 'Africa';
          } else {
            return 'Other';
          }
        }
      }
    };
  }]); 