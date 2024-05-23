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

// Fetch trader details using JOIN query
$query = "
    SELECT u.userid, u.username, u.contactnumber, u.email, u.name, t.address, t.secondarycontact
    FROM Users u
    JOIN Trader t ON u.userid = t.userid
    WHERE u.username = :username
";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$trader = oci_fetch_assoc($stmt);

if (!$trader) {
    echo "Error: Trader details not found.";
    exit();
}

// Update trader profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $contactnumber = $_POST['contactnumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $secondarycontact = $_POST['secondarycontact'];

    // Hash the password if it's being changed
    if (!empty($password)) {
        $hashed_password = md5($password);
        $query = "UPDATE Users SET password = :password WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':password', $hashed_password);
        oci_bind_by_name($stmt, ':userid', $trader['USERID']);
        oci_execute($stmt);
    }

    // Update Users table
    $query = "UPDATE Users SET name = :name, username = :username, contactnumber = :contactnumber, email = :email WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':name', $name);
    oci_bind_by_name($stmt, ':username', $username);
    oci_bind_by_name($stmt, ':contactnumber', $contactnumber);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':userid', $trader['USERID']);
    oci_execute($stmt);

    // Update Trader table
    $query = "UPDATE Trader SET address = :address, secondarycontact = :secondarycontact WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':address', $address);
    oci_bind_by_name($stmt, ':secondarycontact', $secondarycontact);
    oci_bind_by_name($stmt, ':userid', $trader['USERID']);
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
    <title>Edit Profile - Collective Shop Sphere</title>
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
        .form-group input {
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
    <h2>Edit Profile</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?= $trader['NAME'] ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= $trader['USERNAME'] ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password (leave blank if not changing)</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="contactnumber">Contact Number</label>
            <input type="text" id="contactnumber" name="contactnumber" value="<?= $trader['CONTACTNUMBER'] ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= $trader['EMAIL'] ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?= $trader['ADDRESS'] ?>" required>
        </div>
        <div class="form-group">
            <label for="secondarycontact">Secondary Contact</label>
            <input type="text" id="secondarycontact" name="secondarycontact" value="<?= $trader['SECONDARYCONTACT'] ?>">
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
