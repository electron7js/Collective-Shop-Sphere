<?php
session_start();

// Include the config.php file for database connection
include 'config.php';
include 'email_function.php';


// Variable to hold error messages
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $gender = $_POST['gender'];
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate form data
    if ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if the email or username already exists
        $query = "SELECT * FROM Users WHERE email = :email OR username = :username";
        $stmt = oci_parse($conn, $query);
        oci_bind_by_name($stmt, ':email', $email);
        oci_bind_by_name($stmt, ':username', $username);
        oci_execute($stmt);
        if (oci_fetch_assoc($stmt)) {
            $error = 'Email or username already exists.';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            //unhashed password in use for now

            $user_id = 0;
            $query = "INSERT INTO Users (userid, username, password, email) VALUES (seq_userid.NEXTVAL, :username, :password, :email) RETURNING userid INTO :user_id";
            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ':username', $username);
            oci_bind_by_name($stmt, ':email', $email);
            oci_bind_by_name($stmt, ':password', $password);
            oci_bind_by_name($stmt, ':user_id', $user_id);
            oci_execute($stmt);

            // Insert into Customer table
            $query = "INSERT INTO Customer (userid, firstname, lastname, gender, address, dateofbirth, verified) VALUES (:userid, :firstname, :lastname, :gender, '', TO_DATE(:dob, 'YYYY-MM-DD'), 0)";
            $stmt = oci_parse($conn, $query);
            oci_bind_by_name($stmt, ':userid', $user_id);
            oci_bind_by_name($stmt, ':firstname', $first_name);
            oci_bind_by_name($stmt, ':lastname', $last_name);
            oci_bind_by_name($stmt, ':gender', $gender);
            oci_bind_by_name($stmt, ':dob', $dob);
            oci_execute($stmt);

            sendVerificationEmail($user_id);

            $success = 'Registration successful! Verification code sent to email';
        }

        // Close the database connection
        oci_close($conn);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Collective Shop Sphere</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .form {
            max-width: 700px;
            margin: 100px auto;
            padding: 50px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        .container {
            min-height: 65vh;
        }

        h1{
            text-align: center;
            position: relative;
            top:5vh;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
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
        .error, .success {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
        .success {
            color: green;
        }
        .submit-btn {
            width: 50%;
            padding: 10px;
            margin: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            align:center;
            text-align:center;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
        }
        .form-row .form-group {
            width: 48%;
        }

        .bottom-form{
            display: flex;
    flex-direction: column;
    align-content: center;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center; 
    }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Welcome</h1>
    <div class="form">
        <h2>Sign Up</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="phone" name="phone" placeholder="Phone" required>
                </div>
                <div class="form-group">
                    <input type="email" id="email" name="email" placeholder="Email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <select id="gender" name="gender" required>
                        <option value="">Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="date" id="dob" name="dob" placeholder="Date of birth" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-group">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter Password" required>
                </div>
            </div>
            <div class="bottom-form">
            <div class="form-group" style="display:flex">
                <input type="checkbox" id="terms" name="terms" style="height:1rem; width:1rem; margin-right:1rem;" required> <p style="display:block;"> I hereby accept all the terms and conditions. </p>
            </div>
            <button type="submit" class="submit-btn">Register</button>
            <button type="button" class="submit-btn" onclick="window.location.href='register_trader.php'">Register as a trader</button>
        </form>
        <p>Already have an Account? <a href="login.php">login</a></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>