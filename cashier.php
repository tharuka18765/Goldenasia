<?php
// cashier.php

session_start();
require "connecton.php"; 
// Ensure you have the correct path to your connection file


// Check if the user is logged in
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

$branchName = $user['bname'];
$executiveName = $user['name']; // Fetch branch name from session


// Function to fetch customer data based on the center
function getCustomersByCenter($con, $center) {
    $customersData = [];
    $stmt = $con->prepare("SELECT nic, name, cname, customer_code, loan_code, loan_amount, due_date, loan_balance, loan_date, week_payment FROM customer WHERE center = ? AND full_loan != payment");
    $stmt->bind_param("s", $center);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($customer = $result->fetch_assoc()) {
        // Use DateTime objects to compare dates
        $today = new DateTime(); // Today's date
        $dueDate = new DateTime($customer['due_date']);

        // Compare dates to set the status
        $customer['status'] = ($dueDate < $today) ? 'Lag' : 'Active';

        // Fetch the total arriarse from paymentdates for each NIC
        $arriarseStmt = $con->prepare("SELECT SUM(arriarse) AS arriarse, due_date_weekly FROM paymentdates WHERE loan_code = ?");
        $arriarseStmt->bind_param("s", $customer['loan_code']);
        $arriarseStmt->execute();
        $arriarseResult = $arriarseStmt->get_result();
        
        if ($arriarseRow = $arriarseResult->fetch_assoc()) {
            $customer['arriarse'] = $arriarseRow['arriarse'] ?? 0; // Sum of arriarse for all weeks
            $customer['due_date_weekly'] = $arriarseRow['due_date_weekly'] ?? 'No due date found'; // Earliest due date weekly if required
        } else {
            $customer['arriarse'] = 0;
            $customer['due_date_weekly'] = 'No due date found';
        }
        $arriarseStmt->close();

        $customersData[] = $customer; // Append the modified $customer with 'status' and 'total_arriarse'
    }
    $stmt->close();
    return $customersData;
}


$selectedDate = date("Y-m-d");

// Convert the current date to the day of the week
$dayOfWeek = date('l', strtotime($selectedDate));

// Construct SQL query to select centers from branch table based on branch name and day
$centersQuery = "SELECT center FROM branch WHERE bname = ? AND day = ?";
$stmt = $con->prepare($centersQuery);
$stmt->bind_param("ss", $branchName, $dayOfWeek);
$stmt->execute();
$centersResult = $stmt->get_result();
$centers = mysqli_fetch_all($centersResult, MYSQLI_ASSOC);
$stmt->close();



// Handle AJAX request
if (isset($_POST['action']) && $_POST['action'] == 'getCustomers') {
    $center = $_POST['center'];
    $customers = getCustomersByCenter($con, $center);
    header('Content-Type: application/json');
    echo json_encode($customers);
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == 'submitPayment') {
    $loanCode = $_POST['loanCode'];
    $paymentAmount = floatval($_POST['paymentAmount']); // Ensure this is treated as a float
    $paymentDate = date('Y-m-d'); // Current date

    // Start a transaction
    $con->begin_transaction();

     // Assuming you also want to log this payment in a separate 'payments' table
     $stmt = $con->prepare("INSERT INTO payments (loan_code, payment_date, payment) VALUES (?, ?, ?)");
     $stmt->bind_param("ssd", $loanCode, $paymentDate, $paymentAmount);
     $stmt->execute();
     $stmt->close();

     // If everything's fine, commit the transaction
     $con->commit();
}
// Fetch all centers for dropdown


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa; /* Light grey background */
            margin: 0;
            
        }
        .cash{
            margin-left:10px;
            width: calc(100% - 260px);
        }
        h3 {
            color: #333;
        }

        #center-select {
            margin-bottom: 20px;
        }

        table {
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
/* Styles for the Submit Payments button */
#submit-payments {
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
    
    color:#fff;/* Darker shade of orange for hover effect */
}

#submit-payments:focus {
    outline: none; /* Removes the outline to keep the design clean */
}

/* Additional styles for the submit container for better alignment */
.submit-container {
    display: flex;
    justify-content: center; /* Centers the button horizontally */
    margin-top: 50px; 
    margin-left:970px;/* Adds some space above the button */
}

/* Style for the overlay */
/* Style for the overlay */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent black */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Ensures overlay is on top */
}

/* Style for the popup box */
.popup {
    background-color: #fff;
    padding: 30px; /* Increased padding */
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    width: 400px; /* Increased width */
    z-index: 1001; /* Ensures popup is above the overlay */
}

/* Style for the popup close button */
.popup-close-btn {
    float: right;
    cursor: pointer;
    color: #aaa;
    padding: 8px 15px; /* Increased padding for larger clickable area */
    font-size: 18px; /* Increased font size */
    font-weight: bold; /* Make the text bold */
}

.popup-close-btn:hover {
    color: #777; /* Darker shade on hover */
}

/* Style for the popup header */
.popup-header {
    font-size: 20px; /* Increased font size for the header */
    margin-bottom: 20px; /* Increased bottom margin */
}

/* Style for the popup content */
.popup-content {
    font-size: 18px; /* Increased font size for content */
}

body > div.home > i{
    font-size:22px;
    margin-left:220px;
    height: 50px;
  min-width: 78px;
  text-align: center;
  line-height: 50px;
  color: black;
  font-size: 35px;
  cursor: pointer;
  transition: all 0.3s ease;
}
.home-section {
  transition: left 0.5s, width 0.5s;
}
.sidebar.close ~ .home-section {
  left: 78px; /* Width of the closed sidebar */
  width: calc(100% - 78px); /* Adjust the width of the home section */
}
#single-payments{
    font-size:18px;
    font-weight: bold;
    background-color: #FAA300; /* Orange color to match the table header */
    color: black;
    width:300px;
    height:70px;
    border:none;
    text-transform:uppercase;
    letter-spacing:4px;
    margin-left:190px;
    border-radius:5px;
}
body > div > section > div.cash > div.header-container > div.single{
    width:200px;
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}
#customer-table tfoot tr:first-child td {
    padding-top: 40px; /* Adds padding to the top of the first row in tfoot */
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
<div class="cash">

