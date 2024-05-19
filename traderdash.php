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
    SELECT u.userid, u.username, u.contactnumber, u.email, t.address, t.secondarycontact, t.verified, s.name AS shopname, s.logo
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

// Fetch top selling products
$query = "
    SELECT p.name, SUM(pd.quantity) AS amount
    FROM Purchase_detail pd
    JOIN Product p ON pd.productid = p.productid
    JOIN Shop s ON p.shopid = s.shopid
    WHERE s.userid = :userid
    GROUP BY p.name
    ORDER BY amount DESC
    FETCH FIRST 4 ROWS ONLY
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $trader['USERID']);
oci_execute($stmt);

$topProducts = [];
while ($product = oci_fetch_assoc($stmt)) {
    $topProducts[] = $product;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trader's Dashboard</title>
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
        .details {
            display: flex;
            flex-direction: column;
        }
        .details div {
            margin-bottom: 10px;
        }
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 20px;
        }
        .button-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .button-group .logout-btn {
            background-color: #dc3545;
            color: white;
        }
        .button-group .action-btn {
            background-color: #007bff;
            color: white;
        }

        .product-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .product-container .product-list {
            list-style: none;
            padding: 0;
        }
        .product-container .product-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .product-container .product-list .product-details {
            display: flex;
            flex-direction: column;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Trader's Dashboard</h2>

    <div class="shop-logo">
        <label for="shop-logo">Shop logo</label>
        <?php if ($trader['LOGO']): ?>
            <img src="<?php echo $trader['LOGO']; ?>" alt="Shop Logo" style="max-width: 150px; display: block; margin-bottom: 10px;">
        <?php endif; ?>
    </div>

    <div class="details">
        <div>Shop Name: <?= $trader['SHOPNAME'] ?></div>
        <div>Username: <?= $trader['USERNAME'] ?></div>
        <div>Contact Number: <?= $trader['CONTACTNUMBER'] ?></div>
        <div>Email Address: <?= $trader['EMAIL'] ?></div>
        <div>Secondary Contact: <?= $trader['SECONDARYCONTACT'] ?></div>
        <div>Address: <?= $trader['ADDRESS'] ?></div>
    </div>

    <div class="button-group">
        <button class="action-btn" onclick="window.location.href='offers.php'">Offers</button>
        <button class="action-btn" onclick="window.location.href='trader_edit_shop.php'">Edit Shop</button>
        <button class="action-btn" onclick="window.location.href='trader_add_product.php'">Add Product</button>
        <button class="action-btn" onclick="window.location.href='edit_product.php'">Edit Product</button>
        <button class="action-btn" onclick="window.location.href='trader_edit_account.php'">Edit Account</button>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
</div>

<div class="product-container">
    <h2>Top Selling Products</h2>
    <ul class="product-list">
        <?php foreach ($topProducts as $index => $product): ?>
            <li>
                <div class="product-details">
                    <span>Top <?= $index + 1 ?>: <?= $product['NAME'] ?></span>
                    <span>Amount Sold: <?= $product['AMOUNT'] ?></span>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
