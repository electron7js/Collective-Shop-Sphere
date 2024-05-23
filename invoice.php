<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';
include 'functions.php';

$purchaseid = $_GET['purchaseid'];

if (!$purchaseid) {
    header('Location: index.php');
    exit();
}

$purchaseid = $_GET['purchaseid'];

$pickupDetails = getPickupDetails($purchaseid);

$collectionDate = $pickupDetails['COLLECTION_DATE'];
$collectionStart = $pickupDetails['COLLECTION_START'];
$collectionEnd = $pickupDetails['COLLECTION_END'];

// Convert date and time using DateTime::createFromFormat
$collectionDateTime = DateTime::createFromFormat('d-M-y', $collectionDate);
$collectionStartTime = DateTime::createFromFormat('d-M-y h.i.s.u A', $collectionStart);
$collectionEndTime = DateTime::createFromFormat('d-M-y h.i.s.u A', $collectionEnd);

// Fetch purchase details
$query = "SELECT p.purchaseid, p.purchase_date, u.username, c.firstname, c.lastname, c.address
          FROM Purchase p
          JOIN Users u ON p.userid = u.userid
          JOIN Customer c ON p.userid = c.userid
          WHERE p.purchaseid = :purchaseid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
oci_execute($stmt);
$purchase = oci_fetch_assoc($stmt);

if (!$purchase) {
    header('Location: index.php');
    exit();
}

// Fetch purchase items
$query = "SELECT pd.productid, pd.quantity, pd.price, pd.quantity * pd.price AS amount, pr.name
          FROM Purchase_detail pd
          JOIN Product pr ON pd.productid = pr.productid
          WHERE pd.purchaseid = :purchaseid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
oci_execute($stmt);
$items = [];
$total = 0;

while ($row = oci_fetch_assoc($stmt)) {
    $items[] = $row;
    $discount= checkDiscount($row['PRODUCTID']);
    $total +=( $discount?($row['PRICE']-($discount['DISCOUNTPERCENT']/100*$row['PRICE'])):$row['PRICE'] )* $row['QUANTITY'] ; 

}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <link rel="stylesheet" href="style.css">
    <style>

        .outer-container{
            min-height:60vh;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            margin-top : 6rem;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        h1, h2, p {
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 0px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        .text-center {
            text-align: center;
        }
        .btn {
            display: block;
            width: 100px;
            padding: 10px;
            margin: 20px auto;
            background-color: #28a745;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<div class="outer-container">
<div class="container">
    <h1 class="text-center">Invoice</h1>
    <div style="  padding:10px ;border: 1px solid black;">
    <p>Invoice number: <?= $purchase['PURCHASEID'] ?></p>
    <p style="float:right; position:relative; top:-4vh;">Date of issue: <?= date('Y-m-d', strtotime($purchase['PURCHASE_DATE'])) ?></p>
    <p>Pickup Date and Time: <?= $collectionDateTime->format('l, F j, Y') ?> <?= $collectionStartTime->format('h:i A') ?> - <?= $collectionEndTime->format('h:i A') ?></p>
    <h2>Bill No: <?= $purchase['PURCHASEID'] ?></h2>
    <p>Client name: <?= $purchase['FIRSTNAME'] . ' ' . $purchase['LASTNAME'] ?></p> 
    <hr>
    <p>Address: <?= $purchase['ADDRESS'] ?></p>
    <hr>
    <table style="margin:0px;">
        <thead>
            <tr>
                <th style="width:20%">Item</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>Discount</th>
                <th>Amount</th>
            </tr>
        </thead>
        </table>
        <hr>
    <table>
        <tbody>
            <?php foreach ($items as $index => $item): ?>
                <tr>
                    <td style="width:25%" ><?= $item['NAME'] ?></td>
                    <td style="width:15%" ><?= $item['QUANTITY'] ?></td>
                    <td>$<?= number_format($item['PRICE'], 2) ?></td>
                    <td><?php 
            $discount = checkDiscount($item['PRODUCTID']);
            echo  ($discount?$discount['DISCOUNTPERCENT']:0 ).'%'; ?></td>
                    <td>$<?= number_format($item['PRICE']*$item['QUANTITY']*(1-(($discount?$discount['DISCOUNTPERCENT']:0)/100)), 2)?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>

    </table>
    <p>Total: $<?= $total?></p>

    </div>
</div>
<a href="index.php" class="btn">Go Back</a>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
