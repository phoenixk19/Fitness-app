<?php
// File: home/sections/about.php

$about_title = $settings['about_title'] ?? 'What We Do';
$about_text = $settings['about_text'] ?? 'We provide personalized fitness coaching that adapts to YOUR lifestyle. No generic plans, no cookie-cutter programs. Every client gets a custom approach based on their goals, schedule, and preferences. Whether you want to lose weight, build muscle, or just feel better - we create a plan that works for you.';
?>
<section class="container py-5" id="about">
    <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <h2 class="mb-4"><?php echo htmlspecialchars($about_title); ?></h2>
            <p class="lead"><?php echo nl2br(htmlspecialchars($about_text)); ?></p>
            <div class="mt-4">
                <h5>What makes us different:</h5>
                <ul>
                    <li>No contracts - month to month commitment</li>
                    <li>Workouts you can do at home or gym</li>
                    <li>Direct messaging with your coach</li>
                    <li>Adjustable plans as you progress</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&h=400&fit=crop" class="img-fluid rounded-4 shadow" alt="Personal Training">
        </div>
    </div>
</section>