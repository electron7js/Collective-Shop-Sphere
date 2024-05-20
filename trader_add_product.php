<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Check user role
$userRole = $_SESSION['user_role'];
if ($userRole != 'Trader') {
    header('Location: dashboard.php');
    exit();
}

// Function to save the uploaded image and return the file path
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

$error = '';
$success = '';

// Fetch brands and categories
$brands = [];
$categories = [];

$query = "SELECT brandid, brandname FROM Brand";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $brands[] = $row;
}

$query = "SELECT categoryid, title FROM Category";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
while ($row = oci_fetch_assoc($stmt)) {
    $categories[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $productName = $_POST['product_name'];
        $productDescription = $_POST['product_description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $brandId = $_POST['brand_id'];
        $categoryId = $_POST['category_id'];
        $imagePath = saveProductImage($_FILES['product_image']);

        // Get the logged-in user's ID
        $username = $_SESSION['username'];
        $query = "SELECT userid FROM Users WHERE username = :username";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':username', $username);
        oci_execute($stmt);
        $user = oci_fetch_assoc($stmt);
        $userid = $user['USERID'];

        // Get the shop ID for the trader
        $query = "SELECT shopid FROM Shop WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_execute($stmt);
        $shop = oci_fetch_assoc($stmt);
        $shopid = $shop['SHOPID'];

        // Insert the product details into the Product table
        $query = "INSERT INTO Product (productid, name, description, image, price, remainingstock, activestatus, brandid, categoryid, shopid) 
                  VALUES (seq_productid.NEXTVAL, :name, :description, :image, :price, :stock, 0, :brandid, :categoryid, :shopid)";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':name', $productName);
        oci_bind_by_name($stmt, ':description', $productDescription);
        oci_bind_by_name($stmt, ':image', $imagePath);
        oci_bind_by_name($stmt, ':price', $price);
        oci_bind_by_name($stmt, ':stock', $stock);
        oci_bind_by_name($stmt, ':brandid', $brandId);
        oci_bind_by_name($stmt, ':categoryid', $categoryId);
        oci_bind_by_name($stmt, ':shopid', $shopid);
        oci_execute($stmt);
        oci_commit($conn);

        $success = "Product added successfully!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group input[type="file"] {
            padding: 3px;
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
        .btn-group .add-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Add Product</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_image">Product Image</label>
            <input type="file" id="product_image" name="product_image" required>
        </div>
        <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" id="product_name" name="product_name" required>
        </div>
        <div class="form-group">
            <label for="product_description">Product Description</label>
            <input type="text" id="product_description" name="product_description" required>
        </div>
        <div class="form-group">
            <label for="brand_id">Brand</label>
            <select id="brand_id" name="brand_id" required>
                <option value="">Select a brand</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?= $brand['BRANDID'] ?>"><?= $brand['BRANDNAME'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select a category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['CATEGORYID'] ?>"><?= $category['TITLE'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" required>
        </div>
        <div class="btn-group">
            <button type="submit" class="add-btn">Add</button>
            <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
