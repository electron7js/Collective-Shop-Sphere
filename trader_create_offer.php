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

// Fetch trader's shop ID
$query = "
    SELECT s.shopid
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

// Fetch products for the trader's shop
$query = "
    SELECT p.productid, p.name, c.title AS category, p.price, p.remainingstock, p.image
    FROM Product p
    JOIN Category c ON p.categoryid = c.categoryid
    WHERE p.shopid = :shopid
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':shopid', $shopid);
oci_execute($stmt);

$products = [];
while ($product = oci_fetch_assoc($stmt)) {
    $products[] = $product;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productid = $_POST['productid'];
    $discountpercent = $_POST['discountpercent'];
    $startdate = $_POST['startdate'];
    $enddate = $_POST['enddate'];
    $description = $_POST['description'];

    // Insert into Discount table
    $query = "
        INSERT INTO Discount (discountid, discountpercent, startdate, enddate, shopid)
        VALUES (seq_discountid.NEXTVAL, :discountpercent, TO_DATE(:startdate, 'YYYY-MM-DD'), TO_DATE(:enddate, 'YYYY-MM-DD'), :shopid)
        RETURNING discountid INTO :discountid
    ";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':discountpercent', $discountpercent);
    oci_bind_by_name($stmt, ':startdate', $startdate);
    oci_bind_by_name($stmt, ':enddate', $enddate);
    oci_bind_by_name($stmt, ':shopid', $shopid);
    oci_bind_by_name($stmt, ':discountid', $discountid, -1, SQLT_INT);
    oci_execute($stmt);

    // Insert into Product_Discount table
    $query = "
        INSERT INTO Product_Discount (pdid, discountid, productid)
        VALUES (seq_pdid.NEXTVAL, :discountid, :productid)
    ";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':discountid', $discountid);
    oci_bind_by_name($stmt, ':productid', $productid);
    oci_execute($stmt);

    // Redirect to trader dashboard or offers page
    header('Location: traderdash.php');
    exit();
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Offer</title>
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
        .button-group {
            display: flex;
            justify-content: space-between;
        }
        .button-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button-group .save-btn {
            background-color: #28a745;
            color: white;
        }
        .button-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }

        .offer-container img{
            width: 10rem;
            height: 10rem;
            object-fit: contain;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Create Offer</h2>

    <form method="post" action="">
        <div class="form-group">
            <label for="productid">Product</label>
            <select name="productid" id="productid" required onchange="updateProductDetails(this.value)">
                <option value="">Select a product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= $product['PRODUCTID'] ?>"><?= $product['NAME'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="product-details" style="display: none;">
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" readonly>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="text" id="price" readonly>
            </div>
            <div class="form-group">
                <label for="remainingstock">Stock</label>
                <input type="text" id="remainingstock" readonly>
            </div>
            <div class="form-group">
                <img id="product-image" src="" alt="Product Image">
            </div>
        </div>

        <div class="form-group">
            <label for="discountpercent">Discount Percent</label>
            <input type="number" name="discountpercent" id="discountpercent" required>
        </div>

        <div class="form-group">
            <label for="startdate">Start Date</label>
            <input type="date" name="startdate" id="startdate" required>
        </div>

        <div class="form-group">
            <label for="enddate">End Date</label>
            <input type="date" name="enddate" id="enddate" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" rows="4"></textarea>
        </div>

        <div class="button-group">
            <button type="submit" class="save-btn">Save</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='traderdash.php'">Cancel</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

<script>
    const products = <?= json_encode($products) ?>;

    function updateProductDetails(productId) {
        const product = products.find(p => p.PRODUCTID == productId);
        if (product) {
            document.getElementById('category').value = product.CATEGORY;
            document.getElementById('price').value = product.PRICE;
            document.getElementById('remainingstock').value = product.REMAININGSTOCK;
            document.getElementById('product-image').src = product.IMAGE;
            document.getElementById('product-details').style.display = 'block';
        } else {
            document.getElementById('product-details').style.display = 'none';
        }
    }
</script>
</body>
</html>
