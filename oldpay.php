<?php
// singlepayment.php

session_start();
require "connecton.php"; // Corrected the filename

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$status = $user['status'];  
$branchName = $user['bname']; // Fetch branch name from session

$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
}

// Function to fetch customer data based on the NIC
function getCustomerByNIC($con, $nic) {
    $customerData = [];

    // Fetch basic customer information
    $stmt = $con->prepare("SELECT name, cname, customer_code, nic, loan_code, loan_amount,full_loan, loan_balance, loan_date, week_payment, due_date FROM customer WHERE nic = ? OR customer_code=?");
    $stmt->bind_param("ss", $nic,$nic);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($customer = $result->fetch_assoc()) {
        $customerData = $customer;

        // Calculate status based on the due date
        $today = new DateTime(); // Today's date
        $dueDate = new DateTime($customer['due_date']); // Assuming due_date is fetched correctly
        $customerData['status'] = ($dueDate < $today) ? 'Lag' : 'Active';

     $arriarseStmt = $con->prepare("
    SELECT SUM(arriarse) AS arriarse, due_date_weekly, loan_code 
    FROM paymentdates 
    WHERE nic = ? OR customer_code = ?
    GROUP BY nic, customer_code
");
$arriarseStmt->bind_param("ss", $nic, $nic);
        $arriarseStmt->execute();
        $arriarseResult = $arriarseStmt->get_result();
        if ($arriarseRow = $arriarseResult->fetch_assoc()) {
            $customerData['arriarse'] = $arriarseRow['arriarse'] ?? 0;
            $customerData['due_date_weekly'] = $arriarseRow['due_date_weekly']; // Add due date to customer data
            $customerData['loan_code'] = $arriarseRow['loan_code']; // Assuming you want to distinguish this loan_code
        } else {
            $customerData['arriarse'] = 0; // Default to 0 if not found
            $customerData['due_date_weekly'] = 'No due date found'; // Fallback message
            $customerData['loan_code'] = 'No loan code found'; // Fallback message for loan_code
        }
        $arriarseStmt->close();
    }
    $stmt->close();

    return $customerData; // Return the combined data
}


// Handle AJAX request for customer search by NIC
if (isset($_POST['action']) && $_POST['action'] == 'searchCustomer') {
    $nic = $_POST['nic'];
    $customer = getCustomerByNIC($con, $nic);
    header('Content-Type: application/json');
    echo json_encode($customer ?: ['error' => 'Customer not found']);
    exit();
}

// Check for Submit Payments action
if (isset($_POST['action']) && $_POST['action'] == 'submitPayment') {
    $loanCode = $_POST['loanCode'];
    $paymentAmount = floatval($_POST['paymentAmount']); // Ensure this is treated as a float
    $paymentDate = date('Y-m-d'); 
    $executiveName = $user['name'];// Current date

    // Start a transaction
    $con->begin_transaction();

    try {
        // Fetch current loan_amount, total payment made so far, and NIC
        $stmt = $con->prepare("SELECT loan_amount,full_loan, payment, nic, loan_date FROM customer WHERE loan_code = ?");
        $stmt->bind_param("s", $loanCode);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $loanAmount = $row['loan_amount'];
            $fulloan = $row['full_loan'];
            $currentPayment = $row['payment'] ?? 0;
            $nic = $row['nic']; // Fetch the NIC
            $loanDate = $row['loan_date']; // Fetch the loan date

            $currentWeekNumber = ceil((time() - strtotime($loanDate)) / (7 * 86400));

            // Fetch the latest week_payment for the given loan_code
$fetchStmt = $con->prepare("SELECT week_payment FROM paymentdates WHERE loan_code = ? ORDER BY week_number DESC LIMIT 1");
$fetchStmt->bind_param("s", $loanCode);
$fetchStmt->execute();
$fetchResult = $fetchStmt->get_result();
if ($fetchRow = $fetchResult->fetch_assoc()) {
    $weekPayment = $fetchRow['week_payment'];
} else {
    $weekPayment = 0;  // Default to 0 if no payment history is found
}
$fetchStmt->close();

// Calculate new balance and updated payment
$newPayment = $currentPayment + $paymentAmount;
$newBalance = $fulloan - $newPayment;
$newArriarse = $fulloan - $newPayment; // Complete the assignment here

// Update the customer table
$updateStmt = $con->prepare("UPDATE customer SET payment = ?, loan_balance = ?, payment_date = CURDATE() WHERE loan_code = ?");
$updateStmt->bind_param("dds", $newPayment, $newBalance, $loanCode);
$updateStmt->execute();

// Add data to response
$responseData[] = ['loanCode' => $loanCode, 'newBalance' => $newBalance, 'payment' => $paymentAmount];

// Update or insert into paymentdates table
$updatePaymentDatesStmt = $con->prepare("UPDATE paymentdates SET payment = ?, arriarse = ?, name = ?, payment_date = CURDATE() WHERE loan_code = ? AND week_number = 1");
$updatePaymentDatesStmt->bind_param("ddss", $paymentAmount, $newArriarse, $executiveName, $loanCode);

if ($updatePaymentDatesStmt->execute() === false || $updatePaymentDatesStmt->affected_rows === 0) {
    // If there is no existing record for the current week, insert a new one
   
}

$updatePaymentDatesStmt->close();
$updateStmt->close(); // Close the update statement
$con->commit(); // Commit the transaction

            echo json_encode(['success' => true, 'message' => 'Payment submitted successfully', 'updatedBalance' => $newBalance]);
        } else {
            $con->rollback(); // Rollback the transaction
            echo json_encode(['success' => false, 'message' => 'Loan code not found']);
        }
    } catch (Exception $e) {
        $con->rollback(); // Rollback the transaction on exception
        echo json_encode(['success' => false, 'message' => 'Failed to submit payment: ' . $e->getMessage()]);
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Old Lap Payments</title>
    <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Add any additional CSS or JS you need here -->
    <style>
         body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa; /* Light grey background */
            margin: 0;
            
        }
        .cash{
            margin-left:20px;
            width: calc(100% - 260px);
        }
        body > div > section > div.header-container > div > h3{
            margin-left:70px;
            color: #333;
        }
        body > div > section > div.header-container > div > div:nth-child(3) > div{
            margin-left:70px;
            width:800px;
            
        }
        #nic-search{
            padding:15px;
            width:500px;
            height:50px;
        }
        #search-btn{
            margin-top:5px;
            font-size:16px;
    font-weight: bold;
    background-color: #FAA300; /* Orange color to match the table header */
    color: black;
            padding:15px;
            width:200px;
            height:50px;
            border:none;
    text-transform:uppercase;
    letter-spacing:4px;
    border-radius:5px;
        }
        #center-select {
            margin-bottom: 20px;
        }

        table {
            margin-left:70px;
            width: 1300px;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #dee2e6;
        }

        th, td {
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #FAA300;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        .control-label {
            font-weight: bold;
        }

        .control-group {
            margin-bottom: 10px;
        }
        .header-container {
            display: flex;
            
            
            margin-bottom: 20px;
        }

        .header-container h3 {
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 24px;
        }

        .control-group {
            margin-top:30px;
            display: flex;
            margin-left:200px;
            
        }

        .control-label {
            width:140px;
            margin-top:8px;
            font-size:16px;
            font-weight: bold;
            margin-right: 20px;
        }

        #center-select {
            width:250px;
            font-size:16px;
            padding: 5px 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        /* Add styles for other elements as needed */

        /* Additional styles for input fields */
