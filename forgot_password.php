<?php
session_start();

// Include the config.php file for database connection
include 'config.php';
include 'email_function.php';

// Variable to hold error messages
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username from the form
    $username = $_POST['username'];

    // Prepare the query to select the user
    $query = "SELECT * FROM Users WHERE username = :username";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':username', $username);
    oci_execute($stmt);

    // Fetch the user
    $user = oci_fetch_assoc($stmt);

    if ($user) {
        // User exists, send the forgot password email
        send_forgot_password_email($username, $user['EMAIL']);
        $message = 'A password reset link has been sent to your email address.';
    } else {
        // User does not exist
        $error = 'Invalid username';
    }

    // Close the database connection
    oci_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Collective Shop Sphere</title>
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
        .form .login-link {
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Forgot Password</h1>
    <div class="form">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php elseif (isset($message)): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
        <div class="login-link">
            Remembered your password? <a href="login.php">Login</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
