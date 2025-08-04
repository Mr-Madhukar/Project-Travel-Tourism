const express = require('express');
const router = express.Router();
const { pool } = require('../config/database');
const { authenticate } = require('../middleware/authMiddleware');

// Get all destinations
router.get('/', async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [destinations] = await connection.query('SELECT * FROM destinations ORDER BY name');
    connection.release();
    
    // If no destinations in database, return default destinations
    if (destinations.length === 0) {
      const defaultDestinations = [
        { name: 'London', country: 'United Kingdom', price: 150000, image_url: './image/london.jpg', description: 'Explore the iconic landmarks of London, from Big Ben to Buckingham Palace.' },
        { name: 'Vancouver', country: 'Canada', price: 199999, image_url: './image/canada.jpg', description: 'Experience the natural beauty of Vancouver, nestled between mountains and the Pacific Ocean.' },
        { name: 'Monaco', country: 'Monaco', price: 139999, image_url: './image/monaco.jpg', description: 'Discover the luxury and glamour of Monaco, home to the famous Monte Carlo Casino.' },
        { name: 'Paris', country: 'France', price: 149999, image_url: './image/paris.jpg', description: 'Fall in love with the City of Light, known for its art, culture, and iconic Eiffel Tower.' },
        { name: 'Tokyo', country: 'Japan', price: 169999, image_url: './image/tokyo.jpg', description: 'Immerse yourself in the unique blend of traditional and ultramodern that is Tokyo.' },
        { name: 'Zurich', country: 'Switzerland', price: 159999, image_url: './image/switzerland.jpg', description: 'Enjoy the picturesque beauty of Zurich, surrounded by lakes and mountains.' }
      ];
      
      return res.json({
        success: true,
        destinations: defaultDestinations
      });
    }
    
    res.json({
      success: true,
      destinations
    });
  } catch (error) {
    console.error('Error fetching destinations:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch destinations'
    });
  }
});

// Get a specific destination
router.get('/:id', async (req, res) => {
  try {
    const { id } = req.params;
    
    const connection = await pool.getConnection();
    const [destinations] = await connection.query('SELECT * FROM destinations WHERE id = ?', [id]);
    connection.release();
    
    if (destinations.length === 0) {
      return res.status(404).json({
        success: false,
        message: 'Destination not found'
      });
    }
    
    res.json({
      success: true,
      destination: destinations[0]
    });
  } catch (error) {
    console.error('Error fetching destination:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch destination'
    });
  }
});

// Get prices for destinations (with dynamic pricing logic)
router.get('/prices/all', async (req, res) => {
  try {
    const connection = await pool.getConnection();
    const [destinations] = await connection.query('SELECT id, name, price FROM destinations');
    connection.release();
    
    // Calculate dynamic prices
    const currentDate = new Date();
    const month = currentDate.getMonth() + 1; // 1-12
    
    const prices = destinations.map(dest => {
      // Apply seasonal factors (high season in summer and Christmas)
      let seasonFactor = 1.0;
      if (month >= 6 && month <= 8) {
        seasonFactor = 1.2; // Summer high season
      } else if (month === 12) {
        seasonFactor = 1.3; // Christmas high season
      } else if (month >= 3 && month <= 5) {
        seasonFactor = 1.1; // Spring medium season
      } else if (month >= 9 && month <= 11) {
        seasonFactor = 1.0; // Fall medium season
      } else {
        seasonFactor = 0.9; // Winter low season (except December)
      }
      
      // Apply demand factor (random for demonstration)
      const demandFactor = (Math.random() * 0.2) + 0.9; // 0.9-1.1
      
      // Special events factor
      const hasSpecialEvent = Math.random() > 0.8; // 20% chance of special event
      const specialEventFactor = hasSpecialEvent ? 1.15 : 1.0;
      
      // Calculate dynamic price
      const basePrice = dest.price;
      const dynamicPrice = basePrice * seasonFactor * demandFactor * specialEventFactor;
      
      // Format prices for display
      const formattedBasePrice = '₹' + basePrice.toLocaleString('en-IN');
      const formattedDynamicPrice = '₹' + Math.round(dynamicPrice).toLocaleString('en-IN');
      
      return {
        id: dest.id,
        name: dest.name,
        base_price: basePrice,
        dynamic_price: Math.round(dynamicPrice),
        formatted_base_price: formattedBasePrice,
        formatted_dynamic_price: formattedDynamicPrice,
        factors: {
          season: seasonFactor.toFixed(2),
          demand: demandFactor.toFixed(2),
          special_event: hasSpecialEvent
        }
      };
    });
    
    res.json({
      success: true,
      prices
    });
  } catch (error) {
    console.error('Error fetching prices:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to fetch prices'
    });
  }
});

// Create a new destination (admin only)
router.post('/', authenticate, async (req, res) => {
  try {
    const {
      name,
      description,
      country,
      price,
      image_url
    } = req.body;
    
    // Validate required fields
    if (!name || !country || !price) {
      return res.status(400).json({
        success: false,
        message: 'Required fields are missing'
      });
    }
    
    const connection = await pool.getConnection();
    const [result] = await connection.query(
      'INSERT INTO destinations (name, description, country, price, image_url, created_at) VALUES (?, ?, ?, ?, ?, NOW())',
      [name, description, country, price, image_url]
    );
    connection.release();
    
    res.status(201).json({
      success: true,
      message: 'Destination created successfully',
      destinationId: result.insertId
    });
  } catch (error) {
    console.error('Error creating destination:', error);
    res.status(500).json({
      success: false,
      message: 'Failed to create destination'
    });
  }
});

module.exports = router; 