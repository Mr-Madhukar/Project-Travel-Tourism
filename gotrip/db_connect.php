<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection settings
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP default has no password
$database = "travel_tourism"; 
$port = 3306; // Default MySQL port

// Function to display user-friendly error messages
function showErrorMessage($message) {
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Return JSON for AJAX requests
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
    } else {
        // Return HTML for direct page requests
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Database Connection Error</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
                .error-container { max-width: 600px; margin: 0 auto; background: #f8d7da; border-left: 5px solid #dc3545; padding: 20px; }
                h1 { color: #dc3545; margin-top: 0; }
                .steps { background: #f8f9fa; padding: 15px; margin-top: 20px; border-radius: 5px; }
                .steps ol { margin-bottom: 0; }
                .note { margin-top: 20px; font-size: 0.9em; color: #666; }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>Database Connection Error</h1>
                <p><strong>Error:</strong> $message</p>
                <div class='steps'>
                    <p><strong>How to fix:</strong></p>
                    <ol>
                        <li>Open XAMPP Control Panel</li>
                        <li>Click the 'Start' button next to MySQL</li>
                        <li>Wait for MySQL to start (green status)</li>
                        <li>Refresh this page</li>
                    </ol>
                </div>
                <p class='note'>If you've already started MySQL and still see this error, check the MySQL logs in XAMPP for any issues.</p>
            </div>
        </body>
        </html>";
    }
    exit;
}

// First check if MySQL is running
$mysql_running = @fsockopen($servername, $port, $errno, $errstr, 1);
if (!$mysql_running) {
    showErrorMessage("MySQL server is not running. Please start the MySQL service in XAMPP Control Panel.");
}

// Try to connect to the database
try {
    $conn = new mysqli($servername, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8mb4");
    
    // Log successful connection for debugging
    error_log("Connected to database successfully");
    
    // Check if bookings table has the required fields
    $check_bookings = $conn->query("DESCRIBE bookings");
    if ($check_bookings) {
        $missing_fields = [];
        $has_booking_date = false;
        $has_user_id = false;
        
        while ($field = $check_bookings->fetch_assoc()) {
            if ($field['Field'] === 'booking_date') {
                $has_booking_date = true;
            }
            if ($field['Field'] === 'user_id') {
                $has_user_id = true;
            }
        }
        
        // Add missing fields if needed
        if (!$has_booking_date) {
            error_log("Adding missing booking_date field to bookings table");
            $conn->query("ALTER TABLE bookings ADD COLUMN booking_date DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
        
        if (!$has_user_id) {
            error_log("Adding missing user_id field to bookings table");
            $conn->query("ALTER TABLE bookings ADD COLUMN user_id INT");
        }
        
        // Add index to improve query performance
        $conn->query("ALTER TABLE bookings ADD INDEX idx_user_id (user_id)");
    }
    
} catch (Exception $e) {
    // First connection failed, try to create the database
    try {
        $conn = new mysqli($servername, $username, $password);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Create database if it doesn't exist
        $sql = "CREATE DATABASE IF NOT EXISTS $database";
        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error creating database: " . $conn->error);
        }
        
        // Select the database
        $conn->select_db($database);
        
        // Set character set
        $conn->set_charset("utf8mb4");
        
    } catch (Exception $inner_e) {
        showErrorMessage($inner_e->getMessage());
    }
}

// Create tables if they don't exist
try {
    // Create users table if it doesn't exist
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows == 0) {
        // Create users table
        $sql = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(255),
            phone VARCHAR(20),
            address TEXT,
            profile_image VARCHAR(255),
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error creating users table: " . $conn->error);
        }
    }

    // Create bookings table if it doesn't exist
    $result = $conn->query("SHOW TABLES LIKE 'bookings'");
    if ($result->num_rows == 0) {
        $sql = "CREATE TABLE bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_ref VARCHAR(10) NOT NULL,
            user_id INT,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            destination VARCHAR(255) NOT NULL,
            travel_date DATE NOT NULL,
            check_in_date DATE NOT NULL,
            check_out_date DATE NOT NULL,
            travelers INT NOT NULL DEFAULT 1,
            room_type VARCHAR(50),
            airport_pickup TINYINT(1) NOT NULL DEFAULT 0,
            tour_guide TINYINT(1) NOT NULL DEFAULT 0,
            insurance TINYINT(1) NOT NULL DEFAULT 0,
            special_requests TEXT,
            total_amount DECIMAL(10, 2) NOT NULL,
            booking_date DATETIME NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
            INDEX (user_id),
            INDEX (booking_ref)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        if ($conn->query($sql) !== TRUE) {
            throw new Exception("Error creating bookings table: " . $conn->error);
        }
    } else {
        // Check if we need to alter the table to add the user_id field
        $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'user_id'");
        if ($result->num_rows == 0) {
            // Add user_id column
            $conn->query("ALTER TABLE bookings ADD COLUMN user_id INT AFTER booking_ref, ADD INDEX (user_id)");
            error_log("Added user_id column to bookings table");
        }
        
        // Check if we need to alter the table to add check_in_date and check_out_date fields
        $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'check_in_date'");
        if ($result->num_rows == 0) {
            // Add check_in_date column
            $conn->query("ALTER TABLE bookings ADD COLUMN check_in_date DATE AFTER travel_date");
            error_log("Added check_in_date column to bookings table");
        }
        
        $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'check_out_date'");
        if ($result->num_rows == 0) {
            // Add check_out_date column
            $conn->query("ALTER TABLE bookings ADD COLUMN check_out_date DATE AFTER check_in_date");
            error_log("Added check_out_date column to bookings table");
        }
        
        // Check if the username column exists (which is causing problems)
        $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'username'");
        if ($result->num_rows > 0) {
            // username column exists, we should remove it to avoid conflicts
            try {
                $conn->query("ALTER TABLE bookings DROP COLUMN username");
                error_log("Removed username column from bookings table to avoid conflicts");
            } catch (Exception $e) {
                error_log("Failed to remove username column: " . $e->getMessage());
            }
        }
    }
} catch (Exception $e) {
    // Log error but don't exit - we'll assume tables exist or will be created elsewhere
    error_log("GoTrip database setup error: " . $e->getMessage());
}

// Make sure session is active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
