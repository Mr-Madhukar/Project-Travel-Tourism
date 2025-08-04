/**
 * Travel Alerts System
 * Handles real-time travel alerts and notifications
 */

class TravelAlertsSystem {
    constructor() {
        this.alertsData = [];
        this.currentDestination = null;
        this.alertsContainer = null;
        this.alertsBadge = null;
        // Initialize the notification sound system
        this.notificationSound = null;
        this.checkInterval = 5 * 60 * 1000; // Check every 5 minutes by default
        this.init();
    }

    async init() {
        // Initialize notification sound
        this.notificationSound = new NotificationSound();
        await this.notificationSound.initialize();
        
        // Create alerts UI if needed
        this.createAlertsUI();
        
        // Load initial alerts
        this.loadAlerts();
        
        // Set up interval for checking new alerts
        setInterval(() => this.checkForNewAlerts(), this.checkInterval);
        
        // Set up event listeners
        document.addEventListener('DOMContentLoaded', () => {
            // Parse URL to check for destination info
            this.parseUrlForDestination();
            
            // Check if we're on a destination page
            if (document.querySelector('.destination-page')) {
                this.detectDestinationFromPage();
            }
        });
    }

    /**
     * Create the alerts UI components that will be added to the page
     */
    createAlertsUI() {
        // Create bell icon with badge for the navigation bar
        const navMenu = document.querySelector('.menu');
        if (navMenu) {
            // Create alerts bell icon
            const alertsLi = document.createElement('li');
            alertsLi.className = 'alerts-icon';
            
            const alertsLink = document.createElement('a');
            alertsLink.href = '#';
            alertsLink.innerHTML = '<i class="fas fa-bell"></i>';
            alertsLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggleAlertsPanel();
            });
            
            // Create notification badge
            this.alertsBadge = document.createElement('span');
            this.alertsBadge.className = 'alerts-badge hidden';
            this.alertsBadge.textContent = '0';
            
            alertsLink.appendChild(this.alertsBadge);
            alertsLi.appendChild(alertsLink);
            navMenu.appendChild(alertsLi);
            
            // Create alerts dropdown panel
            this.alertsContainer = document.createElement('div');
            this.alertsContainer.className = 'alerts-container hidden';
            this.alertsContainer.innerHTML = `
                <div class="alerts-header">
                    <h3>Travel Alerts</h3>
                    <button class="close-alerts">&times;</button>
                </div>
                <div class="alerts-content">
                    <div class="alerts-list"></div>
                    <div class="alerts-empty">No alerts at this time</div>
                </div>
            `;
            
            // Add event listener to close button
            this.alertsContainer.querySelector('.close-alerts').addEventListener('click', () => {
                this.toggleAlertsPanel(false);
            });
            
