<?php
session_start();
require "connecton.php"; // Corrected the filename to "connection.php"

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}

// Get user information
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

// Check if form is submitted
$message = ''; // Variable to store success or error messages

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['date']) && isset($_POST['executive_name']) && isset($_POST['plot']) && isset($_POST['bname'])) {
    // Get form data
    $date = $_POST['date'];
    $executive_name = $_POST['executive_name'];
    $plot = $_POST['plot'];
    $bname = $_POST['bname'];

    // Check if the plot has already been inserted for the same date and name
    $checkQuery = $con->prepare("SELECT * FROM plots WHERE date = ? AND name = ?");
    $checkQuery->bind_param("ss", $date, $executive_name);
    $checkQuery->execute();
    $checkResult = $checkQuery->get_result();

    if ($checkResult->num_rows > 0) {
        // Plot already inserted
        $message = "Plot has already been submitted for this date and name.";
    } else {
        // Prepare an INSERT statement
        $stmt = $con->prepare("INSERT INTO plots (date, name, bname, plot, collection, newloan, balance) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Error preparing statement: " . $con->error); // Check for errors in preparing the statement
        }

        $zero = 0; // Default value for collection, newloan, and balance
        $stmt->bind_param("sssssii", $date, $executive_name, $bname, $plot, $zero, $zero, $zero); // Adjusted data types and placeholders

        // Execute the statement
        if ($stmt->execute()) {
            $message = "Record inserted successfully";
        } else {
            $message = "Error inserting record: " . $stmt->error;
        }

        $stmt->close(); // Close the statement
    }

    $checkQuery->close(); // Close the check query
}
?>




<!DOCTYPE html>
<!-- Coding by CodingNepal | www.codingnepalweb.com -->
<html lang="en" dir="ltr">

<head>
    
  <title> Golden Asia </title>
  <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">

  <style>
body > div > section > div.home-content > div > form > input[type=text]{
  margin-right:50px;
  width:300px;
  border:1px solid black;
  color:black;
}
body > div > section > div.home-content > div > form > button{
  width:180px;
  height:35px;
  font-weight:500;
    text-transform:uppercase;
   border-radius:4px;
   cursor: pointer;
    border:none;
    background-color: #FAA300;
    color: white;

}
body > div > section > div.home-content > div > span{
  margin-right:100px;
}

.top-bar button:hover {
  background-color: black;
            color:white;
}

/* Customer profile styling */
.customer-profile {
    margin-top: 20px;
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-width: 600px;
    margin-left: 70px;
    margin-right: auto;
    text-align: left;
}

.customer-profile img {
    display: block;
    margin: auto;
    width: 150px;
    height: 150px;
    border-radius: 50%; /* Circular image */
    margin-bottom: 20px;
}

.customer-profile p {
    font-size: 16px;
    line-height: 1.5;
    color: #333;
    margin-bottom: 10px;
}

.customer-profile p span {
    font-weight: bold;
}
.guarantor-details {
    background: #ffffff;
    padding: 20px;
    margin-top: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    max-width: 600px;
    margin-left: 70px;
    margin-right: auto;
}

.guarantor-details h3 {
    color: #333;
    margin-bottom: 15px;
}

.guarantor-details div {
    margin-bottom: 10px;
    padding: 10px;
    background: rgba(245, 245, 245, 1); /* Light grey background for each guarantor */
    border-radius: 5px;
}

.guarantor-details p {
    font-size: 16px;
    line-height: 1.5;
    color: #333;
    margin: 5px 0;
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}



.form-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 1100px;
    margin-top: 30px;
    margin-left:50px;
    background: #ffffff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.input-container {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    margin-right: 10px;
}
#bname{
  height:40px;
}

.input-container label {
    margin-right: 15px;
}

.input-container input[type="date"],
.input-container input[type="text"] {
    padding: 8px;
    margin-right: 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
}


.submit-button,body > div > section > div.card > div > button {
  margin-top:25px;
    width: 180px;
    height: 35px;
    font-weight: 500;
    text-transform: uppercase;
    border-radius: 4px;
    cursor: pointer;
    border: none;
    background-color: #FAA300;
    color: white;
}

.submit-button,body > div > section > div.card > div > button:hover {
    background-color: black;
    color: white;
}
body > div > section > div.card > div > button{
  
  margin-bottom:10px;
  height: 35px;
}
/* Adjust the last input container's margin */
.input-container:last-child {
    margin-right: 0;
}
.card {
    width: 90%;
    
    margin-left:50px;
    padding: 20px;
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    cursor: pointer;
}
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
     background-color: #fff;
}

  </style>
</head>

<body>

