<?php
// singlepayment.php

session_start();
require "connecton.php";
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}
$user = $_SESSION['user'];
$status = $user['status'];  
$customerDetails = [];
$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}


if (isset($_POST['searchSubmit'])) {
    $searchNIC = mysqli_real_escape_string($con, $_POST['searchNIC']);
    $query = "
    SELECT 
        c.bname, 
        c.bcode, 
        c.cname, 
        c.nic, 
        c.address, 
        c.phone1, 
        c.phone2, 
        c.center, 
        c.ccode, 
        c.`group`, 
        c.customer_code, 
        c.image, 
        c.period, 
        c.interest 
    FROM 
        customer c 
    WHERE 
        (c.nic = ? OR c.customer_code = ?) 
        AND (
            SELECT 
                loan_balance 
            FROM 
                customer c 
            WHERE 
                c.customer_code = ?
            ORDER BY 
                c.loan_date DESC 
            LIMIT 1
        ) = 0;
    ";

    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "sss", $searchNIC,$searchNIC, $searchNIC);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $bname, $bcode, $cname, $nic, $address, $phone1, $phone2, $centerName, $centerCode, $group,$customer_code, $image, $weeks, $interest);
        if (mysqli_stmt_fetch($stmt)) {
            $customerDetails = [
                'bname' => $bname,
                'bcode' => $bcode,
                'cname' => $cname,
                'nic' => $nic,
                'address' => $address,
                'phone1' => $phone1,
                'phone2' => $phone2,
                'center' => $centerName,
                'ccode' => $centerCode,
                'group' => $group,
                'customer_code' => $customer_code,
                'image' => $image,
                'period' => $weeks,
                'interest' => $interest
            ];
            $_SESSION['customerDetails'] = $customerDetails; // Store customer details in session
        } else {
            echo "<div class='message-container'><div class='error-message'>Customer not found.</div></div>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<div class='message-container'><div class='error-message'>Error preparing the statement.</div></div>";
    }
}

