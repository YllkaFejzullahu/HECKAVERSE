<?php

// Modern login implementation using improved architecture
require_once 'bootstrap.php';

use App\Utils\Response;
use App\Utils\Validator;

// If already logged in, redirect to profile
if (isset($_SESSION['user_id'])) {
    Response::redirect('profile.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize input
        $email = Validator::sanitizeString($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validate input
        $errors = Validator::validateUserInput($_POST, [
            'email' => ['required' => true, 'email' => true],
            'password' => ['required' => true]
        ]);
        
        if (!empty($errors)) {
            Response::error('Validation failed', 400, $errors);
        }
        
        // Check user exists and get password
        $user = queryOne("SELECT id, email, password, role FROM users WHERE email = ?", [$email]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            logInfo("Failed login attempt", ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
            Response::error('Invalid email or password', 401);
        }
        
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        
        logInfo("Successful login", ['user_id' => $user['id'], 'email' => $email]);
        
        // Check if profile is complete
        $profile = queryOne("SELECT id, first_name, last_name FROM profiles WHERE user_id = ?", [$user['id']]);
        
        $redirectUrl = $profile ? 'profile.php' : 'profile-setup.html';
        
        // Return JSON for AJAX requests, redirect for regular form submissions
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            Response::success('Login successful', [
                'redirect' => $redirectUrl,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $profile ? trim($profile['first_name'] . ' ' . $profile['last_name']) : null
                ]
            ]);
        } else {
            Response::redirect($redirectUrl);
        }
        
    } catch (Exception $e) {
        logError("Login error", [
            'error' => $e->getMessage(),
            'email' => $_POST['email'] ?? 'unknown'
        ]);
        
        Response::error('An unexpected error occurred. Please try again.', 500);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - HerMatchUp</title>
    
    <!-- Modern CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    
    <!-- Custom Styles -->
    <link href="assets/css/main.css" rel="stylesheet">
    
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            background: linear-gradient(135deg, #ff6b9d, #667eea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #ff6b9d, #e91e63);
            border: none;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 107, 157, 0.4);
        }
        
        .social-login {
            text-align: center;
            margin: 2rem 0;
        }
        
        .divider {
            position: relative;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }
        
        .divider span {
            background: rgba(255, 255, 255, 0.95);
            padding: 0 1rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card animate__animated animate__fadeInUp">
            <div class="logo">
                <h1><i class="fas fa-handshake"></i> HerMatchUp</h1>
                <p class="text-muted">Welcome back! Sign in to continue your journey.</p>
            </div>
            
            <form id="loginForm" method="POST" data-ajax="true">
                <div class="form-floating">
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                    <label for="email"><i class="fas fa-envelope me-2"></i>Email address</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            
            <div class="divider">
                <span>or</span>
            </div>
            
            <div class="social-login">
                <button class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fab fa-google me-2"></i>Continue with Google
                </button>
                <button class="btn btn-outline-secondary w-100">
                    <i class="fab fa-linkedin me-2"></i>Continue with LinkedIn
                </button>
            </div>
            
            <div class="text-center mt-4">
                <p class="mb-2">
                    <a href="forgot-password.html" class="text-decoration-none">Forgot your password?</a>
                </p>
                <p>
                    Don't have an account? 
                    <a href="signup.php" class="text-decoration-none fw-bold">Sign up</a>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Modern JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Modern form handling with AJAX
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            
            try {
                const formData = new FormData(this);
                const response = await axios.post(window.location.href, formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Welcome back!',
                        text: response.data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.data.data.redirect;
                    });
                } else {
                    throw new Error(response.data.message);
                }
            } catch (error) {
                let errorMessage = 'An unexpected error occurred. Please try again.';
                
                if (error.response && error.response.data && error.response.data.message) {
                    errorMessage = error.response.data.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: errorMessage
                });
            } finally {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        // Add some visual feedback
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
    </script>
</body>
</html>