<?php
    
    if (!isset($_SESSION['user'])) {
        header('location: login-user.php');
        exit();
    }
    $user = $_SESSION['user'];
    $status = $user['status'];

    // Database connection
    require 'connecton.php'; // Ensure your database connection file is correct

    $customer = null;
    
    if (isset($_POST['search'])) {
      $searchTerm = mysqli_real_escape_string($con, $_POST['search_term']);
      
      // Fetch Customer details
      $customerQuery = $con->prepare("SELECT bname, center, name, cname, nic, address, phone1, phone2, loan_amount, full_loan, loan_balance, image FROM customer WHERE nic = ? OR customer_code = ?");
      $customerQuery->bind_param("ss", $searchTerm, $searchTerm);
      $customerQuery->execute();
      $result = $customerQuery->get_result();
      $customer = $result->fetch_assoc();
      

      $loan_balance_stmt = $con->prepare("SELECT loan_balance FROM customer WHERE nic = ? OR customer_code = ?");
if (!$loan_balance_stmt) {
    die("Error preparing loan balance statement: " . $con->error);
}

$loan_balance_stmt->bind_param("ss", $searchTerm, $searchTerm);
$loan_balance_stmt->execute();
$loan_balance_stmt->bind_result($loan_balance);
$loan_balance_stmt->fetch();
$loan_balance_stmt->close();

      $arrears_stmt = $con->prepare("SELECT SUM(arriarse) AS arriarse FROM paymentdates WHERE nic = ? OR customer_code = ?");
      if (!$arrears_stmt) {
          die("Error preparing arrears statement: " . $con->error);
      }
  
      $arrears_stmt->bind_param("ss", $searchTerm, $searchTerm);
      $arrears_stmt->execute();
      $arrears_stmt->bind_result($arrears_sum);
      $arrears_stmt->fetch();
      $arrears_stmt->close();

      if ($loan_balance == 0) {
        $arrears_sum = 0;
    }
      
      // Fetch Guarantor details
      $guarantorQuery = $con->prepare("SELECT gname, gNic, gAddress, gPhone FROM garantor WHERE cnic = ?");
      $guarantorQuery->bind_param("s", $searchTerm);
      $guarantorQuery->execute();
      $guarantorResult = $guarantorQuery->get_result();
      $guarantors = [];
      while ($row = $guarantorResult->fetch_assoc()) {
          $guarantors[] = $row;
      }
      $customerQuery->close();
      $guarantorQuery->close();
  }
  
    ?>

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
                <div class="top-bar">
                    <i class='bx bx-menu'></i>
                    <form action="" method="POST">
                    <input type="text" placeholder="Enter NIC or Customer Code" name="search_term" required>
                        <button type="submit" name="search">Search</button>
                        
                    </form>
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
            </div>

            <form action="" method="post" class="form-container">
    <div class="input-container">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>" readonly>
    </div>
    <div class="input-container">
        <label for="executive_name">Executive Name:</label>
        <input type="text" id="executive_name" name="executive_name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
    </div>
    <div class="input-container">
        <label for="executive_name">Branch:</label>
        <input type="text" id="bname" name="bname" value="<?php echo htmlspecialchars($user['bname']); ?>" readonly>
    </div>
    <div class="input-container">
        <label for="plot">Plot :</label>
        <input type="text" id="plot" name="plot" required placeholder="Rs.0000.00">

    </div>
    <button type="submit" class="submit-button">Submit</button>
</form>
<?php if ($message): ?>
            <div class="alert alert-info" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>


            <?php if ($customer): ?>
                <div class="customer-profile">
                  <h2> Customer Profile </h2>
                    <img src="customer_images/<?= htmlspecialchars($customer['image']) ?>" alt="Customer Image" style="width: 150px; height: 150px;">
                    <p>Branch Name: <?= htmlspecialchars($customer['bname']) ?></p>
                    <p>Center Name: <?= htmlspecialchars($customer['center']) ?></p>
                    <p>Name: <?= htmlspecialchars($customer['cname']) ?></p>
                    <p>NIC: <?= htmlspecialchars($customer['nic']) ?></p>
                    <p>Address: <?= htmlspecialchars($customer['address']) ?></p>
                    <p>Phone Number: <?= htmlspecialchars($customer['phone1']) ?></p>
                    <p>Phone Number 2: <?= htmlspecialchars($customer['phone2']) ?></p>
                    <p>Loan Amount: <?= htmlspecialchars($customer['loan_amount']) ?></p>
                    <p>Full Loan Amount: <?= htmlspecialchars($customer['full_loan']) ?></p>
                    <p>Balance: <?= htmlspecialchars($loan_balance) ?></p>
                    <p>Arrirase : <?= htmlspecialchars($arrears_sum) ?></p>
                </div>

                <?php if (!empty($guarantors)): ?>
                    <div class="guarantor-details">
                        <h3>Guarantor Details</h3>
                        <?php foreach ($guarantors as $guarantor): ?>
                            <div>
                                <p>Name: <?= htmlspecialchars($guarantor['gname']) ?></p>
                                <p>NIC: <?= htmlspecialchars($guarantor['gNic']) ?></p>
                                <p>Address: <?= htmlspecialchars($guarantor['gAddress']) ?></p>
                                <p>Phone: <?= htmlspecialchars($guarantor['gPhone']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                
            

            <?php endif; ?>
              
   <div class="card">
    <div class="card-header">
            <h3>Get Details</h3>
            <button onclick="navigateToStock()" class="navigate-button">Stock</button>
        </div>
    
</div>
            
        </section>
    </div>
  <script>
  function navigateToStock() {
            window.location.href = 'stock.php';
        }
  function navigateToRepayment() {
            window.location.href = 'repayment.php';
        }
function navigateTomanage() {
            window.location.href = 'managecus.php';
        }
        function navigateTocenter() {
            window.location.href = 'center.php';
        }
        function navigateToArriarse() {
            window.location.href = 'areasreport.php';
        }
         function navigateToNotPaid() {
            window.location.href = 'notpaid.php';
        }
 function navigateToLending() {
            window.location.href = 'lending.php';
        }
        function navigateToCenter() {
            window.location.href = 'centermanage.php';
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