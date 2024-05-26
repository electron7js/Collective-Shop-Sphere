<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';
include 'functions.php';

// Fetch all shops
$query = "SELECT s.shopid, s.name, u.username 
          FROM Shop s 
          JOIN Users u ON s.userid = u.userid";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$shops = [];
while ($shop = oci_fetch_assoc($stmt)) {
    $shops[] = $shop;
}

// Fetch shop details if a shop is selected
$selectedShop = null;
if (isset($_GET['shopid'])) {
    $shopid = $_GET['shopid'];
    $query = "SELECT s.*, u.username 
              FROM Shop s 
              JOIN Users u ON s.userid = u.userid 
              WHERE s.shopid = :shopid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':shopid', $shopid);
    oci_execute($stmt);
    $selectedShop = oci_fetch_assoc($stmt);
}

// Update shop details if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['shopid'])) {
    $shopid = $_POST['shopid'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $activestatus=$_POST['activestatus'];
    // Handle logo upload
    $logo = $selectedShop['LOGO'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        try {
            $logo = saveTraderImage($_FILES['logo']);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    }

    // Update Shop table
    $query = "UPDATE Shop SET name = :name, description = :description, location = :location, logo = :logo, activestatus=:activestatus WHERE shopid = :shopid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':description', $description);
    oci_bind_by_name($stmt, ':location', $location);
    oci_bind_by_name($stmt, ':logo', $logo);
    oci_bind_by_name($stmt, ':shopid', $shopid);
    oci_bind_by_name($stmt, ':shopid', $shopid);
    oci_bind_by_name($stmt, ':activestatus', $activestatus);


    if (oci_execute($stmt)) {
        echo "Shop details updated successfully.";
        header("Location: admin_edit_shop.php?shopid=$shopid");
        exit();
    } else {
        echo "Error updating shop details.";
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Shop Details - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            margin-top: 6rem;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea,
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
        .btn-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-group .save-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }
        .outer-container{
            min-height:70vh;
        }
    </style>
    <script>
        function fetchShopDetails(shopid) {
            window.location.href = `admin_edit_shop.php?shopid=${shopid}`;
        }
    </script>
</head>
<body>
<?php include 'header.php'; ?>
<div class="outer-container">
<div class="container">
    <h2>Edit Shop Details</h2>
    <div class="form-group">
        <label for="shop">Shop</label>
        <select id="shop" name="shop" onchange="fetchShopDetails(this.value)">
            <option value="">Select a shop</option>
            <?php foreach ($shops as $shop): ?>
                <option value="<?= $shop['SHOPID'] ?>" <?= isset($selectedShop) && $selectedShop['SHOPID'] == $shop['SHOPID'] ? 'selected' : '' ?>>
                    <?= $shop['NAME'] ?> (<?= $shop['USERNAME'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($selectedShop): ?>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="shopid" value="<?= $selectedShop['SHOPID'] ?>">
            <div class="form-group">
                <label for="logo">Shop Logo</label>
                <?php if ($selectedShop['LOGO']): ?>
                    <img src="<?php echo $selectedShop['LOGO']; ?>" alt="Shop Logo" style="max-width: 150px; display: block; margin-bottom: 10px;">
                <?php endif; ?>
                <input type="file" id="logo" name="logo">
            </div>
            <div class="form-group">
                <label for="name">Shop Name</label>
                <input type="text" id="name" name="name" value="<?= $selectedShop['NAME'] ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Shop Description</label>
                <textarea id="description" name="description" required><?= $selectedShop['DESCRIPTION'] ?></textarea>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="<?= $selectedShop['LOCATION'] ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Active Status(1 for active, less than 1 for inactive)</label>
                <input type="text" id="activestatus" name="activestatus" value="<?= $selectedShop['ACTIVESTATUS'] ?>" required>
            </div>
            <div class="btn-group">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
            </div>
        </form>
    <?php endif; ?>
</div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>
