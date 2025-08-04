// Trip Planner JavaScript - Enhanced Version

// Global variables to store planner data
let destinations = [];
let selectedDestination = null;
let selectedAccommodation = null;
let selectedTransportation = null;
let selectedAttractions = [];
let tripDays = 0;
let dailyItinerary = {}; // Stores activities for each day
let isLoggedIn = false;

// Initialize the planner
document.addEventListener('DOMContentLoaded', function() {
    initializePlanner();
    
    // Initialize AOS animations if not already initialized
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
    
    // Add scroll animation for "Start Planning" button
    const startPlanningBtn = document.querySelector('.header-buttons .btn-primary');
    if (startPlanningBtn) {
        startPlanningBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const destinationSection = document.getElementById('destination-section');
            if (destinationSection) {
                destinationSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
    
    // Add smooth scrolling for all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && document.querySelector(targetId)) {
                e.preventDefault();
                document.querySelector(targetId).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Check if user is logged in and initialize the planner accordingly
function initializePlanner() {
    // Check login status from localStorage
    isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    
    // Show/hide appropriate nav items
    const loginItem = document.querySelector('.login-item');
    const bookingItem = document.querySelector('.booking-item');
    const logoutItem = document.querySelector('.logout-item');
    
    if (isLoggedIn) {
        loginItem.style.display = 'none';
        bookingItem.style.display = 'block';
        logoutItem.style.display = 'block';
        
        // Show the planner interface
        document.getElementById('auth-required').style.display = 'none';
        document.getElementById('planner-interface').style.display = 'block';
        
        // Load saved itineraries
        loadSavedItineraries();
    } else {
        loginItem.style.display = 'block';
        bookingItem.style.display = 'none';
        logoutItem.style.display = 'none';
        
        // Show the auth required message
        document.getElementById('auth-required').style.display = 'block';
        document.getElementById('planner-interface').style.display = 'none';
        document.getElementById('saved-itineraries-section').style.display = 'none';
    }
    
    // Add logout handler
    document.getElementById('logoutLink').addEventListener('click', function(e) {
        e.preventDefault();
        logout();
    });
    
    // Load destinations
    loadDestinations();
    
    // Setup event listeners
    setupEventListeners();
}

// Set up all event listeners
function setupEventListeners() {
    // Destination select
    const destinationSelect = document.getElementById('destination-select');
    destinationSelect.addEventListener('change', function() {
        const destinationId = parseInt(this.value);
        if (destinationId) {
            loadDestinationDetails(destinationId);
        } else {
            document.querySelector('.destination-info').innerHTML = '';
            resetAccommodationsTransportAttractions();
        }
    });
    
    // Date inputs
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    startDateInput.addEventListener('change', updateTripDuration);
    endDateInput.addEventListener('change', updateTripDuration);
    
    // Set minimum date to today
    const today = new Date();
    const formattedDate = today.toISOString().split('T')[0];
    startDateInput.min = formattedDate;
    endDateInput.min = formattedDate;
    
    // Save button
    document.getElementById('save-btn').addEventListener('click', saveItinerary);
    
    // Book button
    document.getElementById('book-btn').addEventListener('click', bookItinerary);
}

// Load all destinations from the API
function loadDestinations() {
    try {
        fetch('../../get_trip_planner_data.php?action=destinations')
            .then(response => response.json())
            .then(data => {
                destinations = data;
                populateDestinationDropdown(data);
            })
            .catch(error => {
                console.error('Error loading destinations from API:', error);
                
                // Fallback: Add static destinations if API call fails
                const staticDestinations = [
                    { id: 1, name: "Paris", country: "France", price: 149999.00, image_url: "/gotrip/public/image/paris.jpg", description: "The City of Light with iconic landmarks like the Eiffel Tower and Louvre Museum." },
                    { id: 2, name: "Tokyo", country: "Japan", price: 169999.00, image_url: "/gotrip/public/image/tokyo.jpg", description: "A bustling metropolis blending ultramodern and traditional elements." },
                    { id: 3, name: "Bern", country: "Switzerland", price: 159999.00, image_url: "/gotrip/public/image/switzerland.png", description: "Beautiful alpine landscapes with pristine lakes and mountains." },
                    { id: 4, name: "Vancouver", country: "Canada", price: 199999.00, image_url: "/gotrip/public/image/canada.png", description: "Stunning natural beauty with vast forests and breathtaking mountain ranges." },
                    { id: 5, name: "Seoul", country: "South Korea", price: 149999.00, image_url: "/gotrip/public/image/korea.png", description: "A fascinating blend of ancient traditions and cutting-edge technology." },
                    { id: 6, name: "Monaco", country: "Monaco", price: 139999.00, image_url: "/gotrip/public/image/monaco.jpg", description: "A luxurious microstate on the Mediterranean known for its casinos and yacht-lined harbor." }
                ];
                
                destinations = staticDestinations;
                populateDestinationDropdown(staticDestinations);
            });
    } catch (e) {
        console.error('Exception in loadDestinations:', e);
        // Ensure we have static destinations even if fetch fails completely
        const staticDestinations = [
            { id: 1, name: "Paris", country: "France", price: 149999.00, image_url: "/gotrip/public/image/paris.jpg", description: "The City of Light with iconic landmarks like the Eiffel Tower and Louvre Museum." },
            { id: 2, name: "Tokyo", country: "Japan", price: 169999.00, image_url: "/gotrip/public/image/tokyo.jpg", description: "A bustling metropolis blending ultramodern and traditional elements." },
            { id: 3, name: "Bern", country: "Switzerland", price: 159999.00, image_url: "/gotrip/public/image/switzerland.png", description: "Beautiful alpine landscapes with pristine lakes and mountains." },
            { id: 4, name: "Vancouver", country: "Canada", price: 199999.00, image_url: "/gotrip/public/image/canada.png", description: "Stunning natural beauty with vast forests and breathtaking mountain ranges." },
            { id: 5, name: "Seoul", country: "South Korea", price: 149999.00, image_url: "/gotrip/public/image/korea.png", description: "A fascinating blend of ancient traditions and cutting-edge technology." },
            { id: 6, name: "Monaco", country: "Monaco", price: 139999.00, image_url: "/gotrip/public/image/monaco.jpg", description: "A luxurious microstate on the Mediterranean known for its casinos and yacht-lined harbor." }
        ];
        
        destinations = staticDestinations;
        populateDestinationDropdown(staticDestinations);
    }
}

// Helper function to populate the destination dropdown
function populateDestinationDropdown(destinationsData) {
    const destinationSelect = document.getElementById('destination-select');
    
    // Clear existing options except the default one
    while (destinationSelect.options.length > 1) {
        destinationSelect.remove(1);
    }
    
    // Add destinations to select
    destinationsData.forEach(dest => {
        const option = document.createElement('option');
        option.value = dest.id;
        option.textContent = `${dest.name}, ${dest.country}`;
        destinationSelect.appendChild(option);
    });
}

// Load details for a selected destination
function loadDestinationDetails(destinationId) {
    selectedDestination = destinations.find(dest => dest.id === destinationId);
    
    if (selectedDestination) {
        // Create proper image path with fallback
        let imageSrc = '/gotrip/public/image/default-destination.jpg'; // Default fallback
        
        if (selectedDestination.image_url) {
            if (selectedDestination.image_url.startsWith('http')) {
                // If it's already an absolute URL, use it as is
                imageSrc = selectedDestination.image_url;
            } else {
                // If it's a relative path, convert to absolute
                // Remove leading '../' if present
                const cleanPath = selectedDestination.image_url.replace(/^\.\.\//, '');
                imageSrc = `/gotrip/public/${cleanPath}`;
            }
        } else {
            // Try to find a matching image by destination name
            const destName = selectedDestination.name.toLowerCase();
            if (destName === 'paris') {
                imageSrc = '/gotrip/public/image/paris.jpg';
            } else if (destName === 'tokyo') {
                imageSrc = '/gotrip/public/image/tokyo.jpg';
            } else if (destName === 'bern' || destName === 'switzerland') {
                imageSrc = '/gotrip/public/image/switzerland.png';
            } else if (destName === 'vancouver' || destName === 'canada') {
                imageSrc = '/gotrip/public/image/canada.png';
            } else if (destName === 'seoul' || destName === 'korea') {
                imageSrc = '/gotrip/public/image/korea.png';
            } else if (destName === 'monaco') {
                imageSrc = '/gotrip/public/image/monaco.jpg';
            }
        }
        
        // Show destination details
        const destinationInfo = document.querySelector('.destination-info');
        destinationInfo.innerHTML = `
            <div class="destination-card" data-aos="fade-up">
                <div class="destination-image">
                    <img src="${imageSrc}" alt="${selectedDestination.name}" onerror="this.src='/gotrip/public/image/default-destination.jpg'">
                </div>
                <div class="destination-details">
                    <h3>${selectedDestination.name}, ${selectedDestination.country}</h3>
                    <p>${selectedDestination.description}</p>
                    <p class="destination-price">Base price: ₹ ${parseFloat(selectedDestination.price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                </div>
            </div>
        `;
        
        // Refresh AOS animations
        if (typeof AOS !== 'undefined') {
            setTimeout(() => {
                AOS.refresh();
            }, 500);
        }
        
        // Load accommodations, transportation, and attractions for this destination
        loadAccommodations(destinationId);
        loadTransportation(destinationId);
        loadAttractions(destinationId);
        
        // Scroll to date section after a short delay
        setTimeout(() => {
            document.getElementById('date-section').scrollIntoView({ behavior: 'smooth' });
        }, 800);
    }
}

// Reset accommodations, transportation, and attractions when destination changes
function resetAccommodationsTransportAttractions() {
    document.getElementById('accommodations-list').innerHTML = '';
    document.getElementById('transportation-list').innerHTML = '';
    document.getElementById('attractions-list').innerHTML = '';
    
    document.getElementById('accommodation-loading').style.display = 'none';
    document.getElementById('transportation-loading').style.display = 'none';
    document.getElementById('attractions-loading').style.display = 'none';
    
    selectedAccommodation = null;
    selectedTransportation = null;
    selectedAttractions = [];
    dailyItinerary = {};
    
    updateCostSummary();
}

// Calculate and update trip duration when dates change
function updateTripDuration() {
    const startDate = new Date(document.getElementById('start-date').value);
    const endDate = new Date(document.getElementById('end-date').value);
    
    // Reset if invalid dates
    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        document.getElementById('duration-display').textContent = 'Trip duration: 0 days';
        tripDays = 0;
        resetItineraryBuilder();
        return;
    }
    
    // Ensure end date is not before start date
    if (endDate < startDate) {
        document.getElementById('end-date').value = document.getElementById('start-date').value;
        updateTripDuration();
        return;
    }
    
    // Calculate duration in days (inclusive of start and end date)
    const durationMs = endDate - startDate;
    const durationDays = Math.ceil(durationMs / (1000 * 60 * 60 * 24)) + 1;
    
    document.getElementById('duration-display').textContent = `Trip duration: ${durationDays} days`;
    tripDays = durationDays;
    
    // Update itinerary builder with day tabs
    updateItineraryBuilder();
    
    // Update cost summary for accommodation based on days
    updateCostSummary();
    
    // Scroll to accommodation section after a short delay
    setTimeout(() => {
        document.getElementById('accommodation-section').scrollIntoView({ behavior: 'smooth' });
    }, 600);
}

// Load accommodations for a selected destination
function loadAccommodations(destinationId) {
    document.getElementById('accommodation-loading').style.display = 'block';
    document.getElementById('accommodations-list').innerHTML = '';
    
    fetch(`../../get_trip_planner_data.php?action=accommodations&destination_id=${destinationId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('accommodation-loading').style.display = 'none';
            
            if (data.length === 0) {
                document.getElementById('accommodations-list').innerHTML = '<p class="no-data">No accommodations found for this destination.</p>';
                return;
            }
            
            renderAccommodations(data);
        })
        .catch(error => {
            console.error('Error loading accommodations:', error);
            document.getElementById('accommodation-loading').style.display = 'none';
            
            // Fallback: Add static accommodations if API call fails
            const staticAccommodations = [
                { id: 1, destination_id: destinationId, name: "Luxury Hotel", description: "5-star luxury hotel with all amenities", image_url: "/gotrip/public/image/accommodations/le-meurice.jpg", price_per_night: 15000.00, rating: 4.9, amenities: ["WiFi", "Pool", "Spa", "Restaurant"] },
                { id: 2, destination_id: destinationId, name: "Boutique Hotel", description: "Charming boutique hotel with personalized service", image_url: "/gotrip/public/image/accommodations/plaza-athenee.jpg", price_per_night: 8000.00, rating: 4.7, amenities: ["WiFi", "Breakfast", "Bar"] },
                { id: 3, destination_id: destinationId, name: "Budget Inn", description: "Affordable lodging for budget travelers", image_url: "/gotrip/public/image/accommodations/hotel-crillon.jpg", price_per_night: 3000.00, rating: 4.2, amenities: ["WiFi", "TV"] }
            ];
            
            renderAccommodations(staticAccommodations);
        });
}

// Load transportation options for a selected destination
function loadTransportation(destinationId) {
    document.getElementById('transportation-loading').style.display = 'block';
    document.getElementById('transportation-list').innerHTML = '';
    
    fetch(`../../get_trip_planner_data.php?action=transportation&destination_id=${destinationId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('transportation-loading').style.display = 'none';
            
            if (data.length === 0) {
                document.getElementById('transportation-list').innerHTML = '<p class="no-data">No transportation options found for this destination.</p>';
                return;
            }
            
            renderTransportation(data);
        })
        .catch(error => {
            console.error('Error loading transportation:', error);
            document.getElementById('transportation-loading').style.display = 'none';
            
            // Fallback: Add static transportation options if API call fails
            const staticTransportation = [
                { id: 1, destination_id: destinationId, type: "Taxi", description: "Convenient door-to-door service", price: 5000.00 },
                { id: 2, destination_id: destinationId, type: "Public Transit", description: "Affordable option to get around the city", price: 1500.00 },
                { id: 3, destination_id: destinationId, type: "Rental Car", description: "Freedom to explore at your own pace", price: 7500.00 }
            ];
            
            renderTransportation(staticTransportation);
        });
}

// Load attractions for a selected destination
function loadAttractions(destinationId) {
    document.getElementById('attractions-loading').style.display = 'block';
    document.getElementById('attractions-list').innerHTML = '';
    
    fetch(`../../get_trip_planner_data.php?action=attractions&destination_id=${destinationId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('attractions-loading').style.display = 'none';
            
            if (data.length === 0) {
                document.getElementById('attractions-list').innerHTML = '<p class="no-data">No attractions found for this destination.</p>';
                return;
            }
            
            renderAttractions(data);
        })
        .catch(error => {
            console.error('Error loading attractions:', error);
            document.getElementById('attractions-loading').style.display = 'none';
            
            // Fallback: Add static attractions if API call fails
            const staticAttractions = [
                { id: 1, destination_id: destinationId, name: "Famous Landmark", description: "Iconic landmark and must-see attraction", image_url: "/gotrip/public/image/attractions/eiffel-tower.jpg", price: 2000.00, duration: 120, category: "landmark" },
                { id: 2, destination_id: destinationId, name: "Historic Museum", description: "Rich history and cultural artifacts", image_url: "/gotrip/public/image/attractions/louvre.jpg", price: 1500.00, duration: 180, category: "museum" },
                { id: 3, destination_id: destinationId, name: "City Park", description: "Beautiful green space for relaxation", image_url: "/gotrip/public/image/attractions/notre-dame.jpg", price: 0.00, duration: 90, category: "park" },
                { id: 4, destination_id: destinationId, name: "Local Market", description: "Experience local culture and cuisine", image_url: "/gotrip/public/image/attractions/arc-de-triomphe.jpg", price: 500.00, duration: 120, category: "shopping" }
            ];
            
            renderAttractions(staticAttractions);
        });
}

// Render accommodations with enhanced UI
function renderAccommodations(accommodations) {
    const accommodationsList = document.getElementById('accommodations-list');
    accommodationsList.innerHTML = '';
    
    accommodations.forEach((accommodation, index) => {
        const card = document.createElement('div');
        card.className = 'accommodation-card';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        // Format price as Indian currency
        const pricePerNight = parseFloat(accommodation.price_per_night).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        });
        
        // Get amenities as array or empty array if not available
        const amenities = accommodation.amenities || [];
        
        // Create HTML for amenities
        let amenitiesHTML = '';
        if (amenities.length > 0) {
            amenitiesHTML = '<div class="accommodation-amenities">';
            amenities.forEach(amenity => {
                const icon = getAmenityIcon(amenity);
                amenitiesHTML += `<span class="amenity"><i class="${icon}"></i> ${amenity}</span>`;
            });
            amenitiesHTML += '</div>';
        }
        
        // Ensure we have a proper image path
        let imageSrc = '/gotrip/public/image/default-destination.jpg'; // Default fallback
        
        if (accommodation.image_url) {
            // Use the provided image URL if it exists
            imageSrc = accommodation.image_url;
        } else {
            // Try to use a specific image based on hotel name
            const hotelName = accommodation.name.toLowerCase().replace(/\s+/g, '-');
            // Check if we can find a matching image in accommodations folder
            const potentialImagePath = `/gotrip/public/image/accommodations/${hotelName}.jpg`;
            
            // For now, use one of the existing images based on index to ensure something loads
            const fallbackImages = [
                '/gotrip/public/image/accommodations/hotel-crillon.jpg',
                '/gotrip/public/image/accommodations/le-meurice.jpg',
                '/gotrip/public/image/accommodations/plaza-athenee.jpg'
            ];
            
            imageSrc = fallbackImages[index % fallbackImages.length];
        }
        
        card.innerHTML = `
            <div class="accommodation-image">
                <img src="${imageSrc}" 
                     alt="${accommodation.name}"
                     onerror="this.src='/gotrip/public/image/default-destination.jpg'">
            </div>
            <div class="accommodation-details">
                <h3>${accommodation.name}</h3>
                <div class="accommodation-rating">
                    ${getRatingStars(accommodation.rating)}
                </div>
                ${amenitiesHTML}
                <div class="accommodation-price">₹ ${pricePerNight} / night</div>
                <button class="select-btn" data-id="${accommodation.id}">Select</button>
            </div>
        `;
        
        accommodationsList.appendChild(card);
        
        // Add event listener for the select button
        card.querySelector('.select-btn').addEventListener('click', function() {
            selectAccommodation(accommodation);
        });
    });
    
    // Refresh AOS animations
    if (typeof AOS !== 'undefined') {
        setTimeout(() => {
            AOS.refresh();
        }, 500);
    }
}

// Get appropriate icon for amenity
function getAmenityIcon(amenity) {
    const amenityIcons = {
        'Free WiFi': 'fas fa-wifi',
        'Pool': 'fas fa-swimming-pool',
        'Gym': 'fas fa-dumbbell',
        'Restaurant': 'fas fa-utensils',
        'Bar': 'fas fa-glass-cheers',
        'Spa': 'fas fa-spa',
        'Room Service': 'fas fa-concierge-bell',
        'Parking': 'fas fa-parking',
        'Air Conditioning': 'fas fa-wind',
        'Breakfast': 'fas fa-coffee',
        'Laundry': 'fas fa-tshirt',
        'Mini Bar': 'fas fa-cocktail'
    };
    
    return amenityIcons[amenity] || 'fas fa-check';
}

// Render transportation options with enhanced UI
function renderTransportation(transportationOptions) {
    const transportationList = document.getElementById('transportation-list');
    transportationList.innerHTML = '';
    
    transportationOptions.forEach((transport, index) => {
        const card = document.createElement('div');
        card.className = 'transportation-card';
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        // Format price as Indian currency
        const pricePerDay = parseFloat(transport.price_per_day).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        });
        
        card.innerHTML = `
            <div class="transportation-icon">
                <i class="${getTransportIcon(transport.type)}"></i>
            </div>
            <div class="transportation-details">
                <h3>${transport.name}</h3>
                <p>${transport.description}</p>
                <div class="transportation-price">₹ ${pricePerDay} / day</div>
                <button class="select-btn" data-id="${transport.id}">Select</button>
            </div>
        `;
        
        transportationList.appendChild(card);
        
        // Add event listener for the select button
        card.querySelector('.select-btn').addEventListener('click', function() {
            selectTransportation(transport);
        });
    });
    
    // Refresh AOS animations
    if (typeof AOS !== 'undefined') {
        setTimeout(() => {
            AOS.refresh();
        }, 500);
    }
}

// Render attractions with enhanced UI
function renderAttractions(attractions) {
    const attractionsList = document.getElementById('attractions-list');
    attractionsList.innerHTML = '';
    
    attractions.forEach((attraction, index) => {
        const isSelected = selectedAttractions.some(a => a.id === attraction.id);
        const card = document.createElement('div');
        card.className = `attraction-card ${isSelected ? 'selected' : ''}`;
        card.setAttribute('data-aos', 'fade-up');
        card.setAttribute('data-aos-delay', (index * 100).toString());
        
        // Format price as Indian currency
        const price = parseFloat(attraction.price).toLocaleString('en-IN', {
            maximumFractionDigits: 2,
            minimumFractionDigits: 2
        });
        
        // Ensure we have a proper image path
        let imageSrc = '/gotrip/public/image/default-destination.jpg'; // Default fallback
        
        if (attraction.image_url) {
            // Use the provided image URL if it exists
            imageSrc = attraction.image_url;
        } else {
            // Try to use a specific image based on attraction name
            const attractionName = attraction.name.toLowerCase().replace(/\s+/g, '-');
            
            // Use known attractions images if available
            const knownAttractions = {
                'eiffel-tower': '/gotrip/public/image/attractions/eiffel-tower.jpg',
                'louvre-museum': '/gotrip/public/image/attractions/louvre.jpg',
                'louvre': '/gotrip/public/image/attractions/louvre.jpg',
                'notre-dame': '/gotrip/public/image/attractions/notre-dame.jpg',
                'arc-de-triomphe': '/gotrip/public/image/attractions/arc-de-triomphe.jpg'
            };
            
            if (knownAttractions[attractionName]) {
                imageSrc = knownAttractions[attractionName];
            } else {
                // If not found in known attractions, try a more general approach
                // Fallback to one of the existing images
                const fallbackImages = [
                    '/gotrip/public/image/attractions/eiffel-tower.jpg',
                    '/gotrip/public/image/attractions/louvre.jpg',
                    '/gotrip/public/image/attractions/notre-dame.jpg',
                    '/gotrip/public/image/attractions/arc-de-triomphe.jpg'
                ];
                
                imageSrc = fallbackImages[index % fallbackImages.length];
            }
        }
        
        card.innerHTML = `
            <div class="attraction-image">
                <img src="${imageSrc}" 
                     alt="${attraction.name}" 
                     onerror="this.src='/gotrip/public/image/default-destination.jpg'">
                <div class="attraction-category">${attraction.category}</div>
            </div>
            <div class="attraction-details">
                <h3>${attraction.name}</h3>
                <div class="attraction-info">
                    <div class="attraction-duration"><i class="far fa-clock"></i> ${attraction.duration} hours</div>
                    <div class="attraction-price">₹ ${price}</div>
                </div>
                <button class="select-btn">${isSelected ? 'Remove' : 'Add to Itinerary'}</button>
            </div>
        `;
        
        attractionsList.appendChild(card);
        
        // Add event listener for the select button
        card.querySelector('.select-btn').addEventListener('click', function() {
            if (isSelected) {
                removeAttraction(attraction.id);
            } else {
                addAttraction(attraction);
            }
            renderAttractions(attractions); // Re-render to update selected state
        });
    });
    
    // Refresh AOS animations
    if (typeof AOS !== 'undefined') {
        setTimeout(() => {
            AOS.refresh();
        }, 500);
    }
}

// Helper function to generate rating stars
function getRatingStars(rating) {
    const fullStars = Math.floor(rating);
    const halfStar = rating % 1 >= 0.5;
    const emptyStars = 5 - fullStars - (halfStar ? 1 : 0);
    
    return (
        '<span class="stars">' +
        '<i class="fas fa-star"></i>'.repeat(fullStars) +
        (halfStar ? '<i class="fas fa-star-half-alt"></i>' : '') +
        '<i class="far fa-star"></i>'.repeat(emptyStars) +
        ` <span class="rating-value">${rating}</span>` +
        '</span>'
    );
}

// Helper function to get appropriate icon for transportation type
function getTransportIcon(type) {
    const typeToIcon = {
        'Taxi': 'fas fa-taxi',
        'Rental Car': 'fas fa-car',
        'Bus': 'fas fa-bus',
        'Train': 'fas fa-train',
        'Metro': 'fas fa-subway',
        'Public Transit': 'fas fa-bus-alt',
        'Bike Rental': 'fas fa-bicycle',
        'Walking Tour': 'fas fa-walking'
    };
    
    return typeToIcon[type] || 'fas fa-shuttle-van';
}

// Enhance selection with animation
function selectAccommodation(accommodation) {
    // Remove selected class from all cards
    document.querySelectorAll('.accommodation-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to the chosen card
    const selectedCard = document.querySelector(`.accommodation-card .select-btn[data-id="${accommodation.id}"]`).closest('.accommodation-card');
    selectedCard.classList.add('selected');
    
    // Add animation effect
    selectedCard.style.animation = 'popIn 0.5s ease-out';
    setTimeout(() => {
        selectedCard.style.animation = '';
    }, 500);
    
    // Store selected accommodation data
    selectedAccommodation = accommodation;
    
    // Update cost summary
    updateCostSummary();
    
    // Scroll to transportation section after a short delay
    setTimeout(() => {
        document.getElementById('transportation-section').scrollIntoView({ behavior: 'smooth' });
    }, 600);
}

// Select transportation with enhanced UI
function selectTransportation(transportation) {
    // Remove selected class from all cards
    document.querySelectorAll('.transportation-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to the chosen card
    const selectedCard = document.querySelector(`.transportation-card .select-btn[data-id="${transportation.id}"]`).closest('.transportation-card');
    selectedCard.classList.add('selected');
    
    // Add animation effect
    selectedCard.style.animation = 'popIn 0.5s ease-out';
    setTimeout(() => {
        selectedCard.style.animation = '';
    }, 500);
    
    // Store selected transportation data
    selectedTransportation = transportation;
    
    // Update cost summary
    updateCostSummary();
    
    // Scroll to attractions section after a short delay
    setTimeout(() => {
        document.getElementById('attractions-section').scrollIntoView({ behavior: 'smooth' });
    }, 600);
}

// Add attraction to selected list with animation
function addAttraction(attraction) {
    selectedAttractions.push(attraction);
    
    // Update cost summary
    updateCostSummary();
    
    // Show notification
    showNotification(`Added ${attraction.name} to your itinerary`, 'success');
    
    // Update itinerary if days are available
    if (tripDays > 0) {
        // By default, add to day 1 if no day already selected
        const currentDay = document.querySelector('.day-tab.active') 
            ? parseInt(document.querySelector('.day-tab.active').textContent.replace('Day ', ''))
            : 1;
            
        addAttractionToDay(currentDay, attraction.id);
        showDayPlanner(currentDay);
    }
}

// Remove an attraction from the selected list
function removeAttraction(attractionId) {
    // Remove attraction from selected list
    selectedAttractions = selectedAttractions.filter(a => a.id !== attractionId);
    
    // Remove highlight from card
    const card = document.querySelector(`.attraction-card[data-id="${attractionId}"]`);
    if (card) {
        card.classList.remove('selected');
        card.querySelector('.select-btn').textContent = 'Add to Itinerary';
    }
    
    // Remove attraction from all days in the itinerary
    for (const day in dailyItinerary) {
        dailyItinerary[day] = dailyItinerary[day].filter(item => item.attraction_id !== attractionId);
    }
    
    // Update cost summary
    updateCostSummary();
    
    // Update the itinerary builder
    if (tripDays > 0) {
        updateItineraryBuilder();
    }
}

// Update the itinerary builder with day tabs based on trip duration
function updateItineraryBuilder() {
    if (tripDays <= 0) {
        resetItineraryBuilder();
        return;
    }
    
    const itineraryDays = document.getElementById('itinerary-days');
    itineraryDays.innerHTML = '';
    
    // Create day tabs
    for (let i = 1; i <= tripDays; i++) {
        const dayTab = document.createElement('button');
        dayTab.className = 'day-tab';
        dayTab.textContent = `Day ${i}`;
        dayTab.dataset.day = i;
        
        // Add click event
        dayTab.addEventListener('click', function() {
            showDayPlanner(i);
        });
        
        itineraryDays.appendChild(dayTab);
    }
    
    // Initialize daily itinerary object for each day if not already exists
    for (let i = 1; i <= tripDays; i++) {
        if (!dailyItinerary[i]) {
            dailyItinerary[i] = [];
        }
    }
    
    // Remove days from dailyItinerary that are no longer part of the trip
    for (const day in dailyItinerary) {
        if (parseInt(day) > tripDays) {
            delete dailyItinerary[day];
        }
    }
    
    // Show day 1 by default
    showDayPlanner(1);
}

// Reset the itinerary builder
function resetItineraryBuilder() {
    document.getElementById('itinerary-days').innerHTML = '';
    document.getElementById('day-planner').innerHTML = '<div class="empty-day-message">Select a destination and travel dates to start planning your daily activities</div>';
    dailyItinerary = {};
}

// Show the day planner for a specific day
function showDayPlanner(day) {
    // Highlight the active day tab
    const dayTabs = document.querySelectorAll('.day-tab');
    dayTabs.forEach(tab => {
        if (parseInt(tab.dataset.day) === day) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });
    
    const dayPlanner = document.getElementById('day-planner');
    
    // If no attractions selected, show message
    if (selectedAttractions.length === 0) {
        dayPlanner.innerHTML = '<div class="empty-day-message">Select attractions to add to your itinerary</div>';
        return;
    }
    
    // Show the day planner
    dayPlanner.innerHTML = `
        <h3>Day ${day} - Itinerary</h3>
        <div class="day-attractions">
            <h4>Available Attractions</h4>
            <div class="available-attractions-list">
                ${selectedAttractions.map(attraction => {
                    // Check if attraction is already in this day's itinerary
                    const isInDay = dailyItinerary[day] && dailyItinerary[day].some(item => item.attraction_id === attraction.id);
                    
                    if (!isInDay) {
                        return `
                            <div class="available-attraction" data-id="${attraction.id}">
                                <span>${attraction.name}</span>
                                <button class="add-to-day-btn" data-id="${attraction.id}">Add</button>
                            </div>
                        `;
                    }
                    return '';
                }).join('')}
            </div>
        </div>
        <div class="day-timeline">
            <h4>Day ${day} Timeline</h4>
            <div class="timeline-container">
                ${dailyItinerary[day] && dailyItinerary[day].length > 0 
                    ? dailyItinerary[day].map((item, index) => {
                        const attraction = selectedAttractions.find(a => a.id === item.attraction_id);
                        if (!attraction) return '';
                        
                        return `
                            <div class="timeline-item" data-id="${attraction.id}">
                                <div class="timeline-time">${item.start_time || 'Time not set'}</div>
                                <div class="timeline-content">
                                    <div class="timeline-attraction">
                                        <h5>${attraction.name}</h5>
                                        <div class="timeline-details">
                                            <span class="timeline-duration"><i class="far fa-clock"></i> ${Math.floor(attraction.duration / 60)}h ${attraction.duration % 60}m</span>
                                            <input type="time" class="timeline-time-input" data-index="${index}" value="${item.start_time || ''}">
                                        </div>
                                        <div class="timeline-notes">
                                            <textarea placeholder="Add notes (optional)" data-index="${index}">${item.notes || ''}</textarea>
                                        </div>
                                    </div>
                                    <div class="timeline-actions">
                                        <button class="timeline-btn move-up-btn" data-index="${index}" ${index === 0 ? 'disabled' : ''}><i class="fas fa-arrow-up"></i></button>
                                        <button class="timeline-btn move-down-btn" data-index="${index}" ${index === dailyItinerary[day].length - 1 ? 'disabled' : ''}><i class="fas fa-arrow-down"></i></button>
                                        <button class="timeline-btn remove-btn" data-index="${index}"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('')
                    : '<div class="empty-timeline">No attractions added to this day yet</div>'
                }
            </div>
        </div>
    `;
    
    // Add event listeners to the day planner buttons
    dayPlanner.querySelectorAll('.add-to-day-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const attractionId = parseInt(this.dataset.id);
            addAttractionToDay(day, attractionId);
        });
    });
    
    // Add event listeners for timeline item actions
    if (dailyItinerary[day] && dailyItinerary[day].length > 0) {
        // Time input change
        dayPlanner.querySelectorAll('.timeline-time-input').forEach(input => {
            input.addEventListener('change', function() {
                const index = parseInt(this.dataset.index);
                dailyItinerary[day][index].start_time = this.value;
            });
        });
        
        // Notes textarea change
        dayPlanner.querySelectorAll('.timeline-notes textarea').forEach(textarea => {
            textarea.addEventListener('change', function() {
                const index = parseInt(this.dataset.index);
                dailyItinerary[day][index].notes = this.value;
            });
        });
        
        // Move up button
        dayPlanner.querySelectorAll('.move-up-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (index > 0) {
                    // Swap with previous item
                    [dailyItinerary[day][index], dailyItinerary[day][index - 1]] = [dailyItinerary[day][index - 1], dailyItinerary[day][index]];
                    showDayPlanner(day); // Refresh view
                }
            });
        });
        
        // Move down button
        dayPlanner.querySelectorAll('.move-down-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                if (index < dailyItinerary[day].length - 1) {
                    // Swap with next item
                    [dailyItinerary[day][index], dailyItinerary[day][index + 1]] = [dailyItinerary[day][index + 1], dailyItinerary[day][index]];
                    showDayPlanner(day); // Refresh view
                }
            });
        });
        
        // Remove button
        dayPlanner.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                removeAttractionFromDay(day, index);
            });
        });
    }
}

