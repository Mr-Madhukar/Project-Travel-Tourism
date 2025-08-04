const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const { pool } = require('../config/database');
const { authenticate } = require('../middleware/authMiddleware');

// Get current user profile
router.get('/profile', authenticate, async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [users] = await connection.query(
      'SELECT id, username, email, full_name, phone, address, profile_image, created_at FROM users WHERE id = ?',
      [req.user.id]
    );
    connection.release();
    
    if (users.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    res.json({
      success: true,
      user: users[0]
    });
  } catch (error) {
    console.error('Error fetching user profile:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch user profile'
    });
  }
});

// Update user profile
router.put('/profile', authenticate, async (req, res) => {
  try {
    const { fullName, phone, address } = req.body;
    
    const connection = await pool.getConnection();
    await connection.query(
      'UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?',
      [fullName, phone, address, req.user.id]
    );
    connection.release();
    
    res.json({
      success: true,
      message: 'Profile updated successfully'
    });
  } catch (error) {
    console.error('Error updating profile:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to update profile'
    });
  }
});

// Change password
router.put('/change-password', authenticate, async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;
    
    if (!currentPassword || !newPassword) {
      return res.status(400).json({
        success: false,
        message: 'Current password and new password are required'
      });
    }
    
    const connection = await pool.getConnection();
    const [users] = await connection.query(
      'SELECT password FROM users WHERE id = ?',
      [req.user.id]
    );
    
    if (users.length === 0) {
      connection.release();
      return res.status(404).json({
        success: false,
        message: 'User not found'
      });
    }
    
    const user = users[0];
    
    // Verify current password
    const isPasswordValid = await bcrypt.compare(currentPassword, user.password);
    
    if (!isPasswordValid) {
      connection.release();
      return res.status(401).json({
        success: false,
        message: 'Current password is incorrect'
      });
    }
    
    // Hash the new password
    const hashedNewPassword = await bcrypt.hash(newPassword, 10);
    
    // Update password
    await connection.query(
      'UPDATE users SET password = ? WHERE id = ?',
      [hashedNewPassword, req.user.id]
    );
    
    connection.release();
    
    res.json({
      success: true,
      message: 'Password changed successfully'
    });
  } catch (error) {
    console.error('Error changing password:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to change password'
    });
  }
});

// Get user's travel history
router.get('/travel-history', authenticate, async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [bookings] = await connection.query(
      `SELECT 
        booking_ref, 
        destination, 
        travel_date, 
        status, 
        booking_date 
      FROM 
        bookings 
      WHERE 
        username = ? 
      ORDER BY 
        travel_date DESC`,
      [req.user.username]
    );
    connection.release();
    
    res.json({
      success: true,
      history: bookings
    });
  } catch (error) {
    console.error('Error fetching travel history:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch travel history'
    });
  }
});

module.exports = router; 