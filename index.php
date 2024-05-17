<!DOCTYPE html>
<html lang="en">

<head>
    <title>Collective Shop Sphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        html {
            width: 100%;
            height: 100%;
            margin: 0px;
            padding: 0px;
            overflow-x: hidden;
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
    <section class="searchsection">
            <form class="search-form">
        <div class="search">
            <span class="search-icon material-symbols-outlined"> search</span>
            <input class="search-input" type="search" placeholder="Search">
        </div>
        <div style="width:500%; position:relative;top:5vh;left:-150%">
            <hr>
        </div>
    </form>
    </section>

    <section class="product">
        <h2 class="product-category">Best Products</h2>
        <button class="pre-btn"><img src="images/arrow.png" alt=""></button>
        <button class="nxt-btn"><img src="images/arrow.png" alt=""></button>
        <div class="product-container">
            <?php while ($product = oci_fetch_assoc($product_stmt)): ?>
                <a class="product-card" href="product_detail.php?id=<?php echo $product['PRODUCTID']; ?>" style="text-decoration:none; color:black;" class="item">

                    <div class="product-image">
                        <img src="<?php echo $product['IMAGE']; ?>" class="product-thumb" alt="">
                        <button class="card-btn">add to cart</button>
                    </div>
                    <div class="product-info" >
                        <h2 class="product-brand"><?php echo $product['NAME']; ?></h2>
                        <span class="price">$<?php echo number_format($product['PRICE'], 2); ?></span>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
    </section>


    <section>
        <hr>
        <div class="container">
        <h2 style="text-align:center; padding:3vh;font-size: 2rem;">New Products</h2>
        <hr style="width:300%; position:relative; left:-100%">

            <div class="listProduct">
                <div class="row">
                    <?php
                    // Fetch and display products in a list
                    oci_execute($product_stmt);
                    $count = 0;
                    while ($product = oci_fetch_assoc($product_stmt)):
                        if ($count % 3 == 0 && $count != 0): ?>
                        </div>
                        <div class="row"> <!-- Close current row and start a new row after every 3 items -->
                        <?php endif; ?>
                        <a href="product_detail.php?id=<?php echo $product['PRODUCTID']; ?>" style="text-decoration:none" class="item">
                            <img src="<?php echo $product['IMAGE']; ?>" alt="">
                            <h2><?php echo $product['NAME']; ?></h2>
                            <div class="price">$<?php echo number_format($product['PRICE'], 2); ?></div>
                        </a>
                        <?php
                        $count++;
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
        <hr>
    </section>
            
    <section id="about">
        <div class="about-1">
            <h1>ABOUT US</h1>
            <p><br>At Collective Shop Sphere, we are committed to enhancing the vitality and competitiveness of small
                independent businesses in Cleckhuddersfax through innovative digital solutions.
                Our initiative centers around an e-commerce platform designed to empower local vendors by extending
                their market reach without the need to increase their physical shop hours.
                This strategic approach not only supports our community's unique business ecosystem but also upholds the
                work-life balance of vendors.<br><br>
                Our platform offers a user-friendly interface where consumers can explore and purchase products from
                multiple local shops and pick them up at a convenient time.
                By integrating features like order history viewing and secure payment options such as PayPal, we ensure
                a seamless and secure shopping experience.<br><br>
                Our mission is to help local businesses thrive in the face of competition from national chains by
                providing them with advanced analytics to improve stock management and sales strategies.
                The Collective Shop Sphere is not just a marketplace but a community-focused solution designed to foster
                growth and sustainability for small traders. Through our website, we bring convenience to consumers and
                support to traders, crafting a stronger, more connected Cleckhuddersfax.
            </p>
        </div>
    </section>
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