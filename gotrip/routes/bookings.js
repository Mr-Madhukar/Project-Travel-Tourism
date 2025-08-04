const express = require('express');
const router = express.Router();
const { v4: uuidv4 } = require('uuid');
const { pool } = require('../config/database');
const { authenticate } = require('../middleware/authMiddleware');

// Get all bookings for current user
router.get('/', authenticate, async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [bookings] = await connection.query(
      'SELECT * FROM bookings WHERE username = ? ORDER BY booking_date DESC',
      [req.user.username]
    );
    connection.release();
    
    res.json({
      success: true,
      bookings
    });
  } catch (error) {
    console.error('Error fetching bookings:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch bookings'
    });
  }
});

// Get a specific booking by reference
router.get('/:bookingRef', authenticate, async (req, res) => {
  try {
    const { bookingRef } = req.params;
    
    const connection = await pool.getConnection();
    const [bookings] = await connection.query(
      'SELECT * FROM bookings WHERE booking_ref = ? AND username = ?',
      [bookingRef, req.user.username]
    );
    connection.release();
    
    if (bookings.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Booking not found'
      });
    }
    
    res.json({
      success: true,
      booking: bookings[0]
    });
  } catch (error) {
    console.error('Error fetching booking:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch booking'
    });
  }
});

// Create a new booking
router.post('/', authenticate, async (req, res) => {
  try {
    const {
      fullName,
      email,
      phone,
      destination,
      travelDate,
      travelers,
      roomType,
      airportPickup,
      tourGuide,
      insurance,
      specialRequests,
      totalAmount
    } = req.body;
    
    // Validate required fields
    if (!fullName || !email || !destination || !travelDate || !totalAmount) {
      return res.status(400).json({
        success: false,
        message: 'Required fields are missing'
      });
    }
    
    // Generate a booking reference (8 random alphanumeric characters)
    const bookingRef = uuidv4().substring(0, 8).toUpperCase();
    
    // Create booking date
    const bookingDate = new Date().toISOString().slice(0, 19).replace('T', ' ');
    
    // Set booking status
    const status = 'confirmed';
    
    // Process airportPickup, tourGuide, and insurance as booleans
    const hasAirportPickup = airportPickup ? 1 : 0;
    const hasTourGuide = tourGuide ? 1 : 0;
    const hasInsurance = insurance ? 1 : 0;
    
    const connection = await pool.getConnection();
    
    // Insert booking into database
    const [result] = await connection.query(
      `INSERT INTO bookings (
        booking_ref, 
        username, 
        full_name, 
        email, 
        phone, 
        destination, 
        travel_date, 
        travelers, 
        room_type, 
        airport_pickup, 
        tour_guide, 
        insurance, 
        special_requests, 
        total_amount, 
        booking_date, 
        status
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        bookingRef,
        req.user.username,
        fullName,
        email,
        phone,
        destination,
        travelDate,
        travelers,
        roomType,
        hasAirportPickup,
        hasTourGuide,
        hasInsurance,
        specialRequests,
        totalAmount,
        bookingDate,
        status
      ]
    );
    
    connection.release();
    
    res.status(201).json({
      success: true,
      message: 'Booking created successfully',
      bookingRef,
      bookingId: result.insertId
    });
  } catch (error) {
    console.error('Error creating booking:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create booking'
    });
  }
});

// Cancel a booking
router.put('/:bookingRef/cancel', authenticate, async (req, res) => {
  try {
    const { bookingRef } = req.params;
    
    const connection = await pool.getConnection();
    
    // Check if booking exists and belongs to user
    const [bookings] = await connection.query(
      'SELECT * FROM bookings WHERE booking_ref = ? AND username = ?',
      [bookingRef, req.user.username]
    );
    
    if (bookings.length === 0) {
      connection.release();
      return res.status(404).json({
        success: false,
        message: 'Booking not found'
      });
    }
    
    // Update booking status
    await connection.query(
      'UPDATE bookings SET status = ? WHERE booking_ref = ?',
      ['cancelled', bookingRef]
    );
    
    connection.release();
    
    res.json({
      success: true,
      message: 'Booking cancelled successfully'
    });
  } catch (error) {
    console.error('Error cancelling booking:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to cancel booking'
    });
  }
});

module.exports = router; 