<?php
// travel_alerts.php - Proxy for the travel alerts API in the public directory

// Set CORS headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle OPTIONS request (preflight for CORS)
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Forward request to the parent directory travel_alerts.php
// Get query parameters from the original request
$query = $_SERVER['QUERY_STRING'];
$parent_url = '../travel_alerts.php' . ($query ? "?$query" : '');

// Load data from the parent directory endpoint
$response = file_get_contents($parent_url);

// If we couldn't get a response, return a default empty array
if ($response === false) {
    // Create a default set of alerts
    $default_alerts = [
        [
            'id' => 1,
            'destination_id' => 1,
            'alert_type' => 'weather',
            'title' => 'Sunny Weather in Paris',
            'description' => 'Enjoy beautiful sunny weather in Paris this week with temperatures reaching 25°C.',
            'destination_name' => 'Paris',
            'country' => 'France',
            'severity' => 'low',
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'destination_id' => 2,
            'alert_type' => 'travel',
            'title' => 'Temporary Metro Closure in Tokyo',
            'description' => 'The Yamanote Line in Tokyo will be undergoing maintenance on weekends. Please plan alternative routes.',
            'destination_name' => 'Tokyo',
            'country' => 'Japan',
            'severity' => 'medium',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
    
    echo json_encode($default_alerts);
} else {
    // Output the response from the parent directory travel_alerts.php
    echo $response;
}
?> 