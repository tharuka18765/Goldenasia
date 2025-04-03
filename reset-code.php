<?php 
session_start();
require "connecton.php"; // Ensure this points to your database connection
if (isset($_POST['check-reset-otp'])) {
    $otp = mysqli_real_escape_string($con, $_POST['otp']);
    $check_code = "SELECT * FROM usertable WHERE code='$otp' AND email='{$_SESSION['email']}'";
    $code_res = mysqli_query($con, $check_code);
    if(mysqli_num_rows($code_res) > 0){
        $_SESSION['otp'] = $otp;
        header('Location: new-password.php'); // Redirect to set a new password
        exit();
    }else{
        echo "You've entered an incorrect code!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Code Verification</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
<div class="logo">
        <img src="logo.png" alt="Logo" style="width: 300px; position: absolute; top: 10px; left: 10px;">
    </div>
    <form action="reset-code.php" method="post">
        <h2>Enter Reset Code</h2>
        <input type="text" name="otp" placeholder="Enter code" required>
        <button type="submit" name="check-reset-otp">Submit</button>
    </form>
</body>
</html>