// Add an attraction to a specific day in the itinerary
function addAttractionToDay(day, attractionId) {
    // Initialize the day's itinerary if it doesn't exist
    if (!dailyItinerary[day]) {
        dailyItinerary[day] = [];
    }
    
    // Add attraction to the day
    dailyItinerary[day].push({
        attraction_id: attractionId,
        start_time: '', // Default empty time
        notes: ''
    });
    
    // Refresh the day planner
    showDayPlanner(day);
}

// Remove an attraction from a specific day in the itinerary
function removeAttractionFromDay(day, index) {
    if (dailyItinerary[day] && dailyItinerary[day][index]) {
        dailyItinerary[day].splice(index, 1);
        showDayPlanner(day); // Refresh view
    }
}

// Update the cost summary based on selected items
function updateCostSummary() {
    let accommodationCost = 0;
    let transportationCost = 0;
    let attractionsCost = 0;
    
    // Calculate accommodation cost
    if (selectedAccommodation && tripDays > 0) {
        accommodationCost = parseFloat(selectedAccommodation.price_per_night) * tripDays;
    }
    
    // Calculate transportation cost
    if (selectedTransportation) {
        transportationCost = parseFloat(selectedTransportation.price);
    }
    
    // Calculate attractions cost
    selectedAttractions.forEach(attraction => {
        attractionsCost += parseFloat(attraction.price);
    });
    
    // Base price of destination
    let basePrice = selectedDestination ? parseFloat(selectedDestination.price) : 0;
    
    // Update the cost elements
    document.getElementById('accommodation-cost').textContent = formatCurrency(accommodationCost);
    document.getElementById('transportation-cost').textContent = formatCurrency(transportationCost);
    document.getElementById('attractions-cost').textContent = formatCurrency(attractionsCost);
    
    // Calculate total cost
    const totalCost = basePrice + accommodationCost + transportationCost + attractionsCost;
    document.getElementById('total-cost').textContent = formatCurrency(totalCost);
}

