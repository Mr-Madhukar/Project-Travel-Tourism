<?php
// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

// Return a simple JSON response
echo json_encode([
    'status' => 'success',
    'message' => 'Test login endpoint working correctly',
    'timestamp' => date('Y-m-d H:i:s')
]);
?> 