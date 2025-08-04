// Database configuration for Node.js with MySQL
const mysql = require('mysql2/promise');
require('dotenv').config();

// Database connection settings
const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'travel_tourism',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};

// Create a connection pool
const pool = mysql.createPool(dbConfig);

// Test the database connection
async function testConnection() {
  try {
    const connection = await pool.getConnection();
    console.log('Database connection established successfully!');
    connection.release();
    return true;
  } catch (error) {
    console.error('Database connection failed:', error.message);
    return false;
  }
}

// Initialize database if needed
async function initDatabase() {
  try {
    const connection = await pool.getConnection();

    // Create database if it doesn't exist
    await connection.query(`CREATE DATABASE IF NOT EXISTS ${dbConfig.database}`);
    await connection.query(`USE ${dbConfig.database}`);

    // Create users table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(255),
        phone VARCHAR(20),
        address TEXT,
        profile_image VARCHAR(255),
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (username)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);

    // Create bookings table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_ref VARCHAR(10) NOT NULL,
        username VARCHAR(100) NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        destination VARCHAR(255) NOT NULL,
        travel_date DATE NOT NULL,
        travelers INT NOT NULL DEFAULT 1,
        room_type VARCHAR(50),
        airport_pickup TINYINT(1) NOT NULL DEFAULT 0,
        tour_guide TINYINT(1) NOT NULL DEFAULT 0,
        insurance TINYINT(1) NOT NULL DEFAULT 0,
        special_requests TEXT,
        total_amount DECIMAL(10, 2) NOT NULL,
        booking_date DATETIME NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'confirmed',
        INDEX (username),
        INDEX (booking_ref)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);

    // Create destinations table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS destinations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        country VARCHAR(100) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        image_url VARCHAR(255),
        rating FLOAT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    `);

    connection.release();
    console.log('Database initialized successfully');
    return true;
  } catch (error) {
    console.error('Failed to initialize database:', error.message);
    return false;
  }
}

module.exports = {
  pool,
  testConnection,
  initDatabase
}; 