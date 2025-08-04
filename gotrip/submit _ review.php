<?php
session_start();
include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a review']);
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the review data
$userId = $_SESSION['user_id'];
$destinationId = filter_input(INPUT_POST, 'destination_id', FILTER_VALIDATE_INT);
$rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
$review = filter_input(INPUT_POST, 'review', FILTER_SANITIZE_STRING);
$title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);

// Validate inputs
if (!$destinationId || !$rating || !$review) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate rating is between 1 and 5
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit;
}

try {
    // Check if the user has already submitted a review for this destination
    $checkStmt = $conn->prepare("SELECT id FROM reviews WHERE user_id = ? AND destination_id = ?");
    $checkStmt->bind_param("ii", $userId, $destinationId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing review
        $row = $result->fetch_assoc();
        $reviewId = $row['id'];
        
        $updateStmt = $conn->prepare("UPDATE reviews SET rating = ?, review_text = ?, title = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->bind_param("issi", $rating, $review, $title, $reviewId);
        $success = $updateStmt->execute();
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Review updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update review']);
        }
    } else {
        // Insert new review
        $insertStmt = $conn->prepare("INSERT INTO reviews (user_id, destination_id, rating, review_text, title, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $insertStmt->bind_param("iiiss", $userId, $destinationId, $rating, $review, $title);
        $success = $insertStmt->execute();
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
        }
    }
} catch (Exception $e) {
    error_log("Error submitting review: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your review']);
}

$conn->close();
?>
