<?php
// my_bookings.php - Display user bookings
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get all bookings for the user
$sql = "SELECT * FROM bookings WHERE ";

// Check if we should use user_id for lookup
if (!empty($user_id)) {
    $sql .= "user_id = $user_id ";
} else {
    // No user_id available - we'll show a message later
    error_log("No user_id available to retrieve bookings");
    $sql .= "1=0"; // This will return no results
}

// Order by ID instead of booking_date which doesn't exist
$sql .= " ORDER BY id DESC";

// Add detailed logging
error_log("Bookings query: $sql for user_id: $user_id, username: $username");

$result = $conn->query($sql);

// Check and log number of bookings found
if ($result) {
    error_log("Found " . $result->num_rows . " bookings for user");
} else {
    error_log("Error retrieving bookings: " . $conn->error);
}

// Get user data
$profile_sql = "SELECT * FROM users WHERE id = $user_id";
$profile_result = $conn->query($profile_sql);
$user_data = $profile_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Go Trip</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="./image/logo.png">
    
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

    <style>
        /* Navigation bar styling */
        .nav-bar {
            background-color: #1a2b49;
        }
        .nav-bar .logo span, .nav-bar .menu li a {
            color: #ffffff;
        }
        .nav-bar .menu li a:hover {
            color: #ff7d00;
        }
        
        /* Fix navigation alignment */
        .nav-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
        }
        .menu {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }
        .menu li {
            list-style: none;
            margin-left: 20px;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .logo span {
            margin-left: 10px;
            font-size: 20px;
            font-weight: 600;
        }
        
        .dashboard-container {
            margin-bottom: 60px; /* Add space between dashboard and footer */
        }
    </style>
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
                        <img src="public/image/logo.png" alt="Go Trip Logo" class="logo-img">
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
                    <img src="<?php echo !empty($user_data['profile_image']) ? $user_data['profile_image'] : '/image/default-avatar.jpg'; ?>" alt="User Profile">
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p><?php echo !empty($user_data['email']) ? htmlspecialchars($user_data['email']) : 'No email provided'; ?></p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="my_bookings.php" class="active"><i class="fas fa-ticket-alt"></i> <span>My Bookings</span></a></li>
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
                <h1>My Bookings</h1>
                <p>View and manage all your travel bookings</p>
            </div>
            
            <!-- Booking Filters -->
            <div class="bookings-filters" style="display: flex; gap: 15px; margin-bottom: 20px;">
                <button class="btn btn-secondary active">All Bookings</button>
                <button class="btn btn-secondary">Upcoming</button>
                <button class="btn btn-secondary">Completed</button>
                <button class="btn btn-secondary">Cancelled</button>
                
                <div style="margin-left: auto;">
                    <select class="form-control" style="padding: 8px 15px;">
                        <option>Sort by: Latest First</option>
                        <option>Sort by: Oldest First</option>
                        <option>Sort by: Price (High to Low)</option>
                        <option>Sort by: Price (Low to High)</option>
                    </select>
                </div>
            </div>
            
            <!-- Bookings List -->
            <div class="bookings-list">
                <?php if ($result && $result->num_rows > 0) : ?>
                    <?php while ($booking = $result->fetch_assoc()) : 
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
                        $destination = isset($booking['destination']) ? strtolower($booking['destination']) : '';
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
                            <img src="<?php echo $dest_image; ?>" alt="<?php echo isset($booking['destination']) ? htmlspecialchars($booking['destination']) : 'Destination'; ?>">
                        </div>
                        <div class="booking-details">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <h3 class="booking-title"><?php echo isset($booking['destination']) ? htmlspecialchars($booking['destination']) : 'Unknown Destination'; ?></h3>
                                <div class="booking-status <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </div>
                            </div>
                            
                            <div class="booking-meta">
                                <span><i class="far fa-calendar-alt"></i> 
                                    <?php 
                                    // Use check_in_date and check_out_date if available, otherwise fall back to travel_date
                                    if (isset($booking['check_in_date']) && isset($booking['check_out_date'])) {
                                        echo date('d M Y', strtotime($booking['check_in_date'])) . ' - ' . date('d M Y', strtotime($booking['check_out_date']));
                                    } elseif (isset($booking['travel_date'])) {
                                        echo date('d M Y', strtotime($booking['travel_date']));
                                    } else {
                                        echo 'Date not available';
                                    }
                                    ?>
                                </span>
                                <span><i class="fas fa-users"></i> <?php echo isset($booking['travelers']) ? htmlspecialchars($booking['travelers']) : '1'; ?> Travelers</span>
                                <span><i class="fas fa-tag"></i> Booking #<?php echo isset($booking['booking_ref']) ? htmlspecialchars($booking['booking_ref']) : 'N/A'; ?></span>
                                <?php if(isset($booking['transportation'])) : ?>
                                <span><i class="fas fa-plane"></i> <?php echo ucfirst(htmlspecialchars($booking['transportation'])); ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                                <p class="booking-price">Total: ₹<?php echo number_format($booking['total_price'], 2); ?></p>
                                
                                <div style="display: flex; gap: 10px;">
                                    <a href="view_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                    
                                    <?php if ($status !== 'cancelled') : ?>
                                    <button class="btn btn-secondary btn-sm">Cancel Booking</button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline btn-sm"><i class="fas fa-download"></i> Invoice</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="empty-state">
                        <i class="fas fa-suitcase-rolling"></i>
                        <h3>No bookings found</h3>
                        <p>You don't have any bookings yet. Start planning your dream vacation today!</p>
                        <a href="public/views/packages.html" class="btn btn-primary">Explore Packages</a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($result && $result->num_rows > 5) : ?>
            <div class="pagination" style="display: flex; justify-content: center; margin-top: 30px;">
                <a href="#" class="page-link disabled">&laquo;</a>
                <a href="#" class="page-link active">1</a>
                <a href="#" class="page-link">2</a>
                <a href="#" class="page-link">3</a>
                <a href="#" class="page-link">&raquo;</a>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <!-- Footer (simplified) -->
    <footer class="footer">
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-content">
                    <p>&copy; 2024 Go Trip. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const dashboardContainer = document.getElementById('dashboardContainer');
        
        sidebarToggle.addEventListener('click', function() {
            dashboardContainer.classList.toggle('sidebar-collapsed');
            
            // Change direction of arrow icon
            const icon = this.querySelector('i');
            if (dashboardContainer.classList.contains('sidebar-collapsed')) {
                icon.classList.replace('fa-chevron-left', 'fa-chevron-right');
            } else {
                icon.classList.replace('fa-chevron-right', 'fa-chevron-left');
            }
        });
        
        // Filter buttons
        const filterButtons = document.querySelectorAll('.bookings-filters .btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                // Filter logic would go here
            });
        });
        
        // Logout functionality
        const logoutLink = document.getElementById('logoutLink');
        const sidebarLogout = document.getElementById('sidebarLogout');
        
        function logout() {
            localStorage.removeItem('isLoggedIn');
            localStorage.removeItem('username');
            window.location.href = 'public/index.html';
        }
        
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
        
        sidebarLogout.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    </script>

    <!-- Scripts -->
    <script src="public/sounds/notification.js"></script>
    <script src="public/js/travel-alerts.js"></script>
</body>
</html>

<?php
$conn->close();
?> 