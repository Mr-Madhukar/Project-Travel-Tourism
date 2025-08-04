<?php
// Database connection check script
require_once 'config.php';

// Get all tables in the database
$tables_query = "SHOW TABLES";
$tables_result = $conn->query($tables_query);

echo "<h2>Database Tables:</h2>";
echo "<ul>";
while ($table = $tables_result->fetch_array()) {
    echo "<li>" . $table[0] . "</li>";
}
echo "</ul>";

// Check bookings table structure
echo "<h2>Bookings Table Structure:</h2>";
$structure_query = "DESCRIBE bookings";
$structure_result = $conn->query($structure_query);

if (!$structure_result) {
    echo "Error: " . $conn->error;
} else {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($field = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . $field['Default'] . "</td>";
        echo "<td>" . $field['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check for any bookings in the database
echo "<h2>Sample Bookings:</h2>";
$bookings_query = "SELECT * FROM bookings LIMIT 5";
$bookings_result = $conn->query($bookings_query);

if (!$bookings_result) {
    echo "Error: " . $conn->error;
} else {
    if ($bookings_result->num_rows === 0) {
        echo "No bookings found in the database.";
    } else {
        echo "Found " . $bookings_result->num_rows . " bookings:<br>";
        echo "<table border='1'>";
        
        // Get column names
        $fields = $bookings_result->fetch_fields();
        echo "<tr>";
        foreach ($fields as $field) {
            echo "<th>" . $field->name . "</th>";
        }
        echo "</tr>";
        
        // Reset pointer
        $bookings_result->data_seek(0);
        
        // Display data
        while ($row = $bookings_result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Check login table/users table for the current user
echo "<h2>Users table check:</h2>";
$users_query = "DESCRIBE users";
$users_result = $conn->query($users_query);

if (!$users_result) {
    echo "Error: " . $conn->error . " (users table might not exist)";
} else {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($field = $users_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $field['Field'] . "</td>";
        echo "<td>" . $field['Type'] . "</td>";
        echo "<td>" . $field['Null'] . "</td>";
        echo "<td>" . $field['Key'] . "</td>";
        echo "<td>" . $field['Default'] . "</td>";
        echo "<td>" . $field['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Get the structure of the sessions table if it exists
    echo "<h2>Sessions table check:</h2>";
    $sessions_query = "SHOW TABLES LIKE 'sessions'";
    $sessions_result = $conn->query($sessions_query);
    
    if ($sessions_result->num_rows > 0) {
        $structure_query = "DESCRIBE sessions";
        $structure_result = $conn->query($structure_query);
        
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        while ($field = $structure_result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $field['Field'] . "</td>";
            echo "<td>" . $field['Type'] . "</td>";
            echo "<td>" . $field['Null'] . "</td>";
            echo "<td>" . $field['Key'] . "</td>";
            echo "<td>" . $field['Default'] . "</td>";
            echo "<td>" . $field['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No sessions table found.";
    }
}

$conn->close();
?> 