input[type="text"] {
    padding: 8px;
    border-radius: 4px;
    border: 1px solid #ced4da;
}

/* Style for input fields within table cells */
#customer-table td input[type="text"] {
    width: 100%;
    box-sizing: border-box; /* Ensures padding doesn't affect the width */
}
#submit-payments {
    margin-top:50px;
    margin-left:1150px;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        background-color: #FAA300; /* Orange color to match the table header */
        color: black;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    #submit-payments:hover {
        background-color: black;
        color: #fff; /* Darker shade of orange for hover effect */
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
    <div>
    <h3><?php echo htmlspecialchars($branchName); ?> Branch - Lap Payment -  <span><?php
// Get the current date
$selectedDate = date("Y-m-d");

// Convert the current date to the day of the week
$dayOfWeek = date('l', strtotime($selectedDate));

// Output the day of the week
echo htmlspecialchars($dayOfWeek); // Output: Monday (for example)
?>
</span></h3>

    
    <br>
    <div>
    <div class="search-container">
        <input type="text" id="nic-search" placeholder="Search by NIC...">
        <button id="search-btn">Search</button>
    </div>
</div>
    <div class="search-results">
        <table id="search-results-table">
        <thead>
    <tr>
        <th>Loan Code</th>
        <th>Customer Code</th>
        <th>Name</th>
        <th>Amount</th>
        <th>Loan Date</th>
        <th>Weekly Payment</th>
        <th>This Week Payment</th>
        <th>Payment</th>
        <th>Balance</th>
        <th>Due Date Weekly</th>
        <th>Arriarse</th>  <!-- New header for arriarse -->
        <th>Status</th>
    </tr>
