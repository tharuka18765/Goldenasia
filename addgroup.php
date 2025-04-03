<?php
session_start();
require "connecton.php"; // Ensure this is the correct path to your connection file



if (!isset($_SESSION['user'])) {
  header('location: login-user.php');
  exit();
}
$user = $_SESSION['user'];
$status = $user['status'];




$query = "SELECT bname, bcode, center FROM branch WHERE id = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $bname, $bcode, $center);
if (mysqli_stmt_fetch($stmt)) {
    // Set session variables with actual data
    $_SESSION['bname'] = $bname;
    $_SESSION['bcode'] = $bcode;
    $_SESSION['center'] = $center;
} 


// Adjust this line based on how your session variables are named

// Query to fetch centers that match the branch code
$centersQuery = "SELECT center FROM branch WHERE bcode = ?";
$stmt = mysqli_prepare($con, $centersQuery);
mysqli_stmt_bind_param($stmt, 's', $branchCode);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$centers = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);

$centersQuery = "SELECT DISTINCT center,ccode FROM branch  WHERE bcode = ? "; // Example, adjust based on your actual columns and relationships
$stmt = mysqli_prepare($con, $centersQuery);
mysqli_stmt_bind_param($stmt, "s", $user['bcode']); // Assuming 'bcode' is in your session and is the filter criteria
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt); // Get the result of the query

$query = "SELECT MAX(`group`) AS max_group FROM branch WHERE bname = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "s", $escapedBranchName); // Assuming $escapedBranchName is defined earlier
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $maxGroup);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Calculate the next group number
$nextGroup = $maxGroup ? $maxGroup + 1 : 1;

$centers = mysqli_fetch_all($result, MYSQLI_ASSOC);


$groupAddedSuccess = false;
$errorMessage = ''; 

if (isset($_POST['submit'])) {
    $bname = mysqli_real_escape_string($con, $_POST['bname']);
    $bcode = mysqli_real_escape_string($con, $_POST['bcode']);
    $center = mysqli_real_escape_string($con, $_POST['center']);
    $centerCode = mysqli_real_escape_string($con, $_POST['centerCode']);
    $groupName = mysqli_real_escape_string($con, $_POST['groupName']);

    // Check for duplicate group number in the same center
    $duplicateCheckQuery = "SELECT * FROM branch WHERE center = ? AND `group` = ?";
    $stmt = mysqli_prepare($con, $duplicateCheckQuery);
    mysqli_stmt_bind_param($stmt, "ss", $center, $groupName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $errorMessage = "A group with this number already exists in the center.";
    } else {
        // Proceed with inserting the new group
        $insertQuery = "INSERT INTO branch (bname, bcode, center, ccode, `group`) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $insertQuery);
        mysqli_stmt_bind_param($stmt, 'sssss', $bname, $bcode, $center, $centerCode, $groupName);


        if (mysqli_stmt_execute($stmt)) {
            $groupAddedSuccess = true;
        } else {
            $errorMessage = "Error: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Group</title>
    <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <style>

body {
    margin: 0;
    padding: 0;
    font-family: 'Arial', sans-serif;
    position: relative;
}

h2{
    margin-bottom:8px;
}
body > div > section > div > div{
    margin-top:800px;
    max-width: 900px;
}
form {
    margin-top:600px;
    max-width: 900px;
    margin: 50px auto;
    background: transparent;
    width:750px;
    
    
    border-radius: 5px; /* Optional: Adds rounded corners to your form */
}

label {
    display: block; /* Makes the label take the full width */
    font-weight: bold;
    color: #333;
    margin-top: 15px;
}

input[type="text"], input[type="email"], input[type="password"], select {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box; /* Ensures padding doesn't affect the overall width */
}

button {
    font-weight: 500;
    text-transform: uppercase;
    width: 100%;
    padding: 10px;
    background-color: #FAA300; /* Customize button color to fit your theme */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

button:hover {
    background-color: #333; /* Button hover effect */
    color: white;
}

.success-message {
    background-color: #4CAF50; /* Green background for success */
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
    animation: fadeOut 2s ease-out 3s forwards; /* Waits 3 seconds and then fades out in 2 seconds */
}

.error-message {
    background-color: #f44336; /* Red background for errors */
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
    animation: fadeOut 2s ease-out 3s forwards; /* Similar fade-out effect for consistency */
}

@keyframes fadeOut {
    to {
        opacity: 0;
        visibility: hidden;
    }
}

/* If you want the inputs to be non-editable visually distinct, you could add: */
input[readonly] {
    background-color: #f3f3f3; /* Light grey background */
    cursor: not-allowed; /* Shows a 'not allowed' cursor on hover */
}

body > div > section > div > div{
    margin-left:80px;
    width:2000px;
}
body > div > section > div > div > hr{
    margin-left:10px;
    width: 100%;
    border: 1px solid #FAA300;
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}
</style>

    <!-- Include your CSS here -->
</head>
<body>
<?php if ($groupAddedSuccess): ?>
    <div class="success-message">Group added successfully!</div>
<?php elseif (!empty($errorMessage)): ?>
    <div class="error-message"><?php echo $errorMessage; ?></div>
<?php endif; ?>

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

        <div class = "container">
        


<div>
    <h2>Add New Group</h2>
    
</div>
<hr>
<form action="addgroup.php" method="post">
    <label for="branchName"style="font-weight:bold; font-family:Arial;">Branch Name:</label>
    <input type="text" name="bname" id="bname" value="<?php echo htmlspecialchars($user['bname']); ?>" readonly>

    <label for="branchCode"style="font-weight:bold; font-family:Arial;">Branch Code:</label>
    <input type="text" name="bcode" id="bcode" value="<?php echo htmlspecialchars($user['bcode']); ?>" readonly>

    <label for="center"style="font-weight:bold; font-family:Arial;">Center:</label>
<select name="center" id="center" onchange="updateCenterCode()">
<option value="">Select Center</option>
    <?php foreach ($centers as $center): ?>
        <option value="<?php echo htmlspecialchars($center['center']); ?>" data-center-code="<?php echo htmlspecialchars($center['ccode']); ?>">
            <?php echo htmlspecialchars($center['center']); ?>
        </option>
    <?php endforeach; ?>
</select>

<label for="centerCode"style="font-weight:bold; font-family:Arial;">Center Code:</label>
<input type="text" name="centerCode" id="centerCode" readonly>



<label for="groupName"style="font-weight:bold; font-family:Arial;">Group Number:</label>
<select name="groupName" required>
    <?php
    $nextGroup = isset($nextGroup) ? $nextGroup : "001"; // Assuming $nextGroup holds the current value or default to "001"
    for ($i = 1; $i <= 20; $i++) {
        $formattedNumber = sprintf("%03d", $i); // Format the number with leading zeros
        echo "<option value=\"$formattedNumber\"" . ($nextGroup == $formattedNumber ? " selected" : "") . ">$formattedNumber</option>";
    }
    ?>
</select>


    
    <button type="submit" name="submit">Add Group</button>
</form>

    </div>
    </section>
<script>
function updateCenterCode() {
    var centerSelect = document.getElementById('center');
    var centerCodeInput = document.getElementById('centerCode');
    var selectedOption = centerSelect.options[centerSelect.selectedIndex];

    // Update the center code input with the value from the selected option's data attribute
    centerCodeInput.value = selectedOption.getAttribute('data-center-code');
}

// Call updateCenterCode initially to set the correct center code for the initially selected center
window.onload = function() {
    updateCenterCode();

    // Your existing messages hide logic
    var messages = document.querySelectorAll('.success-message, .error-message');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.display = 'none';
        }, 5000);
    });
};


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

