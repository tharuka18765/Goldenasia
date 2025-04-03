<?php
session_start();
require "connecton.php"; 

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}

$user = $_SESSION['user'];
$status = $user['status'];
$bname = $user['bname']; // Get branch name from session

$centerAddedSuccess = false;

// Handle form submission
if (isset($_POST['add_center'])) {
    // Retrieve and sanitize form data
    $branch = mysqli_real_escape_string($con, $_POST['branch']);
    $branchCode = mysqli_real_escape_string($con, $_POST['branchCode']);
    $day = mysqli_real_escape_string($con, $_POST['day']);
    $centerName = mysqli_real_escape_string($con, $_POST['centerName']);
    $ccode = mysqli_real_escape_string($con, $_POST['ccode']);
    $group = mysqli_real_escape_string($con, $_POST['group']);
    $name = mysqli_real_escape_string($con, $_POST['name']);

    // Insert query
    $insertQuery = "INSERT INTO branch (bname, bcode, day, center, ccode, `group`, name) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'sssssss', $branch, $branchCode, $day, $centerName, $ccode, $group, $name);

    if (mysqli_stmt_execute($stmt)) {
        $centerAddedSuccess = true; // Set to true when addition is successful
    } else {
        echo "Error: " . mysqli_error($con);
    }

    mysqli_stmt_close($stmt);
}

// Fetch branches based on user status
$branches = [];
if ($status === 'branch_manager') {
    $selectQuery = "SELECT DISTINCT bname, bcode FROM branch WHERE bname = ?";
    $stmt = mysqli_prepare($con, $selectQuery);
    mysqli_stmt_bind_param($stmt, 's', $bname);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $selectQuery = "SELECT DISTINCT bname, bcode FROM branch";
    $result = mysqli_query($con, $selectQuery);
}

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $branches[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($con);
}

// Fetch executives based on user status
$executives = [];
if ($status === 'branch_manager') {
    $execQuery = "SELECT DISTINCT name FROM usertable WHERE bname = ?";
    $stmt = mysqli_prepare($con, $execQuery);
    mysqli_stmt_bind_param($stmt, 's', $bname);
    mysqli_stmt_execute($stmt);
    $execResult = mysqli_stmt_get_result($stmt);
} else {
    $execQuery = "SELECT DISTINCT name FROM usertable";
    $execResult = mysqli_query($con, $execQuery);
}

