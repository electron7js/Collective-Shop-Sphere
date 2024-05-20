<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Get the logged-in admin's ID
$username = $_SESSION['username'];

// Fetch admin details
$query = "SELECT * FROM Admin, Users WHERE username = :username AND Admin.userid=users.userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$admin = oci_fetch_assoc($stmt);

if (!$admin) {
    echo "Error: Admin details not found.";
    exit();
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .button-group .action-btn {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Admin Dashboard</h2>

    <div class="details">
        <div>Admin Name: <?= $admin['NAME'] ?></div>
        <div>Username: <?= $admin['USERNAME'] ?></div>
    </div>

    <div class="button-group">
        <button class="action-btn" onclick="window.location.href='admin_edit_details.php'">Edit Admin Details</button>
        <button class="action-btn" onclick="window.location.href='admin_edit_customer.php'">Edit Customer Details</button>
        <button class="action-btn" onclick="window.location.href='admin_edit_trader.php'">Edit Trader Details</button>
        <button class="action-btn" onclick="window.location.href='admin_edit_shop.php'">Edit Shop Details</button>
        <button class="action-btn" onclick="window.location.href='admin_view_orders.php'">View All Orders</button>
        <button class="action-btn" onclick="window.location.href='admin_verify_products.php'">Verify Products</button>
        <button class="action-btn" onclick="window.location.href='admin_verify_shops.php'">Verify Shops</button>
        <button class="action-btn" onclick="window.location.href='admin_view_payments.php'">View Payments</button>
        <button class="action-btn" onclick="window.location.href='admin_review_reviews.php'">Review Reviews</button>
        <button class="action-btn" onclick="window.location.href='admin_create_collection_slot.php'">Create Collection Slot</button>

    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
