<?php
// Include the CORS middleware
require_once 'cors_middleware.php';

// Start the session
session_start();
require_once 'db_connect.php';

// Set header for JSON response
header('Content-Type: application/json');

// Log for debugging
error_log("Login request received with method: " . $_SERVER["REQUEST_METHOD"]);
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input data
    $login = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';
    
    // Debug log
    error_log("Login attempt: " . $login);
    
    // Validate input
    if (empty($login) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }
    
    // Prepare statement to check user by email or username
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['logged_in'] = true;
            
            // Set session cookie parameters for better security
            $current_time = time();
            $expiry = $current_time + 86400; // 24 hours
            setcookie(session_name(), session_id(), $expiry, "/", "", false, true);
            
            error_log("Login successful for user: " . $user['username']);
            
            // Return success with user ID
            echo json_encode([
                'status' => 'success', 
                'message' => 'Login successful',
                'username' => $user['username'],
                'user_id' => $user['id']
            ]);
        } else {
            error_log("Invalid password for user: " . $login);
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } else {
        error_log("User not found: " . $login);
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }
    
    $stmt->close();
} else {
    error_log("Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

$conn->close();
?> 