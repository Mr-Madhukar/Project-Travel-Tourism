<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to make a booking']);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user ID from session
    $user_id = $_SESSION['user_id'];
    
    // Get booking details from POST data
    $destination_id = $conn->real_escape_string($_POST['destination_id']);
    $check_in_date = $conn->real_escape_string($_POST['check_in_date']);
    $check_out_date = $conn->real_escape_string($_POST['check_out_date']);
    $transportation = $conn->real_escape_string($_POST['transportation']);
    $num_travelers = (int)$_POST['num_travelers'];
    
    // Validate data
    if (empty($destination_id) || empty($check_in_date) || empty($check_out_date) || $num_travelers < 1) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        exit();
    }
    
    // Get destination price from database
    $price_query = "SELECT price FROM destinations WHERE id = '$destination_id'";
    $price_result = $conn->query($price_query);
    
    if ($price_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Destination not found']);
        exit();
    }
    
    $destination = $price_result->fetch_assoc();
    $price_per_person = $destination['price'];
    
    // Calculate total price
    $total_price = $price_per_person * $num_travelers;
    
    // Insert booking into database
    $sql = "INSERT INTO bookings (user_id, destination_id, check_in_date, check_out_date, transportation, num_travelers, total_price, status) 
            VALUES ('$user_id', '$destination_id', '$check_in_date', '$check_out_date', '$transportation', '$num_travelers', '$total_price', 'pending')";
    
    if ($conn->query($sql) === TRUE) {
        $booking_id = $conn->insert_id;
        echo json_encode([
            'status' => 'success', 
            'message' => 'Booking successful!',
            'booking_id' => $booking_id,
            'total_price' => $total_price
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error creating booking: ' . $conn->error]);
    }

    $conn->close();
}
?> 