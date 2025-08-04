# GoTrip - Travel & Tourism Website

A modern, responsive travel and tourism website built with HTML, CSS, JavaScript, and PHP. This project offers a complete travel booking experience with user authentication, destination management, and dynamic pricing.

## 🌟 Features

### Frontend Features
- **Responsive Design**: Mobile-first approach with modern UI/UX
- **Interactive Destinations**: Dynamic destination cards with hover effects
- **Search Functionality**: Real-time destination search with autocomplete
- **User Authentication**: Login/signup system with session management
- **Travel Alerts**: Real-time travel notifications and alerts
- **Trip Planner**: Interactive trip planning tool
- **Booking System**: Complete booking workflow
- **Testimonials**: Customer reviews with carousel
- **Blog Section**: Travel blog with articles
- **Contact Forms**: Multiple contact options

### Backend Features
- **PHP Backend**: Server-side processing and API endpoints
- **MySQL Database**: User management and booking storage
- **Authentication System**: Secure login/logout functionality
- **Dynamic Pricing**: Real-time price calculations based on demand
- **File Upload**: Image and document handling
- **Email Notifications**: Booking confirmations and alerts
- **CORS Support**: Cross-origin resource sharing enabled

### Technical Features
- **Modern CSS**: Flexbox, Grid, and CSS animations
- **JavaScript ES6+**: Modern JavaScript with async/await
- **AOS Animations**: Scroll-triggered animations
- **Font Awesome Icons**: Professional iconography
- **Google Fonts**: Typography optimization
- **Progressive Web App**: PWA capabilities

## 🚀 Technologies Used

### Frontend
- HTML5
- CSS3 (Flexbox, Grid, Animations)
- JavaScript (ES6+)
- Font Awesome Icons
- AOS Animation Library
- Google Fonts

### Backend
- PHP 7.4+
- MySQL Database
- Apache Server (XAMPP)

### Development Tools
- Git & GitHub
- VS Code (Recommended)
- XAMPP (Local Development)

## 📁 Project Structure

```
gotrip/
├── public/                 # Main website files
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   ├── image/             # Images and assets
│   ├── views/             # HTML pages
│   ├── controllers/       # JavaScript controllers
│   ├── services/          # API services
│   └── sounds/            # Audio files
├── config/                # Configuration files
├── middleware/            # Authentication middleware
├── routes/                # API routes
├── database.sql           # Database schema
├── app.js                 # Main application file
└── README.md             # Project documentation
```

## 🛠️ Installation & Setup

### Prerequisites
- XAMPP (Apache + MySQL + PHP)
- Git
- Modern web browser

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Mr-Madhukar/Project-Travel-Tourism.git
   cd Project-Travel-Tourism
   ```

2. **Setup XAMPP**
   - Start Apache and MySQL services
   - Place the project in `htdocs` folder

3. **Database Setup**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `gotrip`
   - Import `database.sql` file

4. **Configuration**
   - Update database credentials in `config/database.js`
   - Ensure PHP extensions are enabled (mysqli, json)

5. **Access the Website**
   - Open browser and navigate to `http://localhost/gotrip/public/`

## 🎯 Key Features Explained

### 1. Dynamic Destination Search
- Real-time search with autocomplete
- Database-driven destination list
- Smart filtering and suggestions

### 2. User Authentication System
- Secure login/signup process
- Session management
- Password encryption
- User dashboard

### 3. Booking Management
- Complete booking workflow
- Payment integration ready
- Booking confirmation emails
- Booking history tracking

### 4. Travel Alerts System
- Real-time travel notifications
- Weather alerts
- Price change notifications
- Customizable alert preferences

### 5. Responsive Design
- Mobile-first approach
- Tablet and desktop optimized
- Touch-friendly interface
- Cross-browser compatibility

## 🔧 Configuration

### Database Configuration
Update the database settings in `config/database.js`:
```javascript
const dbConfig = {
    host: 'localhost',
    user: 'your_username',
    password: 'your_password',
    database: 'gotrip'
};
```

### Email Configuration
Configure email settings for notifications in PHP files.

## 📱 Responsive Design

The website is fully responsive and optimized for:
- Mobile devices (320px+)
- Tablets (768px+)
- Desktop (1024px+)
- Large screens (1440px+)

## 🎨 Design Features

- **Modern UI/UX**: Clean and professional design
- **Smooth Animations**: AOS library integration
- **Color Scheme**: Consistent branding
- **Typography**: Google Fonts integration
- **Icons**: Font Awesome iconography

## 🔒 Security Features

- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Token-based security
- **Password Hashing**: Secure password storage
- **Session Security**: Secure session management

## 🚀 Deployment

### Local Development
1. Use XAMPP for local development
2. Access via `http://localhost/gotrip/public/`

### Production Deployment
1. Upload files to web server
2. Configure database on production server
3. Update configuration files
4. Set up SSL certificate
5. Configure domain and DNS

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Developer

**Mr. Madhukar**
- GitHub: [@Mr-Madhukar](https://github.com/Mr-Madhukar)
- Project: [GoTrip Travel Website](https://github.com/Mr-Madhukar/Project-Travel-Tourism)

## 🙏 Acknowledgments

- Font Awesome for icons
- Google Fonts for typography
- AOS library for animations
- XAMPP for local development environment

## 📞 Support

For support and questions:
- Email: info@gotrip.com
- Phone: +91 9473452441
- Address: G.P PURNEA, Bihar, India

---

**GoTrip** - Your trusted travel partner since 1996! ✈️🌍
