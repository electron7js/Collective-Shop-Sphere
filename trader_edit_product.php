<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Check user role
$userRole = $_SESSION['user_role'];
if ($userRole != 'Trader') {
    header('Location: dashboard.php');
    exit();
}

// Get the logged-in user's ID
$username = $_SESSION['username'];

// Fetch trader details using JOIN query
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

// Fetch products for the shop
$query = "SELECT productid, name FROM Product WHERE shopid = :shopid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':shopid', $shopid);
oci_execute($stmt);

$products = [];
while ($product = oci_fetch_assoc($stmt)) {
    $products[] = $product;
}

// Fetch categories
$query = "SELECT categoryid, title FROM Category";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$categories = [];
while ($category = oci_fetch_assoc($stmt)) {
    $categories[] = $category;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            margin-top: 6rem;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .btn-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-group .save-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .remove-btn {
            background-color: #dc3545;
            color: white;
        }
        .btn-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }
        .outer-container{
            min-height:70vh;
        }
        .currimage{
            width: 100%;
            object-fit:contain;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<div class="outer-container">
<div class="container">
    <h2>Edit Product</h2>
    <form method="post" action="update_product.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product">Product Name</label>
            <select name="productid" id="product" onchange="loadProductDetails()" required>
                <option value="">Select a product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['PRODUCTID'] ?>"><?= $product['NAME'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="product-details" style="display: none;">
            <div class="form-group">
                <label for="category">Product Category</label>
                <select name="categoryid" id="category" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['CATEGORYID'] ?>"><?= $category['TITLE'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" id="price" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" name="remainingstock" id="stock" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="image">Upload Image</label>
                <input type="file" name="image" id="image">
            </div>
            <div class="form-group">
                <label for="image">Current Image</label>
                <img id="currimage" class="currimage">
            </div>
            <div class="btn-group">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="remove-btn" onclick="removeProduct()">Remove</button>
                <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
            </div>
        </div>
    </form>
</div>
</div>
<?php include 'footer.php'; ?>

<script>
    function loadProductDetails() {
        const productId = document.getElementById('product').value;
        if (productId) {
            fetch(`get_product_details.php?productid=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {

                        console.log(data);
                        document.getElementById('product-details').style.display = 'block';
                        document.getElementById('category').value = data.product.CATEGORYID;
                        document.getElementById('price').value = data.product.PRICE;
                        document.getElementById('stock').value = data.product.REMAININGSTOCK;
                        document.getElementById('description').value = data.product.DESCRIPTION;
                        document.getElementById('currimage').src = data.product.IMAGE;

                    } else {
                        alert('Failed to load product details.');
                    }
                });
        } else {
            document.getElementById('product-details').style.display = 'none';
        }
    }

    function removeProduct() {
        const productId = document.getElementById('product').value;
        if (productId && confirm('Are you sure you want to remove this product?')) {
            window.location.href = `remove_product.php?productid=${productId}`;
        }
    }
</script>

</body>
</html>