if (isset($_POST['renewSubmit'])) {
    $customerDetails = $_SESSION['customerDetails'] ?? [];

    $bname = mysqli_real_escape_string($con, $_POST['bname']);
    $bcode =  mysqli_real_escape_string($con, $_POST['bcode']);
    $center = mysqli_real_escape_string($con, $_POST['center']);
    $ccode = isset($_POST['ccode']) ? mysqli_real_escape_string($con, $_POST['ccode']) : '';
    $group = mysqli_real_escape_string($con, $_POST['group']);
    $executiveName = mysqli_real_escape_string($con,  $_POST['executive_name']);
    $customerName = mysqli_real_escape_string($con, $_POST['customerName']);
    $customerId = mysqli_real_escape_string($con, $_POST['customerId']);
    $address = mysqli_real_escape_string($con, $_POST['address']);
    $phone1 = mysqli_real_escape_string($con, $_POST['phone1']);
    $phone2 = mysqli_real_escape_string($con, $_POST['phone2']);
    $image = mysqli_real_escape_string($con, $_POST['image']);
    
    

    $loanAmount = mysqli_real_escape_string($con, $_POST['loanAmount']);
    $type = mysqli_real_escape_string($con, $_POST['type']);
    $interest = mysqli_real_escape_string($con, $_POST['interest']);
    $period = mysqli_real_escape_string($con, $_POST['period']);

    $currentDate = date('Y-m-d'); // Current date and time in 'YYYYMMDDHHMMSS' format
    


   
    $current_date = date('Ymd'); 

   
    $customer_code = $customerDetails['customer_code'];

    $loanCode = $customer_code . '/' . $current_date;

    // Assuming $currentDate is the start date of the loan
    $currentDate = date('Y-m-d');
    $startDate = new DateTime($currentDate);
    
    // Example: $period contains the value like "12 Weeks". Adjust this based on your actual form input.
    $period = $_POST['period']; // Ensure this matches your form's input name
    
    // Extract the numeric value from the "Weeks" part of the period string
    $loanPeriodInWeeks = (int) preg_replace('/\D/', '', $period); // Extract digits from period string
    
    // Calculate due date only if a valid loan period is found
    if ($loanPeriodInWeeks > 0) {
        $dueDate = $startDate->modify("+{$loanPeriodInWeeks} weeks")->format('Y-m-d');
    } else {
        // Set a default due date or handle the error as needed
        $dueDate = $currentDate; // Default to current date or choose a suitable action
    }
    
    // Assuming $loanAmount has already been captured from the form and sanitized
    $numberOfWeeks = $_POST['period'] == '12' ? 12 : 13; // Set the number of weeks based on the selected period

    // Set the interest rate based on the selected period
    $interestRate = $_POST['period'] == '12' ? 0.20 : 0.30;
    
    $fullLoanAmount = $loanAmount + ($loanAmount * $interestRate);
    
    $weeklyPayment = $fullLoanAmount / $numberOfWeeks;
    
    $documentrate = 0.01;
    $documentfee = $loanAmount * $documentrate;
    
    $insurancerate = 0.01;
    $insurancefee = $loanAmount * $insurancerate;

    $payment = 0.00;
$paymentDate = date('Y-m-d');

    // Adjusted SQL INSERT query to include `customer_code`
    $query = "INSERT INTO customer (bname, bcode, center, ccode, `group`, name, cname, nic, address, phone1, phone2, loan_amount,type, interest, period, customer_code, loan_date, loan_code, due_date,payment_date,loan_balance, full_loan, week_payment, document_fee, insurance_fee, image) VALUES (?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare and bind, now including $weeklyPayment in the bind_param call
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssssssssssssssssssssssssss", $bname, $bcode, $center, $ccode, $group, $executiveName, $customerName, $customerId, $address, $phone1, $phone2, $loanAmount,$type, $interest, $period, $customer_code, $currentDate, $loanCode, $dueDate,$paymentDate, $fullLoanAmount,$fullLoanAmount, $weeklyPayment, $documentfee, $insurancefee, $image);

    if (mysqli_stmt_execute($stmt)) {
        echo "<div class='message-container'><div class='success-message'>New record created successfully.</div></div>";
    } else {
        $errorMsg = mysqli_error($con);
        echo "<div class='message-container'><div class='error-message'>Error: Unable to execute the query.<br>$errorMsg</div></div>";
    }
    
    // Close statement
    mysqli_stmt_close($stmt);

    $payment = 0.00;
    $arriarse = 0;
    $name = 0;
    $payment_date = 0;
    $currentDate = date('Y-m-d');
    $startDate = new DateTime($currentDate);
    $initialDueDate = $startDate->add(new DateInterval('P7D'));

    $numberOfPayments = 13;

// Check if 12 weeks option is selected
if (isset($_POST['period']) && $_POST['period'] === '12') {
    $numberOfPayments = 12;
}

for ($week = 1; $week <= $numberOfPayments; $week++) {
    $dueweek = $initialDueDate->format('Y-m-d');
   
       
        $query = "INSERT INTO paymentdates (loan_code,customer_code, week_number, due_date_weekly, bname, center,ccode,payment, nic,name,payment_date,week_payment,arriarse) VALUES (?, ?, ?, ?, ?, ?, ?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ssissssssssss", $loanCode,$customer_code, $week, $dueweek, $bname, $center,$ccode,$payment, $customerId,$name,$payment_date, $weeklyPayment,$arriarse);

        mysqli_stmt_execute($stmt);
        $initialDueDate->add(new DateInterval('P7D'));
    }
    mysqli_stmt_close($stmt);
}
$userName = $user['name'];

