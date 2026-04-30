<?php
// File: C:\xampp\htdocs\appF\signup.php

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

$success = false;
$error = '';
$submittedData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission';
    } else {
        // Collect and sanitize data
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
        
        // Validate required fields
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
            // Save to database
            $db = getDB();
            
            // Check if email already exists in leads
            $stmt = $db->prepare("SELECT id FROM leads WHERE email = ?");
            $stmt->bind_param("s", $submittedData['email']);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'You have already submitted an application. We will contact you soon!';
            } else {
                // Insert lead
                $stmt = $db->prepare("
                    INSERT INTO leads (first_name, last_name, email, phone, fitness_goal, message, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'new', NOW())
                ");
                
                $fullName = $submittedData['first_name'] . ' ' . $submittedData['last_name'];
                $message = "Fitness Level: " . $submittedData['fitness_level'] . "\n";
                $message .= "Health Goal: " . $submittedData['health_goal'] . "\n";
                $message .= "Motivation: " . $submittedData['motivation'] . "\n";
                $message .= "Challenges: " . $submittedData['challenges'];
                
                $stmt->bind_param("ssssss", 
                    $submittedData['first_name'],
                    $submittedData['last_name'],
                    $submittedData['email'],
                    $submittedData['phone'],
                    $submittedData['health_goal'],
                    $message
                );
                
                if ($stmt->execute()) {
                    $leadId = $db->insert_id;
                    $success = true;
                    
                    // Optional: Send email notification to admin
                    // mail('admin@fitcoach.com', 'New Lead Application', $message, 'From: leads@fitcoach.com');
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
    <title>Apply for Membership - FitCoach Pro</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        /* ... (keep all existing CSS from your original signup.php) ... */
        /* I'm not repeating the CSS here to save space, but keep your existing CSS */
        
        /* Additional styles for form messages */
        .alert {
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .success-message {
            display: <?php echo $success ? 'block' : 'none'; ?>;
        }
        
        .membership-form {
            display: <?php echo $success ? 'none' : 'block'; ?>;
        }
    </style>
</head>
<body>
    <!-- Fixed Progress Indicator -->
    <div class="progress-indicator-container">
        <div class="progress-indicator">
            <div class="progress-fill" id="progressFill"></div>
            
            <div class="progress-step" id="step1">
                <div class="step-circle active">1</div>
                <div class="step-label active">Personal Info</div>
            </div>
            
            <div class="progress-step" id="step2">
                <div class="step-circle">2</div>
                <div class="step-label">Health & Fitness</div>
            </div>
            
            <div class="progress-step" id="step3">
                <div class="step-circle">3</div>
                <div class="step-label">Goals & Preferences</div>
            </div>
            
            <div class="progress-step" id="step4">
                <div class="step-circle">4</div>
                <div class="step-label">Review & Submit</div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <a href="index.php" class="back-btn">
        <i class="bi bi-arrow-left"></i> Back to Home
    </a>

    <!-- Signup Container -->
    <div class="signup-container">
        <div class="signup-card">
            <!-- Header -->
            <div class="signup-header">
                <h2><i class="bi bi-person-plus-fill me-2"></i> Client Membership Application</h2>
                <p class="mb-0">Apply to join a fitness coaching program</p>
            </div>

            <!-- Application Form -->
            <div class="signup-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="membership-form">
                    <form id="membershipForm" method="POST" action="signup.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <!-- Section 1: Personal Information -->
                        <div class="form-section" id="personalInfo">
                            <h4 class="section-title">
                                <i class="bi bi-person-circle"></i> Personal Information
                            </h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="firstName" class="form-label required">First Name</label>
                                    <div class="input-group">
                                        <i class="bi bi-person input-icon"></i>
                                        <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo htmlspecialchars($submittedData['first_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="lastName" class="form-label required">Last Name</label>
                                    <div class="input-group">
                                        <i class="bi bi-person input-icon"></i>
                                        <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo htmlspecialchars($submittedData['last_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label for="email" class="form-label required">Email Address</label>
                                    <div class="input-group">
                                        <i class="bi bi-envelope input-icon"></i>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($submittedData['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label required">Phone Number</label>
                                    <div class="input-group">
                                        <i class="bi bi-phone input-icon"></i>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($submittedData['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="dob" class="form-label required">Date of Birth</label>
                                    <div class="input-group">
                                        <i class="bi bi-calendar input-icon"></i>
                                        <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($submittedData['dob'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="gender" class="form-label required">Gender</label>
                                    <div class="input-group">
                                        <i class="bi bi-gender-ambiguous input-icon"></i>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="male" <?php echo ($submittedData['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo ($submittedData['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="other" <?php echo ($submittedData['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                            <option value="prefer-not-to-say" <?php echo ($submittedData['gender'] ?? '') == 'prefer-not-to-say' ? 'selected' : ''; ?>>Prefer not to say</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Health & Fitness Information -->
                        <div class="form-section" id="healthFitness">
                            <h4 class="section-title">
                                <i class="bi bi-heart-pulse"></i> Health & Fitness Information
                            </h4>
                            
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="height" class="form-label">Height (cm)</label>
                                    <div class="input-group">
                                        <i class="bi bi-arrows-expand input-icon"></i>
                                        <input type="number" class="form-control" id="height" name="height" min="100" max="250" value="<?php echo htmlspecialchars($submittedData['height'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="weight" class="form-label">Weight (kg)</label>
                                    <div class="input-group">
                                        <i class="bi bi-speedometer input-icon"></i>
                                        <input type="number" class="form-control" id="weight" name="weight" min="30" max="200" value="<?php echo htmlspecialchars($submittedData['weight'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="fitnessLevel" class="form-label required">Fitness Level</label>
                                    <select class="form-select" id="fitnessLevel" name="fitnessLevel" required>
                                        <option value="">Select Level</option>
                                        <option value="beginner" <?php echo ($submittedData['fitness_level'] ?? '') == 'beginner' ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="intermediate" <?php echo ($submittedData['fitness_level'] ?? '') == 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="advanced" <?php echo ($submittedData['fitness_level'] ?? '') == 'advanced' ? 'selected' : ''; ?>>Advanced</option>
                                        <option value="athlete" <?php echo ($submittedData['fitness_level'] ?? '') == 'athlete' ? 'selected' : ''; ?>>Athlete</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label mb-3">Do you have any medical conditions or injuries?</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="medicalConditions" id="medicalNo" value="no" <?php echo ($submittedData['medical_conditions'] ?? 'no') == 'no' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="medicalNo">
                                        No, I don't have any medical conditions or injuries
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="medicalConditions" id="medicalYes" value="yes" <?php echo ($submittedData['medical_conditions'] ?? '') == 'yes' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="medicalYes">
                                        Yes, I have medical conditions or injuries
                                    </label>
                                </div>
                                <div id="medicalDetails" style="display: <?php echo ($submittedData['medical_conditions'] ?? '') == 'yes' ? 'block' : 'none'; ?>; margin-top: 15px;">
                                    <label for="conditionsDetails" class="form-label">Please provide details:</label>
                                    <textarea class="form-control" id="conditionsDetails" name="conditionsDetails" rows="3" placeholder="Describe your medical conditions or injuries..."><?php echo htmlspecialchars($submittedData['conditions_details'] ?? ''); ?></textarea>
                                    <div class="info-text">This information will be kept confidential and only shared with your assigned coach</div>
                                </div>
                            </div>

                            <div>
                                <label for="healthGoals" class="form-label required">Primary Health Goal</label>
                                <select class="form-select" id="healthGoals" name="healthGoals" required>
                                    <option value="">Select your primary goal</option>
                                    <option value="weight-loss" <?php echo ($submittedData['health_goal'] ?? '') == 'weight-loss' ? 'selected' : ''; ?>>Weight Loss</option>
                                    <option value="muscle-gain" <?php echo ($submittedData['health_goal'] ?? '') == 'muscle-gain' ? 'selected' : ''; ?>>Muscle Gain</option>
                                    <option value="strength" <?php echo ($submittedData['health_goal'] ?? '') == 'strength' ? 'selected' : ''; ?>>Strength Building</option>
                                    <option value="endurance" <?php echo ($submittedData['health_goal'] ?? '') == 'endurance' ? 'selected' : ''; ?>>Endurance Improvement</option>
                                    <option value="rehabilitation" <?php echo ($submittedData['health_goal'] ?? '') == 'rehabilitation' ? 'selected' : ''; ?>>Injury Rehabilitation</option>
                                    <option value="general-fitness" <?php echo ($submittedData['health_goal'] ?? '') == 'general-fitness' ? 'selected' : ''; ?>>General Fitness</option>
                                    <option value="sports-specific" <?php echo ($submittedData['health_goal'] ?? '') == 'sports-specific' ? 'selected' : ''; ?>>Sports Specific Training</option>
                                </select>
                            </div>
                        </div>

                        <!-- Section 3: Program Preferences -->
                        <div class="form-section" id="programPreferences">
                            <h4 class="section-title">
                                <i class="bi bi-clipboard-check"></i> Program Preferences
                            </h4>
                            
                            <div class="coach-selection">
                                <h5><i class="bi bi-person-badge me-2"></i> Coach Selection</h5>
                                <p class="text-muted mb-3">Select a coach or let us assign the best match for you</p>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="coachSelection" id="autoAssign" value="auto" <?php echo ($submittedData['coach_selection'] ?? 'auto') == 'auto' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="autoAssign">
                                        <strong>Auto-assign coach</strong> - We'll match you with the best coach based on your goals
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="coachSelection" id="selectCoach" value="select" <?php echo ($submittedData['coach_selection'] ?? '') == 'select' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="selectCoach">
                                        <strong>Select specific coach</strong>
                                    </label>
                                </div>
                                <div id="coachList" style="display: <?php echo ($submittedData['coach_selection'] ?? '') == 'select' ? 'block' : 'none'; ?>; margin-top: 15px;">
                                    <select class="form-select" id="specificCoach" name="specificCoach">
                                        <option value="">Select a coach</option>
                                        <option value="coach1" <?php echo ($submittedData['specific_coach'] ?? '') == 'coach1' ? 'selected' : ''; ?>>Sarah Johnson - Weight Loss Specialist</option>
                                        <option value="coach2" <?php echo ($submittedData['specific_coach'] ?? '') == 'coach2' ? 'selected' : ''; ?>>Mike Chen - Strength & Conditioning</option>
                                        <option value="coach3" <?php echo ($submittedData['specific_coach'] ?? '') == 'coach3' ? 'selected' : ''; ?>>Lisa Rodriguez - Yoga & Mobility</option>
                                        <option value="coach4" <?php echo ($submittedData['specific_coach'] ?? '') == 'coach4' ? 'selected' : ''; ?>>David Wilson - Sports Performance</option>
                                        <option value="coach5" <?php echo ($submittedData['specific_coach'] ?? '') == 'coach5' ? 'selected' : ''; ?>>Emma Davis - Senior Fitness</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="form-label mb-3">Preferred Training Schedule</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="preferredDays" class="form-label">Preferred Days</label>
                                        <select class="form-select" id="preferredDays" name="preferredDays[]" multiple>
                                            <option value="monday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'monday') !== false ? 'selected' : ''; ?>>Monday</option>
                                            <option value="tuesday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'tuesday') !== false ? 'selected' : ''; ?>>Tuesday</option>
                                            <option value="wednesday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'wednesday') !== false ? 'selected' : ''; ?>>Wednesday</option>
                                            <option value="thursday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'thursday') !== false ? 'selected' : ''; ?>>Thursday</option>
                                            <option value="friday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'friday') !== false ? 'selected' : ''; ?>>Friday</option>
                                            <option value="saturday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'saturday') !== false ? 'selected' : ''; ?>>Saturday</option>
                                            <option value="sunday" <?php echo strpos($submittedData['preferred_days'] ?? '', 'sunday') !== false ? 'selected' : ''; ?>>Sunday</option>
                                        </select>
                                        <div class="info-text">Hold Ctrl/Cmd to select multiple days</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="preferredTime" class="form-label">Preferred Time of Day</label>
                                        <select class="form-select" id="preferredTime" name="preferredTime">
                                            <option value="any" <?php echo ($submittedData['preferred_time'] ?? '') == 'any' ? 'selected' : ''; ?>>Any Time</option>
                                            <option value="morning" <?php echo ($submittedData['preferred_time'] ?? '') == 'morning' ? 'selected' : ''; ?>>Morning (6AM - 12PM)</option>
                                            <option value="afternoon" <?php echo ($submittedData['preferred_time'] ?? '') == 'afternoon' ? 'selected' : ''; ?>>Afternoon (12PM - 5PM)</option>
                                            <option value="evening" <?php echo ($submittedData['preferred_time'] ?? '') == 'evening' ? 'selected' : ''; ?>>Evening (5PM - 9PM)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label for="sessionType" class="form-label">Preferred Session Type</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="oneOnOne" name="sessionType[]" value="one-on-one" <?php echo strpos($submittedData['session_types'] ?? '', 'one-on-one') !== false ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="oneOnOne">One-on-One Sessions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="group" name="sessionType[]" value="group" <?php echo strpos($submittedData['session_types'] ?? '', 'group') !== false ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="group">Group Sessions</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="virtual" name="sessionType[]" value="virtual" <?php echo strpos($submittedData['session_types'] ?? '', 'virtual') !== false ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="virtual">Virtual/Online Sessions</label>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Additional Information -->
                        <div class="form-section" id="additionalInfo">
                            <h4 class="section-title">
                                <i class="bi bi-chat-text"></i> Additional Information
                            </h4>
                            
                            <div class="mb-4">
                                <label for="motivation" class="form-label">What motivates you to start fitness training?</label>
                                <textarea class="form-control" id="motivation" name="motivation" rows="3" placeholder="Share what inspires you to begin your fitness journey..."><?php echo htmlspecialchars($submittedData['motivation'] ?? ''); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label for="challenges" class="form-label">What challenges do you anticipate?</label>
                                <textarea class="form-control" id="challenges" name="challenges" rows="3" placeholder="Time constraints, motivation, physical limitations, etc..."><?php echo htmlspecialchars($submittedData['challenges'] ?? ''); ?></textarea>
                            </div>

                            <div class="agreement-box">
                                <h6>Membership Agreement</h6>
                                <p class="small">
                                    By submitting this application, you agree to the following terms:
                                    1. You understand that this is an application for membership and acceptance is subject to coach approval.
                                    2. You commit to providing accurate health information for your safety.
                                    3. You agree to the terms of service and privacy policy of FitCoach Pro.
                                    4. You understand that membership fees will be discussed with your assigned coach.
                                    5. You consent to receiving communications regarding your application status.
                                </p>
                            </div>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" <?php echo ($submittedData['agree_terms'] ?? false) ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="agreeTerms">
                                    I have read and agree to the membership terms and conditions
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="receiveUpdates" name="receiveUpdates" <?php echo ($submittedData['receive_updates'] ?? true) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="receiveUpdates">
                                    I would like to receive updates, tips, and offers from FitCoach Pro
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn submit-btn">
                            <i class="bi bi-send-check"></i> Submit Application
                        </button>
                    </form>
                </div>

                <!-- Success Message -->
                <div id="successMessage" class="success-message">
                    <i class="bi bi-check-circle-fill display-4 mb-3"></i>
                    <h3>Application Submitted Successfully!</h3>
                    <p class="mb-3">Thank you for applying. A coach will review your application and contact you within 2-3 business days.</p>
                    <p class="small">Check your email for a confirmation and next steps.</p>
                    <a href="index.php" class="btn btn-light mt-3">
                        <i class="bi bi-house-door me-2"></i> Return to Home
                    </a>
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

        // Medical Conditions Toggle
        const medicalNo = document.getElementById('medicalNo');
        const medicalYes = document.getElementById('medicalYes');
        const medicalDetails = document.getElementById('medicalDetails');
        
        if (medicalNo && medicalYes) {
            medicalNo.addEventListener('change', function() {
                medicalDetails.style.display = 'none';
                document.getElementById('conditionsDetails').required = false;
            });
            
            medicalYes.addEventListener('change', function() {
                medicalDetails.style.display = 'block';
                document.getElementById('conditionsDetails').required = true;
            });
        }

        // Coach Selection Toggle
        const autoAssign = document.getElementById('autoAssign');
        const selectCoach = document.getElementById('selectCoach');
        const coachList = document.getElementById('coachList');
        const specificCoach = document.getElementById('specificCoach');
        
        if (autoAssign && selectCoach) {
            autoAssign.addEventListener('change', function() {
                coachList.style.display = 'none';
                specificCoach.required = false;
            });
            
            selectCoach.addEventListener('change', function() {
                coachList.style.display = 'block';
                specificCoach.required = true;
            });
        }

        // Update progress indicator on scroll
        const sections = document.querySelectorAll('.form-section');
        const progressSteps = document.querySelectorAll('.progress-step');
        const progressFill = document.getElementById('progressFill');

        function updateProgressIndicator() {
            let currentSection = 0;
            const scrollPosition = window.scrollY + 120;
            
            sections.forEach((section, index) => {
                const sectionTop = section.offsetTop - 120;
                if (scrollPosition >= sectionTop) {
                    currentSection = index + 1;
                }
            });
            
            // Update step circles and labels
            progressSteps.forEach((step, index) => {
                const circle = step.querySelector('.step-circle');
                const label = step.querySelector('.step-label');
                
                if (index < currentSection) {
                    circle.classList.remove('active');
                    circle.classList.add('completed');
                    label.classList.remove('active');
                    label.classList.add('completed');
                } else if (index === currentSection - 1) {
                    circle.classList.add('active');
                    circle.classList.remove('completed');
                    label.classList.add('active');
                    label.classList.remove('completed');
                } else {
                    circle.classList.remove('active', 'completed');
                    label.classList.remove('active', 'completed');
                }
            });
            
            // Update progress bar fill
            if (currentSection > 0) {
                const progressPercentage = ((currentSection - 1) / (sections.length - 1)) * 100;
                progressFill.style.width = `${progressPercentage}%`;
            } else {
                progressFill.style.width = '0%';
            }
        }

        window.addEventListener('scroll', updateProgressIndicator);
        updateProgressIndicator();

        // Auto-focus on first field
        document.getElementById('firstName').focus();

        // Today's date as max for date of birth
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
        
        document.getElementById('dob').max = maxDate.toISOString().split('T')[0];

        // Real-time validation for email
        const emailInput = document.getElementById('email');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.value && !emailPattern.test(this.value)) {
                    this.style.borderColor = '#dc3545';
                    this.style.boxShadow = '0 0 0 0.25rem rgba(220, 53, 69, 0.25)';
                }
            });
        }

        // Real-time validation for phone
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                const phonePattern = /^[\d\s\-\+\(\)]{10,}$/;
                if (this.value && !phonePattern.test(this.value)) {
                    this.style.borderColor = '#dc3545';
                    this.style.boxShadow = '0 0 0 0.25rem rgba(220, 53, 69, 0.25)';
                }
            });
        }

        // Add smooth scrolling to sections when clicking progress steps
        progressSteps.forEach((step, index) => {
            step.addEventListener('click', () => {
                sections[index].scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>