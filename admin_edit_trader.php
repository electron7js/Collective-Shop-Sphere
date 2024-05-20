<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Fetch all traders
$query = "SELECT u.userid, u.username FROM Users u JOIN Trader t ON u.userid = t.userid";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$traders = [];
while ($trader = oci_fetch_assoc($stmt)) {
    $traders[] = $trader;
}

// Fetch trader details if a trader is selected
$selectedTrader = null;
if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];
    $query = "SELECT u.userid,u.username, u.contactnumber, u.email, t.address, t.secondarycontact, t.verified
              FROM Users u
              JOIN Trader t ON u.userid = t.userid
              WHERE u.userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);
    $selectedTrader = oci_fetch_assoc($stmt);
}

// Update trader details if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['userid'])) {
    $userid = $_POST['userid'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $contactnumber = $_POST['contactnumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $secondarycontact = $_POST['secondarycontact'];



    $updateUserQuery = "UPDATE Users SET username = :username, contactnumber = :contactnumber, email = :email WHERE userid = :userid";
    $updateUserStmt = oci_parse($conn, $updateUserQuery);
    oci_bind_by_name($updateUserStmt, ':username', $username);
    oci_bind_by_name($updateUserStmt, ':contactnumber', $contactnumber);
    oci_bind_by_name($updateUserStmt, ':email', $email);
    oci_bind_by_name($updateUserStmt, ':userid', $userid);

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE Users SET password = :password WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':password', $hashed_password);
        oci_bind_by_name($stmt, ':userid',  $userid);
        oci_execute($stmt);
    }

    $updateTraderQuery = "UPDATE Trader SET address = :address, secondarycontact = :secondarycontact WHERE userid = :userid";
    $updateTraderStmt = oci_parse($conn, $updateTraderQuery);
    oci_bind_by_name($updateTraderStmt, ':address', $address);
    oci_bind_by_name($updateTraderStmt, ':secondarycontact', $secondarycontact);
    oci_bind_by_name($updateTraderStmt, ':userid', $userid);

    if (oci_execute($updateUserStmt) && oci_execute($updateTraderStmt)) {
        echo "Trader details updated successfully.";
        header("Location: admin_edit_trader.php?userid=$userid");
        exit();
    } else {
        echo "Error updating trader details.";
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trader</title>
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
        .form-group input, .form-group select {
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
        .button-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }
    </style>
    <script>
        function fetchTraderDetails(userid) {
            window.location.href = `admin_edit_trader.php?userid=${userid}`;
        }
    </script>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h2>Edit Trader</h2>
    <div class="form-group">
        <label for="trader">Trader</label>
        <select id="trader" name="trader" onchange="fetchTraderDetails(this.value)">
            <option value="">Select a trader</option>
            <?php foreach ($traders as $trader): ?>
                <option value="<?= $trader['USERID'] ?>" <?= isset($selectedTrader) && $selectedTrader['USERID'] == $trader['USERID'] ? 'selected' : '' ?>>
                    <?= $trader['USERNAME'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($selectedTrader): ?>
        <form method="post" action="">
            <input type="hidden" name="userid" value="<?= $selectedTrader['USERID'] ?>">
            <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= $selectedTrader['USERNAME'] ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password (leave blank if not changing)</label>
            <input type="password" id="password" name="password">
        </div>
            <div class="form-group">
                <label for="contactnumber">Contact Number</label>
                <input type="text" id="contactnumber" name="contactnumber" value="<?= $selectedTrader['CONTACTNUMBER'] ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $selectedTrader['EMAIL'] ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?= $selectedTrader['ADDRESS'] ?>" required>
            </div>
            <div class="form-group">
                <label for="secondarycontact">Secondary Contact</label>
                <input type="text" id="secondarycontact" name="secondarycontact" value="<?= $selectedTrader['SECONDARYCONTACT'] ?>" required>
            </div>
            <div class="button-group">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='admin_dashboard.php'">Cancel</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
