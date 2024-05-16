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
        <div class="line">
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
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $product['IMAGE']; ?>" class="product-thumb" alt="">
                        <button class="card-btn">add to cart</button>
                    </div>
                    <div class="product-info">
                        <h2 class="product-brand"><?php echo $product['NAME']; ?></h2>
                        <span class="price">$<?php echo number_format($product['PRICE'], 2); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>


    <section class="featured-categories-section">
        <hr>
        <h2 class="featured-categories-title">Featured Categories</h2>
        <hr>
        <button class="featured-pre-btn"><img src="images/arrow.png" alt=""></button>
        <button class="featured-nxt-btn"><img src="images/arrow.png" alt=""></button>
        <div class="featured-categories-container">
            <?php while ($category = oci_fetch_assoc($category_stmt)): ?>
                <div class="featured-category-card">
                    <div class="featured-category-image">
                        <img src="<?php echo $category['IMAGE']; ?>" class="featured-category-thumb" alt="">
                        <button class="featured-card-btn">explore</button>
                    </div>
                    <div class="featured-category-info">
                        <h2 class="featured-category-name"><?php echo $category['TITLE']; ?></h2>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <section id="product-quality">
        <div class="quality">
            <hr>
            <h1>Description Regarding Our Product Quality</h1>
            <p><br>At Collective Shop Sphere, we are committed to delivering excellence in every aspect of our service,
                particularly in the quality of products available on our platform.
                We carefully curate a selection of goods from trusted local vendors, ensuring that each product meets
                our high standards for quality and reliability.<br><br>
                We recognize the importance of offering products that not only meet but exceed customer expectations. To
                achieve this, we collaborate closely with our vendors to maintain strict quality control measures and
                ensure that all items are sourced responsibly and ethically.
                This commitment extends to providing a diverse range of products that uphold the heritage and
                craftsmanship unique to our community in Cleckhuddersfax.<br><br>
                Our platform facilitates an environment where quality assurance is paramount. Each vendor is equipped
                with the tools and insights needed to monitor their stock effectively, allowing for continuous
                improvement based on consumer feedback and sales analytics.
                By prioritizing quality at every level, Collective Shop Sphere ensures a superior shopping experience
                that supports both our customers' satisfaction and our vendors' success.
            </p>
        </div>
        <hr>
    </section>


    <section>
        <div class="container">
        <h2 style="text-align:center; padding:3vh;font-size: 2rem;">New Products</h2>

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
                        <a class="item">
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

    <h2 class="ourshops"> Our Shops</h2>

    
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
                                <a class="item">
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
    <?php
include 'footer.php';
?>

    <script src="script.js"></script>
</body>

</html>

<?php
oci_close($conn);
?>