<?php
session_start();

// Include the config.php file for database connection
include 'config.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the logged-in user's ID
$username = $_SESSION['username'];
$query = "SELECT userid FROM Users WHERE username = :username";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
$userid = $user['USERID'];

// Fetch products in the user's basket
$query = "SELECT pb.productid, p.name, p.price, pb.quantity 
          FROM Product_Basket pb 
          JOIN Basket b ON pb.basketid = b.basketid 
          JOIN Product p ON pb.productid = p.productid 
          WHERE b.userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);

// Calculate the total price
$total = 0;
$products = [];
while ($product = oci_fetch_assoc($stmt)) {
    $products[] = $product;
    $total += $product['PRICE'] * $product['QUANTITY'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the selected date and time
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Insert purchase details into the database
    $purchase_date = $date . ' ' . $time;
    $query = "INSERT INTO Purchase (purchaseid, purchase_date, confirmed, userid) VALUES (seq_purchaseid.NEXTVAL, TO_TIMESTAMP(:purchase_date, 'YYYY-MM-DD HH24:MI:SS'), 1, :userid) RETURNING purchaseid INTO :purchaseid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':purchase_date', $purchase_date);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
    oci_execute($stmt);

    // Insert purchase details for each product
    foreach ($products as $product) {
        $query = "INSERT INTO Purchase_Detail (purchasedetailid, productid, purchaseid) VALUES (seq_purchasedetailid.NEXTVAL, :productid, :purchaseid)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':productid', $product['PRODUCTID']);
        oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
        oci_execute($stmt);
    }

    // Clear the user's basket
    $query = "DELETE FROM Product_Basket WHERE basketid = (SELECT basketid FROM Basket WHERE userid = :userid)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);

    // Redirect to a success page
    header('Location: success.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Purchase - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 700px;
            margin: 100px auto;
            padding: 20px;
        }

        .product-list {
            list-style: none;
            padding: 0;
            font-size:2rem;
            font-weight:300;
        }
        .product-list li {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            min-height:15vh;
            padding:1rem;

        }
        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
            font-size:2rem;
        }
        .selectors{
            width:100%;
            display: flex;
           flex-direction: row;
            justify-content: space-between;
          align-items: flex-end;
        }

        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
        }
        .btn-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-group .confirm-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .back-btn {
            background-color: #ccc;
            color: black;
        }

        h2{
            margin:1rem;
            margin-left:0rem;
        }
        hr{
            margin-top:1rem;
            margin-bottom:1rem;
        }
        .bill-header{
            display: flex;
    flex-wrap: nowrap;
    flex-direction: row;
    justify-content: space-around;
    text-align: left;
    align-items: stretch;
}        

    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Confirm Purchase</h2>
    <hr>
    <div class="bill-header">
    <span style="width:5%">SN</span>
    <span style="width:30%">Name</span>
    <span style="width:12%">Quantity</span>
    <span style="width:10%">Rate</span>
    <span style="width:8%">Net</span>


    </div>
    <ul class="product-list">
        <?php foreach ($products as $index => $product): ?>
            <li>
               <span style="width:5%"><?php echo ($index + 1) . ') '; ?></span><span style="text-align:left;width:30%;"><?php echo  $product['NAME']; ?></span><span style="text-align:left; width:10%;"><?php echo  $product['QUANTITY']; ?>
            </span><span style="text-align:left; width:10%;"><?php echo '$'.$product['PRICE']; ?></span>
                <span style="text-align:left; width:10%;"><?php echo '$' . number_format($product['PRICE']*$product['QUANTITY'], 2); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <hr>
    <div class="total">
        <p>Total: <?php echo '$' . number_format($total, 2); ?></p>
    </div>
    <hr>
    <form method="post" action="">
        <div class="selectors">
        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" required>
        </div>
        <div class="form-group">
            <label for="time">Time</label>
            <input type="time" id="time" name="time" required>
        </div>
        </div>
        <div class="btn-group">
            <button type="submit" class="confirm-btn">Confirm</button>
            <button type="button" class="back-btn" onclick="window.history.back()">Back</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
