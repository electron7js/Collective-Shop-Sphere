<?php
session_start();

// Include the config.php file for database connection
include 'config.php';
include 'functions.php';
// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
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

// Fetch products in the basket
$query = "SELECT p.productid, p.name, p.price, p.image, pb.quantity FROM Product p
          JOIN Product_Basket pb ON p.productid = pb.productid
          JOIN Basket b ON pb.basketid = b.basketid
          WHERE b.userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);

$products = [];
while ($product = oci_fetch_assoc($stmt)) {
    $products[] = $product;
}

$total = array_reduce($products, function ($sum, $product) {
    return $sum + ($product['PRICE'] * $product['QUANTITY']);
}, 0);

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Basket - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container{
            position: relative;
            max-width: 900px;
            margin-top:10vh;
    display: flex;
    justify-content: flex-start;
    flex-direction: column;
    flex-wrap: nowrap;
    align-content: flex-end;
    align-items: flex-start;
        }
        .basket-body {
            width: 95%;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .basket-items{
            min-height:60vh;

        }
        .product-card {
            width: 90%;
            height: 15vh;
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding:1rem;
        }
        .product-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            margin-left: 20px;
        }
        .product-info h2 {
            margin: 0;
        }
        .price, .quantity, .total {
            font-size: 18px;
            font-weight: bold;
        }
        .actions {
            display: flex;
            flex-direction: column;
        }
        .actions button {
            margin-bottom: 10px;
        }
        .bottom-buttons{
            display: flex;
    flex-direction: row;
    justify-content: space-between;
    width: 96%;
    padding: 1rem;
        }
        .total-price {
    display: flex;
    margin: 1rem;
    margin-left: 2rem;
    width: 88%;
    text-align: justify;
    font-size: 2rem;
    font-weight: 400;
    flex-direction: row;
    flex-wrap: wrap;
    align-content: center;
    align-items: center;
    justify-content: space-between;
}
        .checkout-btn, .cancel-btn {
            width: 150px;
            padding: 10px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .checkout-btn {
            background-color: #28a745;
            color: white;
        }
        .checkout-btn:hover {
            background-color: #218838;
        }
        .cancel-btn {
            background-color: #ccc;
            color: black;
        }
        .product-image{
            width: 100%;
            object-fit:contain;
        }
        .top-basket{
            width: 100%;
            align-self: start;
        }
        .container{

        }
        .remove-btn{
            position:relative;
            top:8vh;
            left:1rem;
        }

        .product-image {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.product-name{
    font-weight:500;
}
        .product-thumb{
    width: 100%;
    height: 100%;
    object-fit: contain;
        }
        .quantity{
            float:right;
            position: relative;
            top:-2rem;
            left: -2rem;
        }
        .quantity-selector{
            height:4rem;
            width:7rem;
            font-size:1.5rem;
            
        }

        .product-details{
            width: 150%;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="container">
    <div class="top-basket">
<h1 style="font-family:Segoe UI; font-weight:350;margin:1rem;font-size:2.5rem;">Shopping Basket</h1>
<hr>
</div>
<div class="basket-body">
    <div class="basket-items">
    <?php foreach ($products as $product): ?>
        <div class="product-card">
            <div class="product-image">
                <img src="<?php echo $product['IMAGE']; ?>" class="product-thumb" alt="">
            </div>
            <div class="product-info">
                <div class="product-details">
                <h2 class="product-name"><?php echo $product['NAME']; ?></h2>
                <span class="price">$<?php echo number_format($product['PRICE'], 2); ?></span>
                <div class="quantity">
                    <select class="quantity-selector" id="quantity-<?php echo $product['PRODUCTID']; ?>" onchange="updateQuantity(<?php echo $product['PRODUCTID']; ?>, this.value)">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $i == $product['QUANTITY'] ? 'selected' : ''; ?>><?php echo $i; ?> pcs</option>
                        <?php endfor; ?>
                    </select>
                </div>
                </div>
            </div>
            <div class="actions">
                <button class="remove-btn" style="background-color: #a77364; border: none; padding: 10px 20px; color: white; font-size: 16px; cursor: pointer; text-align: center; text-decoration: none; display: inline-block; border-radius: 0; width: 7rem;" onclick="removeFromBasket(<?php echo $product['PRODUCTID']; ?>) ">Remove from basket</button>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
    <hr>
    <div class="total-price">
        <div>Total:</div>
        <div>$<?php echo number_format($total, 2); ?></div>
         
    </div>
    <div class="actions bottom-buttons">
        <button 
        style="background-color: #a77364; border: none; padding: 16px 40px; color: white; font-size: 16px; cursor: pointer; text-align: center; text-decoration: none; display: inline-block; border-radius: 0; width: 11rem;"  
        class="checkout-btn" onclick="window.location.href = 'confirmation.php'">Checkout</button>
        <button style="background-color: #dddddd ; border: none; padding: 16px 40px; color: #a77364; font-size: 16px; cursor: pointer; text-align: center; text-decoration: none; display: inline-block; border-radius: 0; width: 11rem;" 
         class="cancel-btn"  onclick="window.history.back()" >Cancel</button>
    </div>
</div>
</div>

<?php include 'footer.php'; ?>

<script src="basket.js">
</script>

</body>
</html>
