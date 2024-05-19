<?php
include 'config.php';

$productId = $_GET['productid'];

$query = "SELECT * FROM Product WHERE productid = :productid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':productid', $productId);
oci_execute($stmt);

$product = oci_fetch_assoc($stmt);

oci_close($conn);

if ($product) {
    echo json_encode(['success' => true, 'product' => $product]);
} else {
    echo json_encode(['success' => false]);
}
?>