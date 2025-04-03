<?php
session_start();
require "connecton.php"; // Corrected the typo in the file name



if (!isset($_SESSION['user'])) {
    // If the user_id is not set in the session, redirect to the login page or show an error
    echo "No user session found. Please log in.";
    exit(); // Stop further execution of the script
}


$user = $_SESSION['user']; 
$status = $user['status'];// Fetch the user ID from session

$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}

if (isset($_POST['submit'])) {
    $bname = mysqli_real_escape_string($con, $_POST['bname']);
    $bcode = mysqli_real_escape_string($con, $_POST['bcode']);
    $selectedDate = mysqli_real_escape_string($con, $_POST['date']);
    $center = mysqli_real_escape_string($con, $_POST['center']);
    $ccode = isset($_POST['ccode']) ? mysqli_real_escape_string($con, $_POST['ccode']) : '';
    $group = mysqli_real_escape_string($con, $_POST['group']);
    $executiveName = mysqli_real_escape_string($con, $_POST['executive_name']);
    $customerName = mysqli_real_escape_string($con, $_POST['customerName']);
    $customerId = mysqli_real_escape_string($con, $_POST['customerId']);
    $street = mysqli_real_escape_string($con, $_POST['street']);
    $city = mysqli_real_escape_string($con, $_POST['city']);
    $state = mysqli_real_escape_string($con, $_POST['state']);
    $postal_code = mysqli_real_escape_string($con, $_POST['postal_code']);
    $phone1 = mysqli_real_escape_string($con, $_POST['phone1']);
    $phone2 = mysqli_real_escape_string($con, $_POST['phone2']);

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0 && !empty($_FILES["image"]["name"])) {
        $target_dir = "customer_images/";
        $imageFileName = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $imageFileName;

        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            echo "Error uploading the file.";
            $imageFileName = 'default.jpg';
        }
    } else {
        $imageFileName = 'default.jpg';
    }

    $loanAmount = mysqli_real_escape_string($con, $_POST['loanAmount']);
    $type = mysqli_real_escape_string($con, $_POST['type']);
    $interest = mysqli_real_escape_string($con, $_POST['interest']);
    $period = mysqli_real_escape_string($con, $_POST['period']);
    $address = $street . ', ' . $city . ', ' . $state . ' ' . $postal_code;

    if (isset($_POST['memberno'])) {
        $selectedMemberNo = $_POST['memberno'];
        $next_member_number = $selectedMemberNo;

        $customer_code = sprintf("%s/%s/%s/%02d", $bcode, $ccode, $group, $next_member_number);
    } else {
        echo "Error: Member number is not set.";
        exit();
    }

    $loanCode = sprintf("%s/%s/%s/%02d/%s", $bcode, $ccode, $group, $next_member_number, $selectedDate);
    $startDate = new DateTime($selectedDate);
    $loanPeriodInWeeks = (int) preg_replace('/\D/', '', $period);

    if ($loanPeriodInWeeks > 0) {
        $dueDate = $startDate->modify("+{$loanPeriodInWeeks} weeks")->format('Y-m-d');
    } else {
        $dueDate = $selectedDate;
    }

    $numberOfWeeks = $period == '12' ? 12 : 13;
    $interestRate = $period == '12' ? 0.20 : 0.30;
    $fullLoanAmount = $loanAmount + ($loanAmount * $interestRate);
    $weeklyPayment = $fullLoanAmount / $numberOfWeeks;

    $documentrate = 0.01;
    $documentfee = $loanAmount * $documentrate;
    $insurancerate = 0.01;
    $insurancefee = $loanAmount * $insurancerate;

      
$customerCodeCheckQuery = "SELECT customer_code FROM customer WHERE customer_code = ?";
$customerCodeStmt = mysqli_prepare($con, $customerCodeCheckQuery);
mysqli_stmt_bind_param($customerCodeStmt, "s", $customer_code);
mysqli_stmt_execute($customerCodeStmt);
mysqli_stmt_store_result($customerCodeStmt);

