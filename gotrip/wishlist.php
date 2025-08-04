<?php
// wishlist.php - Display user wishlist
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get wishlist for the user (create this table in your database)
// $sql = "SELECT * FROM wishlist WHERE user_id = $user_id";
// $result = $conn->query($sql);
$hasWishlist = false; // Change to true when database is ready
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist - Go Trip</title>
    
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
                    <img src="public/image/default-avatar.jpg" alt="User Profile">
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p>Travel Enthusiast</p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="my_bookings.php"><i class="fas fa-ticket-alt"></i> <span>My Bookings</span></a></li>
                <li><a href="public/views/trip-planner.html"><i class="fas fa-map-marked-alt"></i> <span>Plan a Trip</span></a></li>
                <li><a href="wishlist.php" class="active"><i class="fas fa-heart"></i> <span>Wishlist</span></a></li>
                <li><a href="travel_history.php"><i class="fas fa-history"></i> <span>Travel History</span></a></li>
                <li><a href="rewards.php"><i class="fas fa-gift"></i> <span>Rewards</span></a></li>
                <li><a href="user_profile.php"><i class="fas fa-user-cog"></i> <span>Profile Settings</span></a></li>
                <li><a href="#" id="sidebarLogout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Your Wishlist</h1>
                <p>Save your dream destinations and plan future adventures</p>
            </div>
            
            <!-- Wishlist Items -->
            <div class="bookings-list">
                <div class="list-header">
                    <h2>Saved Destinations</h2>
                </div>
                
                <?php if ($hasWishlist) : ?>
                <!-- Example wishlist items - replace with database data when available -->
                <div class="booking-card">
                    <div class="booking-img">
                        <img src="public/image/paris.jpg" alt="Paris, France">
                    </div>
                    <div class="booking-details">
                        <h3 class="booking-title">Paris, France</h3>
                        <div class="booking-meta">
                            <span><i class="far fa-clock"></i> 4 Days</span>
                            <span><i class="fas fa-star"></i> 4.8 Rating</span>
                            <span><i class="far fa-calendar-alt"></i> Added on May 10, 2024</span>
                        </div>
                        <p class="booking-price">₹ 1,49,999.00</p>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="destination-paris.html" class="btn btn-primary">View Details</a>
                        <button class="btn btn-secondary"><i class="fas fa-trash"></i> Remove</button>
                    </div>
                </div>
                
                <div class="booking-card">
                    <div class="booking-img">
                        <img src="public/image/tokyo.jpg" alt="Tokyo, Japan">
                    </div>
                    <div class="booking-details">
                        <h3 class="booking-title">Tokyo, Japan</h3>
                        <div class="booking-meta">
                            <span><i class="far fa-clock"></i> 5 Days</span>
                            <span><i class="fas fa-star"></i> 4.9 Rating</span>
                            <span><i class="far fa-calendar-alt"></i> Added on May 8, 2024</span>
                        </div>
                        <p class="booking-price">₹ 1,69,999.00</p>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="destination-tokyo.html" class="btn btn-primary">View Details</a>
                        <button class="btn btn-secondary"><i class="fas fa-trash"></i> Remove</button>
                    </div>
                </div>
                <?php else : ?>
                <div class="empty-state">
                    <i class="far fa-heart"></i>
                    <h3>Your wishlist is empty</h3>
                    <p>Save destinations you love to your wishlist for easy access later</p>
                    <a href="packages.html" class="btn btn-primary">Explore Destinations</a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recommended Packages -->
            <div class="bookings-list">
                <div class="list-header">
                    <h2>Recommended For You</h2>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px;">
                    <div class="card" style="height: 100%;">
                        <div style="height: 200px; overflow: hidden; border-radius: 8px 8px 0 0;">
                            <img src="public/image/switzerland.png" alt="Switzerland" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 15px;">
                            <h3 style="margin-top: 0;">Switzerland</h3>
                            <div style="display: flex; justify-content: space-between; margin: 10px 0;">
                                <span><i class="far fa-clock"></i> 5 Days</span>
                                <span><i class="fas fa-star" style="color: #ffc107;"></i> 4.9</span>
                            </div>
                            <p style="font-weight: bold; color: #ff7d00; margin: 15px 0;">₹ 1,59,999.00</p>
                            <button class="btn btn-primary" style="width: 100%;">Add to Wishlist</button>
                        </div>
                    </div>
                    
                    <div class="card" style="height: 100%;">
                        <div style="height: 200px; overflow: hidden; border-radius: 8px 8px 0 0;">
                            <img src="public/image/canada.png" alt="Canada" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 15px;">
                            <h3 style="margin-top: 0;">Canada</h3>
                            <div style="display: flex; justify-content: space-between; margin: 10px 0;">
                                <span><i class="far fa-clock"></i> 5 Days</span>
                                <span><i class="fas fa-star" style="color: #ffc107;"></i> 4.9</span>
                            </div>
                            <p style="font-weight: bold; color: #ff7d00; margin: 15px 0;">₹ 1,99,999.00</p>
                            <button class="btn btn-primary" style="width: 100%;">Add to Wishlist</button>
                        </div>
                    </div>
                    
                    <div class="card" style="height: 100%;">
                        <div style="height: 200px; overflow: hidden; border-radius: 8px 8px 0 0;">
                            <img src="public/image/monaco.jpg" alt="Monaco" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div style="padding: 15px;">
                            <h3 style="margin-top: 0;">Monaco</h3>
                            <div style="display: flex; justify-content: space-between; margin: 10px 0;">
                                <span><i class="far fa-clock"></i> 4 Days</span>
                                <span><i class="fas fa-star" style="color: #ffc107;"></i> 4.7</span>
                            </div>
                            <p style="font-weight: bold; color: #ff7d00; margin: 15px 0;">₹ 1,39,999.00</p>
                            <button class="btn btn-primary" style="width: 100%;">Add to Wishlist</button>
                        </div>
                    </div>
                </div>
            </div>
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
// Close database connection
if (isset($conn)) {
    $conn->close();
}
?> 