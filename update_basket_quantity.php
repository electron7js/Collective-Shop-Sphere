<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

include 'config.php';

// Get the logged-in user's ID
$username = $_SESSION['username'];
$query = "SELECT userid FROM Users WHERE username = :username";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
$userid = $user['USERID'];

// Get the product ID and quantity from the request
$data = json_decode(file_get_contents("php://input"), true);
$productid = $data['product_id'];
$quantity = $data['quantity'];

// Update the quantity in the Product_Basket table
$query = "UPDATE Product_Basket pb
          JOIN Basket b ON pb.basketid = b.basketid
          SET pb.quantity = :quantity
          WHERE pb.productid = :productid AND b.userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':quantity', $quantity);
oci_bind_by_name($stmt, ':productid', $productid);
oci_bind_by_name($stmt, ':userid', $userid);
$result = oci_execute($stmt);

oci_close($conn);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
}
