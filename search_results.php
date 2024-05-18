<?php
session_start();

// Include the config.php file for database connection
include 'config.php';

$query = isset($_GET['query']) ? $_GET['query'] : '';
$products = [];

if ($query) {
    // Sanitize user input
    $query = htmlspecialchars($query);

    // Search query
    $sql = "SELECT * FROM Product WHERE LOWER(name) LIKE '%' || LOWER(:query) || '%'";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':query', $query);
    oci_execute($stmt);

    while ($product = oci_fetch_assoc($stmt)) {
        $products[] = $product;
    }

    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="search.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 100px auto;
            margin-top:25vh;

            padding: 20px;
        }
        .product-list {
            display: flex;
            list-style: none;
            padding: 0;
            flex-direction: column;
            align-items: flex-start;
        }
        .product-list li {
                display: flex;
                margin-bottom: 11px;
                flex-direction: row;
            }
        .product-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
            width:100% ;
            height:100% ;
        }
        .product-image {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .product-image img {
            max-width: 100%;
            height: 20vh;
            object-fit: contain;
        }
        
        .product-info {
            text-align: left;
            margin-left:2rem;
        }
        .product-info h2 {
            margin: 10px 0;
        }
        .product-info .price {
            font-size: 18px;
            color: #333;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .actions .cart-btn {
            background-color: #28a745;
            color: white;
        }
        .actions .wishlist-btn {
            background-color: #ffc107;
            color: black;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<section class="searchsection">
            <form action="search_results.php" method="get" class="search-form">
        <div class="search">
            <span class="search-icon material-symbols-outlined"> Search</span>
            <input class="search-input" type="text" name="query" id="search" placeholder="Search" onkeyup="liveSearch()">
        </div>
        <div id="search-results" class="search-results"></div>

        <div style="width:500%; position:relative;top:5vh;left:-150%">
            <hr>
        </div>
    </form>
    </section>

<div class="container">

    <h2>Possible search results</h2>
    <ul class="product-list">
        <?php foreach ($products as $product): ?>
            
            <li class="product-card">
                <div class="product-image">
                    <a href="product_detail.php?id=<?php echo $product['PRODUCTID']; ?>" ><img src="<?php echo $product['IMAGE']; ?>" alt="<?php echo $product['NAME']; ?>"></a>
                </div>
                <div class="product-info">
                <a style="text-decoration:none; color:black;" href="product_detail.php?id=<?php echo $product['PRODUCTID']; ?>" > <h2><?php echo $product['NAME']; ?></h2></a>
                    <div class="price">$<?php echo number_format($product['PRICE'], 2); ?></div>
                </div>
                <div class="actions">
                    <button class="cart-btn" onclick="addToCart(<?php echo $product['PRODUCTID']; ?>)">Add To Cart</button>
                    <button class="wishlist-btn" onclick="addToWishlist(<?php echo $product['PRODUCTID']; ?>)">Add To Wishlist</button>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php include 'footer.php'; ?>
<script src="search.js"></script>


</body>
</html>
