<?php
// Include the config.php file for database connection
include 'config.php';

// Fetch shops with activestatus = 0 or NULL
$query = "SELECT shopid, name FROM Shop WHERE activestatus = 0 OR activestatus IS NULL";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$shops = [];
while ($shop = oci_fetch_assoc($stmt)) {
    $shops[] = $shop;
}

// Fetch shop ID from the URL or form submission
$shop_id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['shopid']) ? (int)$_POST['shopid'] : 0);

// Initialize shop as null
$shop = null;

if ($shop_id > 0) {
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
}

// Verify or reject shop if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify'])) {
        $status = 1;
    } elseif (isset($_POST['reject'])) {
        $status = -1;
    }

    // Update the shop's activestatus
    $updateQuery = "UPDATE Shop SET activestatus = :status WHERE shopid = :shopid";
    $updateStmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($updateStmt, ':status', $status);
    oci_bind_by_name($updateStmt, ':shopid', $shop_id);
    if (oci_execute($updateStmt)) {
        echo "Shop status updated successfully.";
        header("Location: admin_verify_shops.php"); // Redirect to avoid form resubmission
        exit();
    } else {
        echo "Error updating shop status.";
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Shops</title>
    <link rel="stylesheet" href="product_details_css.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        .main-container {
            position: relative;
            top: -20vh;
        }
        .shop-details {
            position: relative;
            top: -2vh;
            left: 10rem;
        }
        .shop-name {
            font-size: 4rem !important;
            position: relative;
            top: -4rem;
        }
        .sub-btn {
            position: relative;
            left: -5rem;
        }
        .sub-btn button {
            margin: 1px;
        }
        img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            top: -5rem;
        }
        .verify-btn, .reject-btn {
            width: 100%;
            padding: 10px 20px;
            border: 0;
            outline: 0;
            color: #fff;
            font-size: 15px;
            font-weight: 500;
            border-radius: 30px;
            cursor: pointer;
            transition: .4s linear;
        }
        .verify-btn {
            background: #28a745;
        }
        .reject-btn {
            background: #dc3545;
        }
        .shop-selector {
            max-width: 800px;
            margin: 50px auto;
            margin-top: 6rem;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            position: relative;
            top: -70vh;
            z-index: 999;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .btn-group {
            display: flex;
            justify-content: space-between;
        }
        header {
            min-height: 80vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
    <script>
        function fetchShopDetails(shopId) {
            if (!shopId) {
                document.getElementById('shop-details').innerHTML = '';
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_shop_details.php?id=' + shopId, true);
            xhr.onload = function () {
                if (this.status === 200) {
                    document.getElementById('shop-details').innerHTML = this.responseText;
                } else {
                    document.getElementById('shop-details').innerHTML = 'Error loading shop details.';
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="shop-selector">
    <h2>Verify Shops</h2>
    <div class="form-group">
        <label for="shopid">Select Shop to Verify</label>
        <select name="shopid" id="shopid" onchange="fetchShopDetails(this.value)" required>
            <option value="">Select a shop</option>
            <?php foreach ($shops as $shopItem): ?>
                <option value="<?= $shopItem['SHOPID'] ?>"><?= htmlspecialchars($shopItem['NAME']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div id="shop-details"></div>

<?php include 'footer.php'; ?>

</body>
</html>
