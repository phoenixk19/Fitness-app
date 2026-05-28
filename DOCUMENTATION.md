# FitCoach Pro - Fitness Coaching Platform
## Complete System Documentation

================================================================================
PROJECT OVERVIEW
================================================================================

Project Name: FitCoach Pro
Description: A fitness coaching platform for single coach/business owner to manage clients, workouts, nutrition, and client applications.

Technology Stack:
- Backend: PHP 8.2+
- Database: MySQL 8+
- Frontend: HTML5, CSS3, Bootstrap 5, JavaScript
- Charts: Chart.js
- Icons: Bootstrap Icons

Server Requirements:
- XAMPP / WAMP / LAMP
- PHP 8.0 or higher
- MySQL 8.0 or higher

================================================================================
FILE STRUCTURE
================================================================================

C:\xampp\htdocs\appF\
│
├── .htaccess                          # Apache security & rewrite rules
├── index.php                          # Redirects to /home/index.php
├── login.php                          # User login page
├── signup.php                         # Client membership application
├── logout.php                         # Logout handler
├── forgot-password.php                # Password reset request
│
├── home/                              # Public website
│   ├── index.php                      # Main homepage
│   ├── includes/
│   │   ├── header.php                 # Shared header with dynamic colors
│   │   └── footer.php                 # Shared footer
│   └── sections/
│       ├── hero.php                   # Hero section
│       ├── features.php               # Features section
│       ├── how-it-works.php           # How it works section
│       ├── about.php                  # About section
│       └── testimonials.php           # Testimonials section
│
├── dashboard/                         # User dashboards
│   ├── admin/                         # Admin Dashboard
│   │   ├── index.php                  # System status dashboard
│   │   ├── coaches.php                # Manage coach accounts
│   │   ├── settings.php               # System settings
│   │   ├── branding.php               # Branding & appearance
│   │   └── includes/
│   │       ├── header.php
│   │       └── footer.php
│   │
│   ├── coach/                         # Coach Dashboard (Main User)
│   │   ├── index.php                  # Business overview
│   │   ├── clients.php                # Client management
│   │   ├── leads.php                  # Member applications
│   │   ├── workouts.php               # Workout templates
│   │   ├── nutrition.php              # Nutrition plans
│   │   ├── progress.php               # Client progress tracking
│   │   ├── fitness-tests.php          # Fitness test results
│   │   ├── packages.php               # Coaching packages
│   │   ├── payments.php               # Payment tracking
│   │   ├── analytics.php              # Reports & analytics
│   │   ├── editors.php                # Editor management
│   │   ├── schedule.php               # Workout schedule
│   │   ├── reports.php                # Exportable reports
│   │   ├── messages.php               # Client messaging
│   │   └── includes/
│   │       ├── header.php
│   │       └── footer.php
│   │
│   ├── client/                        # Client Dashboard
│   │   ├── index.php                  # Today's overview
│   │   ├── workouts.php               # My workouts
│   │   ├── nutrition.php              # Meal logging
│   │   ├── progress.php               # Progress charts
│   │   ├── photos.php                 # Progress photos
│   │   ├── leaderboard.php            # Leaderboard
│   │   ├── profile.php                # Profile settings
│   │   ├── messages.php               # Messages with coach
│   │   └── includes/
│   │       ├── header.php
│   │       └── footer.php
│   │
│   └── editor/                        # Editor Dashboard
│       ├── index.php                  # Overview
│       ├── clients.php                # Assigned clients
│       ├── workouts.php               # Log workouts
│       ├── nutrition.php              # Log meals
│       ├── comments.php               # Add comments
│       ├── photos.php                 # Upload photos
│       └── includes/
│           ├── header.php
│           └── footer.php
│
├── api/                               # REST API Endpoints
│   ├── auth.php                       # Login/logout/register
│   ├── workouts.php                   # Workout CRUD operations
│   ├── nutrition.php                  # Meal logging
│   ├── progress.php                   # Progress data
│   ├── upload-photo.php               # Photo upload
│   └── settings.php                   # Site settings
│
├── includes/                          # Core backend files
│   ├── config.php                     # Database configuration
│   ├── functions.php                  # Helper functions
│   ├── auth.php                       # Authentication logic
│   ├── middleware.php                 # Request middleware
│   ├── roles.php                      # Role-based permissions
│   ├── session.php                    # Session management
│   ├── validation.php                 # Input validation
│   └── db/
│       └── schema.sql                 # Database schema
│
└── uploads/                           # User uploaded files
    └── progress-photos/               # Client progress photos

================================================================================
DATABASE SCHEMA
================================================================================

