<!DOCTYPE html>
<html lang="en">

<head>
    <title>Collective Shop Sphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">
    <style>
        html {
            width: 100%;
            height: 100%;
            margin: 0px;
            padding: 0px;
            overflow-x: hidden;
        }

        .support{
            position: relative;
            left:-20%;
            margin:4rem;
            font: 5em sans-serif;
        }
        .maincontainer{
    display: block;

    margin-top: 30vh;
}

    </style>
</head>

<body>

    <?php
    // Include the config.php file
    include 'config.php';

    // Fetch categories
    $category_query = "SELECT * FROM Category";
    $category_stmt = oci_parse($conn, $category_query);
    oci_execute($category_stmt);

    // Fetch shops
    $shop_query = "SELECT * FROM Shop";
    $shop_stmt = oci_parse($conn, $shop_query);
    oci_execute($shop_stmt);

    // Fetch products
    $product_query = "SELECT * FROM Product";
    $product_stmt = oci_parse($conn, $product_query);
    oci_execute($product_stmt);
    ?>
<?php
include 'header.php';
?>

<div class="maincontainer">

<div style="background: #EEE4E1; width:600%; height:45vh; position:absolute; top:-0vh"></div>

    <section class="searchsection" style="top:14vh;" >

        <h1 class="support">Support your local shops</h1>
        <hr style="width:90%; position:relative; left:-15%;top:-3vh;">
            <form class="search-form">
        <div class="search">
            <span class="search-icon material-symbols-outlined">Search</span>
            <input class="search-input" type="search" placeholder="Search">
        </div>
    </form>

    </section>



    <h2 class="ourshops" style="position:relative; top:5vh"> Our Shops</h2>

    
<?php while ($shop = oci_fetch_assoc($shop_stmt)): ?>
    <section>
<div class="sub-products">

    <div class="name-shop">
        <h2><?php echo $shop['NAME']; ?></h2>
    </div>
    <div class="sub-products">
        <div class="sub-list">

            <div class="row-product">
                <?php
                // Fetch products for the current shop
                $shopid = $shop['SHOPID'];
                $product_query = "SELECT * FROM Product WHERE SHOPID = :shopid";
                $product_stmt = oci_parse($conn, $product_query);
                oci_bind_by_name($product_stmt, ':shopid', $shopid);
                oci_execute($product_stmt);

                // Display products
                $count = 0;
                while ($product = oci_fetch_assoc($product_stmt)):
                    if ($count % 3 == 0 && $count != 0): ?>
                    </div>
                    <div class="row-product"> <!-- Close current row and start a new row after every 3 items -->
                    <?php endif; ?>
                    <a  href="product_detail.php?id=<?php echo $product['PRODUCTID']; ?>" style="text-decoration:none; color:black;" class="item">
                        <div class="inside">
                            <img src="<?php echo $product['IMAGE']; ?>" alt="">
                        </div>
                        <div class="side">
                            <h2><?php echo $product['NAME']; ?></h2>
                            <h2>
                                <div class="price">$<?php echo number_format($product['PRICE'], 2); ?></div>
                            </h2>
                            <div class="for-btn">
                                <div class="btn-shop1">
                                    <button class="submit">Add to Cart</button>
                                </div>
                                <div class="btn-shop1-right">
                                    <button class="submit">Add to Wishlist</button>
                                </div>
                            </div>
                        </div>
                    </a>
                    <?php
                    $count++;
                endwhile;
                ?>

            </div>
        </div>
    </div>
    </div>
<hr>
</section>

<?php endwhile; ?>
    </div>
    <?php
include 'footer.php';
?>

    <script src="script.js"></script>
</body>

</html>

<?php
oci_close($conn);
?>