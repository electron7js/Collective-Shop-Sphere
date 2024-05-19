<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Fetch trader details using JOIN query
$username = $_SESSION['username'];
$query = "
    SELECT u.userid, s.shopid
    FROM Users u
    JOIN Trader t ON u.userid = t.userid
    JOIN Shop s ON u.userid = s.userid
    WHERE u.username = :username
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$trader = oci_fetch_assoc($stmt);

if (!$trader) {
    echo "Error: Trader details not found.";
    exit();
}

$shopid = $trader['SHOPID'];

$productid = $_POST['productid'];
$categoryid = $_POST['categoryid'];
$price = $_POST['price'];
$remainingstock = $_POST['remainingstock'];
$description = $_POST['description'];

// Image upload
$image = $_FILES['image'];
$imagePath = '';

if ($image && $image['tmp_name']) {
    include 'functions.php';
    $imagePath = saveProductImage($image);
}

$query = "
    UPDATE Product 
    SET 
        categoryid = :categoryid, 
        price = :price, 
        remainingstock = :remainingstock, 
        description = :description
        " . ($imagePath ? ", image = :image" : "") . "
    WHERE productid = :productid AND shopid = :shopid
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':categoryid', $categoryid);
oci_bind_by_name($stmt, ':price', $price);
oci_bind_by_name($stmt, ':remainingstock', $remainingstock);
oci_bind_by_name($stmt, ':description', $description);
oci_bind_by_name($stmt, ':productid', $productid);
oci_bind_by_name($stmt, ':shopid', $shopid);

if ($imagePath) {
    oci_bind_by_name($stmt, ':image', $imagePath);
}

$result = oci_execute($stmt);

oci_close($conn);

if ($result) {
    header('Location: traderdash.php');
    exit();
} else {
    echo "Error: Could not update product.";
}
?>