// Check if the user is "Pathum Himantha"
if ($userName === "Ravidu Laksri") {
    // If the user's name is "Pathum Himantha", show both options
    $periodOptions = '<option value="12">12 Weeks</option>';
    $periodOptions .= '<option value="13">13 Weeks</option>';
} else {
    // If the user's name is not "Pathum Himantha", show only the default option for 13 weeks
    $periodOptions = '<option value="13">13 Weeks</option>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Renew Loan</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Include any additional CSS or libraries here -->

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
select,
input[type="file"] {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
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


</div>
<div class="header-container">
    <h2>Renew Loan</h2>
    <div class="user-details">
        <!-- Assuming $user contains the user's session data -->
        <span class="branch-name"><?php echo htmlspecialchars($user['bname']); ?></span>
        <span class="branch-code"><?php echo htmlspecialchars($user['bcode']); ?></span>
        <span> <input type="text" id="currentDate" name="currentDate" value="<?php echo date('Y-m-d'); ?>" readonly> </span>
    </div>
</div>
<hr>

<form method="post" action="">
    <label for="searchNIC"style="font-weight:bold; font-family:Arial;">Customer NIC:</label>
    <input type="text" id="searchNIC" name="searchNIC" placeholder="Enter NIC to search" required>
    <input type="submit" name="searchSubmit" value="Search">
</form>

<form action="renewloan.php" method="post" enctype="multipart/form-data">
    <!-- Pre-filled customer details -->
    <input type="hidden" name="bname" value="<?php echo htmlspecialchars($user['bname']); ?>">
    <input type="hidden" name="bcode" value="<?php echo htmlspecialchars($user['bcode']); ?>">
    <input type="hidden" name="executive_name" value="<?php echo htmlspecialchars($user['name']); ?>">
    <input type="hidden" name="type" value="renewloan">

    
    <div class="form-group">
    <label for="centerName"style="font-weight:bold; font-family:Arial;">Center Name</label>
    <input type="text" name="center" value="<?php echo htmlspecialchars($customerDetails['center'] ?? ''); ?>" required readonly>
</div>
<div class="form-group">
    <label for="centerCode"style="font-weight:bold; font-family:Arial;">Center Code</label>
    <input type="text" name="ccode" value="<?php echo htmlspecialchars($customerDetails['ccode'] ?? ''); ?>" required readonly>
</div>
<div class="form-row">
<div class="form-group">
    <label for="group"style="font-weight:bold; font-family:Arial;">Group</label>
    <input type="text" name="group" value="<?php echo htmlspecialchars($customerDetails['group'] ?? ''); ?>" required>
</div>
<div class="form-group">
    <label for="memberno"style="font-weight:bold; font-family:Arial;">Customer Code</label>
    <input type="text" name="memberno" value="<?php echo htmlspecialchars($customerDetails['customer_code'] ?? ''); ?>" required>
</div>
</div>
<div class="form-group">
    <label for="customerName"style="font-weight:bold; font-family:Arial;">Customer Name</label>
    <input type="text" name="customerName" value="<?php echo htmlspecialchars($customerDetails['cname'] ?? ''); ?>" required>
</div>
<div class="form-group">
    <label for="customerId"style="font-weight:bold; font-family:Arial;">Customer NIC</label>
    <input type="text" name="customerId" value="<?php echo htmlspecialchars($customerDetails['nic'] ?? ''); ?>" required >
</div>
<div class="form-group">
    <label for="address"style="font-weight:bold; font-family:Arial;">Customer Address</label>
    <input type="text" name="address" value="<?php echo htmlspecialchars($customerDetails['address'] ?? ''); ?>" required>
</div>
<div class="form-group">
    <label for="phone1"style="font-weight:bold; font-family:Arial;">Phone Number 1</label>
    <input type="text" name="phone1" value="<?php echo htmlspecialchars($customerDetails['phone1'] ?? ''); ?>" required>
</div>
<div class="form-group">
    <label for="phone2"style="font-weight:bold; font-family:Arial;">Phone Number 2</label>
    <input type="text" name="phone2" value="<?php echo htmlspecialchars($customerDetails['phone2'] ?? ''); ?>">
</div>
<!-- Add additional pre-filled fields as needed -->
<div class="form-group">
    <label for="image"style="font-weight:bold; font-family:Arial;">Image</label>
    <input type="text" name="image" value="<?php echo htmlspecialchars($customerDetails['image'] ?? ''); ?>">
</div>

<!-- Fields for loan renewal -->
<div class="form-group">
    <label for="center"style="font-weight:bold; font-family:Arial;">Loan Amount</label>
    <input type="number" name="loanAmount" placeholder="Loan Amount" required min="5000" max="20000">
</div>


<div class="form-group">
    <label for="period"style="font-weight:bold; font-family:Arial;">Loan Period</label>
    <select id="period" name="period" required>
        <?php echo $periodOptions; ?>
        <!-- Add other options if needed -->
    </select>
</div>

<div class="form-group">
    <label for="interest"style="font-weight:bold; font-family:Arial;">Interest Rate</label>
    <select id="interest" name="interest" required>
        <?php
        if ($_POST['period'] == '13') {
            echo '<option value="30%">30%</option>';
        } else {
            echo '<option value="20%">20%</option>';
        }
        ?>
        <!-- Add other options if needed -->
    </select>
</div>

    <!-- Other renewal fields like period and interest -->
    
    <input type="submit" name="renewSubmit" value="Submit Renewal">
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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


    document.getElementById('loanAmount').addEventListener('input', function() {
    if (this.value.length > 5) {
        this.value = this.value.slice(0, 5);
    }
});

    $(document).ready(function() {
        $('#period').change(function() {
            var period = $(this).val();
            if (period === '13') {
                $('#interest').html('<option value="30%">30%</option>');
            } else {
                $('#interest').html('<option value="20%">20%</option>');
            }
        });
    });
    
  </script>
</body>
</html>