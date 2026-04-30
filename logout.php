<?php
// File: C:\xampp\htdocs\appF\logout.php

require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Logout user
logoutUser();

// Redirect to login page
redirect('/appF/login.php?message=logged_out');
?>