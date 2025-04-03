<?php 
session_start();
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}
$user = $_SESSION['user']; // Assuming this contains user info including the profile image URL
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>
<link rel="stylesheet" href="home.css">

</head>
<body>

<div class="sidebar">
<a href="newloan.php"><button>New Loan</button></a>
<a href="cashier.php"> <button>Cashier</button>
  <button>Reports</button>
  <a href="addCenter.php"><button>Branch</button></a>
  <a href="addgroup.php"><button>Group</button></a>
  <a href="registeruser.php"><button>Settings</button></a>
  <a href="login-user.php"><button>Logout</button>
</div>

<div class="top-bar">
  <input type="text" placeholder="Search Bar" />
  <div class="profile-info">
            <span><?php echo htmlspecialchars($user['name']); ?></span>
            <?php if (!empty($user['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" style="height: 50px; width: 50px; border-radius: 50%;">
            <?php endif; ?>
        </div>
</div>

<div class="content">
  <!-- Your main content goes here -->
</div>

</body>
</html>

