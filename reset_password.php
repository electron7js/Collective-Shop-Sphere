<?php
session_start();

// Include the config.php file for database connection
include 'config.php';
include 'functions.php';

// Variable to hold error messages
$error = '';
$message = '';

// Check if token is present in the URL
if (isset($_GET['token']) && isset($_GET['user_id'])  ) {
    $token = $_GET['token'];
    $userid = $_GET['user_id'];

    $query = "
    SELECT * FROM 
    (SELECT * FROM (
            SELECT * FROM VerificationCodes 
            WHERE userid = :userid ORDER BY verificationid DESC ) 
            output WHERE ROWNUM = 1)  
            WHERE code = :token
    ";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_bind_by_name($stmt, ':token', $token);
    oci_execute($stmt);
    $verification = oci_fetch_assoc($stmt);



    if (!$verification) {
        echo "Error: Invalid password reset token.";
        echo $userid. ' ' . $token;

        exit();
    }


    
    $query = "SELECT * FROM Users WHERE userid=:userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userid);
    oci_execute($stmt);

    // Fetch the user
    $user = oci_fetch_assoc($stmt);

    if ($user) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if ($new_password === $confirm_password) {
                // Hash the new password
                $hashed_password = md5($new_password);

                // Update the user's password in the database
                $update_query = "UPDATE Users SET password = :password WHERE userid = :userid";
                $update_stmt = oci_parse($conn, $update_query);
                oci_bind_by_name($update_stmt, ':password', $hashed_password);
                oci_bind_by_name($update_stmt, ':userid', $userid);
                oci_execute($update_stmt);

                $message = 'Your password has been reset successfully.';
            } else {
                $error = 'Passwords do not match.';
            }
        }
    } else {
        $error = 'Invalid or expired token.';
    }

    // Close the database connection
    oci_close($conn);
} else {
    $error = 'No token provided.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Collective Shop Sphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            position: relative;
            top: -4vh;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 65vh;
        }
        .form {
            max-width: 400px;
            width: 100%;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form h2 {
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
        .form-group input {
            width: 95%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .error, .message {
            text-align: center;
            margin-bottom: 15px;
        }
        .error {
            color: red;
        }
        .message {
            color: green;
        }
        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
        }
        .submit-btn:hover {
            background-color: #218838;
        }
        .form a {
            display: block;
            text-align: center;
            margin: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .form a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Reset Password</h1>
    <div class="form">
        <h2>Enter New Password</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php elseif ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if (!$message): ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="submit-btn" onclick="passwordValidation(event)">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function passwordValidation(event) {
            console.log(event)
            var password = document.getElementById('new_password').value;
            var confirmPassword = document.getElementById('confirm_password').value;

            if (password.length < 6) {
                console.log(event);
                alert('Password must be at least 6 characters long.');
                event.preventDefault();
            }
            
           else if (password !== confirmPassword) {
                alert('Passwords do not match.');
                event.preventDefault();
            }
            
            return true;
        }
    </script>  

<?php include 'footer.php'; ?>
</body>
</html>
