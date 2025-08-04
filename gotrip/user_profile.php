<?php
// user_profile.php - User profile settings
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
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user_data = $result->fetch_assoc();

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Process form data
    $new_username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Update user data
    $update_sql = "UPDATE users SET 
                    username = '$new_username',
                    email = '$email',
                    phone = '$phone',
                    address = '$address'
                    WHERE id = $user_id";
    
    if ($conn->query($update_sql) === TRUE) {
        $_SESSION['username'] = $new_username;
        $success_message = "Profile updated successfully!";
        
        // Refresh user data
        $result = $conn->query($sql);
        $user_data = $result->fetch_assoc();
    } else {
        $error_message = "Error updating profile: " . $conn->error;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate current password
    $password_sql = "SELECT password FROM users WHERE id = $user_id";
    $password_result = $conn->query($password_sql);
    $password_row = $password_result->fetch_assoc();
    
    if (password_verify($current_password, $password_row['password'])) {
        // Check if new passwords match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update_password_sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            
            if ($conn->query($update_password_sql) === TRUE) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error changing password: " . $conn->error;
            }
        } else {
            $error_message = "New passwords do not match.";
        }
    } else {
        $error_message = "Current password is incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Go Trip</title>
    
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
        /* Additional Profile Page Styles */
        .profile-content {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
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
        
        .profile-header {
            padding: 30px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #ff7d00;
            margin-right: 20px;
        }
        
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info h2 {
            margin: 0 0 5px;
            font-size: 24px;
        }
        
        .profile-info p {
            margin: 0;
            color: #666;
        }
        
        .profile-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .profile-tab {
            padding: 15px 20px;
            font-weight: 500;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        
        .profile-tab.active {
            border-bottom-color: #ff7d00;
            color: #ff7d00;
        }
        
        .tab-content {
            padding: 30px;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .form-row {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #ff7d00;
            outline: none;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
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
                <li><a href="rewards.php"><i class="fas fa-gift"></i> <span>Rewards</span></a></li>
                <li><a href="user_profile.php" class="active"><i class="fas fa-user-cog"></i> <span>Profile Settings</span></a></li>
                <li><a href="#" id="sidebarLogout"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="dashboard-header">
                <h1>Profile Settings</h1>
                <p>Manage your account preferences and personal information</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Profile Content -->
            <div class="profile-content">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <img src="<?php echo !empty($user_data['profile_image']) ? $user_data['profile_image'] : './image/default-avatar.jpg'; ?>" alt="User Avatar">
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($username); ?></h2>
                        <p>Member since <?php echo !empty($user_data['created_at']) ? date('F Y', strtotime($user_data['created_at'])) : 'Unknown'; ?></p>
                    </div>
                </div>
                
                <!-- Tabs -->
                <div class="profile-tabs">
                    <div class="profile-tab active" data-tab="personal-info">
                        <i class="fas fa-user"></i> Personal Information
                    </div>
                    <div class="profile-tab" data-tab="security">
                        <i class="fas fa-lock"></i> Security
                    </div>
                    <div class="profile-tab" data-tab="preferences">
                        <i class="fas fa-sliders-h"></i> Preferences
                    </div>
                </div>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Personal Information Tab -->
                    <div class="tab-pane active" id="personal-info">
                        <form action="" method="post">
                            <div class="form-row">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user_data['username'] ?? $username); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-row">
                                <label for="address" class="form-label">Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                <button type="reset" class="btn btn-secondary">Cancel</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Security Tab -->
                    <div class="tab-pane" id="security">
                        <form action="" method="post">
                            <div class="form-row">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-row">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                            </div>
                            
                            <div class="form-row">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            </div>
                            
                            <div class="form-row">
                                <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Preferences Tab -->
                    <div class="tab-pane" id="preferences">
                        <form action="" method="post">
                            <div class="form-row">
                                <label class="form-label">Email Notifications</label>
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="notify_deals" checked> Special deals and promotions
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="notify_bookings" checked> Booking confirmations and updates
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="notify_newsletter" checked> Monthly newsletter
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <label class="form-label">Travel Preferences</label>
                                <div class="checkbox-group">
                                    <label>
                                        <input type="checkbox" name="pref_beach"> Beach destinations
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="pref_mountain"> Mountain destinations
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="pref_city"> City breaks
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="pref_cultural"> Cultural experiences
                                    </label>
                                    <br>
                                    <label>
                                        <input type="checkbox" name="pref_adventure"> Adventure trips
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <label for="currency" class="form-label">Preferred Currency</label>
                                <select id="currency" name="currency" class="form-control">
                                    <option value="INR" selected>Indian Rupee (₹)</option>
                                    <option value="USD">US Dollar ($)</option>
                                    <option value="EUR">Euro (€)</option>
                                    <option value="GBP">British Pound (£)</option>
                                </select>
                            </div>
                            
                            <div class="btn-group">
                                <button type="submit" name="update_preferences" class="btn btn-primary">Save Preferences</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form>
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
        
        // Tab switching functionality
        const tabs = document.querySelectorAll('.profile-tab');
        const tabPanes = document.querySelectorAll('.tab-pane');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Hide all tab panes
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Show corresponding tab pane
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Logout functionality
        const logoutLink = document.getElementById('logoutLink');
        const sidebarLogout = document.getElementById('sidebarLogout');
        
        function logout() {
            fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('username');
                    window.location.href = 'public/index.html';
                })
                .catch(error => {
                    console.error('Error:', error);
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('username');
                    window.location.href = 'public/index.html';
                });
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