            // Add the alerts container to the body
            document.body.appendChild(this.alertsContainer);
        }
    }

    /**
     * Toggle the visibility of the alerts panel
     */
    toggleAlertsPanel(show) {
        if (this.alertsContainer) {
            if (show === undefined) {
                this.alertsContainer.classList.toggle('hidden');
            } else if (show) {
                this.alertsContainer.classList.remove('hidden');
            } else {
                this.alertsContainer.classList.add('hidden');
            }
            
            // If showing the panel, mark alerts as read
            if (!this.alertsContainer.classList.contains('hidden')) {
                this.markAlertsAsRead();
            }
        }
    }

    /**
     * Mark all alerts as read and update the UI
     */
    markAlertsAsRead() {
        if (this.alertsBadge) {
            this.alertsBadge.classList.add('hidden');
            this.alertsBadge.textContent = '0';
        }
        
        // Store read status in localStorage
        if (this.alertsData.length > 0) {
            const readAlerts = JSON.parse(localStorage.getItem('readAlerts') || '{}');
            
            this.alertsData.forEach(alert => {
                readAlerts[alert.id] = true;
            });
            
            localStorage.setItem('readAlerts', JSON.stringify(readAlerts));
        }
    }

    /**
     * Try to detect the current destination from URL parameters
     */
    parseUrlForDestination() {
        const urlParams = new URLSearchParams(window.location.search);
        const destination = urlParams.get('destination');
        
        if (destination) {
            // Parse destination if in format "id|name, country"
            if (destination.includes('|')) {
                const [id, name] = destination.split('|');
                this.currentDestination = {
                    id: parseInt(id),
                    name: name
                };
            } else {
                this.currentDestination = {
                    name: destination
                };
            }
            
            // Load destination-specific alerts
            if (this.currentDestination.id) {
                this.loadAlerts(this.currentDestination.id);
            }
        }
    }

    /**
     * Try to detect the current destination from page content
     */
    detectDestinationFromPage() {
        const destinationTitle = document.querySelector('.destination-title, h1');
        if (destinationTitle) {
            const title = destinationTitle.textContent.trim();
            
            // Look for destination ID in a data attribute
            const destinationId = document.querySelector('[data-destination-id]')?.dataset.destinationId;
            
            this.currentDestination = {
                id: destinationId ? parseInt(destinationId) : null,
                name: title
            };
            
            // Load destination-specific alerts
            if (this.currentDestination.id) {
                this.loadAlerts(this.currentDestination.id);
            }
        }
    }

    /**
     * Load alerts from the server
     */
    loadAlerts(destinationId = null) {
        // Use the static JSON file instead of PHP
        // Check if a path override is set (for pages in subdirectories)
        let url = window.travelAlertsJsonPath || 'travel_alerts.json';
        
        // Make sure we're loading a JSON file
        if (!url.endsWith('.json')) {
            url = url.split('.')[0] + '.json';
        }
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Filter by destination ID if provided
                if (destinationId) {
                    data = data.filter(alert => alert.destination_id === destinationId);
                }
                
                this.alertsData = data;
                this.updateAlertsUI();
                this.checkUnreadAlerts();
            })
            .catch(error => {
                console.error('Error loading alerts:', error);
                // Use fallback data if fetch fails
                this.alertsData = this.getDefaultAlerts();
                this.updateAlertsUI();
                this.checkUnreadAlerts();
            });
    }

    /**
     * Check for any new alerts
     */
    checkForNewAlerts() {
        // For static data, we'll simulate checking by using the same JSON file
        // but we'll only add alerts that we don't already have
        
        const lastAlertId = this.alertsData.length > 0 ? 
            Math.max(...this.alertsData.map(alert => alert.id)) : 0;
        
        // Check if a path override is set (for pages in subdirectories)
        let url = window.travelAlertsJsonPath || 'travel_alerts.json';
        
        // Make sure we're loading a JSON file
        if (!url.endsWith('.json')) {
            url = url.split('.')[0] + '.json';
        }
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Filter to only include alerts we don't already have
                let newAlerts = data.filter(alert => alert.id > lastAlertId);
                
                // If we have destination filter, apply it
                if (this.currentDestination && this.currentDestination.id) {
                    newAlerts = newAlerts.filter(alert => alert.destination_id === this.currentDestination.id);
                }
                
                // Only process if we received new alerts
                if (newAlerts.length > 0) {
                    // Add new alerts to our data
                    this.alertsData = [...newAlerts, ...this.alertsData];
                    
                    // Update UI
                    this.updateAlertsUI();
                    
                    // Notify user about new alerts
                    this.notifyAboutNewAlerts(newAlerts);
                }
            })
            .catch(error => console.error('Error checking for new alerts:', error));
    }

    /**
     * Check for unread alerts and update the badge
     */
    checkUnreadAlerts() {
        if (!this.alertsData.length) return;
        
        const readAlerts = JSON.parse(localStorage.getItem('readAlerts') || '{}');
        const unreadCount = this.alertsData.filter(alert => !readAlerts[alert.id]).length;
        
        if (unreadCount > 0) {
            this.alertsBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
            this.alertsBadge.classList.remove('hidden');
        } else {
            this.alertsBadge.classList.add('hidden');
        }
    }

    /**
     * Update the alerts UI with the latest alerts
     */
    updateAlertsUI() {
        if (!this.alertsContainer) return;
        
        const alertsList = this.alertsContainer.querySelector('.alerts-list');
        const alertsEmpty = this.alertsContainer.querySelector('.alerts-empty');
        
        if (this.alertsData.length === 0) {
            alertsList.innerHTML = '';
            alertsEmpty.style.display = 'block';
            return;
        }
        
        alertsEmpty.style.display = 'none';
        alertsList.innerHTML = '';
        
        // Sort alerts by severity and date
        const sortedAlerts = [...this.alertsData].sort((a, b) => {
            const severityOrder = { high: 3, medium: 2, low: 1 };
            const severityDiff = severityOrder[b.severity] - severityOrder[a.severity];
            
            if (severityDiff !== 0) return severityDiff;
            
            // If same severity, sort by date (newest first)
            return new Date(b.created_at) - new Date(a.created_at);
        });
        
        sortedAlerts.forEach(alert => {
            const alertElement = document.createElement('div');
            alertElement.className = `alert-item alert-${alert.severity}`;
            alertElement.dataset.alertId = alert.id;
            
            const alertDate = new Date(alert.created_at);
            const dateFormatted = alertDate.toLocaleDateString();
            
            alertElement.innerHTML = `
                <div class="alert-header">
                    <span class="alert-type">${alert.alert_type}</span>
                    <span class="alert-date">${dateFormatted}</span>
                </div>
                <h4 class="alert-title">${alert.title}</h4>
                <div class="alert-location">${alert.destination_name}, ${alert.country}</div>
                <p class="alert-description">${alert.description}</p>
            `;
            
            alertsList.appendChild(alertElement);
        });
        
        // Also update any inline alerts on the page
        this.updateInlineAlerts();
    }

    /**
     * Update any inline alerts displayed on the page
     */
    updateInlineAlerts() {
        const inlineAlertsContainer = document.querySelector('.inline-alerts');
        if (!inlineAlertsContainer) return;
        
        inlineAlertsContainer.innerHTML = '';
        
        if (this.alertsData.length === 0) {
            inlineAlertsContainer.style.display = 'none';
            return;
        }
        
        inlineAlertsContainer.style.display = 'block';
        
        // Filter to high and medium severity alerts, and sort by severity
        const importantAlerts = this.alertsData
            .filter(alert => ['high', 'medium'].includes(alert.severity))
            .sort((a, b) => {
                return a.severity === 'high' ? -1 : 1;
            });
        
        if (importantAlerts.length === 0) {
            inlineAlertsContainer.style.display = 'none';
            return;
        }
        
        importantAlerts.slice(0, 3).forEach(alert => {
            const alertElement = document.createElement('div');
            alertElement.className = `inline-alert alert-${alert.severity}`;
            
            alertElement.innerHTML = `
                <div class="alert-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="alert-content">
                    <h4>${alert.title}</h4>
                    <p>${alert.description}</p>
                </div>
                <button class="close-alert" data-alert-id="${alert.id}">&times;</button>
            `;
            
            inlineAlertsContainer.appendChild(alertElement);
            
            // Add event listener to close button
            alertElement.querySelector('.close-alert').addEventListener('click', (e) => {
                e.preventDefault();
                alertElement.classList.add('closing');
                setTimeout(() => alertElement.remove(), 300);
                
                // Check if this was the last alert
                if (inlineAlertsContainer.querySelectorAll('.inline-alert:not(.closing)').length === 0) {
                    inlineAlertsContainer.style.display = 'none';
                }
            });
        });
    }

    /**
     * Notify the user about new alerts
     */
    notifyAboutNewAlerts(newAlerts) {
        if (newAlerts.length === 0) return;
        
        // Play notification sound if available
        if (this.notificationSound && this.notificationSound.initialized) {
            this.notificationSound.play(0.4); // Play at 40% volume
        }
        
        // Check if we have notification permission
        if (Notification && Notification.permission === "granted") {
            // Show notification for each new alert
            newAlerts.forEach(alert => {
                const notification = new Notification("GoTrip Travel Alert", {
                    body: alert.message,
                    icon: "/images/gotrip-logo.png"
                });
                
                // Handle notification click
                notification.onclick = () => {
                    window.focus();
                    this.toggleAlertsPanel(true);
                };
            });
        } else {
            // Show inline notification for the first alert
            this.showInlineNotification(newAlerts[0]);
        }
        
        // Update unread count
        this.checkUnreadAlerts();
    }

    /**
     * Show an inline notification for a new alert
     */
    showInlineNotification(alert) {
        if (!alert) return;
        
        const notification = document.createElement('div');
        notification.className = `notification alert-${alert.severity}`;
        
        notification.innerHTML = `
            <div class="notification-header">
                <h4>New Travel Alert</h4>
                <button class="close-notification">&times;</button>
            </div>
            <div class="notification-body">
                <h5>${alert.title}</h5>
                <p>${alert.description}</p>
            </div>
        `;
        
        // Add close button functionality
        notification.querySelector('.close-notification').addEventListener('click', () => {
            notification.classList.add('notification-closing');
            setTimeout(() => notification.remove(), 500);
        });
        
        // Auto-close after 10 seconds
        setTimeout(() => {
            if (document.body.contains(notification)) {
                notification.classList.add('notification-closing');
                setTimeout(() => notification.remove(), 500);
            }
        }, 10000);
        
        // Add to the page
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => notification.classList.add('notification-active'), 10);
    }

    /**
     * Request permission for browser notifications
     */
    requestNotificationPermission() {
        if (Notification && Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }
    }

    /**
     * Get default alerts for fallback when fetch fails
     */
    getDefaultAlerts() {
        return [
            {
                id: 1,
                destination_id: 1,
                alert_type: "weather",
                title: "Sunny Weather in Paris",
                description: "Enjoy beautiful sunny weather in Paris this week with temperatures reaching 25°C.",
                destination_name: "Paris",
                country: "France",
                severity: "low",
                created_at: new Date().toISOString()
            },
            {
                id: 2,
                destination_id: 2,
                alert_type: "travel",
                title: "Metro Information in Tokyo",
                description: "The Tokyo Metro runs from 5AM to midnight. Consider purchasing a day pass for unlimited travel.",
                destination_name: "Tokyo",
                country: "Japan",
                severity: "low",
                created_at: new Date().toISOString()
            }
        ];
    }
}

// Initialize the alerts system
const travelAlerts = new TravelAlertsSystem();

// Add notification permissions button
document.addEventListener('DOMContentLoaded', () => {
    const enableNotifications = document.querySelector('.enable-notifications');
    if (enableNotifications) {
        enableNotifications.addEventListener('click', (e) => {
            e.preventDefault();
            travelAlerts.requestNotificationPermission();
        });
    }
}); 