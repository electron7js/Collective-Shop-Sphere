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

// Fetch orders for the trader's shop
$query = "
    SELECT pd.productid, p.name AS productname, p.image, pd.purchaseid, pd.quantity ,cs.collection_start
    FROM Purchase_detail pd
    JOIN Product p ON pd.productid = p.productid
    JOIN Purchase pc ON pd.purchaseid = pc.purchaseid
    JOIN purchase_collection_slot pcs ON pc.purchaseid = pcs.purchaseid
    JOIN Collection_Slot cs ON pcs.collection_slot_id = cs.collection_slot_id
    WHERE p.shopid = :shopid
    ORDER BY cs.collection_start DESC
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':shopid', $shopid);
oci_execute($stmt);

$orders = [];
while ($order = oci_fetch_assoc($stmt)) {
    $orders[] = $order;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders</title>
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
        .order-list {
            list-style: none;
            padding: 0;
        }
        .order-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .order-list .order-details {
            display: flex;
            flex-direction: column;
        }
        .order-list .order-product {
            display: flex;
            align-items: center;
        }
        .order-list .order-product img {
            width: 50px;
            height: 50px;
            margin-right: 20px;
        }
        .order-list .order-product span {
            font-weight: bold;
        }
        .order-list .manage-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .order-list .manage-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div style="min-height:60vh;">
<div class="container">
    <h2>Orders</h2>
    <ul class="order-list">
        <?php foreach ($orders as $order): ?>
            <li>
                <div class="order-product">
                    <img src="<?= $order['IMAGE'] ?>" alt="Product Image">
                    <div class="order-details">
                        <span>Product: <?= $order['PRODUCTNAME'] ?></span>
                        <span>Quantity: <?= $order['QUANTITY'] ?></span>
                        <span>Order Number: <?= $order['PURCHASEID'] ?></span>
                        <span>Collection Start:  <?php 
                                $collectionStart = DateTime::createFromFormat('d-M-y h.i.s.u A', $order['COLLECTION_START']);
                                echo $collectionStart->format('l, F j, Y h:i A');
                            ?></span>
                    </div>
                </div>
                <button class="manage-btn" onclick="window.location.href='manage_order.php?purchaseid=<?= $order['PURCHASEID'] ?>'">Manage</button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</div>
<?php include 'footer.php'; ?>

</body>
</html>
