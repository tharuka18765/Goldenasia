<?php
session_start();
require "connecton.php"; // Ensure correct filename for database connection

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$branchName = $user['bname'];
$status = $user['status'];

$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
}

// Initialize variables
$search = '';
$results = [];
$paymentResults = [];

// Handle form submission for search
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search = $_POST['search'];
    $sql = "SELECT * FROM customer WHERE (nic = ? OR customer_code = ?) AND loan_balance != 0";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('ss', $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    $results = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission for updating data
if (isset($_POST['update'])) {
    foreach ($_POST['data'] as $id => $data) {
        // Calculate full_loan, loan_balance, and week_payment
        $interest = $data['interest'] / 100; // Convert interest to a decimal
        $full_loan = ($data['loan_amount'] * $interest) + $data['loan_amount'];
        $loan_balance = $full_loan; // Assuming loan_balance is the same as full_loan initially
        $week_payment = $full_loan / $data['period'];

        // Update the customer table
        $sql = "UPDATE customer SET
             name = ?, cname = ?, nic = ?, address = ?, phone1 = ?, loan_amount = ?, type = ?,  loan_balance = ?, full_loan = ?, week_payment = ?
            WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('ssssssssssi',
            $data['name'], $data['cname'], $data['nic'], $data['address'], $data['phone1'], $data['loan_amount'], $data['type'], $data['loan_balance'], $full_loan, $week_payment, $id
        );
        $stmt->execute();

        // Update the paymentdates table
        $updatePaymentDatesSql = "UPDATE paymentdates SET week_payment = ? ,nic = ? WHERE loan_code = ?";
        $stmt = $con->prepare($updatePaymentDatesSql);
        $stmt->bind_param('dss', $week_payment, $data['nic'], $data['loan_code']);
        $stmt->execute();
    }
    header("Location: managecus.php"); // Redirect to avoid resubmission
    exit();
}

// Handle form submission for updating paymentdates table
if (isset($_POST['update_payment'])) {
    foreach ($_POST['payment_data'] as $id => $data) {
        // Update the paymentdates table with the new payment, payment_date, and arriarse
        $sql = "UPDATE paymentdates SET payment = ?, payment_date = ?, arriarse = ? WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('dssd', $data['payment'], $data['payment_date'], $data['arriarse'], $id);
        $stmt->execute();
    }
    header("Location: managecus.php"); // Redirect to avoid resubmission
    exit();
}

// Handle form submission for deleting data
if (isset($_POST['delete'])) {
    if (!empty($_POST['delete_ids'])) {
        foreach ($_POST['delete_ids'] as $id) {
            // Get the loan_code for the customer
            $loanCodeQuery = "SELECT loan_code FROM customer WHERE id = ?";
            $stmt = $con->prepare($loanCodeQuery);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($loan_code);
            $stmt->fetch();
            $stmt->close();

            // Delete the customer record
            $deleteCustomerQuery = "DELETE FROM customer WHERE id = ?";
            $stmt = $con->prepare($deleteCustomerQuery);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            // Delete the corresponding records from paymentdates table
            $deletePaymentQuery = "DELETE FROM paymentdates WHERE loan_code = ?";
            $stmt = $con->prepare($deletePaymentQuery);
            $stmt->bind_param('s', $loan_code);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: managecus.php"); // Redirect to avoid resubmission
    exit();
}

// Handle display of paymentdates table
if (!empty($results)) {
    $loan_codes = array_column($results, 'loan_code');
    $placeholders = implode(',', array_fill(0, count($loan_codes), '?'));

    $sql = "SELECT * FROM paymentdates WHERE loan_code IN ($placeholders)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($loan_codes)), ...$loan_codes);
    $stmt->execute();
    $result = $stmt->get_result();
    $paymentResults = $result->fetch_all(MYSQLI_ASSOC);
}

$con->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Search</title>
    <link rel="stylesheet" href="styledash.css">
    <!-- Boxicons CDN Link -->
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
      body > div:nth-child(2){
        margin-left:300px;
            margin-top:30px;
      }
        body > div > div:nth-child(2){
            margin-left:300px;
            margin-top:30px;

        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        form {
            margin-top:30px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        label {
            margin-right: 15px;
        }
        input[type="date"],
        input[type="text"],
        input[type="submit"] {
            padding: 8px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width:180px;
  height:35px;
  font-weight:500;
    text-transform:uppercase;
   border-radius:4px;
   cursor: pointer;
    border:none;
    background-color: #FAA300;
    color: white;
    margin-top:15px;
        }
        input[type="submit"]:hover {
            background-color: black;
            color:white;
        }body > div > section > div > form {
            width:100%;
        }
        body > div > section > div > form > table{
width: 100%;
overflow:auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        th {
            background-color: #FAA300;
            color: white;
        }
        .table-container {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            overflow-y: auto;
            max-height: 400px; /* Adjust this value to your desired height */
            border: 1px solid #ddd;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1500px; /* Adjust this value based on the content width */
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid #ddd;
            white-space: nowrap; /* Prevents text from wrapping */
        }
        
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        @media (max-width: 480px) {
            body {
                padding: 10px; /* Smaller padding for smaller screens */
            }
            h1, h2 {
                font-size: 18px; /* Smaller font size for headings */
            }
            form {
                padding: 10px; /* Less padding inside the form */
                box-shadow: 0 0 5px rgba(0, 0, 0, 0.1); /* Lighter shadow */
            }
            input[type="date"],
            input[type="text"],
            input[type="submit"] {
                margin: 5px 0; /* Stack inputs vertically with space between */
                width: calc(100% - 20px); /* Adjust width for smaller padding */
            }
            table, th, td {
                font-size: 14px; /* Smaller font size for table content */
            }
        }body > div > section > div > table{
padding:30px;
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

<h2>Center Customers</h2><div class="user-details">
<hr>

</div>
</div>
    <div>
    <form method="post" action="">
        <input type="text" name="search" placeholder="Search by NIC or Customer Code" value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>

    <?php if (!empty($results)): ?>
        <form method="post" action="">
        <h2>Customer Details</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Select</th>
                        <th>ID</th>
                        <th>Branch</th>
                        <th>Center</th>
                        <th>Group</th>
                        <th>Ex Name</th>
                        <th>Name</th>
                        <th>NIC</th>
                        <th>Address</th>
                        <th>Phone1</th>
                        <th>Loan Amount</th>
                        <th>Type</th>
                        <th>Period</th>
                        <th>Interest</th>
                        <th>Customer Code</th>
                        <th>Loan Date</th>
                        <th>Loan Code</th>
                        <th>Due Date</th>
                        <th>Loan Balance</th>
                        <th>Full Loan</th>
                        <th>Week Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><input type="checkbox" name="delete_ids[]" value="<?php echo $row['id']; ?>"></td>
                            <input type="hidden" name="data[<?php echo $row['id']; ?>][id]" value="<?php echo $row['id']; ?>">
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['bname']); ?></td> 
                            <td><?php echo htmlspecialchars($row['center']); ?></td>
                            <td><?php echo htmlspecialchars($row['group']); ?></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][name]" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][cname]" value="<?php echo htmlspecialchars($row['cname']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][nic]" value="<?php echo htmlspecialchars($row['nic']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][address]" value="<?php echo htmlspecialchars($row['address']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][phone1]" value="<?php echo htmlspecialchars($row['phone1']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][loan_amount]" value="<?php echo htmlspecialchars($row['loan_amount']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][type]" value="<?php echo htmlspecialchars($row['type']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][period]" value="<?php echo htmlspecialchars($row['period']); ?>"></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][interest]" value="<?php echo htmlspecialchars($row['interest']); ?>"></td>
                            <td><?php echo htmlspecialchars($row['customer_code']); ?></td>
                            <td><?php echo htmlspecialchars($row['loan_date']); ?></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][loan_code]" value="<?php echo htmlspecialchars($row['loan_code']); ?>" readonly></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td><input type="text" name="data[<?php echo $row['id']; ?>][loan_balance]" value="<?php echo htmlspecialchars($row['loan_balance']); ?>"></td>
                            <td><?php echo htmlspecialchars($row['full_loan']); ?></td>
                            <td><?php echo htmlspecialchars($row['week_payment']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <input type="submit" name="update" value="Update">
        <input type="submit" name="delete" value="Delete Selected">
    </form>
    <?php endif; ?>

    <?php if (!empty($paymentResults)): ?>
        <form method="post" action="">
        <h2>Payment Dates</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Loan Code</th>
                    <th>Customer Code</th>
                    <th>Week Number</th>
                    <th>Due Date Weekly</th>
                    <th>Branch</th>
                    <th>Center</th>
                    <th>Payment</th>
                    <th>NIC</th>
                    <th>Name</th>
                    <th>Payment Date</th>
                    <th>Week Payment</th>
                    <th>Arrears</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paymentResults as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['loan_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['week_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['due_date_weekly']); ?></td>
                        <td><?php echo htmlspecialchars($row['bname']); ?></td>
                        <td><?php echo htmlspecialchars($row['center']); ?></td>
                        <td><input type="text" name="payment_data[<?php echo $row['id']; ?>][payment]" value="<?php echo htmlspecialchars($row['payment']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['nic']); ?></td>
                           <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><input type="date" name="payment_data[<?php echo $row['id']; ?>][payment_date]" value="<?php echo htmlspecialchars($row['payment_date']); ?>"></td>
                        <td><?php echo htmlspecialchars($row['week_payment']); ?></td>
                        <td><input type="text" name="payment_data[<?php echo $row['id']; ?>][arriarse]" value="<?php echo htmlspecialchars($row['arriarse']); ?>"></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <input type="submit" name="update_payment" value="Update Payment Dates">
    </form>
    <?php endif; ?>
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
