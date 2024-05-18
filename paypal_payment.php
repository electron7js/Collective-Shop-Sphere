<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

$purchaseid = $_GET['purchaseid'];

if (!$purchaseid) {
    header('Location: index.php');
    exit();
}

// Update the purchase as confirmed (This would be after successful payment)
$query = "UPDATE Purchase SET confirmed = 1 WHERE purchaseid = :purchaseid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
oci_execute($stmt);

oci_close($conn);

header('Location: confirmation.php');
exit();
?>
