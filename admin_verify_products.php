<?php
// Include the config.php file for database connection
include 'config.php';

// Fetch products with activestatus = 0 or NULL
$query = "SELECT productid, name FROM Product WHERE activestatus = 0 OR activestatus IS NULL";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$products = [];
while ($product = oci_fetch_assoc($stmt)) {
    $products[] = $product;
}

// Fetch product ID from the URL or form submission
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['productid']) ? (int)$_POST['productid'] : 0);

// Initialize product as null
$product = null;

if ($product_id > 0) {
    // Prepare the query to fetch product details
    $query = "SELECT * FROM Product WHERE productid = :productid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':productid', $product_id);
    oci_execute($stmt);

    // Fetch the product details
    $product = oci_fetch_assoc($stmt);

    // Check if the product exists
    if (!$product) {
        echo "Product not found.";
        exit;
    }
}

// Verify or reject product if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify'])) {
        $status = 1;
    } elseif (isset($_POST['reject'])) {
        $status = -1;
    }

    // Update the product's activestatus
    $updateQuery = "UPDATE Product SET activestatus = :status WHERE productid = :productid";
    $updateStmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($updateStmt, ':status', $status);
    oci_bind_by_name($updateStmt, ':productid', $product_id);
    if (oci_execute($updateStmt)) {
        echo "Product status updated successfully.";
        header("Location: admin_verify_products.php"); // Redirect to avoid form resubmission
        exit();
    } else {
        echo "Error updating product status.";
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Products</title>
    <link rel="stylesheet" href="product_details_css.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        .main-container {
            position: relative;
            top: -20vh;
        }
        .product-details {
            position: relative;
            top: -2vh;
            left: 10rem;
        }
        .product-name {
            font-size: 4rem !important;
            position: relative;
            top: -4rem;
        }
        .sub-btn {
            position: relative;
            left: -5rem;
        }
        .sub-btn button {
            margin: 1px;
        }
        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            top: -5rem;
        }
        .basket-btn {
            width: 100%;
            padding: 10px 20px;
            border: 0;
            outline: 0;
            background: #B2967D;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            border-radius: 30px;
            cursor: pointer;
            transition: .4s linear;
        }
        .product-selector {
            max-width: 800px;
            margin: 50px auto;
            margin-top: 6rem;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            position: relative;
            top:-70vh;
            z-index: 999;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
        }
        .btn-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-group .verify-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .reject-btn {
            background-color: #dc3545;
            color: white;
        }
        header{
    min-height: 80vh;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
    </style>
    <script>
        function fetchProductDetails(productId) {
            if (!productId) {
                document.getElementById('product-details').innerHTML = '';
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_product_details.php?id=' + productId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('product-details').innerHTML = this.responseText;
                } else {
                    document.getElementById('product-details').innerHTML = 'Error loading product details.';
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
<script src="wishlist.js"></script>
<script src="basket.js"></script>

<?php include 'header.php'; ?>

<div class="product-selector">
    <h2>Verify Products</h2>
    <div class="form-group">
        <label for="productid">Select Product to Verify</label>
        <select name="productid" id="productid" onchange="fetchProductDetails(this.value)" required>
            <option value="">Select a product</option>
            <?php foreach ($products as $productItem): ?>
                <option value="<?= $productItem['PRODUCTID'] ?>"><?= htmlspecialchars($productItem['NAME']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div id="product-details"></div>

<?php include 'footer.php'; ?>

</body>
</html>
