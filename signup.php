<?php
// File: D:\Program Files\xampp\htdocs\appF\signup.php

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

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

$success = false;
$error = '';
$submittedData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission';
    } else {
        $submittedData = [
            'first_name' => sanitize($_POST['firstName'] ?? ''),
            'last_name' => sanitize($_POST['lastName'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'dob' => sanitize($_POST['dob'] ?? ''),
            'gender' => sanitize($_POST['gender'] ?? ''),
            'height' => sanitize($_POST['height'] ?? ''),
            'weight' => sanitize($_POST['weight'] ?? ''),
            'fitness_level' => sanitize($_POST['fitnessLevel'] ?? ''),
            'medical_conditions' => sanitize($_POST['medicalConditions'] ?? 'no'),
            'conditions_details' => sanitize($_POST['conditionsDetails'] ?? ''),
            'health_goal' => sanitize($_POST['healthGoals'] ?? ''),
            'coach_selection' => sanitize($_POST['coachSelection'] ?? 'auto'),
            'specific_coach' => sanitize($_POST['specificCoach'] ?? ''),
            'preferred_days' => isset($_POST['preferredDays']) ? implode(',', $_POST['preferredDays']) : '',
            'preferred_time' => sanitize($_POST['preferredTime'] ?? ''),
            'session_types' => isset($_POST['sessionType']) ? implode(',', $_POST['sessionType']) : '',
            'motivation' => sanitize($_POST['motivation'] ?? ''),
            'challenges' => sanitize($_POST['challenges'] ?? ''),
            'agree_terms' => isset($_POST['agreeTerms']),
            'receive_updates' => isset($_POST['receiveUpdates'])
        ];
        
        $required = ['first_name', 'last_name', 'email', 'phone', 'dob', 'gender', 'fitness_level', 'health_goal'];
        $validation = validateRequired($submittedData, $required);
        
        if ($validation !== true) {
            $error = 'Please fill in all required fields';
        } elseif (!validateEmail($submittedData['email'])) {
            $error = 'Please enter a valid email address';
        } elseif (!validatePhone($submittedData['phone'])) {
            $error = 'Please enter a valid phone number';
        } elseif (!validateDate($submittedData['dob'])) {
            $error = 'Please enter a valid date of birth';
        } elseif (!$submittedData['agree_terms']) {
            $error = 'Please agree to the terms and conditions';
        } else {
            $db = getDB();
            $stmt = $db->prepare("SELECT id FROM leads WHERE email = ?");
            $stmt->bind_param("s", $submittedData['email']);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'You have already submitted an application. We will contact you soon!';
            } else {
                $stmt = $db->prepare("INSERT INTO leads (first_name, last_name, email, phone, fitness_goal, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())");
                $message = "Fitness Level: " . $submittedData['fitness_level'] . "\nHealth Goal: " . $submittedData['health_goal'] . "\nMotivation: " . $submittedData['motivation'] . "\nChallenges: " . $submittedData['challenges'];
                $stmt->bind_param("ssssss", $submittedData['first_name'], $submittedData['last_name'], $submittedData['email'], $submittedData['phone'], $submittedData['health_goal'], $message);
                
                if ($stmt->execute()) {
                    $success = true;
                } else {
                    $error = 'Failed to submit application. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Membership - <?php echo htmlspecialchars($siteName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --accent-color: <?php echo $accentColor; ?>;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
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
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Progress Indicator */
        .progress-indicator-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--card-bg);
            z-index: 99;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        [data-bs-theme="dark"] .progress-indicator-container {
            background: var(--dark-bg);
            border-bottom: 1px solid #444;
        }

        .progress-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
            position: relative;
        }

        .progress-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50px;
            right: 50px;
            height: 3px;
            background: #e0e0e0;
            transform: translateY(-50%);
            z-index: 1;
        }

        [data-bs-theme="dark"] .progress-indicator::before {
            background: #444;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
            min-width: 70px;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: var(--text-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 8px;
            transition: all 0.3s;
            border: 3px solid var(--card-bg);
        }

        [data-bs-theme="dark"] .step-circle {
            border-color: var(--dark-bg);
        }

        .step-circle.active {
            background: var(--primary-color);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 0 0 3px rgba(74, 111, 165, 0.2);
        }

        .step-circle.completed {
            background: var(--success-color);
            color: white;
        }

        .step-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-align: center;
            font-weight: 500;
        }

        .step-label.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .step-label.completed {
            color: var(--success-color);
        }

        .progress-fill {
            position: absolute;
            top: 50%;
            left: 50px;
            height: 3px;
            background: var(--primary-color);
            transform: translateY(-50%);
            z-index: 1;
            transition: width 0.5s ease;
        }

        /* Signup Card */
        .signup-card {
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 80px auto 0;
            overflow: hidden;
        }

        .signup-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .signup-body {
            padding: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control, .form-select, textarea {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 111, 165, 0.25);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select,
        [data-bs-theme="dark"] textarea {
            background-color: #2d2d2d;
            border-color: #444;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            z-index: 3;
        }

        .input-group .form-control,
        .input-group .form-select {
            padding-left: 45px;
        }

        .submit-btn {
            background: linear-gradient(45deg, var(--success-color), #20c997);
            border: none;
            padding: 15px;
            font-weight: 600;
            border-radius: 10px;
            color: white;
            transition: all 0.3s;
            width: 100%;
            font-size: 1.1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .back-btn {
            position: fixed;
            top: 80px;
            left: 20px;
            background: rgba(0,0,0,0.3);
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            z-index: 100;
        }

        .back-btn:hover {
            background: rgba(0,0,0,0.5);
            color: white;
        }

        .theme-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background: var(--primary-color);
            color: white;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .info-text {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 5px;
            font-style: italic;
        }

        .coach-selection {
            border: 2px dashed var(--accent-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            background: rgba(23, 162, 184, 0.05);
        }

        .agreement-box {
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 10px;
            background: var(--light-bg);
            margin: 1rem 0;
        }

        .success-message {
            display: <?php echo $success ? 'block' : 'none'; ?>;
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            animation: fadeIn 0.5s ease;
        }

        .membership-form {
            display: <?php echo $success ? 'none' : 'block'; ?>;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .signup-body {
                padding: 1.5rem;
            }
            .step-label {
                font-size: 0.7rem;
                max-width: 70px;
            }
            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
            }
            .progress-indicator::before {
                left: 40px;
                right: 40px;
            }
            .progress-fill {
                left: 40px;
            }
            .back-btn {
                top: 70px;
            }
        }

        @media (max-width: 576px) {
            .signup-card {
                margin-top: 100px;
            }
            .progress-step {
                min-width: 60px;
            }
            .step-label {
                font-size: 0.65rem;
                max-width: 60px;
            }
            .step-circle {
                width: 30px;
                height: 30px;
                font-size: 0.8rem;
            }
            .progress-indicator::before {
                left: 35px;
                right: 35px;
            }
            .progress-fill {
                left: 35px;
            }
            .back-btn {
                top: 65px;
                left: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Fixed Progress Indicator -->
    <div class="progress-indicator-container">
        <div class="progress-indicator">
            <div class="progress-fill" id="progressFill"></div>
            <div class="progress-step" id="step1"><div class="step-circle active">1</div><div class="step-label active">Personal Info</div></div>
            <div class="progress-step" id="step2"><div class="step-circle">2</div><div class="step-label">Health & Fitness</div></div>
            <div class="progress-step" id="step3"><div class="step-circle">3</div><div class="step-label">Goals & Preferences</div></div>
            <div class="progress-step" id="step4"><div class="step-circle">4</div><div class="step-label">Review & Submit</div></div>
        </div>
    </div>

    <!-- Back Button -->
    <a href="/appF/index.php" class="back-btn"><i class="bi bi-arrow-left me-1"></i> Back to Home</a>

    <!-- Signup Container -->
    <div class="signup-card">
        <div class="signup-header">
            <h2><i class="bi bi-person-plus-fill me-2"></i> <?php echo htmlspecialchars($siteName); ?></h2>
            <p class="mb-0">Apply to join a fitness coaching program</p>
        </div>

        <div class="signup-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="membership-form">
                <form id="membershipForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Section 1: Personal Information -->
                    <div class="form-section" id="personalInfo">
                        <h4 class="section-title"><i class="bi bi-person-circle"></i> Personal Information</h4>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label required">First Name</label><input type="text" name="firstName" class="form-control" value="<?php echo htmlspecialchars($submittedData['first_name'] ?? ''); ?>" required></div>
                            <div class="col-md-6"><label class="form-label required">Last Name</label><input type="text" name="lastName" class="form-control" value="<?php echo htmlspecialchars($submittedData['last_name'] ?? ''); ?>" required></div>
                            <div class="col-md-6"><label class="form-label required">Email Address</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($submittedData['email'] ?? ''); ?>" required></div>
                            <div class="col-md-6"><label class="form-label required">Phone Number</label><input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($submittedData['phone'] ?? ''); ?>" required></div>
                            <div class="col-md-6"><label class="form-label required">Date of Birth</label><input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($submittedData['dob'] ?? ''); ?>" required></div>
                            <div class="col-md-6"><label class="form-label required">Gender</label><select name="gender" class="form-select" required><option value="">Select</option><option value="male" <?php echo ($submittedData['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option><option value="female" <?php echo ($submittedData['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option><option value="other" <?php echo ($submittedData['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option></select></div>
                        </div>
                    </div>

                    <!-- Section 2: Health & Fitness -->
                    <div class="form-section" id="healthFitness">
                        <h4 class="section-title"><i class="bi bi-heart-pulse"></i> Health & Fitness</h4>
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">Height (cm)</label><input type="number" name="height" class="form-control" value="<?php echo htmlspecialchars($submittedData['height'] ?? ''); ?>"></div>
                            <div class="col-md-4"><label class="form-label">Weight (kg)</label><input type="number" name="weight" class="form-control" value="<?php echo htmlspecialchars($submittedData['weight'] ?? ''); ?>"></div>
                            <div class="col-md-4"><label class="form-label required">Fitness Level</label><select name="fitnessLevel" class="form-select" required><option value="">Select</option><option value="beginner" <?php echo ($submittedData['fitness_level'] ?? '') == 'beginner' ? 'selected' : ''; ?>>Beginner</option><option value="intermediate" <?php echo ($submittedData['fitness_level'] ?? '') == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option><option value="advanced" <?php echo ($submittedData['fitness_level'] ?? '') == 'advanced' ? 'selected' : ''; ?>>Advanced</option><option value="athlete" <?php echo ($submittedData['fitness_level'] ?? '') == 'athlete' ? 'selected' : ''; ?>>Athlete</option></select></div>
                        </div>
                        <div class="mt-3"><label class="form-label required">Primary Health Goal</label><select name="healthGoals" class="form-select" required><option value="">Select</option><option value="weight-loss" <?php echo ($submittedData['health_goal'] ?? '') == 'weight-loss' ? 'selected' : ''; ?>>Weight Loss</option><option value="muscle-gain" <?php echo ($submittedData['health_goal'] ?? '') == 'muscle-gain' ? 'selected' : ''; ?>>Muscle Gain</option><option value="strength" <?php echo ($submittedData['health_goal'] ?? '') == 'strength' ? 'selected' : ''; ?>>Strength Building</option><option value="endurance" <?php echo ($submittedData['health_goal'] ?? '') == 'endurance' ? 'selected' : ''; ?>>Endurance</option><option value="general-fitness" <?php echo ($submittedData['health_goal'] ?? '') == 'general-fitness' ? 'selected' : ''; ?>>General Fitness</option></select></div>
                    </div>

                    <!-- Section 3: Program Preferences -->
                    <div class="form-section" id="programPreferences">
                        <h4 class="section-title"><i class="bi bi-clipboard-check"></i> Program Preferences</h4>
                        <div class="coach-selection">
                            <h5><i class="bi bi-person-badge me-2"></i> Coach Selection</h5>
                            <p class="text-muted mb-3">Select a coach or let us assign the best match for you</p>
                            <div class="form-check mb-2"><input class="form-check-input" type="radio" name="coachSelection" id="autoAssign" value="auto" <?php echo ($submittedData['coach_selection'] ?? 'auto') == 'auto' ? 'checked' : ''; ?>><label class="form-check-label" for="autoAssign"><strong>Auto-assign coach</strong> - We'll match you with the best coach</label></div>
                            <div class="form-check"><input class="form-check-input" type="radio" name="coachSelection" id="selectCoach" value="select" <?php echo ($submittedData['coach_selection'] ?? '') == 'select' ? 'checked' : ''; ?>><label class="form-check-label" for="selectCoach"><strong>Select specific coach</strong></label></div>
                            <div id="coachList" style="display: <?php echo ($submittedData['coach_selection'] ?? '') == 'select' ? 'block' : 'none'; ?>; margin-top: 15px;"><select name="specificCoach" class="form-select"><option value="">Select a coach</option><option value="coach1">Sarah Johnson - Weight Loss Specialist</option><option value="coach2">Mike Chen - Strength & Conditioning</option><option value="coach3">Lisa Rodriguez - Yoga & Mobility</option></select></div>
                        </div>
                        <div class="mt-3"><label class="form-label">Preferred Training Schedule</label><div class="row"><div class="col-md-6"><select name="preferredDays[]" class="form-select" multiple><option value="monday">Monday</option><option value="tuesday">Tuesday</option><option value="wednesday">Wednesday</option><option value="thursday">Thursday</option><option value="friday">Friday</option></select><div class="info-text">Hold Ctrl to select multiple</div></div><div class="col-md-6"><select name="preferredTime" class="form-select"><option value="any">Any Time</option><option value="morning">Morning (6AM - 12PM)</option><option value="afternoon">Afternoon (12PM - 5PM)</option><option value="evening">Evening (5PM - 9PM)</option></select></div></div></div>
                    </div>

                    <!-- Section 4: Additional Information -->
                    <div class="form-section" id="additionalInfo">
                        <h4 class="section-title"><i class="bi bi-chat-text"></i> Additional Information</h4>
                        <div class="mb-3"><label class="form-label">What motivates you to start fitness training?</label><textarea name="motivation" class="form-control" rows="2"><?php echo htmlspecialchars($submittedData['motivation'] ?? ''); ?></textarea></div>
                        <div class="mb-3"><label class="form-label">What challenges do you anticipate?</label><textarea name="challenges" class="form-control" rows="2"><?php echo htmlspecialchars($submittedData['challenges'] ?? ''); ?></textarea></div>
                        <div class="agreement-box"><h6>Membership Agreement</h6><p class="small">By submitting this application, you agree to the terms: 1. Acceptance subject to coach approval. 2. Accurate health information required. 3. Terms of service apply. 4. Fees discussed with coach. 5. Communications consent.</p></div>
                        <div class="form-check mt-3"><input class="form-check-input" type="checkbox" name="agreeTerms" id="agreeTerms" <?php echo ($submittedData['agree_terms'] ?? false) ? 'checked' : ''; ?> required><label class="form-check-label" for="agreeTerms">I agree to the membership terms and conditions</label></div>
                    </div>

                    <button type="submit" class="submit-btn"><i class="bi bi-send-check me-2"></i> Submit Application</button>
                </form>
            </div>

            <div class="success-message">
                <i class="bi bi-check-circle-fill display-4 mb-3"></i>
                <h3>Application Submitted Successfully!</h3>
                <p>Thank you for applying. A coach will review your application and contact you within 2-3 business days.</p>
                <a href="/appF/index.php" class="btn btn-light mt-3"><i class="bi bi-house-door me-2"></i> Return to Home</a>
            </div>
        </div>
    </div>

    <!-- Theme Toggle Button -->
    <button class="theme-toggle" id="themeToggle"><i class="bi bi-moon-fill" id="themeIcon"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const themeToggle = document.getElementById('themeToggle'), themeIcon = document.getElementById('themeIcon'), html = document.documentElement;
        const saved = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        html.setAttribute('data-bs-theme', saved);
        function updateIcon(t) { themeIcon.className = t === 'light' ? 'bi bi-moon-fill' : 'bi bi-sun-fill'; }
        updateIcon(saved);
        themeToggle.onclick = () => { const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light'; html.setAttribute('data-bs-theme', next); localStorage.setItem('theme', next); updateIcon(next); };

        // Progress indicator on scroll
        const sections = document.querySelectorAll('.form-section');
        const steps = document.querySelectorAll('.progress-step');
        const fill = document.getElementById('progressFill');
        function updateProgress() {
            let current = 0;
            const scrollPos = window.scrollY + 150;
            sections.forEach((s, i) => { if(scrollPos >= s.offsetTop) current = i + 1; });
            steps.forEach((s, i) => { const c = s.querySelector('.step-circle'), l = s.querySelector('.step-label');
                if(i < current) { c.classList.remove('active'); c.classList.add('completed'); l.classList.remove('active'); l.classList.add('completed'); }
                else if(i === current-1) { c.classList.add('active'); c.classList.remove('completed'); l.classList.add('active'); l.classList.remove('completed'); }
                else { c.classList.remove('active','completed'); l.classList.remove('active','completed'); }
            });
            fill.style.width = current > 0 ? ((current-1)/(sections.length-1))*100 + '%' : '0%';
        }
        window.addEventListener('scroll', updateProgress);
        updateProgress();

        // Medical conditions toggle
        document.getElementById('medicalNo')?.addEventListener('change', function() { document.getElementById('medicalDetails').style.display = 'none'; });
        document.getElementById('medicalYes')?.addEventListener('change', function() { document.getElementById('medicalDetails').style.display = 'block'; });
        // Coach selection toggle
        document.getElementById('autoAssign')?.addEventListener('change', function() { document.getElementById('coachList').style.display = 'none'; });
        document.getElementById('selectCoach')?.addEventListener('change', function() { document.getElementById('coachList').style.display = 'block'; });
        // Date validation
        document.getElementById('dob').max = new Date(new Date().setFullYear(new Date().getFullYear() - 16)).toISOString().split('T')[0];
        // Smooth scroll on step click
        steps.forEach((s, i) => { s.addEventListener('click', () => { sections[i].scrollIntoView({ behavior: 'smooth' }); }); });
    </script>
</body>
</html>