<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';
include 'functions.php';

$purchaseid = $_GET['purchaseid'];
$externaltransactionid = $_GET['externaltransactionid']; // From PayPal
$paymentamount = $_GET['paymentamount']; // From PayPal
$method = 'PayPal';

if (!$purchaseid || !$externaltransactionid || !$paymentamount) {
    header('Location: index.php');
    exit();
}

// Insert payment details into the Payment table
$query = "INSERT INTO Payment (paymentid, timestamp, externaltransactionid, paymentamount, method, purchaseid)
          VALUES (seq_paymentid.NEXTVAL, SYSTIMESTAMP, :externaltransactionid, :paymentamount, :method, :purchaseid)";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':externaltransactionid', $externaltransactionid);
oci_bind_by_name($stmt, ':paymentamount', $paymentamount);
oci_bind_by_name($stmt, ':method', $method);
oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
$result = oci_execute($stmt);

if ($result) {
    // Confirm the purchase
    $confirmResult = confirmPurchase($purchaseid);
    if ($confirmResult) {
        oci_commit($conn);
        header("Location: invoice.php?purchaseid=" . $purchaseid);
        exit();
    } else {
        oci_rollback($conn);
        echo "Error: Failed to confirm purchase.";
    }
} else {
    oci_rollback($conn);
    echo "Error: " . oci_error($stmt);
}

oci_close($conn);