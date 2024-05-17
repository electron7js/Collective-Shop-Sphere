<?php
session_start();

// Include the config.php file for database connection
include 'config.php';

// Check if the user is logged in
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

// Check if the wishlist exists for the user
$query = "SELECT wishlistid FROM Wishlist WHERE userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$wishlist = oci_fetch_assoc($stmt);

if (!$wishlist) {
    // Create a new wishlist for the user
    $query = "INSERT INTO Wishlist (wishlistid, userid) VALUES (seq_wishlistid.NEXTVAL, :userid) RETURNING wishlistid INTO :wishlistid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':wishlistid', $wishlistid);
    oci_execute($stmt);
} else {
    $wishlistid = $wishlist['WISHLISTID'];
}

// Fetch wishlist items for the logged-in user
$query = "SELECT Product.productid, Product.name, Product.price, Product.image 
          FROM Wishlist_Product 
          JOIN Product ON Wishlist_Product.productid = Product.productid 
          WHERE Wishlist_Product.wishlistid = :wishlistid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':wishlistid', $wishlistid);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;

        }
        .wishlist-container {
            max-width: 800px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .wishlist-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .wishlist-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .wishlist-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .wishlist-item h2 {
            margin: 0;
            font-size: 1.2rem;
        }
        .wishlist-item .price {
            font-size: 1rem;
            color: #333;
        }
        .wishlist-item .actions {
            display: flex;
            flex-direction: column;
        }
        .wishlist-item .actions button {
            margin: 5px 0;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
        }
        .wishlist-item .actions button.remove {
            background-color: #dc3545;
        }
        .wishlist-item .actions button:hover {
            opacity: 0.9;
        }
        .main-container{
            min-height:60vh;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="main-container">
<div class="wishlist-container">
    <h1 class="wishlist-title">Your Wishlist</h1>
    <?php while ($product = oci_fetch_assoc($stmt)): ?>
        <div class="wishlist-item">
            <img src="<?php echo $product['IMAGE']; ?>" alt="<?php echo $product['NAME']; ?>">
            <div>
                <h2><?php echo $product['NAME']; ?></h2>
                <div class="price">$<?php echo number_format($product['PRICE'], 2); ?></div>
            </div>
            <div class="actions">
                <button onclick="addToCart(<?php echo $product['PRODUCTID']; ?>)">Add To Cart</button>
                <button class="remove" onclick="removeFromWishlist(<?php echo $product['PRODUCTID']; ?>)">Remove from Wishlist</button>
            </div>
        </div>
    <?php endwhile; ?>
</div>
</div>
<?php include 'footer.php'; ?>

<script src="wishlist.js"></script>

<script>
function addToCart(productId) {
    // AJAX request to add the product to the cart
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              alert('Product added to cart successfully.');
          } else {
              alert('Failed to add product to cart.');
          }
      });
}
</script>
</body>
</html>
