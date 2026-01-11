<?php
// This is a placeholder file for the forgot password functionality
// In a real application, this would handle password reset logic
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - FitCoach Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #4A6FA5, #166088);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .reset-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="reset-card text-center">
        <h3 class="mb-4">Forgot Password</h3>
        <p class="text-muted mb-4">Enter your email to receive a password reset link</p>
        <div class="mb-3">
            <input type="email" class="form-control" placeholder="Your email address">
        </div>
        <button class="btn btn-primary w-100 mb-3">Send Reset Link</button>
        <a href="login.php" class="btn btn-outline-secondary w-100">Back to Login</a>
    </div>
</body>
</html>