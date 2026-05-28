<?php
// File: dashboard/admin/branding.php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save Colors
    $primaryColor = sanitize($_POST['primary_color'] ?? '#4A6FA5');
    $secondaryColor = sanitize($_POST['secondary_color'] ?? '#166088');
    $accentColor = sanitize($_POST['accent_color'] ?? '#17a2b8');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('primary_color', '$primaryColor') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('secondary_color', '$secondaryColor') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('accent_color', '$accentColor') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    // Save Logo
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        $filename = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename);
        $logoUrl = '/uploads/' . $filename;
        $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('logo_url', '$logoUrl') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    }
    
    // Save Favicon
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/';
        $filename = 'favicon_' . time() . '.' . pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['favicon']['tmp_name'], $uploadDir . $filename);
        $faviconUrl = '/uploads/' . $filename;
        $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('favicon_url', '$faviconUrl') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    }
    
    // Save Homepage Hero Section
    $heroTitle = sanitize($_POST['hero_title'] ?? 'Transform Your Fitness Journey');
    $heroSubtitle = sanitize($_POST['hero_subtitle'] ?? 'Connect with expert coaches, track your progress, and achieve your fitness goals.');
    $heroImage = sanitize($_POST['hero_image'] ?? '');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('hero_title', '$heroTitle') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('hero_subtitle', '$heroSubtitle') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('hero_image', '$heroImage') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    // Save Features Section
    $feature1_title = sanitize($_POST['feature1_title'] ?? 'Expert Coaches');
    $feature1_desc = sanitize($_POST['feature1_desc'] ?? 'Work with certified professionals who care about your success.');
    $feature2_title = sanitize($_POST['feature2_title'] ?? 'Track Progress');
    $feature2_desc = sanitize($_POST['feature2_desc'] ?? 'Monitor your fitness journey with detailed analytics.');
    $feature3_title = sanitize($_POST['feature3_title'] ?? 'Flexible Scheduling');
    $feature3_desc = sanitize($_POST['feature3_desc'] ?? 'Train when it suits you best with online sessions.');
    $feature4_title = sanitize($_POST['feature4_title'] ?? 'Nutrition Guidance');
    $feature4_desc = sanitize($_POST['feature4_desc'] ?? 'Get personalized meal plans and nutrition advice.');
    $feature5_title = sanitize($_POST['feature5_title'] ?? 'Community Support');
    $feature5_desc = sanitize($_POST['feature5_desc'] ?? 'Join a community of like-minded fitness enthusiasts.');
    $feature6_title = sanitize($_POST['feature6_title'] ?? 'Mobile App');
    $feature6_desc = sanitize($_POST['feature6_desc'] ?? 'Access your workouts anywhere, anytime.');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature1_title', '$feature1_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature1_desc', '$feature1_desc') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature2_title', '$feature2_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature2_desc', '$feature2_desc') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature3_title', '$feature3_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature3_desc', '$feature3_desc') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature4_title', '$feature4_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature4_desc', '$feature4_desc') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature5_title', '$feature5_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature5_desc', '$feature5_desc') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature6_title', '$feature6_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('feature6_desc', '$feature6_desc') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    // Save About Section
    $about_title = sanitize($_POST['about_title'] ?? 'About FitCoach Pro');
    $about_text = sanitize($_POST['about_text'] ?? 'FitCoach Pro is a comprehensive platform designed to bridge the gap between fitness professionals and their clients. Our mission is to make professional fitness coaching accessible to everyone, regardless of their location or fitness level.');
    $about_image = sanitize($_POST['about_image'] ?? '');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('about_title', '$about_title') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('about_text', '$about_text') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('about_image', '$about_image') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    // Save Contact Info
    $contact_email = sanitize($_POST['contact_email'] ?? 'support@fitcoachpro.com');
    $contact_phone = sanitize($_POST['contact_phone'] ?? '+1 (555) 123-4567');
    $contact_address = sanitize($_POST['contact_address'] ?? '123 Fitness Street, Health City');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('contact_email', '$contact_email') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('contact_phone', '$contact_phone') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('contact_address', '$contact_address') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    // Save Social Media Links
    $social_facebook = sanitize($_POST['social_facebook'] ?? '');
    $social_instagram = sanitize($_POST['social_instagram'] ?? '');
    $social_twitter = sanitize($_POST['social_twitter'] ?? '');
    $social_linkedin = sanitize($_POST['social_linkedin'] ?? '');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('social_facebook', '$social_facebook') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('social_instagram', '$social_instagram') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('social_twitter', '$social_twitter') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('social_linkedin', '$social_linkedin') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    // Save Footer Text
    $footer_copyright = sanitize($_POST['footer_copyright'] ?? 'All rights reserved.');
    $footer_links = sanitize($_POST['footer_links'] ?? 'Privacy Policy|Terms of Service');
    
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('footer_copyright', '$footer_copyright') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $db->query("INSERT INTO site_settings (setting_key, setting_value) VALUES ('footer_links', '$footer_links') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    
    echo '<script>alert("All settings saved successfully!"); window.location.href="branding.php";</script>';
}

