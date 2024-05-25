<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Fetch total sales amount for each trader for the current week
$query = "

SELECT s.name as shop_name, sum(pd.quantity*pd.price) AS total_amount from purchase_detail pd
JOIN Product p ON pd.productid = p.productid
JOIN SHOP s on s.shopid=p.shopid
JOIN PURCHASE pc on pc.purchaseid=pd.purchaseid
JOIN Trader t ON s.userid = t.userid
JOIN USERS u ON u.userid=t.userid
WHERE pc.PURCHASE_DATE >= TRUNC(SYSDATE, 'IW') AND pc.PURCHASE_DATE  < TRUNC(SYSDATE, 'IW') + 7
GROUP BY s.name
";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$payments = [];
while ($payment = oci_fetch_assoc($stmt)) {
    $payments[] = $payment;
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Payments - Admin</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .outer-container{
            min-height:60vh;

        }
        .container {
            max-width: 1000px;
            margin: 80px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .payment-list {
            list-style: none;
            padding: 0;
        }
        .payment-list li {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .payment-list li:last-child {
            border-bottom: none;
        }
        .payment-header {   
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .payment-header div {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<div class="outer-container">
<div class="container">
    <h2>Weekly Payments to Traders</h2>
    <ul class="payment-list">
        <?php foreach ($payments as $payment): ?>
            <li>
                <div class="payment-header">
                    <div>Trader Name: <?= htmlspecialchars($payment['SHOP_NAME']) ?></div>
                    <div>Weekly Total Amount: $<?= number_format($payment['TOTAL_AMOUNT'], 2) ?></div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
</div>
<?php include 'footer.php'; ?>

</body>
</html>
