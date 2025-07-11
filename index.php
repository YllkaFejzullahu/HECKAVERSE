<?php
session_start();
$isLoggedIn = isset($_SESSION['user']); // Assume you store user data in $_SESSION['user']
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HerMatchUp - STEM Mentorship Made Personal</title>
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <link rel="stylesheet" href="indexStyles.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo" onclick="window.location.href='index.php'">
                <i class="fas fa-handshake"></i>
                <span>HerMatchUp</span>
            </div>
            <div class="nav-menu" id="nav-menu">
                <a href="#home" class="nav-link">Home</a>
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <a href="#about" class="nav-link">About</a>
                <?php if ($isLoggedIn): ?>
        <a href="profile.php" class="nav-link">Profile</a>
    <?php endif; ?>
            </div>
            <div class="nav-buttons">
    <?php if (!$isLoggedIn): ?>
        <button class="btn-secondary" onclick="window.location.href='login.php'">Sign In</button>
        <button class="btn-primary" onclick="window.location.href='signup.php'">Sign Up</button>
    <?php else: ?>
        <button class="btn-secondary" onclick="window.location.href='logout.php'">Logout</button>
    <?php endif; ?>
</div>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
    <h1 class="hero-title">
        Your Journey Matters.<br>So Does the Mentor You Choose.
    </h1>
    <p class="hero-quote">
        "Behind Every Successful Girl is a Woman Who Helped Her Shine."
    </p>
    <h2 class="hero-subheading">
        Swipe. Match. Connect.
    </h2>
    <div class="hero-description-sections">
        <p class="hero-intro">
            Welcome to HerMatchUp, a place where every swipe brings you closer to someone who truly understands your journey in STEM.
        </p>
        <p class="hero-mission">
            It's not just about matching profiles — it's about finding a mentor who believes in your potential and supports your dreams.
        </p>
        <p class="hero-promise">
            Here, you'll receive guidance that's both personal and professional, helping you navigate your technical career with confidence.
        </p>
    </div>
</div>
<div class="hero-visual">
    <div class="swipe-animation-container">
        <div class="swipe-demo-card card-1">
            <div class="demo-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
            <h3>Dr. Sarah Chen</h3>
            <p>AI Research Scientist</p>
            <div class="demo-tags">
                <span>Machine Learning</span>
                <span>PhD Mentor</span>
            </div>
        </div>
        <div class="swipe-demo-card card-2">
            <div class="demo-avatar" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);"></div>
            <h3>Dr. Priya Patel</h3>
            <p>Biomedical Engineer</p>
            <div class="demo-tags">
                <span>Bioengineering</span>
                <span>Startup Founder</span>
            </div>
        </div>
    </div>
    <div class="hero-buttons">
       <?php if (!$isLoggedIn): ?>
    <button class="btn-primary btn-large" onclick="window.location.href='signup.php'">
        <i class="fas fa-heart"></i>
        Start Matching
    </button>
<?php else: ?>
    <button class="btn-primary btn-large" onclick="window.location.href='swipe.php'">
        <i class="fas fa-heart"></i>
        Start Matching
    </button>
<?php endif; ?>

        <button class="btn-secondary btn-large" onclick="scrollToSection('how-it-works')">
            <i class="fas fa-play"></i>
            How It Works
        </button>
    </div>
</div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose HerMatchUp?</h2>
                <p>Designed specifically for women in STEM, addressing unique challenges with personalized solutions</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>STEM-Specialized Matching</h3>
                    <p>Connect based on your specific field, from computer science to bioengineering, ensuring relevant guidance.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Personality Compatibility</h3>
                    <p>Match with mentors who understand your communication style and personality for better connections.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Flexible Scheduling</h3>
                    <p>Find mentors who match your availability and preferred communication methods.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Safe & Supportive</h3>
                    <p>A verified community of women creating a safe space for growth and empowerment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Career Growth Focus</h3>
                    <p>Get guidance on career advancement, technical skills, and navigating workplace challenges.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community Building</h3>
                    <p>Join a network of supportive women in STEM, building lasting professional relationships.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>How It Works</h2>
                <p>Simple, intuitive, and designed for busy STEM professionals</p>
            </div>
            <div class="steps-container">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Create Your Profile</h3>
                        <p>Tell us about your STEM background, goals, and what kind of mentorship you're seeking.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Swipe to Match</h3>
                        <p>Browse through potential mentors or mentees. Swipe right if you're interested, left if not.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Connect & Grow</h3>
                        <p>When both parties swipe right, start your mentorship journey with guided conversations.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Find Your Perfect Match?</h2>
                <p>Join thousands of women in STEM who are already building meaningful mentorship connections.</p>
               <?php if (!$isLoggedIn): ?>
    <button class="btn-primary btn-large" onclick="window.location.href='signup.php'">
        <i class="fas fa-heart"></i>
        Start Your Journey
    </button>
<?php else: ?>
    <button class="btn-primary btn-large" onclick="window.location.href='swipe.php'">
        <i class="fas fa-heart"></i>
        Start Your Journey
    </button>
<?php endif; ?>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <i class="fas fa-handshake"></i>
                        <span>HerMatchUp</span>
                    </div>
                    <p>Empowering women in STEM through meaningful mentorship connections.</p>
                </div>
                <div class="footer-section">
                    <h3>Platform</h3>
                    <ul>
                        <li><a href="swipe.php">Start Matching</a></li>
                        <li><a href="profile.php">Create Profile</a></li>
                        <li><a href="matches.html">My Matches</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety Guidelines</a></li>
                        <li><a href="#">Contact Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Connect</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 HerMatchUp. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
