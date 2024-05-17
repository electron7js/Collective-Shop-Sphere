<?php 
function addToWishlist($userid, $productid) {
    include 'config.php';

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

    // Insert the product into the Wishlist_Product table
    $query = "INSERT INTO Wishlist_Product (wpid, productid, wishlistid) VALUES (seq_wpid.NEXTVAL, :productid, :wishlistid)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':productid', $productid);
    oci_bind_by_name($stmt, ':wishlistid', $wishlistid);
    $result = oci_execute($stmt);

    // Close the database connection
    oci_close($conn);

    return $result;
}

function removeFromWishlist($userid, $productid) {
    include 'config.php';

    $query = "SELECT wishlistid FROM Wishlist WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);
    $wishlist = oci_fetch_assoc($stmt);

    if ($wishlist) {
        $wishlistid = $wishlist['WISHLISTID'];

        // Delete the product from the Wishlist_Product table
        $query = "DELETE FROM Wishlist_Product WHERE productid = :productid AND wishlistid = :wishlistid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':productid', $productid);
        oci_bind_by_name($stmt, ':wishlistid', $wishlistid);
        $result = oci_execute($stmt);

        // Close the database connection
        oci_close($conn);

        return $result;
    }

    // Close the database connection
    oci_close($conn);
    return false;
}

function removeFromBasket($userid, $productid) {
    include 'config.php';

    // Ensure the database connection is established

    // Get the basket ID for the user
    $query = "SELECT basketid FROM Basket WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);
    $basket = oci_fetch_assoc($stmt);

    if ($basket) {
        $basketid = $basket['BASKETID'];

        // Delete the product from the Product_Basket table
        $query = "DELETE FROM Product_Basket WHERE productid = :productid AND basketid = :basketid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':productid', $productid);
        oci_bind_by_name($stmt, ':basketid', $basketid);
        $result = oci_execute($stmt);

        // Close the database connection
        oci_close($conn);

        return $result ? true : false;
    }

    // Close the database connection
    oci_close($conn);
    return false;
}


function addToBasket($userid, $productid) {
    include 'config.php';

    // Check if the user already has a basket
    $query = "SELECT basketid FROM Basket WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);
    $basket = oci_fetch_assoc($stmt);

    if (!$basket) {
        // If the basket doesn't exist, create a new one
        $query = "INSERT INTO Basket (basketid, userid) VALUES (seq_basketid.NEXTVAL, :userid) RETURNING basketid INTO :basketid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_bind_by_name($stmt, ':basketid', $basketid);
        oci_execute($stmt);
    } else {
        $basketid = $basket['BASKETID'];
    }

    // Check if the product is already in the basket
    $query = "SELECT * FROM Product_Basket WHERE productid = :productid AND basketid = :basketid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':productid', $productid);
    oci_bind_by_name($stmt, ':basketid', $basketid);
    oci_execute($stmt);
    $product_in_basket = oci_fetch_assoc($stmt);

    if ($product_in_basket) {
        // If the product is already in the basket, update the quantity
        $query = "UPDATE Product_Basket SET quantity = quantity + 1 WHERE productid = :productid AND basketid = :basketid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':productid', $productid);
        oci_bind_by_name($stmt, ':basketid', $basketid);
        $result = oci_execute($stmt);
    } else {
        // If the product is not in the basket, insert it
        $query = "INSERT INTO Product_Basket (pbid, productid, basketid, quantity) VALUES (seq_pbid.NEXTVAL, :productid, :basketid, 1)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':productid', $productid);
        oci_bind_by_name($stmt, ':basketid', $basketid);
        $result = oci_execute($stmt);
    }

    // Close the database connection
    oci_close($conn);

    return $result ? true : false;
}


function updateBasketQuantity($userid, $productid, $quantity) {
    include 'config.php';
    $query = "UPDATE Product_Basket pb
              SET pb.quantity = :quantity
              WHERE pb.productid = :productid AND pb.basketid = (
                  SELECT b.basketid FROM Basket b WHERE b.userid = :userid
              )";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':quantity', $quantity);
    oci_bind_by_name($stmt, ':productid', $productid);
    oci_bind_by_name($stmt, ':userid', $userid);
    $result = oci_execute($stmt);

    oci_close($conn);

    return $result ? true : false;
}