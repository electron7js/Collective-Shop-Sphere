<?php
session_start();
ob_start(); 

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include 'config.php';
include 'functions.php';

// Get the logged-in user's ID
$username = $_SESSION['username'];
$query = "SELECT userid FROM Users WHERE username = :username";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
$userid = $user['USERID'];

$data = json_decode(file_get_contents("php://input"), true);
$productid = $data['product_id'];

if(getRemainingStock($productid)>=1){

$result = addToBasket($userid, $productid);

       if ($result) {
                ob_end_clean();
                echo json_encode(['success' => true]);
            } 
        else {
                ob_end_clean();
                echo json_encode(['success' => false, 'message' => 'Failed to add product to basket']);
        }
}

else{
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Failed to add product to basket, Out of stock']);
}

