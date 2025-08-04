<?php
// travel_history.php - Display user travel history
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

// Get past trips data
// In actual implementation, this would query a travel_history table
$hasHistory = true; // Change to true when database is ready
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel History - Go Trip</title>
    
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
        
        /* Travel History specific styles */
        .history-stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
        }
        
        .stat-card i {
            font-size: 32px;
            color: #ff7d00;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 28px;
            font-weight: 700;
            color: #1a2b49;
            margin: 10px 0 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .travel-map {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .map-container {
            height: 400px;
            background-color: #f0f0f0;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        }
        
        .map-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: #666;
        }
        
        .map-placeholder i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ccc;
        }
        
        .history-timeline {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 50px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e0e0e0;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 100px;
            padding-bottom: 30px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-dot {
            position: absolute;
            left: 44px;
            top: 0;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background-color: #ff7d00;
            border: 3px solid white;
        }
        
        .timeline-date {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            text-align: right;
            font-size: 14px;
            font-weight: 600;
            color: #666;
        }
        
        .timeline-content {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .timeline-content h3 {
            margin-top: 0;
            color: #1a2b49;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 5px;
        }
        
        .badge-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .badge-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        .badge-warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .trip-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .gallery-img {
            border-radius: 4px;
            overflow: hidden;
            height: 120px;
        }
        
        .gallery-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }
        
        .gallery-img:hover img {
            transform: scale(1.05);
        }
        
        .trip-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin: 15px 0;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            color: #666;
            font-size: 14px;
        }
        
        .meta-item i {
            margin-right: 5px;
            color: #ff7d00;
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
                <li><a href="travel_history.php" class="active"><i class="fas fa-history"></i> <span>Travel History</span></a></li>
                <li><a href="rewards.php"><i class="fas fa-gift"></i> <span>Rewards</span></a></li>
                <li><a href="user_profile.php"><i class="fas fa-user-cog"></i> <span>Profile Settings</span></a></li>
                <li><a href="#" id="sidebarLogout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Your Travel History</h1>
                <p>Track all your past travels and explore your journey statistics</p>
            </div>
            
            <!-- Travel Stats -->
            <div class="history-stats">
                <div class="stat-card">
                    <i class="fas fa-plane"></i>
                    <div class="number">5</div>
                    <div class="label">Countries Visited</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="number">12</div>
                    <div class="label">Cities Explored</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-route"></i>
                    <div class="number">8</div>
                    <div class="label">Total Trips</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-hotel"></i>
                    <div class="number">32</div>
                    <div class="label">Nights Stayed</div>
                </div>
                
                <div class="stat-card">
                    <i class="fas fa-mountain"></i>
                    <div class="number">3</div>
                    <div class="label">Adventure Tours</div>
                </div>
            </div>
            
            <!-- Travel Map -->
            <div class="travel-map">
                <h2>Your Travel Map</h2>
                <p>Places you've visited across the globe</p>
                
                <div class="map-container">
                    <div class="map-placeholder">
                        <i class="fas fa-map-marked-alt"></i>
                        <p>Interactive map will be displayed here</p>
                        <button class="btn btn-primary">View Full Map</button>
                    </div>
                </div>
            </div>
            
            <!-- Travel Timeline -->
            <div class="history-timeline">
                <h2>Your Travel Timeline</h2>
                <p>Relive your past adventures</p>
                
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">2023</div>
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h3>Paris, France</h3>
                            <div style="margin-bottom: 10px;">
                                <span class="badge badge-success">Completed</span>
                                <span class="badge badge-info">Family Trip</span>
                            </div>
                            
                            <div class="trip-meta">
                                <div class="meta-item">
                                    <i class="far fa-calendar-alt"></i> Dec 15 - Dec 22, 2023
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i> 4 Travelers
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-star"></i> 4.8 Rating
                                </div>
                            </div>
                            
                            <p>A wonderful week exploring the City of Lights. We visited the Eiffel Tower, Louvre Museum, and enjoyed authentic French cuisine.</p>
                            
                            <div class="trip-gallery">
                                <div class="gallery-img">
                                    <img src="public/image/paris.jpg" alt="Paris Trip">
                                </div>
                                <div class="gallery-img">
                                    <img src="public/image/paris2.jpg" alt="Paris Trip">
                                </div>
                                <div class="gallery-img">
                                    <img src="public/image/paris3.jpg" alt="Paris Trip">
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button class="btn btn-outline btn-sm">View Details</button>
                                <button class="btn btn-outline btn-sm">Share Trip</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-date">2023</div>
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h3>Tokyo, Japan</h3>
                            <div style="margin-bottom: 10px;">
                                <span class="badge badge-success">Completed</span>
                                <span class="badge badge-warning">Solo Trip</span>
                            </div>
                            
                            <div class="trip-meta">
                                <div class="meta-item">
                                    <i class="far fa-calendar-alt"></i> Aug 5 - Aug 14, 2023
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i> 1 Traveler
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-star"></i> 4.9 Rating
                                </div>
                            </div>
                            
                            <p>An unforgettable cultural experience in Japan. Explored the vibrant neighborhoods of Tokyo, tried amazing local food, and visited traditional temples.</p>
                            
                            <div class="trip-gallery">
                                <div class="gallery-img">
                                    <img src="public/image/tokyo.jpg" alt="Tokyo Trip">
                                </div>
                                <div class="gallery-img">
                                    <img src="public/image/tokyo2.jpg" alt="Tokyo Trip">
                                </div>
                                <div class="gallery-img">
                                    <img src="public/image/tokyo3.jpg" alt="Tokyo Trip">
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button class="btn btn-outline btn-sm">View Details</button>
                                <button class="btn btn-outline btn-sm">Share Trip</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-date">2022</div>
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <h3>Switzerland</h3>
                            <div style="margin-bottom: 10px;">
                                <span class="badge badge-success">Completed</span>
                                <span class="badge badge-info">Family Trip</span>
                            </div>
                            
                            <div class="trip-meta">
                                <div class="meta-item">
                                    <i class="far fa-calendar-alt"></i> Jun 10 - Jun 18, 2022
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i> 3 Travelers
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-star"></i> 5.0 Rating
                                </div>
                            </div>
                            
                            <p>A breathtaking adventure in the Swiss Alps. We enjoyed skiing, hiking, and taking in the stunning mountain views.</p>
                            
                            <div class="trip-gallery">
                                <div class="gallery-img">
                                    <img src="public/image/switzerland.png" alt="Switzerland Trip">
                                </div>
                                <div class="gallery-img">
                                    <img src="public/image/switzerland2.jpg" alt="Switzerland Trip">
                                </div>
                                <div class="gallery-img">
                                    <img src="public/image/switzerland3.jpg" alt="Switzerland Trip">
                                </div>
                            </div>
                            
                            <div style="margin-top: 15px;">
                                <button class="btn btn-outline btn-sm">View Details</button>
                                <button class="btn btn-outline btn-sm">Share Trip</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Export Options -->
            <div style="background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); padding: 20px; margin-bottom: 30px;">
                <h2>Travel Reports</h2>
                <p>Download or share your travel history</p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px;">
                    <button class="btn btn-primary"><i class="fas fa-file-pdf"></i> Export as PDF</button>
                    <button class="btn btn-primary"><i class="fas fa-file-excel"></i> Export as CSV</button>
                    <button class="btn btn-primary"><i class="fas fa-share-alt"></i> Share Timeline</button>
                    <button class="btn btn-primary"><i class="fas fa-print"></i> Print Report</button>
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