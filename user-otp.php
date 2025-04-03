<?php
session_start();
require 'connecton.php';

if (!isset($_SESSION['email'])) {
    header('location: signup-user.php'); // Redirect to signup if email not set in session
    exit();
}

$errors = array();
$email = $_SESSION['email']; // Get email from session

// Process OTP verification
if (isset($_POST['verify_otp'])) {
    $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
    
    // Verify OTP
    $check_code = "SELECT * FROM usertable WHERE code = '$otp_code' AND email = '$email'";
    $code_res = mysqli_query($con, $check_code);
    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $status = 'verified';
        $update_otp = "UPDATE usertable SET code = 0, status = '$status' WHERE email = '$email'";
        $update_res = mysqli_query($con, $update_otp);
        if ($update_res) {
            // Redirect to login page after successful verification
            header('location: login-user.php');
            exit();
        } else {
            $errors['otp-error'] = "Failed while updating code!";
        }
    } else {
        $errors['otp-error'] = "You've entered incorrect code!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <link rel="stylesheet" href="login.css">
    
</head>
<body>
<div class="logo">
        <img src="logo.png" alt="Logo" style="width: 300px; position: absolute; top: 10px; left: 10px;">
    </div>
    <div class ="wrapper">
    <div class="otp-form">
        <form action="user-otp.php" method="post">
            <h2>Enter OTP</h2>
            <?php if (count($errors) > 0): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div>
                <input type="text" name="otp" required placeholder="Enter OTP">
            </div>
            <div>
                <button type="submit" name="verify_otp">Verify OTP</button>
            </div>
        </form>
    </div>
                    </div>
</body>
</html>
