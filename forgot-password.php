<?php
session_start();
require 'test/PHPMailer/PHPMailerAutoload.php';

if (isset($_POST['reset-request'])) {
    require "connecton.php"; // Ensure you point to your actual database connection file
    $email = mysqli_real_escape_string($con, $_POST['email']);

    // Check if email exists
    $query = "SELECT * FROM usertable WHERE email='$email'";
    $results = mysqli_query($con, $query);
    if(mysqli_num_rows($results) > 0){
        $code = rand(999999, 111111); // Generate random code
        // Update the database with the reset code
        $update_code = "UPDATE usertable SET code='$code' WHERE email='$email'";
        $run_query = mysqli_query($con, $update_code);
        if($run_query){
            // Send email with the code using PHPMailer
            $mail = new PHPMailer;

            //$mail->SMTPDebug = 3;                               // Enable verbose debug output

            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'pro6299ject@gmail.com';                 // SMTP username
            $mail->Password = 'kkngahzpwsipeqgu';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to

            $mail->setFrom('pro6299ject@gmail.com', 'Goladenaisa.pvtLtd');
            $mail->addAddress($email);     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = 'Password Reset Code';
            $mail->Body    = "Your password reset code is: $code";
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            if(!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
                $_SESSION['info'] = "We've sent a password reset code to your email - $email";
                $_SESSION['email'] = $email;
                header('Location: reset-code.php'); // Redirect to page where they can enter the code
                exit();
            }
        } else {
            echo "Something went wrong.";
        }
    } else {
        echo "This email address does not exist!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="logo">
    <img src="logo.png" alt="Logo" style="width: 300px; position: absolute; top: 10px; left: 10px;">
</div>
<form action="forgot-password.php" method="post">
    <h2>Forgot Password</h2>
    <input type="email" name="email" placeholder="Enter your email address" required>
    <button type="submit" name="reset-request">Send Reset Code</button>
</form>
</body>
</html>
