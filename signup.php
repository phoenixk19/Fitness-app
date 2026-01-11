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
        :root {
            --primary-color: #4A6FA5;
            --secondary-color: #166088;
            --accent-color: #17a2b8;
            --light-bg: #f8f9fa;
            --dark-bg: #212529;
            --card-bg: #ffffff;
            --text-color: #333333;
            --text-light: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
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
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-color);
            transition: all 0.3s ease;
            min-height: 100vh;
        }

        /* Progress Indicator - Fixed Position */
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
            transition: all 0.3s ease;
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
            transition: all 0.3s ease;
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
            position: relative;
            z-index: 2;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100px;
        }

        .step-label.active {
            color: var(--primary-color);
            font-weight: 600;
        }

        .step-label.completed {
            color: var(--success-color);
        }

        /* Progress bar fill animation */
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

        [data-bs-theme="dark"] .progress-fill {
            background: var(--primary-color);
        }

        .signup-container {
            padding: 2rem 1rem;
            padding-top: 120px; /* Space for fixed progress bar */
        }

        .signup-card {
            background-color: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }

        .signup-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2.5rem;
            text-align: center;
            position: relative;
        }

        .signup-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 20px solid transparent;
            border-right: 20px solid transparent;
            border-top: 20px solid var(--secondary-color);
        }

        .signup-body {
            padding: 3rem 2.5rem;
        }

        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 2px dashed #dee2e6;
            scroll-margin-top: 140px; /* For smooth scrolling to sections */
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
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

        .form-control, .form-select {
            background-color: var(--light-bg);
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            color: var(--text-color);
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 111, 165, 0.25);
        }

        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
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
            padding: 15px 40px;
            font-weight: 600;
            border-radius: 10px;
            color: white;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .back-btn {
            position: fixed;
            top: 70px; /* Adjusted for progress bar */
            left: 20px;
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            background: var(--card-bg);
            padding: 8px 15px;
            border-radius: 50px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }

        .back-btn:hover {
            text-decoration: none;
            background-color: var(--primary-color);
            color: white;
            transform: translateX(-3px);
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

        .form-check {
            margin-bottom: 10px;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .info-text {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 5px;
            font-style: italic;
        }

        .coach-selection {
            border: 2px dashed var(--accent-color);
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
            background: rgba(23, 162, 184, 0.05);
        }

        @media (max-width: 768px) {
            .signup-body {
                padding: 2rem 1.5rem;
            }
            
            .signup-header {
                padding: 2rem 1rem;
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
                top: 65px;
                font-size: 0.9rem;
                padding: 6px 12px;
            }
        }

        @media (max-width: 576px) {
            .signup-container {
                padding: 1rem;
                padding-top: 110px;
            }
            
            .signup-body {
                padding: 1.5rem 1rem;
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
                top: 60px;
                left: 10px;
            }
            
            .row.g-3 {
                --bs-gutter-x: 1rem;
            }
        }

        .success-message {
            display: none;
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin-top: 2rem;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .agreement-box {
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background: var(--light-bg);
            margin: 1rem 0;
        }

        [data-bs-theme="dark"] .agreement-box {
            background: #2d2d2d;
            border-color: #444;
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
                <form id="membershipForm">
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
                                    <input type="text" class="form-control" id="firstName" name="firstName" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName" class="form-label required">Last Name</label>
                                <div class="input-group">
                                    <i class="bi bi-person input-icon"></i>
                                    <input type="text" class="form-control" id="lastName" name="lastName" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="email" class="form-label required">Email Address</label>
                                <div class="input-group">
                                    <i class="bi bi-envelope input-icon"></i>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label required">Phone Number</label>
                                <div class="input-group">
                                    <i class="bi bi-phone input-icon"></i>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="dob" class="form-label required">Date of Birth</label>
                                <div class="input-group">
                                    <i class="bi bi-calendar input-icon"></i>
                                    <input type="date" class="form-control" id="dob" name="dob" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="gender" class="form-label required">Gender</label>
                                <div class="input-group">
                                    <i class="bi bi-gender-ambiguous input-icon"></i>
                                    <select class="form-select" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                        <option value="prefer-not-to-say">Prefer not to say</option>
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
                                    <input type="number" class="form-control" id="height" name="height" min="100" max="250">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <div class="input-group">
                                    <i class="bi bi-speedometer input-icon"></i>
                                    <input type="number" class="form-control" id="weight" name="weight" min="30" max="200">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="fitnessLevel" class="form-label required">Fitness Level</label>
                                <select class="form-select" id="fitnessLevel" name="fitnessLevel" required>
                                    <option value="">Select Level</option>
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                    <option value="athlete">Athlete</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label mb-3">Do you have any medical conditions or injuries?</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="medicalConditions" id="medicalNo" value="no" checked>
                                <label class="form-check-label" for="medicalNo">
                                    No, I don't have any medical conditions or injuries
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="medicalConditions" id="medicalYes" value="yes">
                                <label class="form-check-label" for="medicalYes">
                                    Yes, I have medical conditions or injuries
                                </label>
                            </div>
                            <div id="medicalDetails" style="display: none; margin-top: 15px;">
                                <label for="conditionsDetails" class="form-label">Please provide details:</label>
                                <textarea class="form-control" id="conditionsDetails" name="conditionsDetails" rows="3" placeholder="Describe your medical conditions or injuries..."></textarea>
                                <div class="info-text">This information will be kept confidential and only shared with your assigned coach</div>
                            </div>
                        </div>

                        <div>
                            <label for="healthGoals" class="form-label required">Primary Health Goal</label>
                            <select class="form-select" id="healthGoals" name="healthGoals" required>
                                <option value="">Select your primary goal</option>
                                <option value="weight-loss">Weight Loss</option>
                                <option value="muscle-gain">Muscle Gain</option>
                                <option value="strength">Strength Building</option>
                                <option value="endurance">Endurance Improvement</option>
                                <option value="rehabilitation">Injury Rehabilitation</option>
                                <option value="general-fitness">General Fitness</option>
                                <option value="sports-specific">Sports Specific Training</option>
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
                                <input class="form-check-input" type="radio" name="coachSelection" id="autoAssign" value="auto" checked>
                                <label class="form-check-label" for="autoAssign">
                                    <strong>Auto-assign coach</strong> - We'll match you with the best coach based on your goals
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="coachSelection" id="selectCoach" value="select">
                                <label class="form-check-label" for="selectCoach">
                                    <strong>Select specific coach</strong>
                                </label>
                            </div>
                            <div id="coachList" style="display: none; margin-top: 15px;">
                                <select class="form-select" id="specificCoach" name="specificCoach">
                                    <option value="">Select a coach</option>
                                    <option value="coach1">Sarah Johnson - Weight Loss Specialist</option>
                                    <option value="coach2">Mike Chen - Strength & Conditioning</option>
                                    <option value="coach3">Lisa Rodriguez - Yoga & Mobility</option>
                                    <option value="coach4">David Wilson - Sports Performance</option>
                                    <option value="coach5">Emma Davis - Senior Fitness</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label mb-3">Preferred Training Schedule</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="preferredDays" class="form-label">Preferred Days</label>
                                    <select class="form-select" id="preferredDays" name="preferredDays" multiple>
                                        <option value="monday">Monday</option>
                                        <option value="tuesday">Tuesday</option>
                                        <option value="wednesday">Wednesday</option>
                                        <option value="thursday">Thursday</option>
                                        <option value="friday">Friday</option>
                                        <option value="saturday">Saturday</option>
                                        <option value="sunday">Sunday</option>
                                    </select>
                                    <div class="info-text">Hold Ctrl/Cmd to select multiple days</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="preferredTime" class="form-label">Preferred Time of Day</label>
                                    <select class="form-select" id="preferredTime" name="preferredTime">
                                        <option value="any">Any Time</option>
                                        <option value="morning">Morning (6AM - 12PM)</option>
                                        <option value="afternoon">Afternoon (12PM - 5PM)</option>
                                        <option value="evening">Evening (5PM - 9PM)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="sessionType" class="form-label">Preferred Session Type</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="oneOnOne" name="sessionType[]" value="one-on-one" checked>
                                <label class="form-check-label" for="oneOnOne">One-on-One Sessions</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="group" name="sessionType[]" value="group">
                                <label class="form-check-label" for="group">Group Sessions</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="virtual" name="sessionType[]" value="virtual">
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
                            <textarea class="form-control" id="motivation" name="motivation" rows="3" placeholder="Share what inspires you to begin your fitness journey..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="challenges" class="form-label">What challenges do you anticipate?</label>
                            <textarea class="form-control" id="challenges" name="challenges" rows="3" placeholder="Time constraints, motivation, physical limitations, etc..."></textarea>
                        </div>

                        <div class="agreement-box">
                            <h6>Membership