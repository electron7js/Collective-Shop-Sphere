<?php
session_start();

// Include the config.php file for database connection
include 'config.php';

// Variable to hold error messages
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the username and password from the form
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare the query to select the user
    $query = "SELECT * FROM Users WHERE username = :username";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':username', $username);
    oci_execute($stmt);

    // Fetch the user
    $user = oci_fetch_assoc($stmt);

    // Verify the password
    if ($user && ($password== $user['PASSWORD'])) {
        // Password is correct, start the session
        $_SESSION['username'] = $username;
        header("Location: index.php");
        exit;
    } else {
        // Invalid username or password
        $error = 'Invalid username or password';
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
    <title>Login - Collective Shop Sphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
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
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
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
        .form .signup-link {
            margin-top: 10px;
            text-align: center;
        }

    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <h1>Welcome</h1>
    <div class="form">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="customer">Customer</option>
                    <option value="vendor">Trader</option>
                </select>
            </div>
            <a href="forgot_password.php" style="text-align: left;">Forgot password</a>
            <button type="submit" class="submit-btn">Login</button>
        </form>
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
