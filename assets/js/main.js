// Import dependencies
import 'bootstrap/dist/css/bootstrap.min.css';
import 'animate.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import AOS from 'aos';
import 'aos/dist/aos.css';
import Swal from 'sweetalert2';
import axios from 'axios';

// Import custom styles
import '../css/main.css';

// Global configuration
window.axios = axios;
window.Swal = Swal;

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.post['Content-Type'] = 'application/json';

// CSRF token if available
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        once: true,
        offset: 100
    });

    // Initialize navigation
    initializeNavigation();
    
    // Initialize swipe functionality
    initializeSwipeCards();
    
    // Initialize modals
    initializeModals();
    
    // Initialize form handlers
    initializeFormHandlers();
    
    // Initialize scroll effects
    initializeScrollEffects();
}

function initializeNavigation() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

function initializeSwipeCards() {
    const cards = document.querySelectorAll('.swipe-card');
    
    cards.forEach(card => {
        let isDragging = false;
        let startX = 0;
        let currentX = 0;
        let cardOffset = 0;

        // Touch events
        card.addEventListener('touchstart', handleStart, { passive: true });
        card.addEventListener('touchmove', handleMove, { passive: true });
        card.addEventListener('touchend', handleEnd, { passive: true });

        // Mouse events
        card.addEventListener('mousedown', handleStart);
        card.addEventListener('mousemove', handleMove);
        card.addEventListener('mouseup', handleEnd);

        function handleStart(e) {
            isDragging = true;
            startX = getClientX(e);
            card.style.transition = 'none';
        }

        function handleMove(e) {
            if (!isDragging) return;
            
            e.preventDefault();
            currentX = getClientX(e);
            cardOffset = currentX - startX;
            
            const rotation = cardOffset * 0.1;
            const opacity = Math.max(0.3, 1 - Math.abs(cardOffset) / 300);
            
            card.style.transform = `translateX(${cardOffset}px) rotate(${rotation}deg)`;
            card.style.opacity = opacity;
        }

        function handleEnd() {
            if (!isDragging) return;
            
            isDragging = false;
            card.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            
            const threshold = card.offsetWidth * 0.3;
            
            if (Math.abs(cardOffset) > threshold) {
                // Swipe decision
                const direction = cardOffset > 0 ? 'right' : 'left';
                animateCardExit(card, direction);
                handleSwipeDecision(card, direction);
            } else {
                // Return to center
                card.style.transform = 'translateX(0) rotate(0deg)';
                card.style.opacity = '1';
            }
            
            cardOffset = 0;
        }

        function getClientX(e) {
            return e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        }
    });
}

function animateCardExit(card, direction) {
    const exitDistance = window.innerWidth;
    const rotation = direction === 'right' ? 30 : -30;
    
    card.style.transform = `translateX(${direction === 'right' ? exitDistance : -exitDistance}px) rotate(${rotation}deg)`;
    card.style.opacity = '0';
    
    setTimeout(() => {
        card.remove();
    }, 300);
}

function handleSwipeDecision(card, direction) {
    const userId = card.dataset.userId;
    const action = direction === 'right' ? 'like' : 'pass';
    
    // Send decision to backend
    axios.post('/api/swipe.php', {
        user_id: userId,
        action: action
    })
    .then(response => {
        if (response.data.match) {
            showMatchNotification(response.data.user);
        }
    })
    .catch(error => {
        console.error('Swipe error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Something went wrong with your swipe!'
        });
    });
}

function showMatchNotification(user) {
    Swal.fire({
        icon: 'success',
        title: 'It\'s a Match!',
        html: `You and ${user.name} liked each other!`,
        showConfirmButton: true,
        confirmButtonText: 'Start Chatting',
        showCancelButton: true,
        cancelButtonText: 'Keep Swiping'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `/chat.html?user=${user.id}`;
        }
    });
}

function initializeModals() {
    // Initialize Bootstrap modals if present
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
}

function initializeFormHandlers() {
    // Handle form submissions with AJAX
    const forms = document.querySelectorAll('form[data-ajax="true"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const url = this.action || window.location.href;
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Loading...';
            submitBtn.disabled = true;
            
            axios.post(url, formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.data.message
                        });
                        
                        if (response.data.redirect) {
                            setTimeout(() => {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                    } else {
                        throw new Error(response.data.message);
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.response?.data?.message || error.message || 'Something went wrong!'
                    });
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
        });
    });
}

function initializeScrollEffects() {
    let ticking = false;
    
    function updateScrollEffects() {
        const scrolled = window.pageYOffset;
        const navbar = document.querySelector('.navbar');
        
        if (navbar) {
            if (scrolled > 100) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        }
        
        ticking = false;
    }
    
    function requestScrollUpdate() {
        if (!ticking) {
            requestAnimationFrame(updateScrollEffects);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestScrollUpdate);
}

// Export for global use
window.HerMatchUp = {
    showSuccessMessage: (message) => {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    },
    
    showErrorMessage: (message) => {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    },
    
    confirm: (message) => {
        return Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, do it!'
        });
    }
};