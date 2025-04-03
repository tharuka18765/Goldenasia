<?php
session_start();
require "connecton.php"; // Database connection file
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}
$user = $_SESSION['user'];
$status = $user['status']; 

$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
$registrationSuccess = false;

if (isset($_POST['register'])) {
    // Retrieve and sanitize form data
    $branch = mysqli_real_escape_string($con, $_POST['branch']);
    $branchCode = mysqli_real_escape_string($con, $_POST['branchCode']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    $status = mysqli_real_escape_string($con, $_POST['status']);

    // Validate the data (e.g., check if passwords match)
    if ($password !== $cpassword) {
        // Handle error - passwords do not match
        echo "The passwords do not match.";
        exit();
    }

    // Check if the email already exists
    $emailQuery = "SELECT * FROM usertable WHERE email = '$email'";
    $emailResult = mysqli_query($con, $emailQuery);
    if (mysqli_num_rows($emailResult) > 0) {
        // Handle error - email already exists
        echo "The email you have entered already exists.";
        exit();
    }

    // Fetch all branches (if needed elsewhere in the script)
    $branchQuery = "SELECT bname, bcode FROM branch";
    $branchResult = mysqli_query($con, $branchQuery);
    $branches = mysqli_fetch_all($branchResult, MYSQLI_ASSOC);

    // Password encryption
    $encryptedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Define additional variables for insertion
    $userCode = '333333';  // User code to be inserted
    // User status to be inserted

    // Insert the new user into the database
    $insertQuery = "INSERT INTO usertable (bname, bcode, email, name, password, code, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);
    
    // Now bind the new status variable as well
    mysqli_stmt_bind_param($stmt, 'sssssss', $branch, $branchCode, $email, $name, $encryptedPassword, $userCode, $status);

    if (mysqli_stmt_execute($stmt)) {
        $registrationSuccess = true; // Set to true when registration is successful
    } else {
        // Handle error - failed to insert user
        echo "Error: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>Register New Executive</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
    margin: 0;
    padding: 0;
    background: url('your-background-image.jpg') no-repeat center center fixed; /* Adjust as needed */
    background-size: cover;
    font-family: 'Arial', sans-serif;
}

form {
    max-width: 700px;
    margin-left:160px;
    margin-top:50px;
    background: transparent;
    padding: 20px;
   
}

label {
    font-weight: bold;
    color: #333;
    margin-top: 15px;
}


button {
    font-weight:500;
    text-transform:uppercase;
    width: 100%;
    padding: 10px;
    background-color: #FAA300;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

button:hover {
    background-color: black;
    color:white;
}

a {
    text-decoration: none;
    color: white;
}

body > hr {
    margin-left:600px;
    width: 85%;
    border: 1px solid #FAA300; /* Set the height and color of the hr line */
    margin: auto; /* Center the hr element within its container */
}
.flex-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
}

.flex-col {
    flex: 1;
    margin-right: 20px;
}

.flex-col:last-child {
    margin-right: 0;
}
h2{
    margin-left:100px;
}
select, input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}
.success-message {
    width: 100%;
    background-color: #4CAF50; /* Green background */
    color: white; /* White text color */
    text-align: center;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-size: 18px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Optional: adds a subtle shadow */
    position: fixed; /* Make it fixed at the top */
    top: 10px; /* Position from the top of the viewport */
    left: 50%;
    transform: translateX(-50%); /* Center it horizontally */
    z-index: 1000; /* Ensure it's on top of other elements */
}

/* Optionally, you might want to add some animation for it to slide down or fade in. */
@keyframes slideDown {
    from { top: -50px; }
    to { top: 10px; }
}

.success-message {
    animation: slideDown 0.5s ease-out forwards;
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}
/* Add additional styles here */

</style>
</head>
<body>
  <div class="home">

    <div class="sidebar ">
      <div class="logo">
      <a href="index.php">
        <img src="logo.png" alt="Logo" style="width: 100px; position: absolute; top: 10px; left: 10px;">
      </div>
      <div class="logo-details">
      <a href="index.php">
        <span class="logo_name"> Golden Aisa</span>
      </div>
      <ul class="nav-links">
      <li>
      <div class="iocn-link">
      <a href="<?php echo htmlspecialchars($link); ?>">
        <i class='bx bx-cog'></i>
        <span class="link_name">New Loan</span>
    </a>
      <i class='bx bxs-chevron-down arrow' ></i>
    </div>
    <ul class="sub-menu">
    
      <li><a href="renewloan.php">Renew Loan</a></li>
      <li><a href="garantor.php">Garantor</a></li>
      <?php if ($status == 'admin'): ?>
      <li><a href="aold.php">Old Loan</a></li>
      <?php endif; ?>
  
      
    </ul>
      </li>
      <li>
    <div class="iocn-link">
      <a href="cashier.php">
        <i class='bx bx-money'></i>
        <span class="link_name">Cashier</span>
      </a>
      <i class='bx bxs-chevron-down arrow' ></i>
    </div>
    <ul class="sub-menu">
      <li><a class="link_name" href="#"></a></li>
      <li><a href="singlepayment.php">Single Payment</a></li>
      <li><a href="paymenthistory.php">Payment History</a></li>
    
    </ul>
    
  </li>
     
  </li>
      <li>
        <div class="iocn-link">
        <a href="center.php">
            <i class='bx bxs-report' ></i>
            <span class="link_name">View Centers</span>
          </a>
          
        </div>
  </li>


  
  <li>
    <div class="iocn-link">
      <a href="#">
        <i class='bx bx-cog' ></i>
        <span class="link_name">Reports</span>
      </a>
      <i class='bx bxs-chevron-down arrow'></i>
    </div>
    <ul class="sub-menu">
    <li><a href="dayendreport.php">Center Report</a></li>
    <li><a href="repayment.php">Repayment Report</a></li>
      
      <li><a href="areasreport.php">Arriarse Report</a></li>
      <li><a href="notpaid.php">Not Paid Report</a></li>
      <li><a href="lending.php">Lending Report</a></li>
      <?php if ($status == 'admin'): ?>
      <li><a href="branchday.php">Branch Report</a></li>
      <?php endif; ?>
    </ul>
  </li>

      
        <?php if ($status == 'admin'): ?>
  <li>
    <div class="iocn-link">
      <a href="#">
        <i class='bx bx-cog' ></i>
        <span class="link_name">Settings</span>
      </a>
      <i class='bx bxs-chevron-down arrow'></i>
    </div>
    <ul class="sub-menu">
    <li><a href="addCenter.php">New Branch</a></li>
    <li><a href="holiday.php">Add Holiday</a></li>
      <li><a href="registeruser.php">New Executives</a></li>
      <li><a href="manage_ex.php">Manage Executives</a></li>
 <li><a href="managecus.php">Manage Customers</a></li>
      <li><a href="centermanage.php">Manage Centers</a></li>
    </ul>
  </li>
<?php endif; ?>

<?php if ($status == 'admin' || $status == 'executive' || $status == 'branch_manager'): ?>
<li>
<div class="iocn-link">
  <a href="addcent.php">
    <i class='bx bx-group' ></i>
    <span class="link_name">New Center</span>
  </a>
</div>
</li>
<?php endif; ?>
<?php if ($status == 'admin' || $status == 'executive' || $status == 'branch_manager'): ?>
  <li>
    <div class="iocn-link">
      <a href="addgroup.php">
        <i class='bx bx-group' ></i>
        <span class="link_name">New Group</span>
      </a>
    </div>
  </li>
<?php endif; ?>


    <li>
      <a href="login-user.php">
        <div class="profile-details">
          <div class="profile-content">
            <img src="image1.png" alt="profileImg">
          </div>
          <div class="name-job">
          <a href="login-user.php" class="logout-link">
   
    <span class="link_name">Logout</span>
    <i class='bx bx-log-out'></i>
  </a>
          </div>
        </div>
       
      </a>
    </li>
  </ul>
</div>



<section class="home-section">
      <div class="home-content">

    <i class='bx bx-menu'></i>


</div>
    
<?php if ($registrationSuccess): ?>
    <div class="success-message">
        User registered successfully!
    </div>
<?php endif; ?>
    <div>
        <h2> Executive Registartion</h2>
</div>
<hr>
    <form action="registeruser.php" method="post">
    <div class="flex-row">
    <div class="flex-col">
    <label for="branch">Branch:</label>
    
    <select name="branch" id="branch" onchange="updateBranchCode()" required>
    <option value="">Select Branch Name</option>
    <?php
   $sql = "SELECT DISTINCT bname , bcode FROM branch ORDER BY bname ASC";
    $result = mysqli_query($con, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="' . htmlspecialchars($row['bname']) . '" data-bcode="' . htmlspecialchars($row['bcode']) . '">' . htmlspecialchars($row['bname']) . '</option>';
    }
    ?>
</select>
</div>



<div class="flex-col">
    <label for="branchCode">Branch Code:</label>

        <input type="text" name="branchCode" id="branchCode" required readonly>

</div>
        </div>
        
        
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        
        <label for="cpassword">Confirm Password:</label>
        <input type="password" name="cpassword" required>

        <label for="status">Role:</label>
<select name="status" id="status" required>
    <option value="">Select Role</option>
    <option value="executive">Executive</option>
    <option value="branch_manager">Branch Manager</option>
</select>
        
        <a href="newloan.php"> <button type="submit" name="register">Register</button></a>
    </form>
    <script>
function updateBranchCode() {
    // Get the selected branch option
    var selectedBranch = document.getElementById('branch').selectedOptions[0];
    // Get the branch code from the data-bcode attribute
    var branchCode = selectedBranch.getAttribute('data-bcode');
    // Set the branch code field
    document.getElementById('branchCode').value = branchCode;
}

    let arrow = document.querySelectorAll(".arrow");
    for (var i = 0; i < arrow.length; i++) {
      arrow[i].addEventListener("click", (e) => {
        let arrowParent = e.target.parentElement.parentElement;//selecting main parent of arrow
        arrowParent.classList.toggle("showMenu");
      });
    }
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".bx-menu");
    console.log(sidebarBtn);
    sidebarBtn.addEventListener("click", () => {
      sidebar.classList.toggle("close");
    });


  </script>



</body>
</html>

