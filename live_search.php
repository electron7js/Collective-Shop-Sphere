<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$query = isset($data['query']) ? $data['query'] : '';

$response = ['success' => false, 'products' => []];

if ($query) {
    // Sanitize user input
    $query = htmlspecialchars($query);

    // Search query
    $sql = "SELECT * FROM Product WHERE LOWER(name) LIKE '%' || LOWER(:query) || '%'";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':query', $query);
    oci_execute($stmt);

    while ($product = oci_fetch_assoc($stmt)) {
        $response['products'][] = [
            'PRODUCTID' => $product['PRODUCTID'],
            'NAME' => $product['NAME'],
            'PRICE' => $product['PRICE']
        ];
    }

    if (!empty($response['products'])) {
        $response['success'] = true;
    }

    oci_close($conn);
}

echo json_encode($response);

?>