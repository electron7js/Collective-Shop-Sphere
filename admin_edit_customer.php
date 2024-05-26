<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: login.php');
    exit();
}

include 'config.php';

// Fetch all customers
$query = "SELECT u.userid, u.username FROM Users u JOIN Customer c ON u.userid = c.userid";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

$customers = [];
while ($customer = oci_fetch_assoc($stmt)) {
    $customers[] = $customer;
}

// Fetch customer details if a customer is selected
$selectedCustomer = null;
if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];
    $query = "SELECT u.userid, u.username, u.contactnumber, u.email, c.firstname, c.lastname, c.gender, c.dateofbirth, c.profile_image,u.activestatus
              FROM Users u
              JOIN Customer c ON u.userid = c.userid
              WHERE u.userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);
    $selectedCustomer = oci_fetch_assoc($stmt);
}

// Update customer details if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['userid'])) {
    $userid = $_POST['userid'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $contactnumber = $_POST['contactnumber'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $activestatus = $_POST['activestatus'];

    $dateofbirth = date('Y-m-d', strtotime($_POST['dateofbirth']));


    $updateUserQuery = "UPDATE Users SET contactnumber = :contactnumber, username = :username, email = :email , activestatus=:activestatus WHERE userid = :userid";
    $updateUserStmt = oci_parse($conn, $updateUserQuery);
    oci_bind_by_name($updateUserStmt, ':contactnumber', $contactnumber);
    oci_bind_by_name($updateUserStmt, ':email', $email);
    oci_bind_by_name($updateUserStmt, ':username', $username);
    oci_bind_by_name($updateUserStmt, ':userid', $userid);
    oci_bind_by_name($updateUserStmt, ':activestatus', $activestatus);

    if (!empty($password)) {
        $hashed_password = md5($password);
        $query = "UPDATE Users SET password = :password WHERE userid = :userid";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':password', $hashed_password);
        oci_bind_by_name($stmt, ':userid', $userid);
        oci_execute($stmt);
    }


    $updateCustomerQuery = "UPDATE Customer SET firstname = :firstname, lastname = :lastname, gender = :gender, dateofbirth = TO_DATE(:dateofbirth, 'YYYY-MM-DD') WHERE userid = :userid";
    $updateCustomerStmt = oci_parse($conn, $updateCustomerQuery);
    oci_bind_by_name($updateCustomerStmt, ':firstname', $firstname);
    oci_bind_by_name($updateCustomerStmt, ':lastname', $lastname);
    oci_bind_by_name($updateCustomerStmt, ':gender', $gender);
    oci_bind_by_name($updateCustomerStmt, ':dateofbirth', $dateofbirth);
    oci_bind_by_name($updateCustomerStmt, ':userid', $userid);

    if (oci_execute($updateUserStmt) && oci_execute($updateCustomerStmt)) {
        echo "Customer details updated successfully.";
        header("Location: admin_edit_customer.php?userid=$userid");
        exit();
    } else {
        echo "Error updating customer details.";
    }
}

oci_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
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
        .outer-container{
            min-height:65vh;
        }
    </style>
    <script>
        function fetchCustomerDetails(userid) {
            window.location.href = `admin_edit_customer.php?userid=${userid}`;
        }
    </script>
</head>
<body>

<?php include 'header.php'; ?>
<div class="outer-container">

<div class="container">
    <h2>Edit Customer</h2>
    <div class="form-group">
        <label for="customer">Customer</label>
        <select id="customer" name="customer" onchange="fetchCustomerDetails(this.value)">
            <option value="">Select a customer</option>
            <?php foreach ($customers as $customer): ?>
                <option value="<?= $customer['USERID'] ?>" <?= isset($selectedCustomer) && $selectedCustomer['USERID'] == $customer['USERID'] ? 'selected' : '' ?>>
                    <?= $customer['USERNAME'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($selectedCustomer): ?>
        <form method="post" action="">
            <input type="hidden" name="userid" value="<?= $selectedCustomer['USERID'] ?>">
            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" value="<?= $selectedCustomer['FIRSTNAME'] ?>" required>
            </div>
            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" value="<?= $selectedCustomer['LASTNAME'] ?>" required>
            </div>
            <div class="form-group">
                <label for="contactnumber">Contact Number</label>
                <input type="text" id="contactnumber" name="contactnumber" value="<?= $selectedCustomer['CONTACTNUMBER'] ?>" required>
            </div>
            <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= $selectedCustomer['USERNAME'] ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password (leave blank if not changing)</label>
            <input type="password" id="password" name="password">
        </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= $selectedCustomer['EMAIL'] ?>" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?= $selectedCustomer['GENDER'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $selectedCustomer['GENDER'] == 'Female' ? 'selected' : '' ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dateofbirth">Date of Birth</label>
                <input type="date" id="dateofbirth" name="dateofbirth" value="<?= date('Y-m-d', strtotime($selectedCustomer['DATEOFBIRTH'])) ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Active Status(1 or null for active, less than 1 for inactive)</label>
                <input type="text" id="activestatus" name="activestatus" value="<?= $selectedCustomer['ACTIVESTATUS'] ?>">
            </div>
            <div class="button-group">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='admin_dashboard.php'">Cancel</button>
            </div>
           
        </form>
    <?php endif; ?>
</div>
</div>
<?php include 'footer.php'; ?>

</body>
</html>
