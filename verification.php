<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Get the verification code from the GET or POST request
$verificationCode = isset($_GET['code']) ? $_GET['code'] : (isset($_POST['code']) ? $_POST['code'] : null);

if (!$verificationCode) {
    echo "Error: Verification code is required.";
    exit();
}

// Get the user ID from the session
$username = $_SESSION['username'];

// Fetch the user ID from the Users table using the username
$query = "SELECT userid FROM Users WHERE username = :username";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);

if (!$user) {
    echo "Error: User not found.";
    exit();
}

$userid = $user['USERID'];

// Check the verification code
$query = "
SELECT * FROM 
(SELECT * FROM (
        SELECT * FROM VerificationCodes 
        WHERE userid = :userid ORDER BY verificationid DESC ) 
        output WHERE ROWNUM = 1)  
        WHERE code = :code

";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_bind_by_name($stmt, ':code', $verificationCode);
oci_execute($stmt);
$verification = oci_fetch_assoc($stmt);

if (!$verification) {
    echo "Error: Invalid verification code.";
    exit();
}

else{
// Update the verified status in the Users table
$query = "UPDATE customer SET verified = 1 WHERE userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
if (oci_execute($stmt)) {
    echo "Verification successful. Your account has been verified.";
} else {
    echo "Error: Unable to verify your account. Please try again.";
}
}
// Close the database connection
oci_close($conn);
?>