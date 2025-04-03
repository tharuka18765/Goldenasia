<?php
// singlepayment.php

session_start();
require "connecton.php"; // Corrected the filename

$user = $_SESSION['user'];
$status = $user['status']; // Assume the correct file name is "connection.php"

// Add your admin check back here

$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
} 
// Fetch branch name from session


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the action is 'edit'
    if ($_POST['action'] == 'edit') {
        // Retrieve form data
        $userId = $_POST['userId'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $bname = $_POST['bname']  ; // Set default value to '0' if empty
        $bcode = $_POST['bcode'] ;
        $status = $_POST['status'];
    
        // Update the user details in the database
        $query = "UPDATE usertable SET name=?, email=?, bname=?, bcode=?, status=? WHERE id=?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $bname, $bcode, $status, $userId);
        $success = mysqli_stmt_execute($stmt);
    
        if ($success) {
            echo "<script>showAlert('User details updated successfully.', 'success');</script>";
        } else {
            echo "<script>showAlert('Error updating user details: " . mysqli_error($con) . "', 'error');</script>";
        }
    } elseif ($_POST['action'] == 'delete') {
        // Retrieve user name from the form
        $userName = $_POST['name'];

        // Delete the user where name matches the provided user name
        $query = "DELETE FROM usertable WHERE name='$userName'";
        $result = mysqli_query($con, $query);

        if ($result) {
            echo "User $userName deleted successfully.";
        } else {
            echo "Error deleting user: " . mysqli_error($con);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Search</title>
<link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<style>
  .user-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 20px; /* Add space between user cards */
  }
  body > div > section > div:nth-child(3){
    margin-left:30px;
    margin-right:30px;
    margin-top:60px;
  }
  .user-card {
    flex: 0 1 calc(32% - 10px); /* Adjust width to fit two cards in a row with gap */
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 10px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }
  .user-card input[type="text"] {
    width: 100%;
    margin-bottom: 10px;
    padding: 8px;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  .user-card button {
    width:30%;
    font-weight:500;
    text-transform:uppercase;
   
    padding: 10px;
    background-color: #FAA300;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
    gap: 20px;
  }
  
  body > div > section > div > div > div> form > button{
    margin-left:30px;
  }
  .user-card button:hover {
    background-color: black;
    color:white;
  }
  body > div > section > div:nth-child(2) > h1{
    margin-left:60px;
  }
  .alert {
    display: none;
    padding: 20px;
    border-radius: 5px;
    margin: 20px;
}

.success {
    background-color: #d4edda;
    color: #155724;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
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


</div>

<div>
    <h1> Manage Users</h1>
    <hr>
</div>



<div>

<div id="successAlert" class="alert success"></div>

<!-- Error message box -->
<div id="errorAlert" class="alert error"></div>
<?php
// Assuming $con is your database connection

require "connecton.php"; // Corrected the filename


// Fetch user details based on the search query
$searchQuery = isset($_GET['query']) ? $_GET['query'] : '';
$query = "SELECT * FROM usertable ";
$result = mysqli_query($con, $query);

// Check if any user is found
if (mysqli_num_rows($result) > 0) {
    echo "<div class='user-container'>";
    // Display user details in a form for editing or deletion
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='user-card'>";
        echo "<form action='manage_ex.php' method='post'>";
        echo "<input type='hidden' name='userId' value='" . $row['id'] . "'>";
        echo "Name: <input type='text' name='name' value='" . $row['name'] . "'><br>";
        echo "Email: <input type='text' name='email' value='" . $row['email'] . "'><br>";
        echo "Branch: <input type='text' name='bname' value='" . $row['bname'] . "'><br>";
        echo "Branch Code: <input type='text' name='bcode' value='" . $row['bcode'] . "'><br>";
        echo "Status: <input type='text' name='status' value='" . $row['status'] . "'><br>";
        echo "<button type='submit' name='action' value='edit' onclick='return confirmEdit()'>Edit</button>";
        echo "<button type='submit' name='action' value='delete'>Delete</button>";
        echo "</form>";
        echo "</div>"; // Close the user-card div
    }
    echo "</div>"; // Close the user-container div
} else {
    // If no user found, display a messages
    echo "No user found.";
}
?>
</div>


<!-- JavaScript code for handling search and displaying user details -->
<script>
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

    function confirmEdit() {
    return confirm("Are you sure you want to edit this user?");
}

function showAlert(message, type) {
    var alertBox;
    if (type === 'success') {
        alertBox = document.getElementById('successAlert');
    } else if (type === 'error') {
        alertBox = document.getElementById('errorAlert');
    }

    alertBox.textContent = message;
    alertBox.style.display = 'block';

    // Hide the alert after 3 seconds
    setTimeout(function() {
        alertBox.style.display = 'none';
    }, 3000); // Adjust the time as needed
}
</script>

</body>
</html>
