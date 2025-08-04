# GoTrip - Travel & Tourism Website

## Project Overview
GoTrip is a full-stack web application for a travel and tourism service, built with Node.js for the backend API and AngularJS for the frontend. The application allows users to browse travel destinations, create accounts, make bookings, and manage their bookings.

## Technology Stack
- **Backend**: Node.js with Express.js framework
- **Frontend**: AngularJS (1.x)
- **Database**: MySQL
- **Authentication**: JWT (JSON Web Token)
- **Documentation**: Swagger UI for API documentation

## Features
- User registration and authentication
- Browse travel destinations
- Dynamic pricing 
- Booking management
- User profile management
- Responsive design

## Installation and Setup

### Prerequisites
- Node.js (v14+)
- MySQL database
- npm or yarn

### Setup Instructions

1. **Clone the repository**:
   ```
   git clone <repository-url>
   cd gotrip
   ```

2. **Install dependencies**:
   ```
   npm install
   ```

3. **Set up environment variables**:
   Create a `.env` file based on the `.env.example` template:
   ```
   PORT=3000
   NODE_ENV=development
   DB_HOST=localhost
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=travel_tourism
   JWT_SECRET=your_jwt_secret_key
   JWT_EXPIRES_IN=24h
   ```

4. **Initialize the database**:
   The application will automatically create the required database and tables when started. Ensure your MySQL server is running.

5. **Start the application**:
   ```
   npm start
   ```
   For development with hot-reloading:
   ```
   npm run dev
   ```

6. **Access the application**:
   - Website: http://localhost:3000
   - API Documentation: http://localhost:3000/api-docs

## API Documentation
The API documentation is available at `/api-docs` when the server is running. It provides details about all available endpoints, required parameters, and response formats.

## Directory Structure
- `/config` - Configuration files
- `/middleware` - Custom middleware functions
- `/routes` - API routes
- `/public` - Frontend AngularJS application
  - `/controllers` - AngularJS controllers
  - `/services` - AngularJS services
  - `/views` - HTML templates

## Development
For development purposes, you can run the application in development mode:
```
npm run dev
```
This will start the server with nodemon, which automatically restarts the server when changes are detected.

## License
This project is licensed under the MIT License. 