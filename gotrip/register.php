<?php
// Start at the very top with error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Add CORS headers for development
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set header for JSON response
header('Content-Type: application/json');

// Log to a file
ini_set('log_errors', 1);
ini_set('error_log', 'registration_error.log');

// Log that we're starting the script
error_log("Register.php script started");

// Database connection settings
$servername = "localhost";
$username = "root";
$password = ""; // XAMPP default has no password
$database = "travel_tourism"; // Using the correct database name
$port = 3306; // Default MySQL port

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $database, $port);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Process the registration if it's a POST request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get and sanitize input
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password']; // Don't sanitize password as it will be hashed
        $full_name = filter_input(INPUT_POST, 'fullname', FILTER_SANITIZE_SPECIAL_CHARS);

        // Validate inputs
        if (empty($username) || empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Validate password strength (should match frontend pattern)
        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=]).{8,}$/', $password)) {
            throw new Exception("Password does not meet security requirements");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            throw new Exception("Error checking email: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Email already exists");
        }
        $stmt->close();

        // Check if username already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception("Error checking username: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Username already taken");
        }
        $stmt->close();

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
        if (!$stmt->execute()) {
            throw new Exception("Error creating user: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Log registration success
        error_log("User registered: " . $email);
        echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    
    // Return error response
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} finally {
    // Close the connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>