// Load all settings
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM site_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .settings-section {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid rgba(0,0,0,0.1);
        }
        .settings-section h4 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
        }
        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            margin-top: 5px;
            border: 2px solid #ddd;
        }
        .image-preview {
            max-width: 150px;
            max-height: 80px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="page-title">
    <i class="bi bi-palette"></i>
    <h2 class="mb-0">Branding & Site Settings</h2>
</div>

<form method="POST" enctype="multipart/form-data">
    <!-- Colors Section -->
    <div class="settings-section">
        <h4><i class="bi bi-palette-fill me-2"></i> Colors</h4>
        <div class="row">
            <div class="col-md-4">
                <label class="form-label">Primary Color</label>
                <input type="color" name="primary_color" id="primaryColor" class="form-control" value="<?php echo htmlspecialchars($settings['primary_color'] ?? '#4A6FA5'); ?>" onchange="updatePreview()">
                <div id="primaryPreview" class="color-preview" style="background:<?php echo htmlspecialchars($settings['primary_color'] ?? '#4A6FA5'); ?>"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Secondary Color</label>
                <input type="color" name="secondary_color" id="secondaryColor" class="form-control" value="<?php echo htmlspecialchars($settings['secondary_color'] ?? '#166088'); ?>" onchange="updatePreview()">
                <div id="secondaryPreview" class="color-preview" style="background:<?php echo htmlspecialchars($settings['secondary_color'] ?? '#166088'); ?>"></div>
            </div>
            <div class="col-md-4">
                <label class="form-label">Accent Color</label>
                <input type="color" name="accent_color" id="accentColor" class="form-control" value="<?php echo htmlspecialchars($settings['accent_color'] ?? '#17a2b8'); ?>" onchange="updatePreview()">
                <div id="accentPreview" class="color-preview" style="background:<?php echo htmlspecialchars($settings['accent_color'] ?? '#17a2b8'); ?>"></div>
            </div>
        </div>
        <div class="mt-3 p-3 bg-light rounded">
            <strong>Live Preview:</strong>
            <div style="height:30px; background:<?php echo htmlspecialchars($settings['primary_color'] ?? '#4A6FA5'); ?>; border-radius:5px; margin-top:10px;"></div>
            <div style="height:20px; background:<?php echo htmlspecialchars($settings['secondary_color'] ?? '#166088'); ?>; border-radius:5px; margin-top:5px; width:70%;"></div>
            <div style="height:10px; background:<?php echo htmlspecialchars($settings['accent_color'] ?? '#17a2b8'); ?>; border-radius:5px; margin-top:5px; width:50%;"></div>
        </div>
    </div>

    <!-- Logo & Favicon -->
    <div class="settings-section">
        <h4><i class="bi bi-image me-2"></i> Logo & Favicon</h4>
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                <?php if (!empty($settings['logo_url'])): ?>
                    <img src="<?php echo $settings['logo_url']; ?>" class="image-preview" alt="Current Logo">
                    <div class="small text-muted">Current Logo</div>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Favicon</label>
                <input type="file" name="favicon" class="form-control" accept="image/*">
                <?php if (!empty($settings['favicon_url'])): ?>
                    <img src="<?php echo $settings['favicon_url']; ?>" style="width:32px; height:32px; margin-top:10px;" alt="Favicon">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="settings-section">
        <h4><i class="bi bi-stars me-2"></i> Hero Section</h4>
        <div class="mb-3">
            <label class="form-label">Hero Title</label>
            <input type="text" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($settings['hero_title'] ?? 'Transform Your Fitness Journey'); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Hero Subtitle</label>
            <textarea name="hero_subtitle" class="form-control" rows="2"><?php echo htmlspecialchars($settings['hero_subtitle'] ?? 'Connect with expert coaches, track your progress, and achieve your fitness goals.'); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Hero Image URL (optional)</label>
            <input type="text" name="hero_image" class="form-control" value="<?php echo htmlspecialchars($settings['hero_image'] ?? ''); ?>" placeholder="https://example.com/image.jpg">
        </div>
    </div>

    <!-- Features Section -->
    <div class="settings-section">
        <h4><i class="bi bi-grid-3x3-gap-fill me-2"></i> Features (6 items)</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Feature 1 Title</label>
                <input type="text" name="feature1_title" class="form-control" value="<?php echo htmlspecialchars($settings['feature1_title'] ?? 'Expert Coaches'); ?>">
                <label class="form-label mt-2">Feature 1 Description</label>
                <textarea name="feature1_desc" class="form-control" rows="2"><?php echo htmlspecialchars($settings['feature1_desc'] ?? 'Work with certified professionals who care about your success.'); ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Feature 2 Title</label>
                <input type="text" name="feature2_title" class="form-control" value="<?php echo htmlspecialchars($settings['feature2_title'] ?? 'Track Progress'); ?>">
                <label class="form-label mt-2">Feature 2 Description</label>
                <textarea name="feature2_desc" class="form-control" rows="2"><?php echo htmlspecialchars($settings['feature2_desc'] ?? 'Monitor your fitness journey with detailed analytics.'); ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Feature 3 Title</label>
                <input type="text" name="feature3_title" class="form-control" value="<?php echo htmlspecialchars($settings['feature3_title'] ?? 'Flexible Scheduling'); ?>">
                <label class="form-label mt-2">Feature 3 Description</label>
                <textarea name="feature3_desc" class="form-control" rows="2"><?php echo htmlspecialchars($settings['feature3_desc'] ?? 'Train when it suits you best with online sessions.'); ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Feature 4 Title</label>
                <input type="text" name="feature4_title" class="form-control" value="<?php echo htmlspecialchars($settings['feature4_title'] ?? 'Nutrition Guidance'); ?>">
                <label class="form-label mt-2">Feature 4 Description</label>
                <textarea name="feature4_desc" class="form-control" rows="2"><?php echo htmlspecialchars($settings['feature4_desc'] ?? 'Get personalized meal plans and nutrition advice.'); ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Feature 5 Title</label>
                <input type="text" name="feature5_title" class="form-control" value="<?php echo htmlspecialchars($settings['feature5_title'] ?? 'Community Support'); ?>">
                <label class="form-label mt-2">Feature 5 Description</label>
                <textarea name="feature5_desc" class="form-control" rows="2"><?php echo htmlspecialchars($settings['feature5_desc'] ?? 'Join a community of like-minded fitness enthusiasts.'); ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Feature 6 Title</label>
                <input type="text" name="feature6_title" class="form-control" value="<?php echo htmlspecialchars($settings['feature6_title'] ?? 'Mobile App'); ?>">
                <label class="form-label mt-2">Feature 6 Description</label>
                <textarea name="feature6_desc" class="form-control" rows="2"><?php echo htmlspecialchars($settings['feature6_desc'] ?? 'Access your workouts anywhere, anytime.'); ?></textarea>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <div class="settings-section">
        <h4><i class="bi bi-info-circle-fill me-2"></i> About Section</h4>
        <div class="mb-3">
            <label class="form-label">About Title</label>
            <input type="text" name="about_title" class="form-control" value="<?php echo htmlspecialchars($settings['about_title'] ?? 'About FitCoach Pro'); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">About Text</label>
            <textarea name="about_text" class="form-control" rows="4"><?php echo htmlspecialchars($settings['about_text'] ?? 'FitCoach Pro is a comprehensive platform designed to bridge the gap between fitness professionals and their clients. Our mission is to make professional fitness coaching accessible to everyone, regardless of their location or fitness level.'); ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">About Image URL (optional)</label>
            <input type="text" name="about_image" class="form-control" value="<?php echo htmlspecialchars($settings['about_image'] ?? ''); ?>" placeholder="https://example.com/about-image.jpg">
        </div>
    </div>

    <!-- Contact Information -->
    <div class="settings-section">
        <h4><i class="bi bi-envelope-fill me-2"></i> Contact Information</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email'] ?? 'support@fitcoachpro.com'); ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone'] ?? '+1 (555) 123-4567'); ?>">
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">Address</label>
                <input type="text" name="contact_address" class="form-control" value="<?php echo htmlspecialchars($settings['contact_address'] ?? '123 Fitness Street, Health City'); ?>">
            </div>
        </div>
    </div>

    <!-- Social Media Links -->
    <div class="settings-section">
        <h4><i class="bi bi-share-fill me-2"></i> Social Media Links</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><i class="bi bi-facebook me-1"></i> Facebook URL</label>
                <input type="url" name="social_facebook" class="form-control" value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/yourpage">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><i class="bi bi-instagram me-1"></i> Instagram URL</label>
                <input type="url" name="social_instagram" class="form-control" value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/yourprofile">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><i class="bi bi-twitter me-1"></i> Twitter URL</label>
                <input type="url" name="social_twitter" class="form-control" value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>" placeholder="https://twitter.com/yourhandle">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><i class="bi bi-linkedin me-1"></i> LinkedIn URL</label>
                <input type="url" name="social_linkedin" class="form-control" value="<?php echo htmlspecialchars($settings['social_linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/company/yourcompany">
            </div>
        </div>
    </div>

    <!-- Footer Settings -->
    <div class="settings-section">
        <h4><i class="bi bi-copyright me-2"></i> Footer Settings</h4>
        <div class="mb-3">
            <label class="form-label">Copyright Text</label>
            <input type="text" name="footer_copyright" class="form-control" value="<?php echo htmlspecialchars($settings['footer_copyright'] ?? 'All rights reserved.'); ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Footer Links (separate with | )</label>
            <input type="text" name="footer_links" class="form-control" value="<?php echo htmlspecialchars($settings['footer_links'] ?? 'Privacy Policy|Terms of Service'); ?>">
            <small class="text-muted">Example: Privacy Policy|Terms of Service|Cookie Policy</small>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">Save All Settings</button>
</form>

<script>
function updatePreview() {
    let primary = document.getElementById('primaryColor').value;
    let secondary = document.getElementById('secondaryColor').value;
    let accent = document.getElementById('accentColor').value;
    
    document.getElementById('primaryPreview').style.backgroundColor = primary;
    document.getElementById('secondaryPreview').style.backgroundColor = secondary;
    document.getElementById('accentPreview').style.backgroundColor = accent;
}
</script>

<?php require_once 'includes/footer.php'; ?>