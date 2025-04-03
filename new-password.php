<?php
session_start();
require "connecton.php";

if (isset($_POST['change-password'])) {
  $otp = $_SESSION['otp'];
  $new_password = mysqli_real_escape_string($con, $_POST['password']);
  $confirm_password = mysqli_real_escape_string($con, $_POST['confirm_password']);

  // Validate password match
  if ($new_password !== $confirm_password) {
    echo "Error: Passwords do not match. Please try again.";
    exit(); // Stop further processing if passwords don't match
  }

  $new_password_enc = password_hash($new_password, PASSWORD_BCRYPT);
  $update_pass = "UPDATE usertable SET code=0, password='$new_password_enc' WHERE code='$otp'";
  $run_query = mysqli_query($con, $update_pass);

  if ($run_query) {
    echo "Password changed successfully. <a href='login-user.php'>Login Here</a>";
    header('Location: login-user.php'); // Redirect to login page after successful password change
    exit();
} else {
    echo "Something went wrong. Please try again.";
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>New Password</title>
  <link rel="stylesheet" href="login.css">

  

</head>
<body>
<div class="logo">
        <img src="logo.png" alt="Logo" style="width: 300px; position: absolute; top: 10px; left: 10px;">
    </div>
  <form action="new-password.php" method="post">
    <h2>New Password</h2>
    <input type="password" name="password" placeholder="New Password" required><br><br>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit" name="change-password">Change Password</button>
  </form>
</body>
</html>
