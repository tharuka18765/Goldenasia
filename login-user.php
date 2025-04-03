<?php
session_start();
require "connecton.php"; // Ensure this points to your database connection file
$errors = array();

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    if (count($errors) == 0) {
        $query = "SELECT * FROM usertable WHERE email='$email' LIMIT 1";
        $results = mysqli_query($con, $query);
        if (mysqli_num_rows($results) == 1) {
            $user = mysqli_fetch_assoc($results);
            if (password_verify($password, $user['password'])) {
                // Assign user info to session
                $_SESSION['user'] = $user;
                $_SESSION['success'] = "You are now logged in";
                header('location: index.php'); // Redirect to home/dashboard page
                exit();
            } else {
                array_push($errors, "Wrong username/password combination");
            }
        } else {
            array_push($errors, "User does not exist");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
   
    
    <title>Login</title>
</head>
<body>
<div class="logo">
        <img src="logo.png" alt="Logo" style="width: 300px; position: absolute; top: 10px; left: 10px;">
    </div>

<div class="wrapper">
    <div class="login-form">
        <form action="login-user.php" method="post">
            <h2>Login</h2>
            <?php if (count($errors) > 0): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div class="input-box">
                <input type="email" name="email" required placeholder="Email">
                <i class='bx bxs-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" required placeholder="Password">
                <i class='bx bxs-lock-alt' ></i>
            </div>
            <div class="remember-forgot">
                <label><input type="checkbox">Remember Me</label>
                <a href="forgot-password.php">Forgot Password</a>
            </div>
            <button type="submit" name="login" class="btn">Login</button>
        </form>
    </div>
</div>
</body>
</html>
