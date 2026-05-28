<?php
// File: home/includes/header.php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Get settings from database
$db = getDB();
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$siteName = $settings['site_name'] ?? 'FitCoach Pro';
$primaryColor = $settings['primary_color'] ?? '#4A6FA5';
$secondaryColor = $settings['secondary_color'] ?? '#166088';
$accentColor = $settings['accent_color'] ?? '#17a2b8';
$logoUrl = $settings['logo_url'] ?? '';

$isLoggedIn = isLoggedIn();
$user = null;
$dashboardUrl = '/appF/login.php';

if ($isLoggedIn) {
    $user = getCurrentUser();
    $dashboardUrl = getRoleRedirect($user['role']);
}

// Get feature settings with defaults
$feature1_title = $settings['feature1_title'] ?? 'Personalized Plans';
$feature1_desc = $settings['feature1_desc'] ?? 'Every client gets a custom workout and nutrition plan tailored to their specific goals.';
$feature2_title = $settings['feature2_title'] ?? 'One-on-One Coaching';
$feature2_desc = $settings['feature2_desc'] ?? 'Direct access to your coach with personalized feedback and support.';
$feature3_title = $settings['feature3_title'] ?? 'Progress Tracking';
$feature3_desc = $settings['feature3_desc'] ?? 'Monitor your results with detailed analytics and progress photos.';
$feature4_title = $settings['feature4_title'] ?? 'Flexible Scheduling';
$feature4_desc = $settings['feature4_desc'] ?? 'Train on your schedule with online or in-person sessions.';
$feature5_title = $settings['feature5_title'] ?? 'Nutrition Guidance';
$feature5_desc = $settings['feature5_desc'] ?? 'Get meal plans and nutrition advice that fits your lifestyle.';
$feature6_title = $settings['feature6_title'] ?? 'Accountability';
$feature6_desc = $settings['feature6_desc'] ?? 'Stay motivated with regular check-ins and progress reviews.';

// Get hero settings
$heroTitle = $settings['hero_title'] ?? 'Transform Your Life with Personalized Coaching';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Get a custom workout and nutrition plan designed just for YOU. Start your journey today.';

// Get about settings
$aboutTitle = $settings['about_title'] ?? 'What We Do';
$aboutText = $settings['about_text'] ?? 'We provide personalized fitness coaching that adapts to YOUR lifestyle. No generic plans, no cookie-cutter programs. Every client gets a custom approach based on their goals, schedule, and preferences.';

// Get contact settings
$contactEmail = $settings['contact_email'] ?? 'coach@fitcoachpro.com';
$contactPhone = $settings['contact_phone'] ?? '+1 (555) 123-4567';
$contactAddress = $settings['contact_address'] ?? '123 Fitness Street, Health City';

// Get social media settings
$socialFacebook = $settings['social_facebook'] ?? '';
$socialInstagram = $settings['social_instagram'] ?? '';
$socialTwitter = $settings['social_twitter'] ?? '';

// Get footer settings
$footerCopyright = $settings['footer_copyright'] ?? 'All rights reserved.';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteName); ?> - Personalized Fitness Coaching</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root { 
            --primary-color: <?php echo $primaryColor; ?>; 
            --secondary-color: <?php echo $secondaryColor; ?>; 
            --accent-color: <?php echo $accentColor; ?>; 
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .hero-section { 
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); 
            color: white; 
            padding: 80px 0; 
            margin-bottom: 60px;
        }
        .btn-primary-custom { 
            background: var(--accent-color); 
            border: none; 
            padding: 12px 30px; 
            border-radius: 50px; 
            font-weight: 600;
            transition: all 0.3s;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary-custom:hover { 
            transform: translateY(-3px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            color: white;
        }
        .btn-outline-custom {
            background: transparent;
            border: 2px solid white;
            color: white;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-outline-custom:hover {
            background: white;
            color: var(--primary-color);
        }
        .feature-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            height: 100%;
            text-align: center;
        }
        .feature-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .feature-icon { font-size: 3rem; color: var(--primary-color); margin-bottom: 20px; }
        .step-circle {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        .testimonial-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            text-align: center;
        }
        .footer { background: #1a1a2e; color: white; padding: 50px 0 30px; margin-top: 60px; }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; color: var(--primary-color) !important; }
        .nav-link { font-weight: 500; }
        .btn-login { 
            background: transparent; 
            border: 2px solid var(--primary-color); 
            color: var(--primary-color); 
            padding: 8px 25px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: var(--primary-color);
            color: white;
        }
        .dashboard-btn {
            background: var(--primary-color);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            text-decoration: none;
        }
        .dashboard-btn:hover { color: white; opacity: 0.9; }
        .user-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .hero-section { padding: 40px 0; }
            .hero-section h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-light shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/appF/home/index.php">
                <?php if ($logoUrl): ?>
                    <img src="<?php echo $logoUrl; ?>" height="40" alt="Logo">
                <?php else: ?>
                    <i class="bi bi-activity me-2"></i> <?php echo htmlspecialchars($siteName); ?>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#testimonials">Testimonials</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item ms-2">
                        <?php if ($isLoggedIn): ?>
                            <div class="dropdown">
                                <div class="user-avatar-small dropdown-toggle" data-bs-toggle="dropdown">
                                    <?php echo strtoupper(substr($user['name'], 0, 2)); ?>
                                </div>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="px-3 py-2"><strong><?php echo htmlspecialchars($user['name']); ?></strong><br><small class="text-muted"><?php echo ucfirst($user['role']); ?></small></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="<?php echo $dashboardUrl; ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="/appF/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="/appF/login.php" class="btn-login">Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>