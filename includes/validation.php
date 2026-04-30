<?php
// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone number (basic)
function validatePhone($phone) {
    return preg_match('/^[\d\s\-\+\(\)]{10,}$/', $phone);
}

// Validate date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Validate password strength
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    return empty($errors) ? true : $errors;
}

// Validate workout data
function validateWorkoutLog($sets, $reps, $weight) {
    $errors = [];
    
    if (!is_numeric($sets) || $sets < 1 || $sets > 20) {
        $errors[] = "Invalid sets count";
    }
    if (!is_numeric($reps) || $reps < 1 || $reps > 100) {
        $errors[] = "Invalid reps count";
    }
    if (!is_numeric($weight) || $weight < 0 || $weight > 1000) {
        $errors[] = "Invalid weight";
    }
    
    return empty($errors) ? true : $errors;
}

// Sanitize array
function sanitizeArray($data) {
    return array_map('sanitize', $data);
}

// Validate required fields
function validateRequired($data, $fields) {
    $errors = [];
    
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            $errors[$field] = ucfirst($field) . " is required";
        }
    }
    
    return empty($errors) ? true : $errors;
}
?>