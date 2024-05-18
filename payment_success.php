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

// Assuming the payment process is completed here
header("Location: invoice.php?purchaseid=$purchaseid");
exit();
?>
