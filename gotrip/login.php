<?php
// Start the session
session_start();
require_once 'db_connect.php';

// Check if it's a direct form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input data
    $login = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
    $password = $_POST['password'] ?? '';
    
    // Debug log
    error_log("Direct login attempt: " . $login);
    
    // Validate input
    if (empty($login) || empty($password)) {
        $error = urlencode('All fields are required');
        header("Location: public/views/login.html?error=$error");
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
            
            error_log("Direct login successful for user: " . $user['username']);
            
            // Redirect to dashboard
            header('Location: user_dashboard.php');
            exit();
        } else {
            error_log("Invalid password for user: " . $login);
            $error = urlencode('Invalid password');
            header("Location: public/views/login.html?error=$error");
            exit();
        }
    } else {
        error_log("User not found: " . $login);
        $error = urlencode('User not found');
        header("Location: public/views/login.html?error=$error");
        exit();
    }
    
    $stmt->close();
} else {
    // If not a POST request, redirect to login page
    header('Location: public/views/login.html');
    exit();
}

$conn->close();
?>
