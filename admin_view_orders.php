<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Fetch all orders within the date range if specified
$query = "
    SELECT p.purchaseid, p.purchase_date, p.confirmed, u.username
    FROM Purchase p
    JOIN Users u ON p.userid = u.userid
";

if ($startDate && $endDate) {
    $query .= "WHERE p.purchase_date BETWEEN TO_DATE(:start_date, 'YYYY-MM-DD') AND TO_DATE(:end_date, 'YYYY-MM-DD') ";
}

$query .= "ORDER BY p.purchase_date DESC";

$stmt = oci_parse($conn, $query);

if ($startDate && $endDate) {
    oci_bind_by_name($stmt, ':start_date', $startDate);
    oci_bind_by_name($stmt, ':end_date', $endDate);
}

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
    <title>All Orders - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .outer-container{
            min-height:70vh;
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
            padding:2rem;
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
        .filter-form {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .filter-form input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .filter-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<div class="outer-container">
<div class="container">
    <h2>All Orders</h2>

    <form method="get" class="filter-form">
        <div>
            <label for="start_date">Start Date: </label>
            <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>">
        </div>
        <div>
            <label for="end_date">End Date: </label>
            <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>">
        </div>
        <button type="submit">Filter</button>
    </form>

    <ul class="order-list">
        <?php foreach ($orders as $order): ?>
            <li>
                <a href="invoice.php?purchaseid=<?= $order['PURCHASEID'] ?>">
                    <img style="object-fit:contain" src="images/order_placeholder.png" alt="Order Image" style="width: 50px; height: 50px; margin-right: 20px;">
                    <div class="order-details">
                        <span>Order ID: <?= $order['PURCHASEID'] ?></span>
                        <span>Customer: <?= $order['USERNAME'] ?></span>
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