if ($execResult) {
    while ($row = mysqli_fetch_assoc($execResult)) {
        $executives[] = $row;
    }
} else {
    echo "Error: " . mysqli_error($con);
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Center</title>
    <link rel="stylesheet" href="styledash.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    margin-top:880px;
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
      <a href="newloan.php">
        <i class='bx bx-cog' ></i>
        <span class="link_name">New Loan</span>
      </a>
      <i class='bx bxs-chevron-down arrow' ></i>
    </div>
    <ul class="sub-menu">
      
      <li><a href="renewloan.php">Renew Loan</a></li>
      <li><a href="garantor.php">Garantor</a></li>
      <li><a href="old.php">Old Loans</a></li>
      
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
      <li>
        <div class="iocn-link">
        <a href="dayendreport.php">
            <i class='bx bxs-report' ></i>
            <span class="link_name">Center Report</span>
          </a>
          
        </div>
  </li>
  <?php if ($status == 'admin'): ?>
  <li>
        <div class="iocn-link">
        <a href="branchday.php">
            <i class='bx bxs-report' ></i>
            <span class="link_name">Branch Report</span>
          </a>
          
        </div>
  </li>
  <?php endif; ?>
      
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
      <li><a href="registeruser.php">New Executive</a></li>
      <li><a href="manage_ex.php">Manage Executive</a></li>
      <li><a href="addCenter.php">New Branch</a></li>
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

            <div class="container">
                <div>
                    <h2>Add New Center</h2>
                </div>
                <hr>
                <form action="addcent.php" method="post">
                    <div class="flex-row">
                        <div class="flex-col">
                            <label for="branch">Branch:</label>
                            <?php if ($status === 'executive' || $status === 'branch_manager'): ?>
                                <input type="text" name="branch" id="branch" value="<?php echo htmlspecialchars($bname); ?>" readonly>
                            <?php else: ?>
                                <select name="branch" id="branch" onchange="updateBranchCode()">
                                    <option value="">Select a branch</option>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?php echo htmlspecialchars($branch['bname']); ?>" data-code="<?php echo htmlspecialchars($branch['bcode']); ?>">
                                            <?php echo htmlspecialchars($branch['bname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="flex-col">
                            <label for="branchCode">Branch Code:</label>
                            <?php if ($status === 'executive' || $status === 'branch_manager'): ?>
                                <input type="text" name="branchCode" id="branchCode" value="<?php echo htmlspecialchars($user['bcode']); ?>" readonly>
                            <?php else: ?>
                                <input type="text" name="branchCode" id="branchCode" readonly>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex-col">
                        <label for="name">Executive Name:</label>
                        <?php if ($status === 'executive'): ?>
                            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        <?php else: ?>
                            <select name="name" id="name" required>
                                <option value="">Select an executive</option>
                                <?php foreach ($executives as $exec): ?>
                                    <option value="<?php echo htmlspecialchars($exec['name']); ?>">
                                        <?php echo htmlspecialchars($exec['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <label for="day">Select a Day:</label>
                    <select id="day" name="day" required>
                        <option value="" disabled>Select a day</option>
                        <option value="Monday" <?php if(date('l') === 'Monday') echo 'selected'; ?>>Monday</option>
                        <option value="Tuesday" <?php if(date('l') === 'Tuesday') echo 'selected'; ?>>Tuesday</option>
                        <option value="Wednesday" <?php if(date('l') === 'Wednesday') echo 'selected'; ?>>Wednesday</option>
                        <option value="Thursday" <?php if(date('l') === 'Thursday') echo 'selected'; ?>>Thursday</option>
                        <option value="Friday" <?php if(date('l') === 'Friday') echo 'selected'; ?>>Friday</option>
                        <option value="Saturday" <?php if(date('l') === 'Saturday') echo 'selected'; ?>>Saturday</option>
                        <option value="Sunday" <?php if(date('l') === 'Sunday') echo 'selected'; ?>>Sunday</option>
                    </select>

                    <label for="centerName">Center Name:</label>
                    <input type="text" name="centerName" required>

                    <label for="ccode">Center Code:</label>
                    <input type="text" name="ccode" id="ccode" required>
                    
                    <label for="group">Group Number:</label>
                    <select name="group" required>
                        <?php
                        $nextGroup = isset($nextGroup) ? $nextGroup : "001"; // Assuming $nextGroup holds the current value or default to "001"
                        for ($i = 1; $i <= 20; $i++) {
                            $formattedNumber = sprintf("%03d", $i); // Format the number with leading zeros
                            echo "<option value=\"$formattedNumber\"" . ($nextGroup == $formattedNumber ? " selected" : "") . ">$formattedNumber</option>";
                        }
                        ?>
                    </select>
                    
                    <button type="submit" name="add_center">Add Center</button>
                </form>
            </div>
        </div>
    </section>
    <script>
        function updateBranchCode() {
            var branchSelect = document.getElementById('branch');
            var selectedOption = branchSelect.options[branchSelect.selectedIndex];
            var branchCodeInput = document.getElementById('branchCode');
            branchCodeInput.value = selectedOption.getAttribute('data-code');
        }

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
                let arrowParent = e.target.parentElement.parentElement; // Selecting main parent of arrow
                arrowParent.classList.toggle("showMenu");
            });
        }

        let sidebar = document.querySelector(".sidebar");
        let sidebarBtn = document.querySelector(".bx-menu");
        sidebarBtn.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });
    </script>
</body>
</html>
