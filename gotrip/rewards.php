<?php
// rewards.php - Display user rewards
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get user data
$profile_sql = "SELECT * FROM users WHERE id = $user_id";
$profile_result = $conn->query($profile_sql);
$user_data = $profile_result->fetch_assoc();

// Placeholder rewards data
$reward_points = 1250;
$member_level = "Silver";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Rewards - Go Trip</title>
    
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
        
        /* Rewards specific styles */
        .rewards-summary {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .reward-card {
            flex: 1;
            min-width: 250px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #e0e0e0;
        }
        
        .reward-card i {
            font-size: 40px;
            color: #ff7d00;
            margin-bottom: 15px;
        }
        
        .reward-card h3 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .reward-card p {
            font-size: 24px;
            font-weight: 600;
            color: #1a2b49;
        }
        
        .rewards-progress {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .rewards-progress h2 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .progress-bar-container {
            height: 20px;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin-bottom: 10px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #ff7d00;
            border-radius: 10px;
            width: 50%; /* Adjust based on actual progress */
        }
        
        .progress-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .coupon-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .coupon {
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            overflow: hidden;
            background-color: #f9f9f9;
        }
        
        .coupon h3 {
            margin-top: 0;
            color: #1a2b49;
        }
        
        .coupon p {
            color: #666;
            margin-bottom: 10px;
        }
        
        .coupon .discount {
            font-size: 24px;
            font-weight: 700;
            color: #ff7d00;
            margin: 15px 0;
        }
        
        .coupon .code {
            background-color: #e0e0e0;
            padding: 8px 15px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 16px;
            margin: 10px 0;
            display: inline-block;
        }
        
        .coupon .expiry {
            font-size: 12px;
            color: #999;
        }
        
        .coupon button {
            margin-top: 15px;
        }
        
        .coupon::after {
            content: "";
            position: absolute;
            top: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            background-color: #1a2b49;
            transform: rotate(45deg);
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
                    <img src="<?php echo !empty($user_data['profile_image']) ? $user_data['profile_image'] : './image/default-avatar.jpg'; ?>" alt="User Profile">
                </div>
                <div class="user-info">
                    <h3><?php echo htmlspecialchars($username); ?></h3>
                    <p><?php echo !empty($user_data['email']) ? htmlspecialchars($user_data['email']) : 'No email provided'; ?></p>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="user_dashboard.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
                <li><a href="my_bookings.php"><i class="fas fa-ticket-alt"></i> <span>My Bookings</span></a></li>
                <li><a href="public/views/trip-planner.html"><i class="fas fa-map-marked-alt"></i> <span>Plan a Trip</span></a></li>
                <li><a href="wishlist.php"><i class="fas fa-heart"></i> <span>Wishlist</span></a></li>
                <li><a href="travel_history.php"><i class="fas fa-history"></i> <span>Travel History</span></a></li>
                <li><a href="rewards.php" class="active"><i class="fas fa-gift"></i> <span>Rewards</span></a></li>
                <li><a href="user_profile.php"><i class="fas fa-user-cog"></i> <span>Profile Settings</span></a></li>
                <li><a href="#" id="sidebarLogout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Your Rewards</h1>
                <p>Earn points with every booking and redeem exclusive benefits</p>
            </div>
            
            <!-- Rewards Summary -->
            <div class="rewards-summary">
                <div class="reward-card">
                    <i class="fas fa-trophy"></i>
                    <h3>Member Level</h3>
                    <p><?php echo $member_level; ?></p>
                </div>
                
                <div class="reward-card">
                    <i class="fas fa-coins"></i>
                    <h3>Total Points</h3>
                    <p><?php echo number_format($reward_points); ?></p>
                </div>
                
                <div class="reward-card">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>Available Coupons</h3>
                    <p>3</p>
                </div>
                
                <div class="reward-card">
                    <i class="fas fa-award"></i>
                    <h3>Next Milestone</h3>
                    <p>Gold (750 points to go)</p>
                </div>
            </div>
            
            <!-- Rewards Progress -->
            <div class="rewards-progress">
                <h2>Membership Progress</h2>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: 50%;"></div>
                </div>
                <div class="progress-labels">
                    <span>Silver (1,000)</span>
                    <span>Gold (2,000)</span>
                    <span>Platinum (5,000)</span>
                </div>
                
                <h3>Member Benefits</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; margin-top: 20px;">
                    <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px;">
                        <h4 style="margin-top: 0;"><i class="fas fa-star" style="color: #C0C0C0;"></i> Silver Benefits</h4>
                        <ul style="padding-left: 20px;">
                            <li>5% discount on bookings</li>
                            <li>Early booking access</li>
                            <li>Free cancellation up to 48 hours</li>
                            <li>Priority customer support</li>
                        </ul>
                    </div>
                    
                    <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px;">
                        <h4 style="margin-top: 0;"><i class="fas fa-star" style="color: #FFD700;"></i> Gold Benefits</h4>
                        <ul style="padding-left: 20px;">
                            <li>10% discount on bookings</li>
                            <li>Room upgrades when available</li>
                            <li>Free cancellation up to 24 hours</li>
                            <li>Exclusive seasonal offers</li>
                            <li>Dedicated support line</li>
                        </ul>
                    </div>
                    
                    <div style="border: 1px solid #e0e0e0; border-radius: 8px; padding: 15px;">
                        <h4 style="margin-top: 0;"><i class="fas fa-star" style="color: #b9f2ff;"></i> Platinum Benefits</h4>
                        <ul style="padding-left: 20px;">
                            <li>15% discount on bookings</li>
                            <li>Guaranteed room upgrades</li>
                            <li>Free airport transfers</li>
                            <li>Welcome gifts at destination</li>
                            <li>Exclusive access to premium properties</li>
                            <li>Personal travel concierge</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Available Coupons -->
            <div class="rewards-progress">
                <h2>Your Coupons</h2>
                <p>Special offers and discounts you can redeem on your next booking</p>
                
                <div class="coupon-list">
                    <div class="coupon">
                        <h3>Welcome Bonus</h3>
                        <p>For being a valued member</p>
                        <div class="discount">10% OFF</div>
                        <p>On your next booking</p>
                        <div class="code">WELCOME10</div>
                        <p class="expiry">Valid until: Dec 31, 2024</p>
                        <button class="btn btn-primary">Use Now</button>
                    </div>
                    
                    <div class="coupon">
                        <h3>Birthday Special</h3>
                        <p>Happy Birthday month!</p>
                        <div class="discount">₹ 2,000 OFF</div>
                        <p>On bookings over ₹ 20,000</p>
                        <div class="code">BDAYGIFT</div>
                        <p class="expiry">Valid until: July 31, 2024</p>
                        <button class="btn btn-primary">Use Now</button>
                    </div>
                    
                    <div class="coupon">
                        <h3>Weekend Getaway</h3>
                        <p>For weekend bookings</p>
                        <div class="discount">15% OFF</div>
                        <p>On Friday-Sunday stays</p>
                        <div class="code">WEEKEND15</div>
                        <p class="expiry">Valid until: Aug 31, 2024</p>
                        <button class="btn btn-primary">Use Now</button>
                    </div>
                </div>
            </div>
            
            <!-- How to Earn -->
            <div class="rewards-progress">
                <h2>How to Earn Points</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;">
                    <div style="text-align: center; padding: 15px;">
                        <i class="fas fa-suitcase" style="font-size: 40px; color: #ff7d00; margin-bottom: 15px;"></i>
                        <h3>Book a Trip</h3>
                        <p>Earn 1 point per ₹100 spent on bookings</p>
                    </div>
                    
                    <div style="text-align: center; padding: 15px;">
                        <i class="fas fa-user-plus" style="font-size: 40px; color: #ff7d00; margin-bottom: 15px;"></i>
                        <h3>Refer Friends</h3>
                        <p>Earn 500 points for each friend who books</p>
                    </div>
                    
                    <div style="text-align: center; padding: 15px;">
                        <i class="fas fa-star" style="font-size: 40px; color: #ff7d00; margin-bottom: 15px;"></i>
                        <h3>Write Reviews</h3>
                        <p>Earn 100 points for each review</p>
                    </div>
                    
                    <div style="text-align: center; padding: 15px;">
                        <i class="fas fa-camera" style="font-size: 40px; color: #ff7d00; margin-bottom: 15px;"></i>
                        <h3>Share Photos</h3>
                        <p>Earn 50 points for sharing trip photos</p>
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