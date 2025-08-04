/**
 * Colorful Effects JS - Enhances the Go Trip website with vibrant animations and effects
 */

document.addEventListener('DOMContentLoaded', function() {
  // Initialize animations
  initColorfulEffects();
  initScrollAnimations();
  enhanceTestimonials();
  enhanceBannerSection();
});

/**
 * Initialize colorful hover effects
 */
function initColorfulEffects() {
  // Add hover effect to navigation items
  const navItems = document.querySelectorAll('.menu li a');
  navItems.forEach(item => {
    item.addEventListener('mouseenter', function() {
      this.style.transition = 'all 0.3s ease';
      this.style.color = getRandomBrightColor();
    });
    
    item.addEventListener('mouseleave', function() {
      this.style.transition = 'all 0.3s ease';
      this.style.color = '';
    });
  });
  
  // Add color shift effect to logo
  const logo = document.querySelector('.logo span');
  if (logo) {
    setInterval(() => {
      logo.style.backgroundImage = `linear-gradient(45deg, ${getRandomBrightColor()}, ${getRandomBrightColor()})`;
    }, 3000);
  }
  
  // Add pulsing effect to CTA buttons
  const ctaButtons = document.querySelectorAll('.book, .btn-primary, .view-all-btn');
  ctaButtons.forEach(button => {
    button.classList.add('pulse-effect');
  });
}

/**
 * Enhance banner section with particles and interactive elements
 */
function enhanceBannerSection() {
  const banner = document.querySelector('.banner');
  if (!banner) return;
  
  // Create and add floating particles to the banner
  createBannerParticles();
  
  // Add parallax effect to banner
  window.addEventListener('mousemove', (e) => {
    const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
    const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
    
    const bannerHeading = document.querySelector('.banner-heading');
    if (bannerHeading) {
      bannerHeading.style.transform = `translate(${moveX}px, ${moveY}px)`;
    }
    
    const bannerFeatures = document.querySelector('.banner-features');
    if (bannerFeatures) {
      bannerFeatures.style.transform = `translate(${-moveX}px, ${-moveY}px)`;
    }
  });
  
  // Add subtle scale effect to search container
  const searchContainer = document.querySelector('.search-container');
  if (searchContainer) {
    banner.addEventListener('mousemove', (e) => {
      const rect = banner.getBoundingClientRect();
      const x = e.clientX - rect.left; // x position within the banner
      const y = e.clientY - rect.top; // y position within the banner
      
      // Calculate distance from the center of the banner
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      const distX = (x - centerX) / centerX; // -1 to 1
      const distY = (y - centerY) / centerY; // -1 to 1
      
      // Apply subtle transform
      searchContainer.style.transform = `perspective(1000px) rotateX(${distY * -2}deg) rotateY(${distX * 2}deg) translateY(-5px)`;
    });
    
    banner.addEventListener('mouseleave', () => {
      searchContainer.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(-5px)';
    });
  }
  
  // Add glow effect to banner features on hover
  const features = document.querySelectorAll('.feature');
  features.forEach(feature => {
    feature.addEventListener('mouseenter', function() {
      this.style.boxShadow = `0 10px 25px ${getRandomBrightColor(0.3)}`;
    });
    
    feature.addEventListener('mouseleave', function() {
      this.style.boxShadow = '';
    });
  });
}

/**
 * Create floating particle effects for banner background
 */
function createBannerParticles() {
  const animatedBg = document.querySelector('.animated-bg');
  if (!animatedBg) return;
  
  // Create 50 floating particles
  for (let i = 0; i < 50; i++) {
    const particle = document.createElement('div');
    
    // Random size between 2px and 8px
    const size = Math.random() * 6 + 2;
    
    // Style the particle
    particle.style.position = 'absolute';
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;
    particle.style.background = 'rgba(255, 255, 255, 0.5)';
    particle.style.borderRadius = '50%';
    particle.style.top = `${Math.random() * 100}%`;
    particle.style.left = `${Math.random() * 100}%`;
    particle.style.boxShadow = '0 0 10px rgba(255, 255, 255, 0.5)';
    
    // Add animation
    const duration = Math.random() * 15 + 10;
    const delay = Math.random() * 5;
    particle.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
    particle.style.opacity = Math.random() * 0.6 + 0.2;
    
    animatedBg.appendChild(particle);
  }
}

/**
 * Initialize scroll-based animations
 */
function initScrollAnimations() {
  // Change navbar color on scroll
  window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.nav-bar');
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
    
    // Parallax effect for banner
    const banner = document.querySelector('.banner');
    if (banner) {
      const scrollPosition = window.pageYOffset;
      banner.style.backgroundPositionY = scrollPosition * 0.5 + 'px';
    }
  });
  
  // Animate sections when they come into view
  const sections = document.querySelectorAll('section');
  const animateOnScroll = () => {
    sections.forEach(section => {
      const sectionTop = section.getBoundingClientRect().top;
      const windowHeight = window.innerHeight;
      
      if (sectionTop < windowHeight * 0.8) {
        section.classList.add('in-view');
      }
    });
  };
  
  window.addEventListener('scroll', animateOnScroll);
  animateOnScroll(); // Run once on load
}

/**
 * Enhance testimonial section with auto-rotation
 */
function enhanceTestimonials() {
  const testimonials = document.querySelectorAll('.testimonial-item');
  const dots = document.querySelectorAll('.dot');
  
  if (testimonials.length === 0 || dots.length === 0) return;
  
  let currentIndex = 0;
  let testimonialInterval;
  
  // Show testimonial by index
  function showTestimonial(index) {
    testimonials.forEach((item, i) => {
      item.style.display = i === index ? 'flex' : 'none';
    });
    
    dots.forEach((dot, i) => {
      dot.classList.toggle('active', i === index);
    });
  }
  
  // Initialize testimonials
  showTestimonial(0);
  
  // Set up dot click handlers
  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      currentIndex = i;
      showTestimonial(currentIndex);
      resetInterval();
    });
  });
  
  // Auto-rotation
  function startInterval() {
    testimonialInterval = setInterval(() => {
      currentIndex = (currentIndex + 1) % testimonials.length;
      showTestimonial(currentIndex);
    }, 5000);
  }
  
  function resetInterval() {
    clearInterval(testimonialInterval);
    startInterval();
  }
  
  startInterval();
}

/**
 * Helper function to generate random bright colors
 */
function getRandomBrightColor(alpha = 1) {
  const hue = Math.floor(Math.random() * 360);
  return `hsla(${hue}, 100%, 50%, ${alpha})`;
} 