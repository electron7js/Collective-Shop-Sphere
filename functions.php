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

function getBasketItems($userid) {
    global $conn;
    $query = "SELECT pb.productid, pb.quantity, p.price
              FROM Product_Basket pb
              JOIN Basket b ON pb.basketid = b.basketid
              JOIN Product p ON pb.productid = p.productid
              WHERE b.userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);

    $items = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $items[] = $row;
    }
    return $items;
}

function clearBasket($userid) {
    global $conn;
    $query = "DELETE FROM Product_Basket WHERE basketid = (SELECT basketid FROM Basket WHERE userid = :userid)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);
}

function createPurchase($userid, $basketItems,$collection_slot_id) {
    include 'config.php';

    // Insert a new purchase record
    $query = "INSERT INTO Purchase (purchaseid, purchase_date, confirmed, userid) VALUES (seq_purchaseid.NEXTVAL, SYSDATE, 0, :userid) RETURNING purchaseid INTO :purchaseid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
    oci_execute($stmt);

    $query = "INSERT INTO Purchase_collection_slot (pcsid, purchaseid, collection_slot_id) VALUES (seq_pcsid.NEXTVAL, :purchaseid, :collection_slot_id) RETURNING pcsid INTO :pcsid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':pcsid', $pcsid);
    oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
    oci_bind_by_name($stmt, ':collection_slot_id', $collection_slot_id);
    oci_execute($stmt);

    // Move items from basket to purchase_detail
    foreach ($basketItems as $item) {
        $query = "INSERT INTO Purchase_detail (purchasedetailid, productid, purchaseid, price, quantity) VALUES (seq_purchasedetailid.NEXTVAL, :productid, :purchaseid, :price, :quantity)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':productid', $item['PRODUCTID']);
        oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
        oci_bind_by_name($stmt, ':price', $item['PRICE']);
        oci_bind_by_name($stmt, ':quantity', $item['QUANTITY']);
        oci_execute($stmt);
    }

    // Clear the basket
    clearBasket($userid);

    // Close the database connection
    oci_close($conn);

    return $purchaseid;
}


function confirmPurchase($purchaseid) {
    include 'config.php';

    // Update the purchase confirmation status
    $query = "UPDATE Purchase SET confirmed = 1 WHERE purchaseid = :purchaseid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
    $result = oci_execute($stmt);

    // Close the database connection
    oci_close($conn);

    return $result;
}

function getCollectionSlots($conn) {
    $query_slots = "SELECT collection_slot_id, TO_CHAR(collection_date, 'YYYY-MM-DD') AS collection_date, TO_CHAR(collection_start, 'HH24:MI') AS collection_start, TO_CHAR(collection_end, 'HH24:MI') AS collection_end
                    FROM Collection_Slot
                    WHERE collection_date >= SYSDATE
                    ORDER BY collection_date, collection_start";
    $stmt_slots = oci_parse($conn, $query_slots);
    oci_execute($stmt_slots);

    $slots = [];
    while ($row_slot = oci_fetch_assoc($stmt_slots)) {
        $slots[] = $row_slot;
    }

    return $slots;
}

function getPickupDetails($purchaseid) {
    include 'config.php';

    // Query to find the pickup details
    $query = "SELECT cs.collection_date, cs.collection_start, cs.collection_end
              FROM purchase_collection_slot pcs
              JOIN Collection_Slot cs ON pcs.collection_slot_id = cs.collection_slot_id
              WHERE pcs.purchaseid = :purchaseid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':purchaseid', $purchaseid);
    oci_execute($stmt);

    $pickupDetails = oci_fetch_assoc($stmt);

    // Close the database connection
    oci_close($conn);

    return $pickupDetails;
}

function checkUserRole($username, $conn) {
    include 'config.php';

    $userRole = '';

    // Get the user ID
    $query = "SELECT userid FROM Users WHERE username = :username";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':username', $username);
    oci_execute($stmt);
    $user = oci_fetch_assoc($stmt);
    $userid = $user['USERID'];

    if ($userid) {
        // Check if the user is a customer
        $query = "SELECT COUNT(*) AS count FROM Customer WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);
        if ($row['COUNT'] > 0) {
            $userRole = 'Customer';
        }

        // Check if the user is a trader
        $query = "SELECT COUNT(*) AS count FROM Trader WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);
        if ($row['COUNT'] > 0) {
            $userRole = 'Trader';
        }

        // Check if the user is an admin
        $query = "SELECT COUNT(*) AS count FROM Admin WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_execute($stmt);
        $row = oci_fetch_assoc($stmt);
        if ($row['COUNT'] > 0) {
            $userRole = 'Admin';
        }
    }

    // Store the user role in the session
    $_SESSION['user_role'] = $userRole;

    return $userRole;
}


