<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Get the logged-in admin's ID
$username = $_SESSION['username'];

// Fetch admin details
$query = "SELECT * FROM Admin, Users WHERE username = :username AND Admin.userid=users.userid";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$admin = oci_fetch_assoc($stmt);

if (!$admin) {
    echo "Error: Admin details not found.";
    exit();
}

// Update admin details if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];
    $newContactNumber = $_POST['contactnumber'];
    $newEmail = $_POST['email'];





    $updateQuery = "UPDATE Users SET username = :username, contactnumber = :contactnumber, email = :email WHERE userid = :userid";
    $updateStmt = oci_parse($conn, $updateQuery);
    oci_bind_by_name($updateStmt, ':username', $newUsername);
    oci_bind_by_name($updateStmt, ':contactnumber', $newContactNumber);
    oci_bind_by_name($updateStmt, ':email', $newEmail);
    oci_bind_by_name($updateStmt, ':userid', $admin['USERID']);


    if (!empty($newPassword)) {
        $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE Users SET password = :password WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':password', $hashed_password);
        oci_bind_by_name($stmt, ':userid', $admin['USERID']);
        oci_execute($stmt);
    }


    if (oci_execute($updateStmt)) {
        echo "Admin details updated successfully.";
        // Update session username if changed
        $_SESSION['username'] = $newUsername;
    } else {
        echo "Error updating admin details.";
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin Details</title>
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
        .button-group {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .button-group button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .button-group .save-btn {
            background-color: #28a745;
            color: white;
        }
        .button-group .back-btn {
            background-color: #ccc;
            color: black;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Edit Admin Details</h2>
    <form method="post" action="">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= $admin['USERNAME'] ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password (leave blank if not changing)</label>
            <input type="password" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="contactnumber">Contact Number</label>
            <input type="text" id="contactnumber" name="contactnumber" value="<?= $admin['CONTACTNUMBER'] ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= $admin['EMAIL'] ?>" required>
        </div>
        <div class="button-group">
            <button type="submit" class="save-btn">Save</button>
            <button type="button" class="back-btn" onclick="window.location.href='admin_dashboard.php'">Back</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>