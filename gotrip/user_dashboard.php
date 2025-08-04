<?php
// user_dashboard.php - User Dashboard
session_start();
require_once 'db_connect.php';

// Set the login check based on localStorage or session
$isLoggedIn = false;
$username = '';
$user_id = '';
$newBookingRef = isset($_GET['new_booking']) ? $_GET['new_booking'] : '';

// Enable debug mode if requested
$debugMode = isset($_GET['booking_debug']) && $_GET['booking_debug'] == '1';

// Debug log
if ($debugMode) {
    error_log("User dashboard accessed with debug mode enabled");
}

if (!empty($newBookingRef)) {
    error_log("Dashboard accessed with new booking reference: $newBookingRef");
}

// Check if username is in the GET parameters (from the JavaScript redirect)
if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $isLoggedIn = true;
    
    // Try to get user_id from username
    $user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result->num_rows > 0) {
        $user_id = $user_result->fetch_assoc()['id'];
    }
    $user_stmt->close();
} 
// Check if user is logged in via session
elseif (isset($_SESSION['username']) && isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    $isLoggedIn = true;
}

// If not logged in, redirect to login page
if (!$isLoggedIn) {
    header("Location: public/views/login.html");
    exit();
}

// Get all bookings for the user - try user_id first, then fall back to username
$bookings_result = null;
$bookings_found = false;