<div class="header-container">
        <h3><?php echo htmlspecialchars($branchName); ?> Branch  -  <span><?php
// Get the current date
$selectedDate = date("Y-m-d");

// Convert the current date to the day of the week
$dayOfWeek = date('l', strtotime($selectedDate));

// Output the day of the week
echo htmlspecialchars($dayOfWeek); // Output: Monday (for example)
?>
</span></h3>
        
        <div class="control-group">
            <label for="center-select" class="control-label">Select Center:</label>
            <select id="center-select" name="center">
                <option value="">Select a center</option>
                <!-- Center options will be loaded here -->
                   
            <?php
            foreach ($centers as $center) {
echo '<option value="' . htmlspecialchars($center['center']) . '">' . htmlspecialchars($center['center']) . '</option>';
}
?>

            </select>
        </div>

      

    </div>
    
    <div class="table-responsive">
        <!-- Existing table structure -->
<table id="customer-table">
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
        <th>Due Date Weekly</th> <!-- New column for Payment -->
        <th>Arriarse</th>
        <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <!-- Customer data will be loaded here by AJAX -->
    </tbody>
    <br><br>
    <tfoot>
        <tr>
            <th colspan="5">Total Payments:</th>
            <th id="total-payments">Rs. 0.00</th>
        </tr>
    </tfoot>
</table>

<div class="submit-container">
    <button type="button" id="submit-payments">Submit Payments</button>
</div>


    </div>
</div>
<!-- Overlay and popup structure, initially hidden -->
<div class="overlay" id="overlay" style="display: none;">
    <div class="popup">
        <span class="popup-close-btn" id="closePopup">X</span>
        <div class="popup-header"> UPDATING PAYMENTS</div>
        <div class="popup-content">
            Payments updated successfully.
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    $('#center-select').change(function() {
        var selectedCenter = $(this).val();
        if (selectedCenter) {
            $.ajax({
                type: 'POST',
                url: 'cashier.php',
                data: {action: 'getCustomers', center: selectedCenter},
                dataType: 'json',
                success: function(customers) {
                    var tableRows = customers.map(function(customer) {
                        var totalWeeklyPayment = parseFloat(customer.week_payment) + parseFloat(customer.arriarse || 0);
                        return `<tr>
                                    <td>${customer.loan_code || 'N/A'}</td>
                                    <td>${customer.customer_code || 'N/A'}</td>
                                    <td>${customer.cname || 'N/A'}</td>
                                    <td>${customer.loan_amount || 'N/A'}</td>
                                    <td>${customer.due_date || 'N/A'}</td>
                                    <td>${customer.week_payment || 'N/A'}</td>
                                    <td>${totalWeeklyPayment}</td>
                                    <td><input type="text" name="payment" class="payment-input" placeholder="Rs.0000.00"></td>
                                    <td>${customer.loan_balance || 'N/A'}</td>
                                    <td>${customer.due_date_weekly || 'No due date found'}</td>
                                    <td>${customer.arriarse}</td> 
                                    <td>${customer.status}</td>
                                </tr>`;
                    }).join('');
                    $('#customer-table tbody').html(tableRows);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        } else {
            $('#customer-table tbody').empty();
            $('#total-payments').text('Rs. 0.00'); // Reset total payments when no center is selected
        }
    });

    $('#submit-payments').click(function() {
    const paymentsData = [];
    let totalPayment = 0;

    // Collect payments and compute the total
    $('#customer-table tbody tr').each(function() {
        const loanCode = $(this).find('td:eq(0)').text(); // Ensure this index is correct for your table
        const paymentInput = $(this).find('input[name="payment"]');
        const payment = parseFloat(paymentInput.val());

        if (!isNaN(payment) && payment > 0) {
            paymentsData.push({ loanCode: loanCode, payment: payment });
            totalPayment += payment; // Sum the valid payments
        }
    });

    // Update the footer with the total before showing the confirmation
    $('#total-payments').text(`Rs.${totalPayment.toFixed(2)}`);

    // Check if there are valid payments to submit
    if (paymentsData.length > 0) {
        // Show confirmation dialog
        setTimeout(() => { // Delay the confirmation dialog to ensure the UI updates first
            const confirmMessage = `Total payment to be submitted: Rs.${totalPayment.toFixed(2)}. Proceed?`;
            if (confirm(confirmMessage)) {
                $.ajax({
                    type: 'POST',
                    url: 'updatePayments.php',
                    data: JSON.stringify(paymentsData),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert('Payments updated successfully');
                            // Clear input fields after successful update
                            $('#customer-table tbody input[type="text"]').val('');
                        } else {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Error updating payments: ' + error);
                    }
                });
            } else {
                // Reset the footer if the user cancels the operation
                $('#total-payments').text('Rs. 0.00');
            }
        }, 100);
    } else {
        alert('Please enter valid payment amounts before submitting.');
        $('#total-payments').text('Rs. 0.00'); // Reset the total in the footer if invalid input
    }
});




    $('#closePopup').click(function() {
        $('#overlay').hide();
    });

    let sidebarBtn = document.querySelector('.bx-menu');
    sidebarBtn.addEventListener('click', function() {
        $('.sidebar').toggleClass('close');
    });
});

// Close the popup when the close button is clicked
$('#closePopup').click(function() {
    $('#overlay').hide();
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


</script>
</body>
</html>
