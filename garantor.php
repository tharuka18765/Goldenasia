<?php

session_start();

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
$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
}

if (isset($_POST['submit'])) {
    // Database connection variables
    $host = 'localhost'; // Or your DB host
    $user = 'root'; // Or your DB user
    $password = ''; // Or your DB password
    $dbname = 'micro'; // Your database name

    // Create connection
    $conn = new mysqli($host, $user, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
   
    // Sanitize inputs
    $customerNic = mysqli_real_escape_string($conn, $_POST['customerNic']);
    $customerName = mysqli_real_escape_string($conn, $_POST['customerName']);
    $grantorNic = mysqli_real_escape_string($conn, $_POST['grantorNic']);
 
    $street = mysqli_real_escape_string($conn, $_POST['street']);
$city = mysqli_real_escape_string($conn, $_POST['city']);
$state = mysqli_real_escape_string($conn, $_POST['state']);
$postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    
    $grantorPhone = mysqli_real_escape_string($conn, $_POST['grantorPhone']);

    $grantorAddress = $street . ', ' . $city . ', ' . $state . ' ' . $postal_code;
    // SQL to insert data
    $sql = "INSERT INTO garantor (cNic, gname, gNic, gAddress, gPhone) VALUES (?, ?, ?, ?, ?)";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
    }

    // Bind parameters and execute
    $stmt->bind_param("sssss", $customerNic, $customerName, $grantorNic, $grantorAddress, $grantorPhone);
    if ($stmt->execute()) {
        echo "Grantor registered successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Loan Grantor</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    background: url('path-to-your-background.jpg') no-repeat center center fixed;
    background-size: cover;
   
}

form {
    width:850px;
    background:transparent;
    margin-top:35px;
    border-radius: 8px;
    margin-left:100px;
    
}
body > div > section > hr {
    margin-left:10px;
    width: 85%;
    border: 1px solid #FAA300; /* Set the height and color of the hr line */
     /* Center the hr element within its container */
}

h3 {
    color: #333;
}

input[type="text"],
input[type="tel"],
input[type="number"],
input[type="email"],
input[type="password"],
select,
input[type="file"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

body > div > section > form > button{
    margin-left:100px;
    width:60%;
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
}

input[type="submit"]:hover {
    background-color: black;
    color:white;
}


.user-details p {
    color: #333;
}
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
    font-size: 16px;
    cursor: pointer;
}
.form-row {
    display: flex;
    justify-content: space-between; /* This separates the two dropdowns */
    margin-bottom: 20px; /* Adds some space below the row */
}

.form-group {
    flex: 1; /* Allows each .form-group to take up equal space */
    margin-right: 10px; /* Adds some space between the dropdowns */
}

.form-group:last-child {
    margin-right: 0; /* Removes margin from the last .form-group */
}

/* Existing styles */

/* Responsive adjustments */
/* Existing styles above */

/* Responsive adjustments */
@media (max-width: 768px) {
    .flex-row {
        flex-direction: column;
    }

    .flex-col {
        margin-right: 0;
        margin-bottom: 20px; /* Add space between vertically stacked items */
    }
    
    /* Optional: Adjust input and select field sizes for smaller screens */
    input[type="text"],
    input[type="tel"],
    input[type="number"],
    input[type="file"],
    select,
    input[type="submit"] {
        width: 100%; /* Full width */
    }
}

@media only screen and (max-width: 480px) {
    form {
        width: 100%; /* Allow the form to take full width on very small screens */
        margin: 0; /* Adjust margins as needed for small screens */
    }

    /* Adjust padding and font sizes for smaller screens */
    input, select {
        padding: 8px;
        font-size: 14px; /* Make text slightly smaller for small devices */
    }

    label {
        margin-top: 20px;
        font-size: 14px;
    }
}
body > form > div.user-details{
    font-size:20px;
}
.header-container {
    display: flex; /* Use flexbox for horizontal layout */
    align-items: center; /* Center items vertically */
    justify-content: space-between; /* Space out the header and user details */
}

.header-container h2 {
    margin-right: 20px; /* Add space between the header and user details */
}
.user-details {
            display: flex; /* Align items in a row */
            justify-content: start; /* Start aligning items from the start */
            align-items: center; /* Center items vertically */
    
            background:transparent; /* Light grey background */
            /* Rounded corners */
            margin: 10px 0; 
            font-size: 22px;/* Margin for spacing */
            /* Soft shadow for depth */
        }
        
       .user-details span, .user-details input[type="text"] {
        background:transparent;
            margin-right: 20px; /* Space between elements */
            font-size: 20px; /* Uniform font size */
        }

        .user-details .branch-name, .user-details .branch-code {
            font-weight: bold; /* Make branch name and code bold */
        }
        #currentDate{
            border:none;
        }
