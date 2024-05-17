<?php
// Include the config.php file for database connection
include 'config.php';

// Fetch product ID from the URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if the product ID is valid
if ($product_id <= 0) {
    echo "Invalid product ID.";
    exit;
}


// Prepare the query to fetch product details
$query = "SELECT * FROM Product WHERE productid = :productid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':productid', $product_id);
oci_execute($stmt);

// Fetch the product details
$product = oci_fetch_assoc($stmt);

// Check if the product exists
if (!$product) {
    echo "Product not found.";
    exit;
}

// Close the database connection
oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['NAME']); ?> - Product Details</title>
    <link rel="stylesheet" href="product_details_css.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        .main-container{
            position: relative;
            top:-20vh;
        }
        .product-details{
           position: relative;
           top:-2vh;
           left: 10rem;
        }
        .product-name{
            font-size:4rem !important;
            position: relative;
            top:-4rem;
        }
        .sub-btn{
            position: relative;
            left:-5rem;
        }
        .sub-btn button{
            margin:1px;
        }
        img{
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            top:-5rem;
        }
        .basket-btn{
            width: 100%;
    padding: 10px 20px;
    border: 0;
    outline: 0;
    background: #B2967D;
    color: #fff;
    font-size: 15px;
    font-weight: 500;
    border-radius: 30px;
    cursor: pointer;
    transition: .4s linear;
        }
    </style>
</head>
<body>
<script src="wishlist.js"></script>
<script src="basket.js"></script>

<?php include 'header.php'; ?>

<form class="search-form">
    <div class="search"> 
        <span class="search-icon material-symbols-outlined">search</span>
        <input class="search-input" type="search" placeholder="Search">
    </div>
</form>

<div class="main-container">
<section class="main-wrap">
    <div class="product">
        <div class="image-gallery">     
            <img src="<?php echo htmlspecialchars($product['IMAGE']); ?>">
        </div>

        <div class="product-details">
            <div class="details">
                <h2 class="product-name"><?php echo htmlspecialchars($product['NAME']); ?></h2>
                <div class="price-container">
                    <h3>$<?php echo number_format($product['PRICE'], 2); ?></h3>
                    <br>
                    <div class="another-box">
                    </div>
                </div>
                <p><b>Product information</b><br>
                <?php echo nl2br(htmlspecialchars($product['DESCRIPTION'])); ?>
                <br><br><b>Ingredient</b><br>
                <!-- Example ingredient -->
                Sugar, Flour, Eggs
                <br><b>Allergy Information</b><br>
                <!-- Example allergy information -->
                Contains nuts and dairy
                </p>
            </div>

            <div class="quantity">
                <div class="sub-btn" >
                    <button class="basket-btn" onclick="addToBasket(<?php echo $product['PRODUCTID']; ?>)"  style="position:relative; left:-1rem;">Add to Basket</button>
                    <button class="wishlist" onclick="addToWishlist(<?php echo $product['PRODUCTID']; ?>)" style="position:relative; left:1rem;">Add to Wishlist</button>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="customer-reviews">
    <div class="left">
        <h3>Reviews</h3>
        <div class="star-ratings">
            <div class="star-row">
                <span class="stars">★★★★★</span>
                <span class="count">(5)</span>
            </div>
            <div class="star-row">
                <span class="stars">★★★★</span>
                <span class="count">(4)</span>
            </div>
            <div class="star-row">
                <span class="stars">★★★★★</span>
                <span class="count">(5)</span>
            </div>
            <div class="star-row">
                <span class="stars">★★★</span>
                <span class="count">(3)</span>
            </div>
        </div>
    </div>
</section>
</div>

<?php include 'footer.php'; ?>


</body>
</html>