Total Tables: 23

Core Tables:
| Table Name | Description |
|------------|-------------|
| users | All user accounts (admin, coach, editor, client) |
| user_tokens | Remember me tokens |
| password_resets | Password reset tokens |
| activity_logs | User activity audit |

Relationship Tables:
| Table Name | Description |
|------------|-------------|
| coaches_clients | Coach to client assignments |
| editor_assignments | Editor to client assignments |

Business Tables:
| Table Name | Description |
|------------|-------------|
| leads | Client membership applications |
| exercises | Exercise library |
| workout_templates | Coach-created templates |
| workout_template_exercises | Template exercises |
| client_workouts | Assigned workouts |
| client_workout_exercises | Logged workout sets |

Nutrition Tables:
| Table Name | Description |
|------------|-------------|
| foods | Food database |
| meal_logs | Client meal logs |
| meal_items | Individual food items in meals |

Progress Tables:
| Table Name | Description |
|------------|-------------|
| body_measurements | Weight, waist, chest, etc. |
| progress_photos | Client progress photos |
| fitness_tests | Strength, endurance tests |

Financial Tables:
| Table Name | Description |
|------------|-------------|
| packages | Coaching packages |
| payments | Payment records |

System Tables:
| Table Name | Description |
|------------|-------------|
| leaderboard | Client rankings |
| notifications | System notifications |
| site_settings | Dynamic site configuration |

================================================================================
USER ROLES & PERMISSIONS
================================================================================

1. ADMIN (System Maintenance)
   - Manage coach accounts
   - System settings (timezone, API, email)
   - Branding (colors, logo, homepage content)
   - View system status

2. COACH (Business Owner - MAIN USER)
   - Full client management
   - Create workout templates and assign to clients
   - Create nutrition plans
   - Review and convert leads to clients
   - Create packages and track payments
   - View analytics and reports
   - Manage editors
   - Track client progress
   - Fitness tests tracking

3. CLIENT (End User)
   - View assigned workouts
   - Log workout sets/reps/weight
   - Log meals with nutrition tracking
   - Upload progress photos
   - View progress charts
   - Participate in leaderboard
   - Message coach

4. EDITOR (Assistant)
   - Log workouts for assigned clients
   - Log meals for assigned clients
   - Add comments on progress
   - Upload progress photos for clients

================================================================================
DEFAULT LOGIN CREDENTIALS
================================================================================

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@fitcoach.com | Admin@123 |
| Coach | sarah@fitcoach.com | Admin@123 |
| Client | michael@example.com | Admin@123 |
| Editor | editor@fitcoach.com | Admin@123 |

================================================================================
API ENDPOINTS
================================================================================

Base URL: http://localhost/appF/api/

Authentication:
| Endpoint | Method | Description |
|----------|--------|-------------|
| auth.php?action=login | POST | User login |
| auth.php?action=logout | POST | User logout |
| auth.php?action=check | GET | Check login status |
| auth.php?action=register | POST | Register new user |
| auth.php?action=change-password | POST | Change password |

Workouts:
| Endpoint | Method | Description |
|----------|--------|-------------|
| workouts.php | GET | Get workouts |
| workouts.php?id={id} | GET | Get specific workout |
| workouts.php | POST | Start or log workout |
| workouts.php?id={id} | PUT | Update workout status |

Nutrition:
| Endpoint | Method | Description |
|----------|--------|-------------|
| nutrition.php | GET | Get nutrition data |
| nutrition.php | POST | Log meal |
| nutrition.php?id={id} | DELETE | Delete meal |

Progress:
| Endpoint | Method | Description |
|----------|--------|-------------|
| progress.php?metric=weight | GET | Get weight progress |
| progress.php?metric=measurements | GET | Get body measurements |
| progress.php?action=measurement | POST | Add measurement |
| progress.php?action=fitness_test | POST | Add fitness test |

Upload:
| Endpoint | Method | Description |
|----------|--------|-------------|
| upload-photo.php | POST | Upload progress photo |

Settings:
| Endpoint | Method | Description |
|----------|--------|-------------|
| settings.php | GET | Get all settings |
| settings.php?key={key} | GET | Get specific setting |
| settings.php | PUT | Update settings |

================================================================================
KEY FEATURES BY DASHBOARD
================================================================================

ADMIN DASHBOARD:
- System status monitoring (server load, storage, API, uptime)
- Coach account management (create, password reset, delete)
- Site settings (timezone, date format, contact info)
- Branding (colors, logo, homepage content)

