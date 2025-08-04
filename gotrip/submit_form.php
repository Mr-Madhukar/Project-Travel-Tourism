<?php
// submit_form.php - Handle contact form submissions
session_start();
require_once 'config.php';

// Set headers to handle CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'])) : '';
    $email = isset($_POST['email']) ? trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)) : '';
    $subject = isset($_POST['subject']) ? trim(htmlspecialchars($_POST['subject'])) : '';
    $message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'])) : '';
    
    // Basic validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no validation errors, proceed
    if (empty($errors)) {
        try {
            // Check if contact_messages table exists, if not create it
            $check_table_sql = "SHOW TABLES LIKE 'contact_messages'";
            $result = $conn->query($check_table_sql);
            
            if ($result->num_rows == 0) {
                // Table doesn't exist, create it
                $create_table_sql = "CREATE TABLE contact_messages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    subject VARCHAR(200) NOT NULL,
                    message TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if (!$conn->query($create_table_sql)) {
                    throw new Exception("Error creating table: " . $conn->error);
                }
            }
            
            // Insert message into database
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                // Send email notification (optional)
                $to = "admin@gotrip.com"; // Change to your email
                $email_subject = "New Contact Form Submission: $subject";
                $email_body = "You have received a new message from your website contact form.\n\n" .
                             "Here are the details:\n\nName: $name\n\nEmail: $email\n\nSubject: $subject\n\nMessage: $message";
                $headers = "From: $email";
                
                // Uncomment this line to enable email sending (configure your server's mail settings first)
                // mail($to, $email_subject, $email_body, $headers);
                
                // Set success message and redirect
                $_SESSION['form_success'] = "Thank you for your message! We'll get back to you soon.";
                
                // Redirect back to contact page
                header("Location: public/views/contact.html?success=true");
                exit();
            } else {
                throw new Exception("Error inserting message: " . $stmt->error);
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // Set error message
            $_SESSION['form_error'] = "Sorry, there was an error submitting your message: " . $e->getMessage();
            
            // Redirect back to contact page with error
            header("Location: public/views/contact.html?error=true");
            exit();
        }
    } else {
        // There were validation errors
        $_SESSION['form_error'] = "Please fix the following errors: " . implode(", ", $errors);
        
        // Redirect back to contact page with error
        header("Location: public/views/contact.html?error=true");
        exit();
    }
} else {
    // Not a POST request
    header("Location: public/views/contact.html");
    exit();
}

// Close database connection
$conn->close();
?> 