if (mysqli_stmt_num_rows($customerCodeStmt) > 0) {
    echo "<script>alert('This customer number (customer code) is already taken. Please use a different number.');</script>";
    mysqli_stmt_close($customerCodeStmt);
} else {
    unset($_SESSION['error_message']);
  
    $countQuery = "SELECT COUNT(*) AS member_count FROM customer WHERE center = ? AND `group` = ?";
    $stmtCount = mysqli_prepare($con, $countQuery);
    mysqli_stmt_bind_param($stmtCount, "ss", $center, $group);
    mysqli_stmt_execute($stmtCount);
    mysqli_stmt_bind_result($stmtCount, $memberCount);
    mysqli_stmt_fetch($stmtCount);
    mysqli_stmt_close($stmtCount);

    if ($memberCount >= 5) {
        echo "<div class='message-container'><div class='error-message'>Error: The group already has the maximum number of members (5).</div></div>";
    } else {
        $payment = 0.00;
        $paymentDate = date('Y-m-d');
        $interestRate = $_POST['period'] == '12' ? 0.20 : 0.30;
        $fullLoanAmount = $loanAmount + ($loanAmount * ($interestRate));
        $weeklyPayment = $fullLoanAmount / ($period == '12' ? 12 : 13);
        $documentfee = $loanAmount * 0.01;
        $insurancefee = $loanAmount * 0.01;

        $query = "INSERT INTO customer (bname, bcode, center, ccode, `group`, name, cname, nic, address, phone1, phone2, image, loan_amount, type, interest, period, customer_code, loan_date, loan_code, due_date, payment, payment_date, loan_balance, full_loan, week_payment, document_fee, insurance_fee) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "sssssssssssssssssssssssssss", $bname, $bcode, $center, $ccode, $group, $executiveName, $customerName, $customerId, $address, $phone1, $phone2, $imageFileName, $loanAmount, $type, $interest, $period, $customer_code,  $selectedDate, $loanCode, $dueDate, $payment, $paymentDate, $fullLoanAmount, $fullLoanAmount, $weeklyPayment, $documentfee, $insurancefee);
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='message-container'><div class='success-message'>New record created successfully.</div></div>";

            $payment = 0.00;
            $arriarse = 0;
            $name = 0;
            $payment_date = 0;
            $currentDate = date('Y-m-d');
            $startDate = new DateTime( $selectedDate);
            $initialDueDate = $startDate->add(new DateInterval('P7D'));
            $numberOfPayments = 13;
            if (isset($_POST['period']) && $_POST['period'] === '12') {
                $numberOfPayments = 12;
            }

            for ($week = 1; $week <= $numberOfPayments; $week++) {
                $dueweek = $initialDueDate->format('Y-m-d');
                $query = "INSERT INTO paymentdates (loan_code, customer_code, week_number, due_date_weekly, bname, center, ccode, payment, nic, name, payment_date, week_payment, arriarse) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($con, $query);
                mysqli_stmt_bind_param($stmt, "ssissssssssss", $loanCode, $customer_code, $week, $dueweek, $bname, $center, $ccode, $payment, $customerId, $name, $payment_date, $weeklyPayment, $arriarse);
                mysqli_stmt_execute($stmt);
                $initialDueDate->add(new DateInterval('P7D'));
            }
            mysqli_stmt_close($stmt);
        } else {
            $errorMsg = mysqli_error($con);
            echo "<div class='message-container'><div class='error-message'>Error: Unable to execute the query.<br>$errorMsg</div></div>";
        }
    }
}
    }


$selectedDate = date("Ymd");
$dayOfWeek = date('l', strtotime($selectedDate));
$branchName = $user['bname'];
$userName = $user['name'];

$periodOptions = $userName === "Ravidu Laksri" ? '<option value="12">12 Weeks</option><option value="13">13 Weeks</option>' : '<option value="13">13 Weeks</option>';

$centersSql = "SELECT center FROM branch WHERE day = ? AND bname = ?";
$groupSql = "SELECT `group` FROM branch WHERE bname = ?";

$stmt = $con->prepare($centersSql);
$stmt->bind_param("ss", $dayOfWeek, $branchName);
$stmt->execute();
$centersResult = $stmt->get_result();
$centers = mysqli_fetch_all($centersResult, MYSQLI_ASSOC);

$stmt = $con->prepare($groupSql);
$stmt->bind_param("s", $branchName);
$stmt->execute();
$groupsResult = $stmt->get_result();
$groups = mysqli_fetch_all($groupsResult, MYSQLI_ASSOC);

$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Dropdown with AJAX</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    background: url('path-to-your-background.jpg') no-repeat center center fixed;
    background-size: cover;
   
}

