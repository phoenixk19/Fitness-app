<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitCoach Pro - Fitness Coaching Platform</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-color: #4A6FA5;
            --secondary-color: #166088;
            --accent-color: #17a2b8;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
        }

        [data-bs-theme="dark"] {
            --primary-color: #5d8fd8;
            --secondary-color: #1c7ebd;
            --accent-color: #20c997;
            --light-bg: #121212;
            --dark-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-color: #f8f9fa;
            --text-light: #adb5bd;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 0 0 30px 30px;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .hero-slider {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .carousel-item {
            height: 400px;
        }

        .carousel-item img {
            object-fit: cover;
            height: 100%;
            width: 100%;
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            padding: 20px;
        }

        .app-description {
            background-color: var(--card-bg);
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }

        .get-started-btn {
            background: linear-gradient(45deg, var(--accent-color), var(--primary-color));
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: bold;
            border-radius: 50px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .get-started-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .feature-card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: 15px;
            height: 100%;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
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

        .footer {
            background-color: var(--dark-bg);
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }

        .nav-brand {
            color: var(--primary-color) !important;
            font-weight: bold;
            font-size: 1.5rem;
        }

        .login-btn {
            background-color: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .login-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .hero-slider, .carousel-item {
                height: 300px;
            }
            
            .app-description {
                padding: 2rem 1rem;
            }
            
            .carousel-caption h3 {
                font-size: 1.2rem;
            }
            
            .carousel-caption p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .hero-section {
                padding: 1rem 0;
            }
            
            .hero-slider, .carousel-item {
                height: 250px;
            }
            
            .feature-card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm" style="background-color: var(--card-bg) !important;">
        <div class="container">
            <a class="navbar-brand nav-brand" href="#">
                <i class="bi bi-activity"></i> FitCoach Pro
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>

                        
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn login-btn" href="appf/signup.php">Login/Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Image Slider -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">Transform Your Fitness Journey</h1>
                    <p class="lead mb-4">Connect coaches and clients in one powerful platform. Track progress, schedule sessions, and achieve goals together.</p>
                    <a href="#" class="btn get-started-btn">
                        <i class="bi bi-rocket-takeoff"></i> Get Started Free
                    </a>
                </div>
                
                <div class="col-lg-6">
                    <div class="hero-slider">
                        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                            </div>
                            
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Personal Training Session">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h3>Personalized Coaching</h3>
                                        <p>One-on-one sessions tailored to your goals</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Workout Tracking">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h3>Progress Tracking</h3>
                                        <p>Monitor your fitness journey with detailed analytics</p>
                                    </div>
                                </div>
                                <div class="carousel-item">
                                    <img src="https://images.unsplash.com/photo-1540497077202-7c8a3999166f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Group Training">
                                    <div class="carousel-caption d-none d-md-block">
                                        <h3>Group Sessions</h3>
                                        <p>Join community workouts with your coach</p>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- App Description Section -->
    <section class="container" id="about">
        <div class="app-description">
            <h2 class="text-center mb-4">About FitCoach Pro</h2>
            <p class="lead text-center mb-4">
                FitCoach Pro is a comprehensive platform designed to bridge the gap between fitness professionals and their clients. 
                Our mission is to make fitness coaching more accessible, efficient, and results-driven.
            </p>
            
            <div class="row mt-5">
                <div class="col-md-6 mb-4">
                    <h4><i class="bi bi-check-circle-fill text-success me-2"></i> For Coaches</h4>
                    <p class="text-muted">
                        Manage your entire client base from one dashboard. Create customized workout plans, track client progress, 
                        schedule sessions, and process payments seamlessly. Focus on coaching while we handle the logistics.
                    </p>
                </div>
                <div class="col-md-6 mb-4">
                    <h4><i class="bi bi-check-circle-fill text-success me-2"></i> For Clients</h4>
                    <p class="text-muted">
                        Access personalized workout plans, nutrition guidance, and progress tracking all in one app. 
                        Communicate directly with your coach, join virtual sessions, and stay motivated with achievement badges.
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="#" class="btn get-started-btn">
                    Start Your Free Trial <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container py-5" id="features">
        <h2 class="text-center mb-5">Powerful Features</h2>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-calendar-check-fill fs-1 text-primary"></i>
                    </div>
                    <h4>Smart Scheduling</h4>
                    <p>Automated booking system with calendar sync and reminders for both coaches and clients.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-bar-chart-fill fs-1 text-primary"></i>
                    </div>
                    <h4>Progress Analytics</h4>
                    <p>Track workouts, measurements, and goals with detailed charts and progress reports.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-chat-dots-fill fs-1 text-primary"></i>
                    </div>
                    <h4>Direct Messaging</h4>
                    <p>Secure in-app communication between coaches and clients with file sharing capabilities.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-camera-video-fill fs-1 text-primary"></i>
                    </div>
                    <h4>Virtual Sessions</h4>
                    <p>Integrated video calling for remote training sessions with screen sharing capabilities.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-credit-card-fill fs-1 text-primary"></i>
                    </div>
                    <h4>Secure Payments</h4>
                    <p>Process payments, manage subscriptions, and handle invoicing all within the platform.</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-phone-fill fs-1 text-primary"></i>
                    </div>
                    <h4>Mobile App</h4>
                    <p>Access all features on iOS and Android devices with push notifications and offline mode.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="container py-5 text-center">
        <div class="p-5 rounded" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
            <h2 class="display-5 fw-bold mb-3">Ready to Transform Your Fitness Business?</h2>
            <p class="lead mb-4">Join thousands of coaches and clients already using FitCoach Pro</p>
            <a href="#" class="btn btn-light btn-lg px-5 py-3 fw-bold" style="color: var(--primary-color);">
                <i class="bi bi-person-plus-fill me-2"></i> Sign Up Free
            </a>
            <p class="mt-3">No credit card required • 14-day free trial</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h4 class="mb-3">
                        <i class="bi bi-activity me-2"></i> FitCoach Pro
                    </h4>
                    <p>Connecting fitness coaches with clients worldwide. Making fitness coaching accessible, efficient, and effective.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-linkedin fs-5"></i></a>
                    </div>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5 class="mb-3">Platform</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">For Coaches</a></li>
                        <li><a href="#" class="text-light text-decoration-none">For Clients</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Pricing</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Features</a></li>
                    </ul>
                </div>
                
                <div class="col-md-2 mb-4">
                    <h5 class="mb-3">Company</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-light text-decoration-none">About Us</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Careers</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Blog</a></li>
                        <li><a href="#" class="text-light text-decoration-none">Press</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">Contact Us</h5>
                    <p><i class="bi bi-envelope me-2"></i> support@fitcoachpro.com</p>
                    <p><i class="bi bi-telephone me-2"></i> +1 (555) 123-4567</p>
                    <p><i class="bi bi-geo-alt me-2"></i> 123 Fitness Street, Health City</p>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 FitCoach Pro. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="#" class="text-light text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-light text-decoration-none me-3">Terms of Service</a>
                    <a href="#" class="text-light text-decoration-none">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

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
        
        // Check for saved theme or prefer-color-scheme
        const savedTheme = localStorage.getItem('theme') || 
                          (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply saved theme
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
        
        // Auto-advance carousel every 5 seconds
        const heroCarousel = document.getElementById('heroCarousel');
        if (heroCarousel) {
            const carousel = new bootstrap.Carousel(heroCarousel, {
                interval: 5000,
                wrap: true
            });
        }
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if(this.getAttribute('href') === '#') return;
                
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if(targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 70,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>