function saveImage($imageInput, $targetDir) {
    $imageFileType = strtolower(pathinfo($imageInput['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '.' . $imageFileType;
    $targetFilePath = $targetDir . $fileName;
    $check = getimagesize($imageInput['tmp_name']);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }
    if ($imageInput['size'] > 5000000) {
        throw new Exception("Sorry, your file is too large.");
    }
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedFormats)) {
        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Check if the directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($imageInput['tmp_name'], $targetFilePath)) {
        throw new Exception("Sorry, there was an error uploading your file.");
    }

    // Return the file name
    return $fileName;
}
function saveUserProfileImage($imageInput) {
    $targetDir = "images/userimages/";
    $imageFileType = strtolower(pathinfo($imageInput['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '.' . $imageFileType;
    $targetFilePath = $targetDir . $fileName;
    $check = getimagesize($imageInput['tmp_name']);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }
    if ($imageInput['size'] > 5000000) {
        throw new Exception("Sorry, your file is too large.");
    }
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedFormats)) {
        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Check if the directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($imageInput['tmp_name'], $targetFilePath)) {
        throw new Exception("Sorry, there was an error uploading your file.");
    }

    // Return the file name
    return 'images/userimages/'.$fileName;
}


function saveTraderImage($imageInput) {
    $targetDir = "images/traderimages/";
    $imageFileType = strtolower(pathinfo($imageInput['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '.' . $imageFileType;
    $targetFilePath = $targetDir . $fileName;
    $check = getimagesize($imageInput['tmp_name']);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }
    if ($imageInput['size'] > 5000000) {
        throw new Exception("Sorry, your file is too large.");
    }
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedFormats)) {
        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Check if the directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($imageInput['tmp_name'], $targetFilePath)) {
        throw new Exception("Sorry, there was an error uploading your file.");
    }

    // Return the file name
    return 'images/traderimages/'.$fileName;
}

function saveProductImage($imageInput) {
    $targetDir = "images/productimages/";
    $imageFileType = strtolower(pathinfo($imageInput['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '.' . $imageFileType;
    $targetFilePath = $targetDir . $fileName;
    $check = getimagesize($imageInput['tmp_name']);
    if ($check === false) {
        throw new Exception("File is not an image.");
    }
    if ($imageInput['size'] > 5000000) {
        throw new Exception("Sorry, your file is too large.");
    }
    $allowedFormats = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($imageFileType, $allowedFormats)) {
        throw new Exception("Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
    }

    // Check if the directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Move the uploaded file to the target directory
    if (!move_uploaded_file($imageInput['tmp_name'], $targetFilePath)) {
        throw new Exception("Sorry, there was an error uploading your file.");
    }

    // Return the file name
    return $targetFilePath;
}

function hasPurchasedItem($userid, $productid) {
    include 'config.php'; // Include your database connection

    // Query to check if the user has bought the specific product and the purchase is confirmed
    $query = "
        SELECT COUNT(*) AS purchase_count
        FROM Purchase p
        JOIN Purchase_detail pd ON p.purchaseid = pd.purchaseid
        WHERE p.userid = :userid
          AND pd.productid = :productid
          AND p.confirmed = 1
    ";

    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':productid', $productid);
    oci_execute($stmt);

    $result = oci_fetch_assoc($stmt);
    oci_close($conn);

    // Check if there are any purchases
    return $result['PURCHASE_COUNT'] > 0;
}

function getUserid($username){
    include 'config.php';

    $query = "
    SELECT userid 
    FROM users
    WHERE username = :username";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);

$result = oci_fetch_assoc($stmt);
oci_close($conn);

return $result['USERID'];

}

function checktVerifiedStatus($username){
    include 'config.php'; 

    $query = "
    SELECT Verified 
    FROM users u Join Customer c on u.userid=c.userid
    WHERE username = :username";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);

$result = oci_fetch_assoc($stmt);
oci_close($conn);

return $result['VERIFIED']>0;

}

function checkDiscount($product_id) {
    include 'config.php'; // Include the database connection

    // Prepare the query to fetch discount details for the given product ID
    $query = "
        SELECT d.discountid, d.discountpercent, d.startdate, d.enddate
        FROM Discount d
        JOIN Product_Discount pd ON d.discountid = pd.discountid
        WHERE pd.productid = :productid
          AND d.startdate <= SYSDATE
          AND d.enddate >= SYSDATE";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':productid', $product_id);
    oci_execute($stmt);

    // Fetch the discount details
    $discount = oci_fetch_assoc($stmt);

    oci_close($conn); // Close the database connection

    // Check if a discount is active
    if ($discount) {
        return $discount; // Return the discount details if active
    } else {
        return false; // Return false if no active discount
    }
}