.branch-name, .branch-code {
    color: #333; /* Dark text for readability */
    font-weight: bold; /* Make text bold */
   /* Appropriate text size */
}

.branch-code {
    background:transparent; /* Blue background for the code */
    color: black; /* White text for contrast */
    padding: 2px 6px; /* Padding around the text */
     /* Rounded corners */
}
.message-container {
        text-align: center;
        margin-top: 20px;
    }

    .success-message {
        color: #fff;
        background-color: #4CAF50;
        padding: 15px;
        border-radius: 5px;
        width: 60%;
        margin: auto;
    }

    .error-message {
        color: #fff;
        background-color: #f44336;
        padding: 15px;
        border-radius: 5px;
        width: 60%;
        margin: auto;
    }
    .custom-file-upload {
    display: inline-block;
    padding: 10px 20px;
    cursor: pointer;
    background-color: #4CAF50;
    color: white;
    border-radius: 5px;
    margin-right: 10px;
    margin-bottom:15px;
}

#file-chosen {
    border:1px solid black;
    margin-left: 10px;
    padding: 10px;
    background-color: #fff;
    border-radius: 5px;
    color: #333;
}
body > div > section > h2{
    margin-left:100px;
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}
body > div > section > div > h2{
  margin-left:100px;
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

<h2> Register Garantor </h2>
<hr>

<form action="garantor.php" method="post">
    <div class="form-group">
        <label for="customerNic"style="font-weight:bold; font-family:Arial;"">Customer NIC:</label>
        <input type="text" id="customerNic" name="customerNic" required>
    </div>
    <div class="form-group">
        <label for="customerName"style="font-weight:bold; font-family:Arial;">Grantor Name:</label>
        <input type="text" id="customerName" name="customerName" required>
    </div>
    <div class="form-group">
        <label for="grantorNic"style="font-weight:bold; font-family:Arial;">Grantor NIC:</label>
        <input type="text" id="grantorNic" name="grantorNic" required>
    </div>
    <div class="form-row">
<div class="form-group">
    <label for="street"style="font-weight:bold; font-family:Arial;">Street</label>
    <input type="text" name="street" placeholder="Street" required>
</div>
<div class="form-group">
    <label for="city"style="font-weight:bold; font-family:Arial;">City</label>
    <input type="text" name="city" placeholder="City" required>
</div>
        </div>
        <div class="form-row">
<div class="form-group">
    <label for="state"style="font-weight:bold; font-family:Arial;">State</label>
    <input type="text" name="state" placeholder="State" required>
</div>
<div class="form-group">
    <label for="postal_code"style="font-weight:bold; font-family:Arial;">Disrict</label>
    <input type="text" name="postal_code" placeholder="Postal Code" required>
</div>
        </div>
    <div class="form-group">
        <label for="grantorPhone"style="font-weight:bold; font-family:Arial;">Grantor Phone Number:</label>
        <input type="tel" id="grantorPhone" name="grantorPhone" required>
    </div>
    <button type="submit" name="submit">Register Grantor</button>
</form>
</div>

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


  </script>
</body>
</html>
