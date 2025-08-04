// Handle login functionality
async function handleLogin(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    // Disable the submit button during login attempt
    const submitButton = event.target.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = true;
    
    // Show a loading indicator
    const errorElem = document.getElementById('login-error');
    if (errorElem) {
        errorElem.textContent = 'Logging in...';
        errorElem.style.display = 'block';
        errorElem.style.color = '#3498db';
        errorElem.style.borderLeftColor = '#3498db';
        errorElem.style.backgroundColor = '#edf7fd';
    }
    
    // Function to handle direct form submit as fallback
    const submitDirectForm = () => {
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.action = 'http://localhost/gotrip/login.php';
            loginForm.method = 'post';
            loginForm.submit();
        }
    };
    
    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);

        console.log('Attempting to fetch from login_cors.php...');
        
        // First try the CORS-enabled endpoint
        try {
            const response = await fetch('http://localhost/gotrip/login_cors.php', {
                method: 'POST',
                body: formData,
                credentials: 'include',
                mode: 'cors'
            });

            console.log('Fetch response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Login response data:', data);
            
            if (data.status === 'success') {
                // Store login state in localStorage
                localStorage.setItem('isLoggedIn', 'true');
                localStorage.setItem('username', username);
                localStorage.setItem('user_id', data.user_id);
                
                // Get return URL from query params if available
                const urlParams = new URLSearchParams(window.location.search);
                const returnParam = urlParams.get('return');
                
                // Redirect to appropriate page
                let redirectUrl = 'http://localhost/gotrip/user_dashboard.php';
                
                // Check if there was a stored booking redirect
                if (localStorage.getItem('bookingRedirect')) {
                    redirectUrl = localStorage.getItem('bookingRedirect');
                    localStorage.removeItem('bookingRedirect');
                } 
                // Check if there was a return parameter in the URL
                else if (returnParam) {
                    redirectUrl = returnParam;
                }
                
                window.location.href = redirectUrl;
                return;
            } else {
                // Show error message
                const errorMessage = data.message || 'Login failed. Please check your credentials.';
                if (errorElem) {
                    errorElem.textContent = errorMessage;
                    errorElem.style.color = '#e74c3c';
                    errorElem.style.borderLeftColor = '#e74c3c';
                    errorElem.style.backgroundColor = '#fef5f5';
                } else {
                    alert(errorMessage);
                }
            }
        } catch (corsError) {
            console.error('CORS login failed:', corsError);
            // Fall back to direct form submission
            if (confirm('Secure login failed. Try standard form submission?')) {
                submitDirectForm();
                return;
            }
        }
    } catch (error) {
        console.error('Login error:', error);
        
        // Provide a more helpful error message
        let errorMessage = 'An error occurred during login.';
        
        if (errorElem) {
            errorElem.textContent = errorMessage;
            errorElem.style.color = '#e74c3c';
            errorElem.style.borderLeftColor = '#e74c3c';
            errorElem.style.backgroundColor = '#fef5f5';
        } else {
            // Offer direct form submission as fallback
            if (confirm('Login failed. Would you like to try direct form submission instead?')) {
                submitDirectForm();
            }
        }
    } finally {
        // Re-enable the submit button
        if (submitButton) submitButton.disabled = false;
    }
}

// Handle registration
async function handleRegister(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    // Simple validation
    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    // Disable the submit button during registration
    const submitButton = event.target.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = true;

    try {
        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);

        const response = await fetch('http://localhost/gotrip/register.php', {
            method: 'POST',
            body: formData,
            credentials: 'include' // Include cookies
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Registration successful! Please login to continue.');
            window.location.href = 'http://localhost/gotrip/public/views/login.html';
        } else {
            const errorMessage = data.message || 'Registration failed. Please try again.';
            alert(errorMessage);
        }
    } catch (error) {
        console.error('Registration error:', error);
        alert('An error occurred during registration. Please try again.');
    } finally {
        // Re-enable the submit button
        if (submitButton) submitButton.disabled = false;
    }
}

// Check if user is logged in
function checkLoginStatus() {
    return localStorage.getItem('isLoggedIn') === 'true';
}

// Get current username
function getCurrentUsername() {
    return localStorage.getItem('username');
}

// Logout function
async function logout() {
    try {
        const response = await fetch('http://localhost/gotrip/logout.php', {
            credentials: 'include' // Include cookies
        });
        const data = await response.json();
        
        // Always clear local storage regardless of server response
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('username');
        localStorage.removeItem('user_id');
        
        // Redirect to home page
        window.location.href = 'http://localhost/gotrip/public/index.html';
    } catch (error) {
        console.error('Logout error:', error);
        
        // Even if server-side logout fails, we should clear client-side state
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('username');
        localStorage.removeItem('user_id');
        window.location.href = 'http://localhost/gotrip/public/index.html';
    }
}

// Function to protect pages that require login
function protectPage() {
    if (!checkLoginStatus()) {
        // Store current page URL for redirect after login
        localStorage.setItem('bookingRedirect', window.location.href);
        
        // Redirect to login page
        window.location.href = 'http://localhost/gotrip/public/views/login.html';
        return false;
    }
    return true;
}

// Function to verify server-side session status
async function checkServerSession() {
    if (checkLoginStatus()) {
        try {
            const response = await fetch('http://localhost/gotrip/check_session.php', {
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (data.status === 'error' || !data.logged_in) {
                // Server session is invalid, clear local storage
                localStorage.removeItem('isLoggedIn');
                localStorage.removeItem('username');
                localStorage.removeItem('user_id');
                
                // Redirect to login
                window.location.href = 'http://localhost/gotrip/public/views/login.html';
            }
        } catch (error) {
            console.error('Session check error:', error);
        }
    }
}

// Initialize auth-related UI elements when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Check if server session is valid
    checkServerSession();
    
    // Update navigation menu based on login status
    updateNavMenu();
    
    // Add event listener to logout link
    const logoutLink = document.getElementById('logoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
    
    // Add event listener to sidebar logout if present
    const sidebarLogout = document.getElementById('sidebarLogout');
    if (sidebarLogout) {
        sidebarLogout.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }
    
    // Set up registration form if it exists
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
});

// Update navigation menu based on login status
function updateNavMenu() {
    const isLoggedIn = checkLoginStatus();
    const loginItem = document.querySelector('.login-item');
    const bookingItem = document.querySelector('.booking-item');
    const logoutItem = document.querySelector('.logout-item');
    
    if (loginItem && bookingItem && logoutItem) {
        if (isLoggedIn) {
            // User is logged in
            loginItem.style.display = 'none';
            bookingItem.style.display = 'block';
            logoutItem.style.display = 'block';
        } else {
            // User is not logged in
            loginItem.style.display = 'block';
            bookingItem.style.display = 'none';
            logoutItem.style.display = 'none';
        }
    }
}
