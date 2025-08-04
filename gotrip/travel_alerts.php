<?php
// travel_alerts.php - Manages travel alerts and real-time updates
header('Content-Type: application/json');
require_once 'config.php';

// Function to get current date in Y-m-d format
function getCurrentDate() {
    return date('Y-m-d');
}

// Function to check if an alert exists
function alertExists($conn, $destination_id, $alert_type) {
    $sql = "SELECT id FROM travel_alerts WHERE destination_id = ? AND alert_type = ? AND active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $destination_id, $alert_type);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Create travel_alerts table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS travel_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT,
    alert_type VARCHAR(50) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    start_date DATE,
    end_date DATE,
    severity ENUM('low', 'medium', 'high') DEFAULT 'medium',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
)";

if (!$conn->query($sql)) {
    echo json_encode(['error' => 'Failed to create travel_alerts table: ' . $conn->error]);
    exit;
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Get travel alerts
        if (isset($_GET['destination_id'])) {
            // Get alerts for a specific destination
            $destination_id = intval($_GET['destination_id']);
            $sql = "SELECT a.*, d.name as destination_name, d.country
                    FROM travel_alerts a
                    LEFT JOIN destinations d ON a.destination_id = d.id
                    WHERE a.destination_id = ? AND a.active = 1
                    AND (a.end_date IS NULL OR a.end_date >= ?)
                    ORDER BY a.severity DESC, a.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $current_date = getCurrentDate();
            $stmt->bind_param("is", $destination_id, $current_date);
        } else {
            // Get all active alerts
            $sql = "SELECT a.*, d.name as destination_name, d.country
                    FROM travel_alerts a
                    LEFT JOIN destinations d ON a.destination_id = d.id
                    WHERE a.active = 1
                    AND (a.end_date IS NULL OR a.end_date >= ?)
                    ORDER BY a.severity DESC, a.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $current_date = getCurrentDate();
            $stmt->bind_param("s", $current_date);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $alerts = [];
        while ($row = $result->fetch_assoc()) {
            $alerts[] = $row;
        }
        
        echo json_encode($alerts);
        break;
        
    case 'POST':
        // Admin function: Add a new alert
        // This would typically require admin authentication
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['destination_id']) || !isset($input['alert_type']) || 
            !isset($input['title']) || !isset($input['description'])) {
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        
        $destination_id = intval($input['destination_id']);
        $alert_type = $conn->real_escape_string($input['alert_type']);
        $title = $conn->real_escape_string($input['title']);
        $description = $conn->real_escape_string($input['description']);
        $severity = isset($input['severity']) ? $conn->real_escape_string($input['severity']) : 'medium';
        $start_date = isset($input['start_date']) ? $conn->real_escape_string($input['start_date']) : getCurrentDate();
        $end_date = isset($input['end_date']) ? $conn->real_escape_string($input['end_date']) : null;
        
        // Check if similar alert already exists
        if (alertExists($conn, $destination_id, $alert_type)) {
            $sql = "UPDATE travel_alerts SET 
                    title = ?, 
                    description = ?, 
                    severity = ?, 
                    start_date = ?, 
                    end_date = ?, 
                    active = 1
                    WHERE destination_id = ? AND alert_type = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $title, $description, $severity, $start_date, $end_date, $destination_id, $alert_type);
        } else {
            // Insert new alert
            $sql = "INSERT INTO travel_alerts (destination_id, alert_type, title, description, severity, start_date, end_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssss", $destination_id, $alert_type, $title, $description, $severity, $start_date, $end_date);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Alert created successfully']);
        } else {
            echo json_encode(['error' => 'Failed to create alert: ' . $conn->error]);
        }
        break;
        
    case 'DELETE':
        // Admin function: Delete or deactivate an alert
        if (isset($_GET['id'])) {
            $id = intval($_GET['id']);
            
            // Option 1: Delete the alert
            // $sql = "DELETE FROM travel_alerts WHERE id = ?";
            
            // Option 2: Deactivate the alert (safer)
            $sql = "UPDATE travel_alerts SET active = 0 WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Alert deactivated successfully']);
            } else {
                echo json_encode(['error' => 'Failed to deactivate alert: ' . $conn->error]);
            }
        } else {
            echo json_encode(['error' => 'Missing alert ID']);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Unsupported request method']);
        break;
}

// Close database connection
$conn->close();
?> 