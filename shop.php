<!DOCTYPE html>
<html lang="en">

<head>
    <title>Collective Shop Sphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="search.css">

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

.search-results {
    width: 50%;
}
    </style>
</head>

<body >

    <?php
    include 'config.php';

    $sortByPrice = isset($_POST['sortByPrice']) ? $_POST['sortByPrice'] : false;
    $sortByName = isset($_POST['sortByName']) ? $_POST['sortByName'] : false;

    // Fetch categories
    $category_query = "SELECT * FROM Category";
    $category_stmt = oci_parse($conn, $category_query);
    oci_execute($category_stmt);

    // Fetch shops
    $shop_query = "SELECT * FROM Shop";
    $shop_stmt = oci_parse($conn, $shop_query);
    oci_execute($shop_stmt);

    // Fetch products
    $product_query = "SELECT p.* FROM Product p JOIN Shop s ON p.shopid=s.shopid WHERE s.activestatus>0 ".($sortByPrice?"Order by p.price asc":'') .($sortByName?"Order by p.name asc":'') ;
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
            <input class="search-input" type="text" name="query" id="search" placeholder="Search" onkeyup="liveSearch()">
        </div>
        <div id="search-results" class="search-results"></div>

    </form>

    </section>


    <section class="featured-categories-section" style="position:relative; top:8vh;">
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
        <h2 style="text-align:center; padding:3vh;font-size: 2rem;">Our Products</h2>
        
        <div style="text-align:center; padding:1vh;">
            <label for="sort-products">Sort by:</label>
            <select id="sort-products" onchange="sortBy()">
             <option value="select">select</option>
                <option value="name">Name</option>
                <option value="price">Price</option>
            </select>
        </div>
        
        <div class="listProduct">
            <div class="row" id="product-list">
                <?php
                // Fetch and display products in a list
                oci_execute($product_stmt);
                $count = 0;
                while ($product = oci_fetch_assoc($product_stmt)):
                    if ($count % 3 == 0 && $count != 0): ?>
                    </div>
                    <div class="row"> <!-- Close current row and start a new row after every 3 items -->
                    <?php endif; ?>
                    <a  href="product_detail.php?id=<?php echo $product['PRODUCTID']; ?>" style="text-decoration:none; color:black;" class="item" data-name="<?php echo $product['NAME']; ?>" data-price="<?php echo $product['PRICE']; ?>">
                        <img src="<?php echo $product['IMAGE']; ?>" alt="">
                        <h2><?php echo $product['NAME']; ?></h2>
                        <div class="price">$<?php echo number_format($product['PRICE'], 2); ?></div>
                    </a>
                    <?php
                    $count++;
                endwhile;
                ?>
                
                <a href="allshops.php" style="position:relative; top:7vh; background-color: #a77364; border: none; padding: 10px 20px; color: white; font-size: 16px; cursor: pointer; text-align: center; text-decoration: none; display: inline-block; border-radius: 0; width: 150px;">All Shops</a>
            </div>
        </div>
        <hr>
    </div>
</section>

  
      
    </div>
    <?php
include 'footer.php';
?>

    <script src="script.js"></script>
    <script src="search.js"></script>


<script>
        function saveScrollPosition() {
                    localStorage.setItem('scrollPosition', window.scrollY);
                }
        function sortBy() {
            var form = document.createElement("form");
            form.method = "POST";
            form.action = "";

            var selector = document.getElementById("sort-products");
            
            if(selector.value=="name"){

            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "sortByName";
            input.value = "true";
            }

            if(selector.value=="price"){
            var input = document.createElement("input");
            input.name = "sortByPrice";
            input.value = "true";
            
            }
            saveScrollPosition();
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    
        function restoreScrollPosition() {
            const scrollPosition = localStorage.getItem('scrollPosition');
            if (scrollPosition) {
                window.scrollTo(0, parseInt(scrollPosition, 10));
                localStorage.removeItem('scrollPosition');
            }
        }

        window.onload = function() {
            restoreScrollPosition();
        };

    </script>

</body>

</html>

<?php
oci_close($conn);
?>