// Format currency in Indian Rupees
function formatCurrency(amount) {
    return '₹ ' + amount.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Save the itinerary
function saveItinerary() {
    if (!isLoggedIn) {
        alert('Please login to save your itinerary');
        return;
    }
    
    if (!selectedDestination) {
        alert('Please select a destination');
        return;
    }
    
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    if (!startDate || !endDate) {
        alert('Please select travel dates');
        return;
    }
    
    const itineraryName = document.getElementById('itinerary-name').value.trim();
    if (!itineraryName) {
        alert('Please name your itinerary');
        return;
    }
    
    // Calculate total cost
    let accommodationCost = 0;
    let transportationCost = 0;
    let attractionsCost = 0;
    
    if (selectedAccommodation && tripDays > 0) {
        accommodationCost = parseFloat(selectedAccommodation.price_per_night) * tripDays;
    }
    
    if (selectedTransportation) {
        transportationCost = parseFloat(selectedTransportation.price);
    }
    
    selectedAttractions.forEach(attraction => {
        attractionsCost += parseFloat(attraction.price);
    });
    
    let basePrice = selectedDestination ? parseFloat(selectedDestination.price) : 0;
    const totalCost = basePrice + accommodationCost + transportationCost + attractionsCost;
    
    // Prepare itinerary data
    const itineraryData = {
        name: itineraryName,
        destination_id: selectedDestination.id,
        start_date: startDate,
        end_date: endDate,
        accommodation_id: selectedAccommodation ? selectedAccommodation.id : null,
        transportation_id: selectedTransportation ? selectedTransportation.id : null,
        total_price: totalCost,
        attractions: []
    };
    
    // Add attractions from dailyItinerary
    for (const day in dailyItinerary) {
        dailyItinerary[day].forEach(item => {
            itineraryData.attractions.push({
                attraction_id: item.attraction_id,
                day_number: parseInt(day),
                start_time: item.start_time,
                notes: item.notes
            });
        });
    }
    
    // Send to server
    fetch('../../get_trip_planner_data.php?action=save_itinerary', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(itineraryData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error saving itinerary: ' + data.error);
        } else {
            alert('Itinerary saved successfully!');
            loadSavedItineraries(); // Refresh saved itineraries list
        }
    })
    .catch(error => {
        console.error('Error saving itinerary:', error);
        alert('Failed to save itinerary. Please try again.');
    });
}

