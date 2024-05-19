<?php 

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($userId) {
    // Database connection
    include 'config.php';

    // Fetch email address from the database using the userId
    $query = "SELECT email FROM Users WHERE userid = :userid";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userId);
    oci_execute($stmt);
    $user = oci_fetch_assoc($stmt);

    if (!$user) {
        throw new Exception("User not found.");
    }

    $email = $user['EMAIL'];

    // Generate a 6-digit verification code
    $verificationCode = rand(100000, 999999);

    // Insert the verification code into the Verification table
    $query = "INSERT INTO VerificationCodes (userid, code) VALUES (:userid, :verificationCode)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':userid', $userId);
    oci_bind_by_name($stmt, ':verificationCode', $verificationCode);

    // Ensure the statement is executed correctly
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt); // For oci_execute errors pass the statement handle
        throw new Exception($e['message']);
    }

    // Send email using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'bijeshmanstha';           // SMTP username
        $mail->Password = 'owbiieityojdygir';                    // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('bijeshmanstha@gmail.com', 'CSS');
        $mail->addAddress($email);                            // Add a recipient


        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification';
        $mail->Body = "This is your verification number: $verificationCode<br><a href='http://localhost/frontend/verification.php?code=$verificationCode'>Click here to verify</a>";

        $mail->send();
    } catch (Exception $e) {
        throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}