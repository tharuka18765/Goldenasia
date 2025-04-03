<?php
session_start();
require "connecton.php"; 
if (!isset($_SESSION['user'])) {
  header('location: login-user.php');
  exit();
}
$user = $_SESSION['user'];
$status = $user['status'];// Assume correct file name is "connection.php"

// Add your admin check back here
$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
}

$centerAddedSuccess = false;

if (isset($_POST['add_center'])) {
    // Retrieve and sanitize form data
    $branch = mysqli_real_escape_string($con, $_POST['branch']);
    $branchCode = mysqli_real_escape_string($con, $_POST['branchCode']);
    $day = mysqli_real_escape_string($con, $_POST['day']);
    $centerName = mysqli_real_escape_string($con, $_POST['centerName']);
    $ccode = mysqli_real_escape_string($con, $_POST['ccode']); // Capture the center code
    $group = mysqli_real_escape_string($con, $_POST['group']);

    // Modify the insert query to include the ccode
    $insertQuery = "INSERT INTO branch (bname, bcode,day, center, ccode, `group`) VALUES (?, ?, ?,?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);
    // Bind the new parameter
    mysqli_stmt_bind_param($stmt, 'ssssss', $branch, $branchCode,$day, $centerName, $ccode, $group);

    if (mysqli_stmt_execute($stmt)) {
        $centerAddedSuccess = true; // Set to true when addition is successful
    } else {
        // Handle error
        echo "Error: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Center</title>
    <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Reuse the CSS styling from your previous form -->
    <style>
body {
    margin: 0;
    padding: 0;
    background: url('your-background-image.jpg') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Arial', sans-serif;
}

form {
    margin-top:500px;
    max-width: 900px;
    margin: 50px auto;
    background: transparent;
    
}
body > div > section > div > div > hr{
    margin-left:10px;
    width: 100%;
    border: 1px solid #FAA300;
}
label {
    font-weight: bold;
    color: #333;
    margin-top: 15px;
}

button {
    font-weight: 500;
    text-transform: uppercase;
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
    background-color: #333;
    color: white;
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

select, input[type="text"], input[type="email"], input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 20px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}


.success-message, .error-message {
    color: white;
    text-align: center;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    font-size: 18px;
    position: fixed;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    animation: fadeOut 2s ease-out 3s forwards;
}

.success-message {
    background-color: #4CAF50; /* Green background for success */
}

.error-message {
    background-color: #f44336; /* Red background for errors */
}

@keyframes fadeOut {
    to {
        opacity: 0;
        visibility: hidden;
    }
}
body > div > section > div > div{
    margin-top:700px;
    margin-left:40px;

    
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}
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
        


    
<?php if ($centerAddedSuccess): ?>
    <div class="success-message">
        Center added successfully!
    </div>
<?php endif; ?>

<div class ="container">
<div>
    <h2>Add New Center</h2>
</div>
<hr>
<form action="addCenter.php" method="post">
    <div class="flex-row">
        <div class="flex-col">
            <label for="branch">Branch Name:</label>
            <input type="text" name="branch" id="branch" required>
        </div>
        <div class="flex-col">
            <label for="branchCode">Branch Code:</label>
            <input type="text" name="branchCode" id="branchCode" required>
        </div>
    </div>

    <label for="day">Select a Day:</label>
<select id="day" name="day" required>
    <option value="" disabled selected>Select a day</option>
    <option value="Monday">Monday</option>
    <option value="Tuesday">Tuesday</option>
    <option value="Wednesday">Wednesday</option>
    <option value="Thursday">Thursday</option>
    <option value="Friday">Friday</option>
    <option value="Saturday">Saturday</option>
    <option value="Sunday">Sunday</option>
</select>
    <label for="centerName">Center Name:</label>
    <input type="text" name="centerName" required>

    <label for="ccode">Center Code:</label>
    <input type="text" name="ccode" id="ccode" required>
    
    <label for="group">Group:</label>
    <input type="text" name="group" required>
    
    <button type="submit" name="add_center">Add Center</button>
</form>

</div>
</section>
<script>
window.onload = function() {
    // Check if the success message exists
    var successMessage = document.querySelector('.success-message');
    if (successMessage) {
        // Hide the success message after 5 seconds
        setTimeout(function() {
            successMessage.style.display = 'none';
        }, 5000);
    }
};


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
