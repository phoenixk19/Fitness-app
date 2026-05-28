<?php
// File: C:\xampp\htdocs\appF\login.php

// Include backend files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/middleware.php';

// Get color settings from database
$db = getDB();
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$primaryColor = $settings['primary_color'] ?? '#4A6FA5';
$secondaryColor = $settings['secondary_color'] ?? '#166088';
$accentColor = $settings['accent_color'] ?? '#17a2b8';
$siteName = $settings['site_name'] ?? 'FitCoach Pro';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/appF/dashboard/' . $_SESSION['user_role'] . '/index.php');
}

// Handle login form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $result = loginUser($email, $password, $remember);
        
        if ($result['success']) {
            // Redirect based on role to modular dashboards
            switch ($result['role']) {
                case 'admin':
                    redirect('/appF/dashboard/admin/index.php');
                    break;
                case 'coach':
                    redirect('/appF/dashboard/coach/index.php');
                    break;
                case 'editor':
                    redirect('/appF/dashboard/editor/index.php');
                    break;
                case 'client':
                    redirect('/appF/dashboard/client/index.php');
                    break;
                default:
                    redirect('/appF/index.php');
            }
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - <?php echo htmlspecialchars($siteName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --error-color: #dc3545;
            --success-color: #198754;
        }

        [data-bs-theme="dark"] {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
            --light-bg: #121212;
            --dark-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --text-light: #adb5bd;
            --error-color: #e74c3c;
            --success-color: #27ae60;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            transition: all 0.3s ease;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            background-color: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem;
            text-align: center;
        }

        .login-body {
            padding: 2.5rem;
        }

        .form-control {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 111, 165, 0.25);
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #2d2d2d;
            border-color: #444;
            color: var(--text-color);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .login-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
            width: 100%;
            color: white;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-light);
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #dee2e6;
        }

        .divider span {
            padding: 0 1rem;
        }

        [data-bs-theme="dark"] .divider::before,
        [data-bs-theme="dark"] .divider::after {
            border-bottom-color: #444;
        }

        .social-login {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-btn {
            flex: 1;
            border: 2px solid #e0e0e0;
            background: transparent;
            padding: 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .social-btn:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .forgot-password {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-light);
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border-radius: 10px;
            border: 2px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            font-weight: 500;
        }

        .action-btn:hover {
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
        }

        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 3;
        }

        .input-group .form-control {
            padding-left: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-light);
            cursor: pointer;
            z-index: 3;
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 576px) {
            .login-card {
                margin: 1rem;
            }
            
            .login-header,
            .login-body {
                padding: 1.5rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .social-login {
                flex-direction: column;
            }
        }

        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .back-to-home:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Back to Home Link -->
    <a href="/appF/index.php" class="back-to-home">
        <i class="bi bi-arrow-left"></i> Back to Home
    </a>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Login Header -->
            <div class="login-header">
                <h2><i class="bi bi-activity me-2"></i> <?php echo htmlspecialchars($siteName); ?></h2>
                <p class="mb-0">Welcome back! Sign in to your account</p>
            </div>

            <!-- Login Body -->
            <div class="login-body">
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <a href="/appF/index.php" class="action-btn">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                    <a href="/appF/signup.php" class="action-btn">
                        <i class="bi bi-person-plus me-1"></i> Sign Up
                    </a>
                </div>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <!-- Login Form -->
                <form id="loginForm" action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Email Field -->
                    <div class="input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email"
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               required>
                    </div>

                    <!-- Password Field -->
                    <div class="input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password"
                               placeholder="Enter your password"
                               required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>
                        <a href="/appF/forgot-password.php" class="forgot-password">
                            <i class="bi bi-question-circle me-1"></i> Forgot password?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn login-btn mb-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Sign In
                    </button>
                </form>

                <!-- Demo Login Info -->
                <div class="alert alert-info">
                    <small>
                        <strong>Demo Credentials:</strong><br>
                        Admin: admin@fitcoach.com / Admin@123<br>
                        Coach: sarah@fitcoach.com / Admin@123<br>
                        Client: michael@example.com / Admin@123<br>
                        Editor: editor@fitcoach.com / Admin@123
                    </small>
                </div>

                <!-- Divider -->
                <div class="divider">
                    <span>Or continue with</span>
                </div>

                <!-- Social Login -->
                <div class="social-login">
                    <button type="button" class="social-btn" onclick="alert('Google login coming soon!')">
                        <i class="bi bi-google" style="color: #DB4437;"></i> Google
                    </button>
                    <button type="button" class="social-btn" onclick="alert('Facebook login coming soon!')">
                        <i class="bi bi-facebook" style="color: #4267B2;"></i> Facebook
                    </button>
                </div>

                <!-- Sign Up Link -->
                <div class="signup-link">
                    Don't have an account? <a href="/appF/signup.php">Apply for membership</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle">
        <i class="bi bi-moon-fill" id="themeIcon"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;
        
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        htmlElement.setAttribute('data-bs-theme', savedTheme);
        updateThemeIcon(savedTheme);
        
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
        
        function updateThemeIcon(theme) {
            themeIcon.className = theme === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
        }

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = togglePassword.querySelector('i');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.className = type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        });

        // Form validation
        const loginForm = document.getElementById('loginForm');
        
        loginForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please enter both email and password');
            }
        });

        // Auto-focus on email field
        document.getElementById('email').focus();
    </script>
</body>
</html>