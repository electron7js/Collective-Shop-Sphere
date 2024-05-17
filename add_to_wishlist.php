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

// Get the product ID from the request
$data = json_decode(file_get_contents("php://input"), true);
$productid = $data['product_id'];

// Check if the wishlist exists for the user, if not create one
$query = "SELECT wishlistid FROM Wishlist WHERE userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$wishlist = oci_fetch_assoc($stmt);

if (!$wishlist) {
    $query = "INSERT INTO Wishlist (wishlistid, userid) VALUES (seq_wishlistid.NEXTVAL, :userid) RETURNING wishlistid INTO :wishlistid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':wishlistid', $wishlistid);
    oci_execute($stmt);
} else {
    $wishlistid = $wishlist['WISHLISTID'];
}

// Check if the product is already in the wishlist
$query = "SELECT * FROM Wishlist_Product WHERE wishlistid = :wishlistid AND productid = :productid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':wishlistid', $wishlistid);
oci_bind_by_name($stmt, ':productid', $productid);
oci_execute($stmt);
$product = oci_fetch_assoc($stmt);

if ($product) {
    echo json_encode(['success' => false, 'message' => 'Product already in wishlist']);
} else {
   
$result = addToWishlist($userid, $productid);

if ($result) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to wishlist']);
}

}


ob_end_flush();
