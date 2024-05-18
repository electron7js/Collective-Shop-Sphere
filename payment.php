<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

$purchaseid = $_GET['purchaseid'];

if (!$purchaseid) {
    header('Location: index.php');
    exit();
}

// Fetch purchase total
$query = "SELECT SUM(pd.price * pd.quantity) AS total
          FROM Purchase_detail pd
          WHERE pd.purchaseid = :purchaseid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
oci_execute($stmt);
$row = oci_fetch_assoc($stmt);
$total = $row['TOTAL'];

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .container {
            min-height: 55vh;
            margin-top: 10vh;
        }
        h2 {
            font-size: 3rem;
            font-weight: 300;
            margin: 1rem;
        }
        p {
            font-size: 2rem;
            margin: 2rem;
        }
        h3 {
            font-size: 1.5rem;
            margin: 2rem;
            font-weight: 500;
        }
        button {
            width: 8rem;
            height: 8rem;
            margin-left: 2rem
        }
    </style>
    <script src="https://www.paypal.com/sdk/js?client-id=Adc3603u4s-ZjJy7cXap1rx3XrSRX8D0w6jT0ZVfDnfkdqZvXBXcK2ZatD_XwAfbdNR_j248t5FaLom1"></script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Payment</h2>
    <hr>
    <p>Total Payment: $<?= number_format($total, 2) ?></p>

    <div>
        <h3>Payment options:</h3>
        <div class="form-group">
            <div class="paypal-button-container" id="paypal-button-container"></div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: '<?= number_format($total, 2) ?>'
                    }
                }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Extract payment details from the response
                const externaltransactionid = details.id;
                const paymentamount = details.purchase_units[0].amount.value;

                // Redirect to a success page with payment details
                window.location.href = 'payment_success.php?purchaseid=<?= $purchaseid ?>&externaltransactionid=' + externaltransactionid + '&paymentamount=' + paymentamount;
            });
        }
    }).render('#paypal-button-container');
</script>
</body>
</html>
