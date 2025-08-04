<?php
// Include database configuration
require_once 'config.php';

// Function to calculate dynamic price factors
function calculatePriceFactors() {
    // Dynamic factors that affect pricing
    $factors = [
        'season' => [
            'high' => 1.25,    // 25% increase during high season
            'mid' => 1.0,      // Normal price during mid season
            'low' => 0.85      // 15% discount during low season
        ],
        'demand' => [
            'high' => 1.15,    // 15% increase for high demand
            'normal' => 1.0,   // Normal price for normal demand
            'low' => 0.9       // 10% discount for low demand
        ],
        'special_event' => 1.1, // 10% increase during special events
        'last_minute' => 0.9,   // 10% discount for last-minute bookings
        'early_bird' => 0.95    // 5% discount for early bird bookings
    ];
    
    return $factors;
}

// Function to determine current season based on date
function getCurrentSeason() {
    $month = date('n'); // Current month (1-12)
    
    // Define seasons: high (6-8, summer), mid (3-5, 9-11, spring/fall), low (12, 1-2, winter)
    if ($month >= 6 && $month <= 8) {
        return 'high'; // Summer months
    } elseif (($month >= 3 && $month <= 5) || ($month >= 9 && $month <= 11)) {
        return 'mid';  // Spring/Fall months
    } else {
        return 'low';  // Winter months
    }
}

// Function to determine demand level (simplified example)
function getDemandLevel() {
    // In a real system, this would check booking data, search trends, etc.
    // For this example, we'll use a simple randomized approach for demonstration
    $rand = rand(1, 10);
    
    if ($rand >= 8) {
        return 'high';
    } elseif ($rand >= 4) {
        return 'normal';
    } else {
        return 'low';
    }
}

// Function to check if today is a special event day (simplified)
function isSpecialEvent() {
    // In a real system, this would check a calendar of special events
    // For this example, we'll say weekends are special event days
    $day = date('N'); // 1 (Monday) to 7 (Sunday)
    return ($day >= 6); // Weekend (Saturday or Sunday)
}

// Function to get dynamic prices for all destinations
function getDynamicPrices() {
    global $conn;
    
    // Get base prices from database
    $sql = "SELECT id, name, country, price FROM destinations";
    $result = $conn->query($sql);
    
    if (!$result) {
        return ['error' => 'Database query failed: ' . $conn->error];
    }
    
    $destinations = [];
    while ($row = $result->fetch_assoc()) {
        $destinations[] = $row;
    }
    
    // Get pricing factors
    $factors = calculatePriceFactors();
    $currentSeason = getCurrentSeason();
    $demandLevel = getDemandLevel();
    $hasSpecialEvent = isSpecialEvent();
    
    // Calculate dynamic prices
    $dynamicPrices = [];
    foreach ($destinations as $destination) {
        $basePrice = $destination['price'];
        $multiplier = 1.0;
        
        // Apply season factor
        $multiplier *= $factors['season'][$currentSeason];
        
        // Apply demand factor
        $multiplier *= $factors['demand'][$demandLevel];
        
        // Apply special event factor if applicable
        if ($hasSpecialEvent) {
            $multiplier *= $factors['special_event'];
        }
        
        // Calculate new price
        $newPrice = round($basePrice * $multiplier, 2);
        
        // Format prices for display
        $formattedBasePrice = '₹ ' . number_format($basePrice, 2) . ' / Per Person/$' . number_format($basePrice/84, 0);
        $formattedDynamicPrice = '₹ ' . number_format($newPrice, 2) . ' / Per Person/$' . number_format($newPrice/84, 0);
        
        $dynamicPrices[] = [
            'id' => $destination['id'],
            'name' => $destination['name'],
            'country' => $destination['country'],
            'base_price' => $basePrice,
            'dynamic_price' => $newPrice,
            'formatted_base_price' => $formattedBasePrice,
            'formatted_dynamic_price' => $formattedDynamicPrice,
            'factors' => [
                'season' => $currentSeason,
                'demand' => $demandLevel,
                'special_event' => $hasSpecialEvent
            ]
        ];
    }
    
    return $dynamicPrices;
}

// API endpoint to get dynamic prices
header('Content-Type: application/json');

// Check if specific destination ID is requested
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $allPrices = getDynamicPrices();
    $result = array_filter($allPrices, function($item) use ($id) {
        return $item['id'] == $id;
    });
    echo json_encode(array_values($result));
} else {
    // Return all prices
    echo json_encode(getDynamicPrices());
}
?> 