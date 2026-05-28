<?php
// File: home/sections/how-it-works.php
?>
<section class="bg-light py-5" id="how-it-works">
    <div class="container">
        <h2 class="text-center mb-5">How It Works</h2>
        <div class="row text-center">
            <div class="col-md-3"><div class="step-circle">1</div><h5>Apply Online</h5><p class="text-muted">Fill out the membership application form</p></div>
            <div class="col-md-3"><div class="step-circle">2</div><h5>Free Consultation</h5><p class="text-muted">Schedule a call to discuss your goals</p></div>
            <div class="col-md-3"><div class="step-circle">3</div><h5>Get Your Plan</h5><p class="text-muted">Receive your personalized workout & nutrition plan</p></div>
            <div class="col-md-3"><div class="step-circle">4</div><h5>Start Training</h5><p class="text-muted">Begin your journey with coach support</p></div>
        </div>
        <div class="text-center mt-5">
            <?php if (!$isLoggedIn): ?>
                <a href="/appF/signup.php" class="btn-primary-custom">Apply for Free Consultation</a>
            <?php else: ?>
                <a href="<?php echo $dashboardUrl; ?>" class="btn-primary-custom">Go to Your Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>