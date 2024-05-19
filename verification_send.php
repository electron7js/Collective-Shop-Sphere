<?php
session_start();

require 'config.php';
require 'email_function.php'; 

// Check if userid is provided in the GET request
if (!isset($_GET['userid']) || empty($_GET['userid'])) {
    echo "Error: User ID is required.";
    exit();
}

$userId = $_GET['userid'];

try {
    // Call the sendVerificationEmail function
    sendVerificationEmail($userId);
    echo "Verification email sent successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>