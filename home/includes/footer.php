<?php
// File: home/includes/footer.php

$contactEmail = $settings['contact_email'] ?? 'sarah@fitcoachpro.com';
$contactPhone = $settings['contact_phone'] ?? '+1 (555) 123-4567';
$contactAddress = $settings['contact_address'] ?? '123 Fitness Street, Health City';
$siteName = $settings['site_name'] ?? 'FitCoach Pro';
$socialFacebook = $settings['social_facebook'] ?? '';
$socialInstagram = $settings['social_instagram'] ?? '';
$socialTwitter = $settings['social_twitter'] ?? '';
$footerCopyright = $settings['footer_copyright'] ?? 'All rights reserved.';
?>
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="bi bi-activity me-2"></i> Coach Sarah Chen</h5>
                    <p>Helping you become the best version of yourself through personalized fitness coaching.</p>
                    <?php if ($socialFacebook || $socialInstagram || $socialTwitter): ?>
                    <div class="mt-3">
                        <?php if ($socialFacebook): ?><a href="<?php echo $socialFacebook; ?>" class="text-white me-3" target="_blank"><i class="bi bi-facebook fs-5"></i></a><?php endif; ?>
                        <?php if ($socialInstagram): ?><a href="<?php echo $socialInstagram; ?>" class="text-white me-3" target="_blank"><i class="bi bi-instagram fs-5"></i></a><?php endif; ?>
                        <?php if ($socialTwitter): ?><a href="<?php echo $socialTwitter; ?>" class="text-white me-3" target="_blank"><i class="bi bi-twitter fs-5"></i></a><?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Me</h5>
                    <p><i class="bi bi-envelope me-2"></i> <a href="mailto:<?php echo $contactEmail; ?>" class="text-light text-decoration-none"><?php echo htmlspecialchars($contactEmail); ?></a></p>
                    <p><i class="bi bi-telephone me-2"></i> <a href="tel:<?php echo $contactPhone; ?>" class="text-light text-decoration-none"><?php echo htmlspecialchars($contactPhone); ?></a></p>
                    <p><i class="bi bi-geo-alt me-2"></i> <?php echo htmlspecialchars($contactAddress); ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#home" class="text-light text-decoration-none">Home</a></li>
                        <li><a href="#features" class="text-light text-decoration-none">Why Train With Me</a></li>
                        <li><a href="#how-it-works" class="text-light text-decoration-none">How It Works</a></li>
                        <li><a href="/appF/signup.php" class="text-light text-decoration-none">Apply for Coaching</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color:rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Coach Sarah Chen. <?php echo htmlspecialchars($footerCopyright); ?></p>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>