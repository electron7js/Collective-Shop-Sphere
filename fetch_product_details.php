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

oci_close($conn);
?>

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
                        <form method="post" action="">
                            <input type="hidden" name="productid" value="<?= $product_id ?>">
                            <button type="submit" name="verify" class="basket-btn" style="position:relative; left:-1rem;">Verify Product</button>
                            <button type="submit" name="reject" class="basket-btn" style="position:relative; left:1rem;">Reject Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