// Book the current itinerary
function bookItinerary() {
    if (!isLoggedIn) {
        alert('Please login to book this trip');
        return;
    }
    
    if (!selectedDestination) {
        alert('Please select a destination');
        return;
    }
    
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    if (!startDate || !endDate) {
        alert('Please select travel dates');
        return;
    }
    
    // Redirect to booking page with parameters
    const params = new URLSearchParams();
    params.append('destination_id', selectedDestination.id);
    params.append('start_date', startDate);
    params.append('end_date', endDate);
    
    if (selectedAccommodation) {
        params.append('accommodation_id', selectedAccommodation.id);
    }
    
    if (selectedTransportation) {
        params.append('transportation_id', selectedTransportation.id);
    }
    
    if (selectedAttractions.length > 0) {
        params.append('attractions', JSON.stringify(selectedAttractions.map(a => a.id)));
    }
    
    window.location.href = `book.html?${params.toString()}`;
}

// Load saved itineraries for the user
function loadSavedItineraries() {
    if (!isLoggedIn) {
        return;
    }
    
    document.getElementById('saved-itineraries-section').style.display = 'block';
    document.getElementById('saved-itineraries-loading').style.display = 'block';
    document.getElementById('saved-itineraries-list').innerHTML = '';
    
    fetch('../../get_trip_planner_data.php?action=get_itineraries')
        .then(response => response.json())
        .then(itineraries => {
            document.getElementById('saved-itineraries-loading').style.display = 'none';
            
            if (itineraries.error) {
                document.getElementById('saved-itineraries-list').innerHTML = `<div class="error-message">${itineraries.error}</div>`;
                return;
            }
            
            if (itineraries.length === 0) {
                document.getElementById('saved-itineraries-list').innerHTML = '<div class="empty-state"><i class="fas fa-suitcase"></i><p>You haven\'t saved any itineraries yet</p></div>';
                return;
            }
            
            const container = document.getElementById('saved-itineraries-list');
            container.innerHTML = '';
            
            itineraries.forEach(itinerary => {
                // Format dates
                const startDate = new Date(itinerary.start_date);
                const endDate = new Date(itinerary.end_date);
                const formattedStartDate = startDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                const formattedEndDate = endDate.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                
                // Calculate trip duration
                const durationMs = endDate - startDate;
                const durationDays = Math.ceil(durationMs / (1000 * 60 * 60 * 24)) + 1;
                
                const card = document.createElement('div');
                card.className = 'saved-itinerary-card';
                card.innerHTML = `
                    <div class="itinerary-header">
                        <h3 class="itinerary-title">${itinerary.name}</h3>
                        <span class="itinerary-status status-${itinerary.status}">${itinerary.status.charAt(0).toUpperCase() + itinerary.status.slice(1)}</span>
                    </div>
                    <div class="itinerary-details">
                        <div class="itinerary-detail">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>${itinerary.destination_name}, ${itinerary.country}</span>
                        </div>
                        <div class="itinerary-detail">
                            <i class="fas fa-calendar-alt"></i>
                            <span>${formattedStartDate} - ${formattedEndDate} (${durationDays} days)</span>
                        </div>
                        <div class="itinerary-detail">
                            <i class="fas fa-tag"></i>
                            <span>₹ ${parseFloat(itinerary.total_price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                        </div>
                    </div>
                    <div class="itinerary-actions">
                        <button class="itinerary-btn view-btn" data-id="${itinerary.id}">View Details</button>
                        <button class="itinerary-btn edit-btn" data-id="${itinerary.id}">Edit</button>
                        <button class="itinerary-btn book-btn" data-id="${itinerary.id}" ${itinerary.status === 'booked' ? 'disabled' : ''}>
                            ${itinerary.status === 'booked' ? 'Already Booked' : 'Book Now'}
                        </button>
                    </div>
                `;
                
                container.appendChild(card);
                
                // Add event listeners
                card.querySelector('.view-btn').addEventListener('click', function() {
                    viewItinerary(itinerary.id);
                });
                
                card.querySelector('.edit-btn').addEventListener('click', function() {
                    editItinerary(itinerary.id);
                });
                
                const bookBtn = card.querySelector('.book-btn');
                if (itinerary.status !== 'booked') {
                    bookBtn.addEventListener('click', function() {
                        bookSavedItinerary(itinerary.id);
                    });
                }
            });
        })
        .catch(error => {
            document.getElementById('saved-itineraries-loading').style.display = 'none';
            document.getElementById('saved-itineraries-list').innerHTML = '<div class="error-message">Failed to load saved itineraries. Please try again.</div>';
            console.error('Error loading saved itineraries:', error);
        });
}

// Logout function
function logout() {
    fetch('logout.php')
        .then(response => response.json())
        .then(data => {
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('username');
            window.location.href = 'index.html';
        })
        .catch(error => {
            console.error('Error:', error);
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('username');
            window.location.href = 'index.html';
        });
}

// View a saved itinerary
function viewItinerary(itineraryId) {
    window.location.href = `itinerary.html?id=${itineraryId}`;
}

// Edit a saved itinerary
function editItinerary(itineraryId) {
    // Will be implemented when itinerary edit page is created
    alert('This feature is coming soon!');
}

// Book a saved itinerary
function bookSavedItinerary(itineraryId) {
    window.location.href = `book.html?itinerary_id=${itineraryId}`;
}

// Show notification message
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Add visible class after a short delay for animation
    setTimeout(() => {
        notification.classList.add('visible');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.remove('visible');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
} 