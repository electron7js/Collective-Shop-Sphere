<?php
// Include the config.php file for database connection
include 'config.php';

// Fetch shop ID from the URL
$shop_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if the shop ID is valid
if ($shop_id <= 0) {
    echo "Invalid shop ID.";
    exit;
}

// Prepare the query to fetch shop details
$query = "SELECT * FROM Shop WHERE shopid = :shopid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':shopid', $shop_id);
oci_execute($stmt);

// Fetch the shop details
$shop = oci_fetch_assoc($stmt);

// Check if the shop exists
if (!$shop) {
    echo "Shop not found.";
    exit;
}

// Display shop details
?>

<div class="main-container">
    <section class="main-wrap">
        <div class="shop">
            <div class="image-gallery">
                <img src="<?php echo htmlspecialchars($shop['LOGO']); ?>">
            </div>

            <div class="shop-details">
                <div class="details">
                    <h2 class="shop-name"><?php echo htmlspecialchars($shop['NAME']); ?></h2>
                    <div class="price-container">
                        <h3>Description</h3>
                        <br>
                        <div class="another-box">
                        </div>
                    </div>
                    <p>
                        <?php echo nl2br(htmlspecialchars($shop['DESCRIPTION'])); ?>
                        <br><br><b>Location</b><br>
                        <?php echo htmlspecialchars($shop['LOCATION']); ?>
                    </p>
                </div>

                <div class="quantity">
                    <div class="sub-btn">
                        <form method="post" action="">
                            <input type="hidden" name="shopid" value="<?php echo $shop_id; ?>">
                            <button type="submit" name="verify" class="verify-btn">Verify Shop</button>
                            <button type="submit" name="reject" class="reject-btn">Reject Shop</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Close the database connection
oci_close($conn);
?>
