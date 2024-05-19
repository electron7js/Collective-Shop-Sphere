<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

include 'config.php';
include 'functions.php';

$userRole = $_SESSION['user_role'];
if ($userRole != 'Trader') {
    header('Location: dashboard.php');
    exit();
}

// Get the logged-in user's ID
$username = $_SESSION['username'];
$query = "SELECT userid FROM Users WHERE username = :username";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
$userid = $user['USERID'];

// Fetch shop details
$query = "SELECT * FROM Shop WHERE userid = :userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':userid', $userid);
oci_execute($stmt);
$shop = oci_fetch_assoc($stmt);

if (!$shop) {
    echo "Error: Shop details not found.";
    exit();
}

// Update shop details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];

    // Handle logo upload
    $logo = $shop['LOGO'];
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        try {
            $logo = saveTraderImage($_FILES['logo']);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    }

    // Update Shop table
    $query = "UPDATE Shop SET name = :name, description = :description, location = :location, logo = :logo WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':description', $description);
    oci_bind_by_name($stmt, ':location', $location);
    oci_bind_by_name($stmt, ':logo', $logo);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);

    // Redirect to trader dashboard after update
    header('Location: traderdash.php');
    exit();
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
        .form-group textarea {
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
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Edit Shop Details</h2>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="logo">Shop Logo</label>
            <?php if ($shop['LOGO']): ?>
                <img src="<?php echo $shop['LOGO']; ?>" alt="Shop Logo" style="max-width: 150px; display: block; margin-bottom: 10px;">
            <?php endif; ?>
            <input type="file" id="logo" name="logo">
        </div>
        <div class="form-group">
            <label for="name">Shop Name</label>
            <input type="text" id="name" name="name" value="<?= $shop['NAME'] ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Shop Description</label>
            <textarea id="description" name="description" required><?= $shop['DESCRIPTION'] ?></textarea>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" value="<?= $shop['LOCATION'] ?>" required>
        </div>
        <div class="btn-group">
            <button type="submit" class="save-btn">Save</button>
            <button type="button" class="cancel-btn" onclick="window.history.back()">Cancel</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
