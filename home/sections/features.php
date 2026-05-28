<?php
// File: home/sections/features.php

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
?>
<section class="container py-5" id="features">
    <h2 class="text-center mb-5">Why Train With Coach Sarah?</h2>
    <div class="row g-4">
        <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="bi bi-file-person-fill"></i></div><h4><?php echo htmlspecialchars($feature1_title); ?></h4><p class="text-muted"><?php echo htmlspecialchars($feature1_desc); ?></p></div></div>
        <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="bi bi-chat-dots-fill"></i></div><h4><?php echo htmlspecialchars($feature2_title); ?></h4><p class="text-muted"><?php echo htmlspecialchars($feature2_desc); ?></p></div></div>
        <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="bi bi-graph-up"></i></div><h4><?php echo htmlspecialchars($feature3_title); ?></h4><p class="text-muted"><?php echo htmlspecialchars($feature3_desc); ?></p></div></div>
        <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="bi bi-calendar-check-fill"></i></div><h4><?php echo htmlspecialchars($feature4_title); ?></h4><p class="text-muted"><?php echo htmlspecialchars($feature4_desc); ?></p></div></div>
        <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="bi bi-egg-fried"></i></div><h4><?php echo htmlspecialchars($feature5_title); ?></h4><p class="text-muted"><?php echo htmlspecialchars($feature5_desc); ?></p></div></div>
        <div class="col-md-4"><div class="feature-card"><div class="feature-icon"><i class="bi bi-star-fill"></i></div><h4><?php echo htmlspecialchars($feature6_title); ?></h4><p class="text-muted"><?php echo htmlspecialchars($feature6_desc); ?></p></div></div>
    </div>
</section>