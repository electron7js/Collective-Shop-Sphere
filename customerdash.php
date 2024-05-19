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


// Get the logged-in user's ID
$username = $_SESSION['username'];

// Fetch customer details using JOIN query
$query = "
    SELECT u.username, u.contactnumber, u.email, c.firstname, c.lastname, c.address
    FROM Users u
    JOIN Customer c ON u.userid = c.userid
    WHERE u.username = :username
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$customer = oci_fetch_assoc($stmt);

if (!$customer) {
    echo "Error: Customer details not found.";
    exit();
}

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
    if ($order['CONFIRMED']) { // Only add confirmed orders
        $orders[] = $order;
    }
}


oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
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
            justify-content: space-between;
            margin-top: 20px;
        }
        .button-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button-group .logout-btn {
            background-color: #dc3545;
            color: white;
        }
        .button-group .edit-profile-btn {
            background-color: #007bff;
            color: white;
        }


        .order-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .order-container .order-list {
            list-style: none;
            padding: 0;
        }
        .order-container .order-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
        }
        .order-container .order-list a {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
        }
        .order-container .order-details {
            display: flex;
            flex-direction: column;
        }
        .order-container .order-date {
            font-size: 0.9rem;
            color: #555;
        }
        .order-container .order-status {
            font-size: 0.9rem;
            color: #dc3545;
        }
        .order-container .order-status.confirmed {
            color: #28a745;
        }
        .order-container .view-invoice-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        .order-container .view-invoice-btn:hover {
            background-color: #0056b3;
        }
        .order-container img{
            width: 3rem;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Customer's Dashboard</h2>
    <div class="details">
        <div>First Name: <?= $customer['FIRSTNAME'] ?></div>
        <div>Last Name: <?= $customer['LASTNAME'] ?></div>
        <div>Contact Number: <?= $customer['CONTACTNUMBER'] ?></div>
        <div>Email Address: <?= $customer['EMAIL'] ?></div>
        <div>Address: <?= $customer['ADDRESS'] ?></div>
    </div>

    <div class="button-group">
    <button class="edit-profile-btn" onclick="window.location.href='customerorders.php'">Order History</button>
        <button class="edit-profile-btn" onclick="window.location.href='edit_profile.php'">Edit Profile</button>
        <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>


    <div class="order-container">
    <h2>Confirmed Orders</h2>
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
</div>

<?php include 'footer.php'; ?>

</body>
</html>