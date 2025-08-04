<?php
// get_destinations.php - Retrieve all destinations from database
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

$sql = "SELECT name, country FROM destinations ORDER BY name";
$result = $conn->query($sql);

$destinations = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $destinations[] = array(
            'name' => $row['name'],
            'country' => $row['country']
        );
    }
}

// If no destinations in database, return default destinations
if (empty($destinations)) {
    $destinations = array(
        array('name' => 'London', 'country' => 'United Kingdom'),
        array('name' => 'Vancouver', 'country' => 'Canada'),
        array('name' => 'Monaco', 'country' => 'Monaco'),
        array('name' => 'Paris', 'country' => 'France'),
        array('name' => 'Tokyo', 'country' => 'Japan'),
        array('name' => 'Zurich', 'country' => 'Switzerland')
    );
}

echo json_encode($destinations);
$conn->close();
?> 