COACH DASHBOARD:
- Client management (add, edit, track)
- Lead management (review applications, convert to clients)
- Workout template creation and assignment
- Nutrition plan management
- Progress tracking with charts
- Fitness tests (1RM, endurance, flexibility)
- Package creation (pricing, benefits, colors)
- Payment tracking
- Analytics and reports
- Editor management
- Schedule calendar
- Messaging system

CLIENT DASHBOARD:
- Today's workout with start button
- Nutrition logging with macro tracking
- Weight progress chart
- Workout completion chart
- Week calendar view
- Progress photos gallery
- Leaderboard with opt-in/out
- Body measurements tracking
- Profile management

EDITOR DASHBOARD:
- Assigned clients list
- Workout logging for clients
- Meal logging for clients
- Progress comments
- Progress photo uploads

================================================================================
PUBLIC WEBSITE SECTIONS
================================================================================

1. Hero Section
   - Title and subtitle (editable via admin)
   - Apply for Membership button
   - Hero image

2. Features Section (6 items)
   - Each has title and description (editable via admin)
   - Icons with hover effects

3. How It Works (4 steps)
   - Apply Online → Free Consultation → Get Your Plan → Start Training

4. About Section
   - What we do description
   - Key differentiators (bulleted list)

5. Testimonials Section
   - Client success stories with ratings
   - Names and results

6. Footer
   - Contact information
   - Social media links
   - Copyright text
   - Quick links

================================================================================
DYNAMIC SITE SETTINGS (via Admin → Branding)
================================================================================

| Setting Key | Description | Default Value |
|-------------|-------------|---------------|
| site_name | Website name | FitCoach Pro |
| primary_color | Primary brand color | #4A6FA5 |
| secondary_color | Secondary brand color | #166088 |
| accent_color | Accent color | #17a2b8 |
| logo_url | Logo image path | /assets/images/logo.png |
| hero_title | Homepage hero title | Transform Your Life |
| hero_subtitle | Homepage hero subtitle | Personalized coaching |
| about_title | About section title | What We Do |
| about_text | About section text | (long description) |
| contact_email | Contact email | support@fitcoachpro.com |
| contact_phone | Contact phone | +1 (555) 123-4567 |
| contact_address | Contact address | 123 Fitness Street |
| feature1_title - feature6_title | Feature titles | (various) |
| feature1_desc - feature6_desc | Feature descriptions | (various) |
| social_facebook | Facebook URL | (empty) |
| social_instagram | Instagram URL | (empty) |
| social_twitter | Twitter URL | (empty) |
| footer_copyright | Copyright text | All rights reserved |

================================================================================
INSTALLATION INSTRUCTIONS
================================================================================

1. Copy all files to web server root (e.g., C:\xampp\htdocs\appF\)

2. Create database:
   - Open phpMyAdmin
   - Create database named 'fitcoach_pro'
   - Import includes/db/schema.sql

3. Configure database connection:
   - Edit includes/config.php
   - Update DB_HOST, DB_USER, DB_PASS, DB_NAME if needed

4. Set up directories:
   - Ensure uploads/ and uploads/progress-photos/ are writable

5. Start web server (Apache) and MySQL

6. Access the site:
   - Public homepage: http://localhost/appF/home/index.php
   - Login page: http://localhost/appF/login.php

7. Default login credentials (after first run):
   - Run reset-passwords.php once to generate working password hashes
   - Then use credentials above

================================================================================
TROUBLESHOOTING
================================================================================

Issue: Invalid email or password
Solution: Run reset-passwords.php in browser to regenerate password hashes

Issue: White screen / PHP errors
Solution: Check PHP error log in XAMPP control panel

Issue: Database connection failed
Solution: Verify MySQL is running and config.php credentials are correct

Issue: 404 Not Found
Solution: Ensure .htaccess is present and mod_rewrite is enabled

Issue: Charts not showing
Solution: Check browser console for JavaScript errors

================================================================================
FUTURE INTEGRATION POINTS
================================================================================

Email Notifications:
- Files ready: includes/auth.php (password reset)
- Files ready: signup.php (lead notification)
- Location: Add mail() calls or SMTP library

Payment Gateway (Stripe/PayPal):
- Files ready: api/payments.php
- Files ready: dashboard/coach/payments.php
- Location: Add webhook handlers

Mobile App API:
- All REST endpoints are ready at /api/
- Authentication uses sessions (JWT ready to add)

================================================================================
CONTACT & SUPPORT
================================================================================

For technical support or questions about this documentation:
- Developer: [Your Name]
- Project: FitCoach Pro Fitness Coaching Platform
- Version: 1.0

================================================================================
END OF DOCUMENTATION
================================================================================