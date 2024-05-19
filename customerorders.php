<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

$userRole = $_SESSION['user_role'];

if ($userRole != 'Customer') {
    header('Location: dashboard.php');
    exit();
} 

// Get the logged-in user's username
$username = $_SESSION['username'];

// Fetch customer details using JOIN query
$query = "
    SELECT u.userid 
    FROM Users u
    WHERE u.username = :username
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
$userid = $user['USERID'];

// Fetch orders for the customer
$query = "
    SELECT p.purchaseid, p.purchase_date, p.confirmed
    FROM Purchase p
    WHERE p.userid = :userid
    ORDER BY p.purchase_date DESC
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
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
    <title>Order History - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
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
        .order-list a {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
        }
        .order-details {
            display: flex;
            flex-direction: column;
        }
        .order-date {
            font-size: 0.9rem;
            color: #555;
        }
        .order-status {
            font-size: 0.9rem;
            color: #dc3545;
        }
        .order-status.confirmed {
            color: #28a745;
        }
        .view-invoice-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .view-invoice-btn:hover {
            background-color: #0056b3;
        }
        img{
            width: 3rem;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Order History</h2>
    <ul class="order-list">
        <?php foreach ($orders as $order): ?>
            <li>
                <a href="invoice.php?purchaseid=<?= $order['PURCHASEID'] ?>">
                    <img style="object-fit:contain" src="images/order_placeholder.png" alt="Order Image" style="width: 50px; height: 50px; margin-right: 20px;">
                    <div class="order-details">
                        <span>Order ID: <?= $order['PURCHASEID'] ?></span>
                        <span class="order-date">Date: <?= date('l, F j, Y', strtotime($order['PURCHASE_DATE'])) ?></span>
                        <span class="order-status <?= $order['CONFIRMED'] ? 'confirmed' : '' ?>">
                            <?= $order['CONFIRMED'] ? 'Payment Confirmed' : 'Payment Not Confirmed' ?>
                        </span>
                    </div>
                </a>
                <button class="view-invoice-btn" onclick="window.location.href='invoice.php?purchaseid=<?= $order['PURCHASEID'] ?>'">View Invoice</button>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'footer.php'; ?>

</body>
</html>