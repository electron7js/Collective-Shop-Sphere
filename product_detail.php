<?php
session_start();

// Include the config.php file for database connection and functions
include 'config.php';
include 'functions.php';

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

// Fetch reviews for the product
$query = "SELECT r.*, u.username FROM Review r JOIN Users u ON r.userid = u.userid WHERE r.productid = :productid ORDER BY r.reviewdate DESC";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':productid', $product_id);
oci_execute($stmt);

$reviews = [];
while ($review = oci_fetch_assoc($stmt)) {
    $reviews[] = $review;
}

// Check if the user has bought the product
$canSubmitReview = false;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_id = getUserid($username);
    $canSubmitReview = hasPurchasedItem($user_id, $product_id)>0;
}



// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $canSubmitReview) {
    $rating = $_POST['rating'];
    $body = $_POST['body'];
    $review_date = date('Y-m-d');

    $query = "INSERT INTO Review (reviewid, reviewdate, rating, body, productid, userid) VALUES (seq_reviewid.NEXTVAL, TO_DATE(:reviewdate, 'YYYY-MM-DD'), :rating, :body, :productid, :userid)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':reviewdate', $review_date);
    oci_bind_by_name($stmt, ':rating', $rating);
    oci_bind_by_name($stmt, ':body', $body);
    oci_bind_by_name($stmt, ':productid', $product_id);
    oci_bind_by_name($stmt, ':userid', $user_id);

    if (oci_execute($stmt)) {
        // Reload the page to display the new review
        header("Location: product_details.php?id=" . $product_id);
        exit();
    } else {
        echo "Error: Could not submit review.";
    }
}

oci_close($conn);
?>
<script>
    console.log("<?php echo $canSubmitReview ?>");
</script>

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
        .review-form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .review-form label {
            display: block;
            margin-bottom: 5px;
        }
        .review-form input,
        .review-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .review-form button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #28a745;
            color: white;
        }
        .reviews {
            margin-top: 20px;
            
        }
        .reviews h3 {
            margin-bottom: 10px;
        }
        .review {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .review .rating {
            color: #ffcc00;
        }

        .submit-review{
            margin:5rem;
        }
      .others-reviews{
        display:flex;
      }
      .others-reviews .review{
        margin:3rem;
      }
      .main-wrap {
    height: 50vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: white;
    margin-top: -45vh;
}

header{

    min-height: 20vh !important;
    margin-bottom: 80vh;

}
.image-gallery{
    height:40vh;
}
.image-gallery img{
    object-fit:contain;
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
                <br><br><b>Remaining stock</b><br>
                <?php echo nl2br(htmlspecialchars($product['REMAININGSTOCK'])); ?>
                <br><b>Allergy Information</b><br>
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
    <div class="others-reviews">

        <h3>Reviews</h3>
        <div class="reviews">
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <div class="rating"><?= str_repeat('★', (int)$review['RATING']) . str_repeat('☆', 5 - (int)$review['RATING']) ?></div>
                    <div class="review-body"><?= nl2br(htmlspecialchars($review['BODY'])) ?></div>
                    <div class="review-date"><?= date('F j, Y', strtotime($review['REVIEWDATE'])) ?> by <?= htmlspecialchars($review['USERNAME']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
</div>
        <div class="submit-review">
        <?php if ($canSubmitReview): ?>
            <h3>You've bought this product, Submit Your Review</h3>

            <div class="review-form">
                <form method="post" action="">
                    <label for="rating">Rating (1-5):</label>
                    <input type="number" id="rating" name="rating" min="1" max="5" required>
                    
                    <label for="body">Review:</label>
                    <textarea id="body" name="body" rows="4" required></textarea>
                    
                    <button type="submit">Submit Review</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    </div>
</section>
</div>


<?php include 'footer.php'; ?>

</body>
</html>
