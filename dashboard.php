<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';
include 'functions.php';

$username = $_SESSION['username'];

if (!isset($_SESSION['user_role'])) {
    $userRole = checkUserRole($username, $conn);
} else {
    $userRole = $_SESSION['user_role'];
}

// Redirect based on the user role
if ($userRole == 'Customer') {
    header('Location: customerdash.php');
    exit();
} elseif ($userRole == 'Trader') {
    header('Location: traderdash.php');
    exit();
} elseif ($userRole == 'Admin') {
    header('Location: admindash.php');
    exit();
} else {
    // Handle case where user has no role
    echo "Error: User role not found.";
}
?>