</thead>


            <tbody>
                <!-- Search results will be loaded here -->
            </tbody>
        </table>
    </div>

    <div class="submit-container">
    <button type="button" id="submit-payments">Submit Payments</button>
</div>

</div>

<script>
 $(document).ready(function() {
    $('#search-btn').click(function() {
        var nic = $('#nic-search').val();
        if (nic) {
            $.ajax({
                type: 'POST',
                url: 'oldpay.php',
                data: {
                    action: 'searchCustomer',
                    nic: nic
                },
                dataType: 'json',
                success: function(customer) {
                    if (customer && customer.nic) {
                        // Calculate total weekly payment including arriarse
                        var totalWeeklyPayment = parseFloat(customer.loan_balance);

                        var tableRow = `<tr>
                            <td>${customer.loan_code}</td>
                            <td>${customer.customer_code}</td>
                            <td>${customer.cname}</td>
                            <td>${customer.loan_amount}</td>
                            <td>${customer.loan_date}</td>
                            <td>${customer.week_payment}</td>
                            <td>${totalWeeklyPayment}</td> <!-- Display total weekly payment including arriarse -->
                            <td><input type="text" class="payment-input" placeholder="Rs.0000.00" maxlength="5" oninput="validateInput(this)" /></td>

                            <td>${customer.loan_balance}</td>
                            <td>${customer.due_date_weekly}</td>
                            <td>${customer.arriarse}</td>
                            <td>${customer.status || 'Not available'}</td>
                        </tr>`;
                        $('#search-results-table tbody').html(tableRow);
                    } else {
                        $('#search-results-table tbody').html('<tr><td colspan="11">No results found</td></tr>');  
                    }
                }
        

,
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                }
            });

            $('#submit-payments').click(function() {
                var loanCode = $('#search-results-table tbody tr').find('td:eq(0)').text(); // Corrected the index
                var paymentAmount = $('.payment-input').val(); // Use class to match the corrected input field

                if (paymentAmount) {
                    $.ajax({
                        type: 'POST',
                        url: 'oldpay.php',
                        data: {
                            action: 'submitPayment',
                            loanCode: loanCode,
                            paymentAmount: paymentAmount
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert(response.message); // Display success message
                                // Update the balance column with the new balance
                                $('#search-results-table tbody tr').find('td:eq(6)').text(response.updatedBalance); // Corrected the index
                                $('.payment-input').val(''); // Clear the input field after successful submission
                            } else {
                                alert(response.message); // Display error message
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                } else {
                    alert('Please enter a payment amount.');
                }
            });
        });
    
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


    function validateInput(input) {
    // Remove any non-digit characters
    input.value = input.value.replace(/\D/g, '');

    // If the input value exceeds 5 digits, truncate it
    if (input.value.length > 5) {
        input.value = input.value.slice(0, 5);
    }
}

</script>
</body>
</html>
