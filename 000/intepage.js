// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 50) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  });

  // Create random food icons
  const foodIcons = document.getElementById('foodIcons');
  const foodEmojis = ['🍕', '🍔', '🍟', '🌮', '🍣', '🍜', '🍩', '🍦', '☕', '🥗', '🥪', '🍱'];
  
  for (let i = 0; i < 20; i++) {
    const foodIcon = document.createElement('div');
    foodIcon.className = 'food-icon';
    foodIcon.textContent = foodEmojis[Math.floor(Math.random() * foodEmojis.length)];
    foodIcon.style.left = Math.random() * 100 + 'vw';
    foodIcon.style.top = Math.random() * 100 + 'vh';
    foodIcon.style.animationDelay = Math.random() * 10 + 's';
    foodIcons.appendChild(foodIcon);
  }

  // Add hover effects to canteen cards
  const canteenCards = document.querySelectorAll('.canteen-card');
  canteenCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
      this.querySelector('.canteen-description').style.webkitLineClamp = '3';
    });
    
    card.addEventListener('mouseleave', function() {
      this.querySelector('.canteen-description').style.webkitLineClamp = '2';
    });
  });

  // Contact Form Modal
  const contactLink = document.getElementById('contact-link');
  const contactModal = document.getElementById('contact-modal');
  const closeContactForm = document.getElementById('close-contact-form');
  const contactForm = document.getElementById('contact-form');
  const successMessage = document.getElementById('success-message');
  const successCloseBtn = document.getElementById('success-close-btn');

  // Display user name in the form if logged in
  window.addEventListener('DOMContentLoaded', function() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (currentUser && currentUser.name) {
      const contactNameInput = document.getElementById('contact-name');
      if (contactNameInput) {
        contactNameInput.value = currentUser.name;
      }
      
      const contactEmailInput = document.getElementById('contact-email');
      if (contactEmailInput && currentUser.email) {
        contactEmailInput.value = currentUser.email;
      }
    }
  });

  // Open contact modal
  contactLink.addEventListener('click', function() {
    contactModal.classList.add('active');
    document.body.style.overflow = 'hidden';
  });

  // Close contact modal
  closeContactForm.addEventListener('click', function() {
    contactModal.classList.remove('active');
    document.body.style.overflow = '';
  });

  // Close modal when clicking outside
  contactModal.addEventListener('click', function(e) {
    if (e.target === contactModal) {
      contactModal.classList.remove('active');
      document.body.style.overflow = '';
    }
  });

  // Form submission
  contactForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Form validation
    let isValid = true;
    const name = document.getElementById('contact-name');
    const email = document.getElementById('contact-email');
    const subject = document.getElementById('contact-subject');
    const message = document.getElementById('contact-message');
    
    // Reset error messages
    document.querySelectorAll('.form-error').forEach(error => {
      error.style.display = 'none';
    });
    
    // Validate name
    if (!name.value.trim()) {
      document.getElementById('name-error').style.display = 'block';
      isValid = false;
    }
    
    // Validate email
    if (!email.value.trim() || !isValidEmail(email.value)) {
      document.getElementById('email-error').style.display = 'block';
      isValid = false;
    }
    
    // Validate subject
    if (!subject.value) {
      document.getElementById('subject-error').style.display = 'block';
      isValid = false;
    }
    
    // Validate message
    if (!message.value.trim()) {
      document.getElementById('message-error').style.display = 'block';
      isValid = false;
    }
    
    if (isValid) {
      // Simulate form submission
      setTimeout(function() {
        successMessage.classList.add('active');
      }, 1000);
    }
  });

  // Close success message
  successCloseBtn.addEventListener('click', function() {
    successMessage.classList.remove('active');
    contactModal.classList.remove('active');
    document.body.style.overflow = '';
    contactForm.reset();
  });

  // Helper function to validate email
  function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  }

  // Display user name if logged in
  window.addEventListener('DOMContentLoaded', function() {
    const currentUser = JSON.parse(localStorage.getItem('currentUser'));
    if (currentUser && currentUser.name) {
      const welcomeMessage = document.querySelector('.title-section p');
      if (welcomeMessage) {
        welcomeMessage.textContent = `Welcome, ${currentUser.name}! Explore our three unique canteens, each offering a distinctive culinary experience designed to satisfy your cravings.`;
      }
    }
  });