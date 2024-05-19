<?php
session_start();

// Include the config.php file for database connection and functions.php for utility functions
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the logged-in user's ID
$username = $_SESSION['username'];
$query = "SELECT u.userid, u.username, u.contactnumber, u.email, c.firstname, c.lastname, c.gender, c.profile_image, c.dateofbirth 
          FROM Users u
          JOIN Customer c ON u.userid = c.userid
          WHERE u.username = :username";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':username', $username);
oci_execute($stmt);
$user = oci_fetch_assoc($stmt);
$userid = $user['USERID'];

// Variables to hold form data and error messages
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $profileImageFileName = $user['PROFILE_IMAGE'];

    // Check if a new profile picture is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        try {
            $profileImageFileName = saveUserProfileImage($_FILES['profile_picture']);
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    // Update the Users table
    $query = "UPDATE Users 
              SET contactnumber = :contact_number, email = :email
              WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':contact_number', $contact_number);
    oci_bind_by_name($stmt, ':email', $email);
    oci_bind_by_name($stmt, ':userid', $userid);
    $result1 = oci_execute($stmt);

    // Update the Customer table
    $query = "UPDATE Customer 
              SET firstname = :first_name, lastname = :last_name, gender = :gender, profile_image = :profile_image, dateofbirth = TO_DATE(:dob, 'YYYY-MM-DD')
              WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':first_name', $first_name);
    oci_bind_by_name($stmt, ':last_name', $last_name);
    oci_bind_by_name($stmt, ':gender', $gender);
    oci_bind_by_name($stmt, ':profile_image', $profileImageFileName);
    oci_bind_by_name($stmt, ':dob', $dob);
    oci_bind_by_name($stmt, ':userid', $userid);
    $result2 = oci_execute($stmt);

    if ($result1 && $result2) {
        oci_commit($conn);
        $success = "Profile updated successfully!";
    } else {
        oci_rollback($conn);
        $error = "Failed to update profile.";
    }
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
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 700px;
            margin: 100px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
            margin-right:1rem;

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
        .error, .success {
            text-align: center;
            margin-bottom: 15px;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
        .btn-group {
            margin-top:5vh;
            margin-bottom:1vh;
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
            width: 40%;
            color: white;
        }
        .btn-group .cancel-btn {
            background-color: #ccc;
            color: black;
            width: 40%;

        }
        .selectors{
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }

        .selectors .gender{
            width:40%;
        }
        
        .selectors .dateofbirth{
            width:40%;
        }
    </style>

</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h2>Edit Profile</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="post" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label for="profile_picture">Profile Picture</label>
            <?php if ($user['PROFILE_IMAGE']): ?>
                <img src="<?php echo $user['PROFILE_IMAGE']; ?>" alt="Profile Picture" style="max-width: 150px; display: block; margin-bottom: 10px;">
            <?php endif; ?>
            <input type="file" id="profile_picture" name="profile_picture">
        </div>
        <div class="form-group">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $user['FIRSTNAME']; ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $user['LASTNAME']; ?>" required>
        </div>
        <div class="form-group">
            <label for="contact_number">Contact Number</label>
            <input type="text" id="contact_number" name="contact_number" value="<?php echo $user['CONTACTNUMBER']; ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo $user['EMAIL']; ?>" required>
        </div>
        <div class="selectors">
        <div class="form-group gender">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" required>
                <option value="male" <?php if ($user['GENDER'] == 'male') echo 'selected'; ?>>Male</option>
                <option value="female" <?php if ($user['GENDER'] == 'female') echo 'selected'; ?>>Female</option>
                <option value="other" <?php if ($user['GENDER'] == 'other') echo 'selected'; ?>>Other</option>
            </select>
        </div>
        <div class="form-group dateofbirth">
            <label for="dob">Date of Birth</label>
            <input type="date" id="dob" name="dob" value="<?php echo date('Y-m-d', strtotime($user['DATEOFBIRTH'])); ?>" required>
        </div>
        </div>
        <div class="btn-group">
            <button type="button" class="cancel-btn" onclick="window.location.href='customerdash.php'">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>