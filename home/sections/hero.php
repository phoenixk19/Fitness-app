<?php
// File: home/sections/hero.php
$heroTitle = $settings['homepage_hero_title'] ?? 'Transform Your Fitness Journey';
$heroSubtitle = $settings['homepage_hero_subtitle'] ?? 'Connect with expert coaches, track your progress, and achieve your fitness goals.';
?>
<section class="hero-section" id="home">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h1 class="display-4 fw-bold mb-4"><?php echo htmlspecialchars($heroTitle); ?></h1>
                <p class="lead mb-4"><?php echo htmlspecialchars($heroSubtitle); ?></p>
                <?php if (!$isLoggedIn): ?>
                    <a href="/appF/signup.php" class="btn-primary-custom me-3"><i class="bi bi-person-plus-fill me-2"></i> Apply for Membership</a>
                    <a href="#how-it-works" class="btn-outline-custom">Learn More</a>
                <?php else: ?>
                    <a href="<?php echo $dashboardUrl; ?>" class="btn-primary-custom"><i class="bi bi-speedometer2 me-2"></i> Go to Dashboard</a>
                <?php endif; ?>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=600&h=400&fit=crop" class="img-fluid rounded-4 shadow" alt="Fitness Training">
            </div>
        </div>
    </div>
</section>