<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Page</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 400px;
            margin: 100px auto;
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
        .btn-group .submit-btn {
            background-color: #28a745;
            color: white;
        }
        .btn-group .cancel-btn {
            background-color: #ccc;
            color: black;
        }
        .footerdiv{
            position: static;
            bottom:0;
        }
        .outercontainer{
            min-height:60vh;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="outercontainer">

<div class="container">
    <h2>Enter Verification Code</h2>
    <form method="post" action="verification.php">
        <div class="form-group">
            <label for="code">Verification Code</label>
            <input type="text" id="code" name="code" pattern="\d{6}" title="Please enter a 6-digit number" required>
        </div>
        <div class="btn-group">
            <button type="submit" class="submit-btn">Submit</button>
            <button type="button" class="cancel-btn" onclick="window.location.href='index.php'">Cancel</button>
        </div>
    </form>
</div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