form {
    width:1000px;
    background:transparent;
    margin-top:35px;
    border-radius: 8px;
    margin-left:140px;
    
}
body > div > section > hr {
    margin-left:10px;
    width: 85%;
    border: 1px solid #FAA300; /* Set the height and color of the hr line */
     /* Center the hr element within its container */
}
body > div > section > div.header-container > h2{
    margin-left:50px;
}
body > h2{
    margin-left:340px;
}
h3 {
    color: #333;
}

input[type="text"],
input[type="tel"],
input[type="number"],
input[type="email"],
input[type="password"],
input[type="date"],
select,
input[type="file"] {
    width: 100%;
    padding: 8px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}
body > div > section > form > div:nth-child(6) > button,body > div > section > form > div:nth-child(16) > div > button:nth-child(2),body > div > section > form > div:nth-child(16) > div > button:nth-child(3){
    margin-left:10px;
    width:200px;
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
input[type="submit"] {
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
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}

@media (max-width: 768px) {
    form {
        width: 95%;
        margin-top: 20px;
    }

    .form-row {
        flex-direction: column;
    }

    .form-group {
        margin-right: 0;
        margin-bottom: 10px;
    }

    .input-box i {
        top: 50%;
    }

    h2, h3 {
        font-size: 16px; /* Further reduced font size for smaller headers */
    }
}

@media (max-width: 480px) {

    body > div > section > form > div{
        margin-left:10px;
        width:230px;
    }
    input, select {
        padding: 6px; /* Even smaller padding */
        font-size: 14px; /* Smaller font size */
    }

    input[type="submit"] {
        font-size: 14px;
        padding: 8px;
    }

    .custom-file-upload, #file-chosen {
        width: 100%; /* Full width for smaller devices */
        margin-bottom: 10px;
        padding: 4px 8px; /* Reduced padding for smaller devices */
    }

    .user-details span, .user-details input[type="text"] {
        font-size: 14px; /* Reduced font size for user details */
    }
}
body > div > section > div.header-container > div > div{
    display: flex; 
    
}
body > div > section > div.header-container > div > div > label{
    margin-top:15px;
    margin-right:20px;
}
body > div > section > div.header-container > div > span:nth-child(4){
    margin-right:100px;
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
<div class="header-container">

<h2>Customer Registartion</h2> <div class="user-details">
    <hr>

</div>
</div>
<hr>

<form action="aold.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="executive_name" value="<?php echo htmlspecialchars($user['name']); ?>">


<input type="hidden" name="type" value="newloan">
<div class="form-row">
<div class="form-group">
    <label for="date">Date</label>
    <input type="date" name="date" id="date" >
</div>
</div>

<div class="form-row">
        <div class="form-group">
            <label for="branch">Branch:</label>
            <select id="branch" name="bname">
            <option value="">Select Branch</option>
                <!-- Branch options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="bcode">BCode:</label>
            <input type="text" id="bcode" name="bcode" readonly>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="center">Center:</label>
            <select id="center" name="center" disabled>
                <option value="">Select Center</option>
                <!-- Center options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="ccode">CCode:</label>
            <input type="text" id="ccode" name="ccode" readonly>
        </div>
    </div>
    <div class="form-row">
    <button onclick="navigateToAGroup()">Add Group</button>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="group">Group:</label>
            <select id="group" name="group" disabled>
                <option value="">Select Group</option>
                <!-- Group options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="memberno">Member No</label>
            <select name="memberno" id="memberNo">
                <?php
                for ($i = 1; $i <= 100; $i++) {
                    echo "<option value=\"$i\">$i</option>";
                }
                ?>
            </select>
        </div>

        </div>
    <!-- Additional fields start here -->
    <div class="form-group">
        <label for="customerName">Customer Name</label>
        <input type="text" name="customerName" placeholder="Customer Name" required>
    </div>

    <div class="form-group">
        <label for="customerId">Customer NIC</label>
        <input type="text" name="customerId" placeholder="ID" >
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="street">House No</label>
            <input type="text" name="street" placeholder="Street">
        </div>

        <div class="form-group">
            <label for="city">Address 1</label>
            <input type="text" name="city" placeholder="City">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="state">Address 2</label>
            <input type="text" name="state" placeholder="State">
        </div>

        <div class="form-group">
            <label for="postal_code">Address 3</label>
            <input type="text" name="postal_code" placeholder="Postal Code">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="phone1">Customer Mobile Number</label>
            <input type="tel" name="phone1" placeholder="Phone Number" required>
        </div>

        <div class="form-group">
            <label for="phone2">Customer Mobile Number</label>
            <input type="tel" name="phone2" placeholder="Phone Number">
        </div>
    </div>

    <div class="form-group">
        <label for="image" class="custom-file-upload">Image Of Customer</label>
        <input type="file" name="image" id="image" accept="image/*" style="display: none;">
        <span id="file-chosen">No image chosen</span>
    </div>

    <div class="form-group">
        <label for="loanAmount">Loan Amount</label>
        <input type="number" id="loanAmount" name="loanAmount" placeholder="Loan Amount" required min="5000" max="20000">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="period">Loan Period</label>
            <select id="period" name="period" required>
                <!-- Populate period options dynamically if needed -->
                <?php echo $periodOptions; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="interest">Interest Rate</label>
            <select id="interest" name="interest" required>
                <!-- This will be dynamically populated based on period -->
                <?php
                if (isset($_POST['period']) && $_POST['period'] == '13') {
                    echo '<option value="30%">30%</option>';
                } else {
                    echo '<option value="20%">20%</option>';
                }
                ?>
            </select>
        </div>
   </div>
<div class="form-row">
<div class="form-row">
    <input type="submit" name="submit" value="ADD">
    <button onclick="redirectToOldPay()">Lap Payments</button>
    <button onclick="redirectToTestPay()">Week Payments</button>
</div>
        
    </div>
    </form>
            </div>

<script>

function navigateToAGroup() {
    window.location.href = 'agroup.php';
}
 $(document).ready(function() {
            // Load branches on page load
            $.ajax({
                url: 'get_branch.php',
                type: 'GET',
                success: function(data) {
                    $('#branch').append(data); // Append branch options to the existing "Select Branch" option
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching branches:', error);
                }
            });

            // Event listener for branch selection
            $('#branch').change(function() {
                let selectedOption = $(this).find('option:selected');
                let branchName = selectedOption.val();
                let bcode = selectedOption.data('bcode'); // Get bcode from data attribute

                if (branchName) {
                    // Set bcode value
                    $('#bcode').val(bcode);

                    // Fetch centers based on selected branch
                    $.ajax({
                        url: 'get_center.php',
                        type: 'GET',
                        data: { bname: branchName },
                        success: function(data) {
                            $('#center').html('<option value="">Select Center</option>' + data); // Update center dropdown with options
                            $('#center').removeAttr('disabled'); // Enable the center dropdown
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching centers:', error);
                        }
                    });
                } else {
                    $('#bcode').val('');
                    $('#center').html('<option value="">Select Center</option>').attr('disabled', 'disabled');
                    $('#ccode').val('');
                    $('#group').html('<option value="">Select Group</option>').attr('disabled', 'disabled');
                }
            });
        

    // Event listener for center selection
    $('#center').change(function() {
        let selectedOption = $(this).find('option:selected');
        let center = selectedOption.val(); // Assuming center is the value of the option
        let ccode = selectedOption.data('ccode'); // Get ccode from data attribute

        if (ccode) {
            // Set ccode value
            $('#ccode').val(ccode);
            $('#center_name').val(center); // Assuming you have an input field with id 'center_name'

            // Fetch groups based on selected center
            $.ajax({
                url: 'get_group.php',
                type: 'GET',
                data: { center_id: ccode },
                dataType: 'json', // Expect JSON response
                success: function(data) {
                    $('#group').html(''); // Clear existing options
                    // Iterate through groups and append options to dropdown
                    $.each(data.groups, function(index, group) {
                        $('#group').append(`<option value="${group.group}">${group.group}</option>`);
                    });
                    // Enable the group dropdown
                    $('#group').removeAttr('disabled');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching groups:', error);
                }
            });
        } else {
            $('#ccode').val('');
            $('#group').html('<option value="">Select Group</option>').attr('disabled', 'disabled');
        }
    });
});
function redirectToOldPay() {
        // Add your redirection logic here, for example:
        window.location.href = 'oldpay.php'; // Replace with the actual URL
    }

    function redirectToTestPay() {
        window.location.href = 'testpay.php';
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
