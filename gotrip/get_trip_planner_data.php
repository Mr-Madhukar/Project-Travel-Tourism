<?php
// Include database configuration
require_once 'config.php';

// Set header to return JSON
header('Content-Type: application/json');

// Handle CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Create database connection
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        // Return empty array instead of an error for better client-side fallback
        echo json_encode([]);
        exit;
    }

    // Determine the action based on request parameter
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    switch ($action) {
        case 'destinations':
            getDestinations($conn);
            break;
        case 'attractions':
            getAttractions($conn);
            break;
        case 'accommodations':
            getAccommodations($conn);
            break;
        case 'transportation':
            getTransportation($conn);
            break;
        case 'save_itinerary':
            saveItinerary($conn);
            break;
        case 'get_itineraries':
            getItineraries($conn);
            break;
        default:
            echo json_encode([]);
    }
} catch (Exception $e) {
    // Return empty array on any error
    echo json_encode([]);
}

// Function to get all destinations or a specific one
function getDestinations($conn) {
    try {
        $destination_id = isset($_GET['id']) ? intval($_GET['id']) : null;
        
        if ($destination_id) {
            $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
            $stmt->bind_param("i", $destination_id);
        } else {
            $stmt = $conn->prepare("SELECT * FROM destinations");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $destinations = [];
        
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row;
        }
        
        echo json_encode($destinations);
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([]);
    }
}

// Function to get attractions by destination
function getAttractions($conn) {
    $destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : null;
    
    if (!$destination_id) {
        echo json_encode(['error' => 'Destination ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM attractions WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attractions = [];
    
    while ($row = $result->fetch_assoc()) {
        $attractions[] = $row;
    }
    
    echo json_encode($attractions);
    $stmt->close();
}

// Function to get accommodations by destination
function getAccommodations($conn) {
    $destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : null;
    
    if (!$destination_id) {
        echo json_encode(['error' => 'Destination ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM accommodations WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $accommodations = [];
    
    while ($row = $result->fetch_assoc()) {
        // Convert amenities JSON string to object
        if (isset($row['amenities'])) {
            $row['amenities'] = json_decode($row['amenities']);
        }
        $accommodations[] = $row;
    }
    
    echo json_encode($accommodations);
    $stmt->close();
}

// Function to get transportation options by destination
function getTransportation($conn) {
    $destination_id = isset($_GET['destination_id']) ? intval($_GET['destination_id']) : null;
    
    if (!$destination_id) {
        echo json_encode(['error' => 'Destination ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM transportation WHERE destination_id = ?");
    $stmt->bind_param("i", $destination_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transportation = [];
    
    while ($row = $result->fetch_assoc()) {
        $transportation[] = $row;
    }
    
    echo json_encode($transportation);
    $stmt->close();
}

// Function to save a new itinerary
function saveItinerary($conn) {
    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User not logged in']);
        return;
    }
    
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        echo json_encode(['error' => 'Invalid data provided']);
        return;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert main itinerary
        $stmt = $conn->prepare("INSERT INTO itineraries (user_id, name, destination_id, start_date, end_date, accommodation_id, transportation_id, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $user_id = $_SESSION['user_id'];
        $status = 'draft';
        
        $stmt->bind_param("isissiids", 
            $user_id, 
            $data['name'], 
            $data['destination_id'], 
            $data['start_date'], 
            $data['end_date'], 
            $data['accommodation_id'], 
            $data['transportation_id'], 
            $data['total_price'],
            $status
        );
        
        $stmt->execute();
        $itinerary_id = $conn->insert_id;
        
        // Insert itinerary items (attractions)
        if (isset($data['attractions']) && is_array($data['attractions'])) {
            $stmt_items = $conn->prepare("INSERT INTO itinerary_items (itinerary_id, attraction_id, day_number, start_time, notes) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($data['attractions'] as $item) {
                $stmt_items->bind_param("iiiss", 
                    $itinerary_id, 
                    $item['attraction_id'], 
                    $item['day_number'], 
                    $item['start_time'], 
                    $item['notes']
                );
                $stmt_items->execute();
            }
            
            $stmt_items->close();
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'itinerary_id' => $itinerary_id]);
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        echo json_encode(['error' => 'Failed to save itinerary: ' . $e->getMessage()]);
    }
    
    $stmt->close();
}

// Function to get user's itineraries
function getItineraries($conn) {
    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'User not logged in']);
        return;
    }
    
    $user_id = $_SESSION['user_id'];
    $itinerary_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    
    if ($itinerary_id) {
        // Get a specific itinerary with all details
        $itinerary = getItineraryDetails($conn, $itinerary_id, $user_id);
        echo json_encode($itinerary);
    } else {
        // Get all itineraries for the user
        $stmt = $conn->prepare("
            SELECT i.*, d.name as destination_name, d.country 
            FROM itineraries i
            JOIN destinations d ON i.destination_id = d.id
            WHERE i.user_id = ?
            ORDER BY i.created_at DESC
        ");
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $itineraries = [];
        
        while ($row = $result->fetch_assoc()) {
            $itineraries[] = $row;
        }
        
        echo json_encode($itineraries);
        $stmt->close();
    }
}

// Helper function to get detailed itinerary info
function getItineraryDetails($conn, $itinerary_id, $user_id) {
    // Get main itinerary data
    $stmt = $conn->prepare("
        SELECT i.*, d.name as destination_name, d.country,
               a.name as accommodation_name, a.price_per_night,
               t.type as transportation_type, t.price as transportation_price
        FROM itineraries i
        JOIN destinations d ON i.destination_id = d.id
        LEFT JOIN accommodations a ON i.accommodation_id = a.id
        LEFT JOIN transportation t ON i.transportation_id = t.id
        WHERE i.id = ? AND i.user_id = ?
    ");
    
    $stmt->bind_param("ii", $itinerary_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Itinerary not found or access denied'];
    }
    
    $itinerary = $result->fetch_assoc();
    $stmt->close();
    
    // Get itinerary items (attractions)
    $stmt = $conn->prepare("
        SELECT i.*, a.name as attraction_name, a.price, a.duration, a.category
        FROM itinerary_items i
        JOIN attractions a ON i.attraction_id = a.id
        WHERE i.itinerary_id = ?
        ORDER BY i.day_number, i.start_time
    ");
    
    $stmt->bind_param("i", $itinerary_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = [];
    
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $itinerary['items'] = $items;
    $stmt->close();
    
    return $itinerary;
}

$conn->close();
?> 