// If we have a new booking reference, try to fetch it specifically
if (!empty($newBookingRef)) {
    try {
        $bookings_sql = "SELECT * FROM bookings WHERE booking_ref = ? LIMIT 1";
        $stmt = $conn->prepare($bookings_sql);
        $stmt->bind_param("s", $newBookingRef);
        $stmt->execute();
        $bookings_result = $stmt->get_result();
        
        if ($bookings_result && $bookings_result->num_rows > 0) {
            error_log("Found new booking with reference: $newBookingRef");
            $bookings_found = true;
        } else {
            error_log("No booking found with reference: $newBookingRef");
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log("Error fetching new booking: " . $e->getMessage());
    }
}

// If no specific booking was found or requested, get all bookings for the user
if (!$bookings_found) {
    if ($user_id) {
        // Try with user_id first - try different possible sort fields
        try {
            $bookings_sql = "SELECT * FROM bookings WHERE user_id = ? ORDER BY id DESC LIMIT 5";
            $stmt = $conn->prepare($bookings_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $bookings_result = $stmt->get_result();
            
            if ($bookings_result && $bookings_result->num_rows > 0) {
                $bookings_found = true;
                error_log("Found " . $bookings_result->num_rows . " bookings with user_id: $user_id");
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // If there was an error, log it and continue
            error_log("Error with first query: " . $e->getMessage());
        }
        
        // If no results, fall back to username
        if (!$bookings_found) {
            try {
                $bookings_sql = "SELECT * FROM bookings WHERE username = ? ORDER BY id DESC LIMIT 5";
                $stmt = $conn->prepare($bookings_sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $bookings_result = $stmt->get_result();
                
                if ($bookings_result && $bookings_result->num_rows > 0) {
                    $bookings_found = true;
                    error_log("Found " . $bookings_result->num_rows . " bookings with username: $username");
                }
                
                $stmt->close();
            } catch (Exception $e) {
                // If there was an error, log it
                error_log("Error with username query: " . $e->getMessage());
                
                // Try with a different sort field
                try {
                    $bookings_sql = "SELECT * FROM bookings WHERE username = ? LIMIT 5";
                    $stmt = $conn->prepare($bookings_sql);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $bookings_result = $stmt->get_result();
                    
                    if ($bookings_result && $bookings_result->num_rows > 0) {
                        $bookings_found = true;
                    }
                    
                    $stmt->close();
                } catch (Exception $e) {
                    // If still errors, give up
                    error_log("Error with final query: " . $e->getMessage());
                    $bookings_result = null;
                }
            }
        }
    } else {
        // Only have username
        try {
            $bookings_sql = "SELECT * FROM bookings WHERE username = ? ORDER BY id DESC LIMIT 5";
            $stmt = $conn->prepare($bookings_sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $bookings_result = $stmt->get_result();
            
            if ($bookings_result && $bookings_result->num_rows > 0) {
                $bookings_found = true;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            // If there was an error, try without the sort
            error_log("Error with username query: " . $e->getMessage());
            try {
                $bookings_sql = "SELECT * FROM bookings WHERE username = ? LIMIT 5";
                $stmt = $conn->prepare($bookings_sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $bookings_result = $stmt->get_result();
                
                if ($bookings_result && $bookings_result->num_rows > 0) {
                    $bookings_found = true;
                }
                
                $stmt->close();
            } catch (Exception $e) {
                // If still errors, give up
                error_log("Error with final query: " . $e->getMessage());
                $bookings_result = null;
            }
        }
    }
}

// Count total bookings - use the same approach as above
$booking_count = 0;
try {
    if ($user_id) {
        $count_sql = "SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = ?";
        $stmt = $conn->prepare($count_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $booking_count = $count_result->fetch_assoc()['total_bookings'];
        
        // If no bookings found with user_id, try username
        if ($booking_count == 0) {
            $stmt->close();
            $count_sql = "SELECT COUNT(*) as total_bookings FROM bookings WHERE username = ?";
            $stmt = $conn->prepare($count_sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $count_result = $stmt->get_result();
            $booking_count = $count_result->fetch_assoc()['total_bookings'];
        }
        $stmt->close();
    } else {
        $count_sql = "SELECT COUNT(*) as total_bookings FROM bookings WHERE username = ?";
        $stmt = $conn->prepare($count_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $booking_count = $count_result->fetch_assoc()['total_bookings'];
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error counting bookings: " . $e->getMessage());
    $booking_count = 0;
}

// Get destinations visited (unique destinations)
$destinations_count = 0;
try {
    if ($user_id) {
        $destinations_sql = "SELECT COUNT(DISTINCT destination) as total_destinations FROM bookings WHERE user_id = ?";
        $stmt = $conn->prepare($destinations_sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $destinations_result = $stmt->get_result();
        $destinations_count = $destinations_result->fetch_assoc()['total_destinations'];
        
        // If no destinations found with user_id, try username
        if ($destinations_count == 0) {
            $stmt->close();
            $destinations_sql = "SELECT COUNT(DISTINCT destination) as total_destinations FROM bookings WHERE username = ?";
            $stmt = $conn->prepare($destinations_sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $destinations_result = $stmt->get_result();
            $destinations_count = $destinations_result->fetch_assoc()['total_destinations'];
        }
        $stmt->close();
    } else {
        $destinations_sql = "SELECT COUNT(DISTINCT destination) as total_destinations FROM bookings WHERE username = ?";
        $stmt = $conn->prepare($destinations_sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $destinations_result = $stmt->get_result();
        $destinations_count = $destinations_result->fetch_assoc()['total_destinations'];
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Error counting destinations: " . $e->getMessage());
    $destinations_count = 0;
}

// Get upcoming trips
$upcoming_count = 0;
try {
    if ($user_id) {
        // Try with travel_date
        try {
            $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE user_id = ? AND travel_date > CURDATE()";
            $stmt = $conn->prepare($upcoming_sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $upcoming_result = $stmt->get_result();
            $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
            $stmt->close();
        } catch (Exception $e) {
            // Try with check_in_date as an alternative
            try {
                $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE user_id = ? AND check_in_date > CURDATE()";
                $stmt = $conn->prepare($upcoming_sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $upcoming_result = $stmt->get_result();
                $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                $stmt->close();
            } catch (Exception $e2) {
                // Just count all bookings if we can't filter by date
                try {
                    $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE user_id = ?";
                    $stmt = $conn->prepare($upcoming_sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $upcoming_result = $stmt->get_result();
                    $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                    $stmt->close();
                } catch (Exception $e3) {
                    error_log("Failed all attempts to count upcoming trips with user_id: " . $e3->getMessage());
                }
            }
        }
        
        // If no upcoming trips found with user_id, try username
        if ($upcoming_count == 0) {
            try {
                $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE username = ? AND travel_date > CURDATE()";
                $stmt = $conn->prepare($upcoming_sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $upcoming_result = $stmt->get_result();
                $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                $stmt->close();
            } catch (Exception $e) {
                // Try with check_in_date as an alternative
                try {
                    $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE username = ? AND check_in_date > CURDATE()";
                    $stmt = $conn->prepare($upcoming_sql);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $upcoming_result = $stmt->get_result();
                    $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                    $stmt->close();
                } catch (Exception $e2) {
                    // Just count all bookings if we can't filter by date
                    try {
                        $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE username = ?";
                        $stmt = $conn->prepare($upcoming_sql);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $upcoming_result = $stmt->get_result();
                        $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                        $stmt->close();
                    } catch (Exception $e3) {
                        error_log("Failed all attempts to count upcoming trips with username: " . $e3->getMessage());
                    }
                }
            }
        }
    } else {
        // Only have username
        try {
            $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE username = ? AND travel_date > CURDATE()";
            $stmt = $conn->prepare($upcoming_sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $upcoming_result = $stmt->get_result();
            $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
            $stmt->close();
        } catch (Exception $e) {
            // Try with check_in_date as an alternative
            try {
                $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE username = ? AND check_in_date > CURDATE()";
                $stmt = $conn->prepare($upcoming_sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $upcoming_result = $stmt->get_result();
                $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                $stmt->close();
            } catch (Exception $e2) {
                // Just count all bookings if we can't filter by date
                try {
                    $upcoming_sql = "SELECT COUNT(*) as upcoming_trips FROM bookings WHERE username = ?";
                    $stmt = $conn->prepare($upcoming_sql);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $upcoming_result = $stmt->get_result();
                    $upcoming_count = $upcoming_result->fetch_assoc()['upcoming_trips'];
                    $stmt->close();
                } catch (Exception $e3) {
                    error_log("Failed all attempts to count upcoming trips: " . $e3->getMessage());
                }
            }
        }
    }
} catch (Exception $e) {
    error_log("Error counting upcoming trips: " . $e->getMessage());
    $upcoming_count = 0;
}

// Calculate reward points (for demonstration)
$points = $booking_count * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Go Trip</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="public/image/logo.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/user_dashboard.css">
    <link rel="stylesheet" href="public/css/travel-alerts.css">
    <link rel="stylesheet" href="public/css/modern-additions.css">
    
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Satisfy&display=swap" rel="stylesheet">
    
    <!-- Chart.js for analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body class="dashboard-page">
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
        });
    </script>

    <!-- Navigation Bar -->
    <nav class="nav-bar">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="public/index.html">
                        <img src="public/image/logo.png" alt="Go Trip Logo" class="logo-img" style="width: 32px; height: 32px;">
                        <span>Go Trip</span>
                    </a>
                </div>
                <button class="menu-toggle" id="mobile-menu" aria-label="Toggle menu">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </button>
                <ul class="menu">
                    <li><a href="public/index.html"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="public/views/packages.html"><i class="fas fa-box"></i> Packages</a></li>
                    <li><a href="public/views/tours.html"><i class="fas fa-globe"></i> Tours</a></li>
                    <li><a href="public/views/trip-planner.html"><i class="fas fa-map"></i> Planner</a></li>
                    <li><a href="public/views/blog.html"><i class="fas fa-blog"></i> Blog</a></li>
                    <li><a href="public/views/about.html"><i class="fas fa-info"></i> About</a></li>
                    <li><a href="public/views/contact.html"><i class="fas fa-envelope"></i> Contact</a></li>
                    <li><a href="#" id="logoutLink" data-tooltip="Logout"><i class="fas fa-sign-out-alt"></i></a></li>
                    <li><a href="#" class="enable-alerts-btn" id="enableAlertsBtn" data-tooltip="Notifications"><i class="fas fa-bell"></i></a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container" id="dashboardContainer">
        <!-- Sidebar -->
        <aside class="dashboard-sidebar">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <div class="user-profile">
                <div class="user-avatar">
                    <img src="public/image/default-avatar.jpg" alt="User Profile" style="width: 60px; height: 60px; object-fit: cover;">
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p><?php echo htmlspecialchars($username); ?>@example.com</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="user_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="my_bookings.php"><i class="fas fa-ticket-alt"></i> <span>My Bookings</span></a></li>
                <li><a href="public/views/trip-planner.html"><i class="fas fa-map-marked-alt"></i> <span>Plan a Trip</span></a></li>
                <li><a href="wishlist.php"><i class="fas fa-heart"></i> <span>Wishlist</span></a></li>
                <li><a href="travel_history.php"><i class="fas fa-history"></i> <span>Travel History</span></a></li>
                <li><a href="rewards.php"><i class="fas fa-gift"></i> <span>Rewards</span></a></li>
                <li><a href="user_profile.php"><i class="fas fa-user-cog"></i> <span>Profile Settings</span></a></li>
                <li><a href="#" id="sidebarLogout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
                <p>Here's an overview of your travel activities and upcoming trips</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-suitcase"></i>
                    </div>
                    <div class="stat-data">
                        <h2 class="stat-value"><?php echo $booking_count; ?></h2>
                        <p class="stat-label">Total Bookings</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <div class="stat-data">
                        <h2 class="stat-value"><?php echo $destinations_count; ?></h2>
                        <p class="stat-label">Destinations Visited</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-plane-departure"></i>
                    </div>
                    <div class="stat-data">
                        <h2 class="stat-value"><?php echo $upcoming_count; ?></h2>
                        <p class="stat-label">Upcoming Trips</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <div class="stat-data">
                        <h2 class="stat-value"><?php echo number_format($points); ?></h2>
                        <p class="stat-label">Reward Points</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="bookings-list">
                <div class="list-header">
                    <h2>Recent Bookings</h2>
                    <a href="my_bookings.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <?php if ($bookings_result && $bookings_result->num_rows > 0) : ?>
                    <?php while ($booking = $bookings_result->fetch_assoc()) : 
                        // Determine status class
                        $statusClass = '';
                        $status = isset($booking['status']) ? $booking['status'] : 'confirmed';
                        
                        switch($status) {
                            case 'confirmed':
                                $statusClass = 'status-confirmed';
                                break;
                            case 'pending':
                                $statusClass = 'status-pending';
                                break;
                            case 'cancelled':
                                $statusClass = 'status-cancelled';
                                break;
                            default:
                                $statusClass = 'status-confirmed';
                        }
                        
                        // Get destination image based on destination name
                        $destination = strtolower($booking['destination']);
                        $dest_image = 'public/image/default-destination.jpg';
                        
                        if (strpos($destination, 'paris') !== false) {
                            $dest_image = 'public/image/paris.jpg';
                        } elseif (strpos($destination, 'tokyo') !== false) {
                            $dest_image = 'public/image/tokyo.jpg';
                        } elseif (strpos($destination, 'switzerland') !== false || strpos($destination, 'bern') !== false) {
                            $dest_image = 'public/image/switzerland.png';
                        } elseif (strpos($destination, 'canada') !== false || strpos($destination, 'vancouver') !== false) {
                            $dest_image = 'public/image/canada.png';
                        } elseif (strpos($destination, 'korea') !== false || strpos($destination, 'seoul') !== false) {
                            $dest_image = 'public/image/korea.png';
                        } elseif (strpos($destination, 'monaco') !== false) {
                            $dest_image = 'public/image/monaco.jpg';
                        }
                    ?>
                    <div class="booking-card">
                        <div class="booking-img">
                            <img src="<?php echo $dest_image; ?>" alt="<?php echo htmlspecialchars($booking['destination']); ?>">
                        </div>
                        <div class="booking-details">
                            <h3 class="booking-title"><?php echo htmlspecialchars($booking['destination']); ?></h3>
                            <div class="booking-meta">
                                <span><i class="far fa-calendar-alt"></i> 
                                <?php 
                                    // Handle different date field names
                                    if (isset($booking['travel_date'])) {
                                        echo date('d M Y', strtotime($booking['travel_date']));
                                    } elseif (isset($booking['check_in_date'])) {
                                        echo date('d M Y', strtotime($booking['check_in_date']));
                                    } else {
                                        echo "Date not available";
                                    }
                                ?></span>
                                <span><i class="fas fa-users"></i> <?php echo isset($booking['travelers']) ? $booking['travelers'] : (isset($booking['num_travelers']) ? $booking['num_travelers'] : '1'); ?> Travelers</span>
                                <span><i class="fas fa-building"></i> <?php echo isset($booking['room_type']) ? ucfirst($booking['room_type']) : 'Standard'; ?> Room</span>
                            </div>
                            <div class="booking-footer">
                                <span class="booking-ref">Booking ID: <?php echo isset($booking['booking_ref']) ? $booking['booking_ref'] : (isset($booking['booking_reference']) ? $booking['booking_reference'] : 'N/A'); ?></span>
                                <span class="booking-status <?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                            </div>
                        </div>
                        <div class="booking-amount">
                            <span class="amount"><?php 
                                // Format amount with currency symbol if needed
                                $amount = isset($booking['total_amount']) ? $booking['total_amount'] : (isset($booking['total_price']) ? $booking['total_price'] : '0');
                                if (strpos($amount, '₹') === false && strpos($amount, '$') === false) {
                                    echo '₹ ' . number_format((float)$amount, 2);
                                } else {
                                    echo $amount;
                                }
                            ?></span>
                            <a href="view_booking.php?id=<?php echo isset($booking['booking_ref']) ? $booking['booking_ref'] : (isset($booking['booking_reference']) ? $booking['booking_reference'] : (isset($booking['id']) ? $booking['id'] : '0')); ?>" class="view-btn">View Details</a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-bookings">
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <h3>No Bookings Yet</h3>
                            <p>You haven't made any travel bookings yet. Start planning your next adventure!</p>
                            <a href="public/views/packages.html" class="btn-primary">Explore Packages</a>
                        </div>
                    </div>
                    
                    <!-- Fallback display for bookings from localStorage -->
                    <div id="fallback-booking" style="display: none; margin-top: 20px; border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #fcfcfc; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                        <h3 style="margin-top: 0; color: #333; font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Recent Booking</h3>
                        <div id="fallback-booking-details" style="display: flex; align-items: center; margin-top: 10px;">
                            <div style="flex: 0 0 120px; height: 80px; background: #eee; border-radius: 5px; margin-right: 15px; overflow: hidden;">
                                <img id="fallback-booking-img" src="" alt="Destination" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                            <div style="flex: 1;">
                                <h4 id="fallback-booking-destination" style="margin: 0 0 8px 0; font-size: 16px; color: #222;"></h4>
                                <div style="font-size: 14px; color: #666; margin-bottom: 10px;">
                                    <span id="fallback-booking-date" style="margin-right: 15px;"><i class="far fa-calendar-alt"></i> </span>
                                    <span id="fallback-booking-ref" style="font-weight: bold;"></span>
                                </div>
                                <div>
                                    <span style="background: #e6f7e6; color: #2e7d32; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500;">Confirmed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Booking Statistics -->
            <div class="analytics-container">
                <div class="analytics-card">
                    <h2>Travel Statistics</h2>
                    <canvas id="travelChart"></canvas>
                </div>
                
                <div class="recommendations-card">
                    <h2>Recommended for You</h2>
                    <div class="recommendation-item">
                        <img src="public/image/paris.jpg" alt="Paris" style="width: 100px; height: 70px; object-fit: cover; border-radius: 5px;">
                        <div class="rec-details">
                            <h3>Paris, France</h3>
                            <p>Based on your previous trips</p>
                            <a href="public/views/destination-paris.html" class="view-link">Explore <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <div class="recommendation-item">
                        <img src="public/image/tokyo.jpg" alt="Tokyo" style="width: 100px; height: 70px; object-fit: cover; border-radius: 5px;">
                        <div class="rec-details">
                            <h3>Tokyo, Japan</h3>
                            <p>Trending destination</p>
                            <a href="public/views/destination-tokyo.html" class="view-link">Explore <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Footer -->
    <footer class="dashboard-footer">
        <p>&copy; 2024 Go Trip. All rights reserved.</p>
    </footer>

    <!-- Scripts -->
    <script src="public/sounds/notification.js"></script>
    <script src="public/js/travel-alerts.js"></script>
    
    <!-- Custom JavaScript for interaction -->
    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('dashboardContainer').classList.toggle('sidebar-collapsed');
        });
        
        // Mobile menu toggle
        document.getElementById('mobile-menu').addEventListener('click', function() {
            document.querySelector('.menu').classList.toggle('active');
            this.classList.toggle('is-active');
        });
        
        // Logout functionality
        function logout() {
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('username');
            window.location.href = 'public/index.html';
        }
        
        document.getElementById('logoutLink').addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
        
        document.getElementById('sidebarLogout').addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
        
        // Travel Chart
        const ctx = document.getElementById('travelChart');
        
        // Make sure Chart.js is loaded before creating the chart
        if (window.Chart && ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Completed', 'Upcoming', 'Cancelled'],
                    datasets: [{
                        data: [<?php echo max(0, $booking_count - $upcoming_count); ?>, <?php echo $upcoming_count; ?>, 0],
                        backgroundColor: [
                            '#4CAF50',
                            '#2196F3',
                            '#F44336'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    let value = context.raw || 0;
                                    let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        } else {
            console.error('Chart.js not loaded or canvas element not found');
            // Create a fallback if Chart.js is not available
            if (ctx) {
                const fallbackStats = document.createElement('div');
                fallbackStats.className = 'fallback-stats';
                fallbackStats.innerHTML = `
                    <div class="stat-item">
                        <div class="stat-label">Completed</div>
                        <div class="stat-value"><?php echo max(0, $booking_count - $upcoming_count); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Upcoming</div>
                        <div class="stat-value"><?php echo $upcoming_count; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Cancelled</div>
                        <div class="stat-value">0</div>
                    </div>
                `;
                
                // Replace canvas with fallback
                ctx.parentNode.replaceChild(fallbackStats, ctx);
                
                // Add some inline styles for the fallback
                const style = document.createElement('style');
                style.textContent = `
                    .fallback-stats {
                        display: flex;
                        justify-content: space-around;
                        text-align: center;
                        margin: 20px 0;
                    }
                    .stat-item {
                        padding: 15px;
                    }
                    .stat-value {
                        font-size: 24px;
                        font-weight: bold;
                        margin-bottom: 5px;
                    }
                    .stat-label {
                        color: #666;
                    }
                `;
                document.head.appendChild(style);
            }
        }

        // Check for booking data in localStorage as a fallback
        document.addEventListener('DOMContentLoaded', function() {
            const bookingRef = localStorage.getItem('lastBookingRef');
            const destination = localStorage.getItem('lastBookingDestination');
            const travelDate = localStorage.getItem('lastBookingDate');
            const totalAmount = localStorage.getItem('lastBookingAmount');
            const roomType = localStorage.getItem('lastBookingRoomType');
            const travelers = localStorage.getItem('lastBookingTravelers');
            
            // URL parameter check for new booking or debug mode
            const urlParams = new URLSearchParams(window.location.search);
            const debugMode = urlParams.get('booking_debug');
            const newBooking = urlParams.get('new_booking');
            
            // Show fallback booking if we have data and either there are no bookings in DB or it's a new booking
            const hasNewBooking = newBooking || (bookingRef && destination && (document.querySelector('.no-bookings') || debugMode === '1'));
            
            if (hasNewBooking) {
                // Determine destination image
                let destImage = 'public/image/default-destination.jpg';
                const lowerDestination = (destination || '').toLowerCase();
                
                if (lowerDestination.includes('paris')) {
                    destImage = 'public/image/paris.jpg';
                } else if (lowerDestination.includes('tokyo')) {
                    destImage = 'public/image/tokyo.jpg';
                } else if (lowerDestination.includes('switzerland') || lowerDestination.includes('bern')) {
                    destImage = 'public/image/switzerland.png';
                } else if (lowerDestination.includes('canada') || lowerDestination.includes('vancouver')) {
                    destImage = 'public/image/canada.png';
                } else if (lowerDestination.includes('korea') || lowerDestination.includes('seoul')) {
                    destImage = 'public/image/korea.png';
                } else if (lowerDestination.includes('monaco')) {
                    destImage = 'public/image/monaco.jpg';
                }
                
                // Format date for display
                let formattedDate = travelDate;
                try {
                    formattedDate = new Date(travelDate).toLocaleDateString('en-US', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                } catch(e) {
                    console.log('Error formatting date', e);
                }
                
                // Set fallback booking details
                document.getElementById('fallback-booking-destination').textContent = destination || 'Your Destination';
                document.getElementById('fallback-booking-date').innerHTML = '<i class="far fa-calendar-alt"></i> ' + (formattedDate || 'Upcoming');
                document.getElementById('fallback-booking-ref').textContent = 'Booking ID: ' + (bookingRef || 'N/A');
                document.getElementById('fallback-booking-img').src = destImage;
                
                // Add room type and travelers if available
                if (roomType || travelers) {
                    const detailsDiv = document.createElement('div');
                    detailsDiv.style.fontSize = '14px';
                    detailsDiv.style.color = '#666';
                    detailsDiv.style.marginTop = '8px';
                    
                    if (roomType) {
                        const roomSpan = document.createElement('span');
                        roomSpan.innerHTML = '<i class="fas fa-building"></i> ' + roomType.charAt(0).toUpperCase() + roomType.slice(1);
                        roomSpan.style.marginRight = '15px';
                        detailsDiv.appendChild(roomSpan);
                    }
                    
                    if (travelers) {
                        const travelerSpan = document.createElement('span');
                        travelerSpan.innerHTML = '<i class="fas fa-users"></i> ' + travelers + ' Traveler' + (travelers > 1 ? 's' : '');
                        detailsDiv.appendChild(travelerSpan);
                    }
                    
                    // Add amount if available
                    if (totalAmount) {
                        const amountDiv = document.createElement('div');
                        amountDiv.style.fontWeight = 'bold';
                        amountDiv.style.marginTop = '8px';
                        amountDiv.textContent = totalAmount;
                        detailsDiv.appendChild(amountDiv);
                    }
                    
                    // Add to fallback booking details
                    const fallbackDetails = document.getElementById('fallback-booking-details');
                    const lastChild = fallbackDetails.children[1];
                    lastChild.insertBefore(detailsDiv, lastChild.lastElementChild);
                }
                
                // Only show fallback if no bookings were found in the database
                const noBookingsDiv = document.querySelector('.no-bookings');
                if (noBookingsDiv) {
                    document.getElementById('fallback-booking').style.display = 'block';
                    
                    // Update the "No Bookings" message to be more helpful if this is a new booking
                    if (newBooking) {
                        const emptyStateH3 = noBookingsDiv.querySelector('h3');
                        const emptyStateP = noBookingsDiv.querySelector('p');
                        
                        if (emptyStateH3) {
                            emptyStateH3.textContent = 'Your Booking is Processing';
                        }
                        
                        if (emptyStateP) {
                            emptyStateP.textContent = 'Your new booking is being processed. It may take a moment to appear in your account. You can view the details below.';
                        }
                    }
                } else if (newBooking) {
                    // If there are already bookings but this is a new one, still show the fallback
                    const fallbackElement = document.getElementById('fallback-booking');
                    if (fallbackElement) {
                        fallbackElement.style.marginTop = '20px';
                        fallbackElement.style.display = 'block';
                        
                        // Add a heading to indicate this is a new booking
                        const headingElement = fallbackElement.querySelector('h3');
                        if (headingElement) {
                            headingElement.textContent = 'Your New Booking';
                        }
                        
                        // Try to add it after the booking list
                        const bookingsList = document.querySelector('.bookings-list');
                        if (bookingsList) {
                            bookingsList.appendChild(fallbackElement);
                        }
                    }
                }
                
                // If this is a new booking, attempt to reload the page once after 5 seconds
                // to try and get the booking from the database
                if (newBooking && !debugMode) {
                    const hasReloaded = sessionStorage.getItem('dashboard_reloaded');
                    if (!hasReloaded) {
                        sessionStorage.setItem('dashboard_reloaded', 'true');
                        setTimeout(function() {
                            window.location.href = 'user_dashboard.php';
                        }, 5000);
                    }
                }
            }
        });
    </script>
</body>
</html>