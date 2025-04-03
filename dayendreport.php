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

function updatePlot($date, $name, $collection, $newloan, $balance) {
  global $con;

  // Check if the fields are already updated
  $checkQuery = "SELECT collection, newloan, balance FROM plots WHERE date = ? AND name = ?";
  if ($checkStmt = $con->prepare($checkQuery)) {
      $checkStmt->bind_param("ss", $date, $name);
      $checkStmt->execute();
      $checkStmt->bind_result($existingCollection, $existingNewloan, $existingBalance);
      $checkStmt->fetch();
      $checkStmt->close();

      // If any of the fields are not 0, show a message and do not allow an update
      if ($existingCollection != 0 || $existingNewloan != 0 || $existingBalance != 0) {
          return "These fields have already been updated. Cannot update again.";
      }
  } else {
      return "Error checking existing data: " . $con->error;
  }

  // Update the plots table
  $updateQuery = "
      UPDATE plots 
      SET collection = ?, newloan = ?, balance = ?
      WHERE date = ? AND name = ?
  ";

  if ($stmt = $con->prepare($updateQuery)) {
      $stmt->bind_param("ddsss", $collection, $newloan, $balance, $date, $name);
      if ($stmt->execute()) {
          return true;
      } else {
          return "Error executing statement: " . $stmt->error;
      }
      $stmt->close();
  } else {
      return "Error preparing statement: " . $con->error;
  }
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
  $date = $_POST['date'];
  $name = $_POST['name'];
  $collection = $_POST['totalpayments'];
  $newloan = $_POST['totalloans'];
  $balance = $_POST['balance'];

  // Update the plots table
  $updateResult = updatePlot($date, $name, $collection, $newloan, $balance);

  if ($updateResult === true) {
      $successMessage = "Plot data updated successfully.";
  } else {
      $errorMessage = $updateResult; // Capture error message
  }
}

function getCentersByDateAndExecutive($selectedDate, $executiveName) {
  global $con;

  $query = "
      SELECT 
          pd.center AS center,
          pd.ccode AS ccode,
          SUM(pd.payment) AS total_payment, 
          0 AS total_document_fee,
          0 AS total_insurance_fee,
          0 AS total_loan_amount
      FROM 
          paymentdates pd
      WHERE 
          pd.name = ? 
          AND pd.payment_date = ?
      GROUP BY 
          pd.center, pd.ccode

      UNION ALL

      SELECT 
          c.center AS center,
          c.ccode AS ccode,
          0 AS total_payment, 
          SUM(c.document_fee) AS total_document_fee,
          SUM(c.insurance_fee) AS total_insurance_fee,
          SUM(c.loan_amount) AS total_loan_amount
      FROM 
          customer c
      WHERE 
          c.name = ?
          AND c.loan_date = ?
      GROUP BY 
          c.center, c.ccode
  ";

  if ($stmt = $con->prepare($query)) {
      $stmt->bind_param("ssss", $executiveName, $selectedDate, $executiveName, $selectedDate);
      $stmt->execute();
      $result = $stmt->get_result();

      $consolidatedData = [];

      while ($row = $result->fetch_assoc()) {
          $key = $row['ccode'] . '_' . $row['center'];

          if (!isset($consolidatedData[$key])) {
              $consolidatedData[$key] = $row;
          } else {
              $consolidatedData[$key]['total_payment'] += $row['total_payment'];
              $consolidatedData[$key]['total_document_fee'] += $row['total_document_fee'];
              $consolidatedData[$key]['total_insurance_fee'] += $row['total_insurance_fee'];
              $consolidatedData[$key]['total_loan_amount'] += $row['total_loan_amount'];
          }
      }

      $stmt->close();

      $centersData = array_values($consolidatedData);
      $totalPayments = array_sum(array_column($centersData, 'total_payment'));
      $totalDocumentFees = array_sum(array_column($centersData, 'total_document_fee'));
      $totalInsuranceFees = array_sum(array_column($centersData, 'total_insurance_fee'));
      $totalLoanAmount = array_sum(array_column($centersData, 'total_loan_amount'));

      return [$centersData, $totalPayments, $totalDocumentFees, $totalInsuranceFees, $totalLoanAmount];
  } else {
      die("Error preparing statement: " . $con->error);
  }
}




$centersData = [];
$totals = [];
$selectedDate = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['view_centers'])) {
    $selectedDate = $_POST['selected_date'];
    $executiveName = $_POST['executive_name'];
    list($centersData, $totalPayments, $totalDocumentFees, $totalInsuranceFees, $totalLoanAmount) = getCentersByDateAndExecutive($selectedDate, $executiveName);
    
    // Calculate total collection and balance
    $totalCollection = $totalPayments + $totalDocumentFees + $totalInsuranceFees;
    if (isset($plotData)) {
        $totalCollection += $plotData['plot'];
    }
    $balance = $totalCollection - $totalLoanAmount;
}

// Handle CSV Download separately
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['download_csv'])) {
    $selectedDate = $_POST['selected_date'];
    $executiveName = $_POST['executive_name'];
    list($centersData, $totalPayments, $totalDocumentFees, $totalInsuranceFees, $totalLoanAmount) = getCentersByDateAndExecutive($selectedDate, $executiveName);

    $query = "SELECT plot FROM plots WHERE date = ? AND name = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("ss", $selectedDate, $executiveName);
    $stmt->execute();
    $plotResult = $stmt->get_result();
    $plotData = $plotResult->fetch_assoc();
    $stmt->close();
}
    // Calculate total collection and balance
    $totalCollection = $plotData['plot'] + $totalPayments + $totalDocumentFees+ $totalInsuranceFees - $totalLoanAmount;
    
    $balance = $totalCollection ;

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="center_report.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Center NO', 'Center Name', 'Total Loan Amounts', 'Total Payments', 'Total Document Fees', 'Total Insurance Fees'));

    foreach ($centersData as $center) {
        fputcsv($output, array($center['ccode'], $center['center'],$center['total_loan_amount'], $center['total_payment'], $center['total_document_fee'], $center['total_insurance_fee']));
    }

    // Add totals to the CSV
    fputcsv($output, array('Total', '',$totalLoanAmount, $totalPayments, $totalDocumentFees, $totalInsuranceFees));
    // Add balance to the CSV
    fputcsv($output, array('', '', '', '', ''));
    fputcsv($output, array('Balance: Rs. ' . $balance . '.00'));

    fclose($output);
    exit;
}

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Center Report</title>
    <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
        }
        input[type="submit"]:hover {
            background-color: black;
            color:white;
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
<div>
    <h1><?= htmlspecialchars($branchName) ?> Branch - Center Report - Day End</h1>
    <form action="" method="post">
    <label for="selected_date">Date:</label>
    <input type="date" id="selected_date" name="selected_date" required>

    <label for="executive_name">Executive Name:</label>
    <input type="text" id="executive_name" name="executive_name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

    <input type="submit" name="view_centers" value="View Centers">
</form>

<?php
// Fetch plot information
$query = "SELECT plot FROM plots WHERE date = ? AND name = ?";
if ($stmt = $con->prepare($query)) {
    $stmt->bind_param("ss", $selectedDate, $executiveName);
    $stmt->execute();
    $plotResult = $stmt->get_result();
    $plotData = $plotResult->fetch_assoc();
    $stmt->close();
} else {
    die("Error preparing statement: " . $con->error);
}
?>



<!-- Display plot amount -->
<h2>Plot Amount: 
<?php if ($plotData): ?>
    Rs.<?= htmlspecialchars($plotData['plot']) ?>.00
<?php else: ?>
    No plot amount found for the selected date and executive.
<?php endif; ?> 
</h2>




<?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['view_centers']) && !empty($centersData)): ?>
    <h2>Center Report for <?= htmlspecialchars($selectedDate) ?></h2>
    <table border="1">
        <thead>
            <tr>
                <th>Center NO</th>
                <th>Center Name</th>
                <th>Total Loan Amounts</th>
                <th>Total Payments</th>
                <th>Total Document Fees</th>
                <th>Total Insurance Fees</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($centersData as $center): ?>
                <tr>
                    <td><?= htmlspecialchars($center['ccode']) ?></td>
                    <td><?= htmlspecialchars($center['center']) ?></td> 
                    <td><?= htmlspecialchars($center['total_loan_amount']) ?></td>
                    <td><?= htmlspecialchars($center['total_payment']) ?></td>
                    <td><?= htmlspecialchars($center['total_document_fee']) ?></td>
                    <td><?= htmlspecialchars($center['total_insurance_fee']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="2">Total</th>
                <td><?= htmlspecialchars($totalLoanAmount) ?></td>
                <td><?= htmlspecialchars($totalPayments) ?></td>
                <td><?= htmlspecialchars($totalDocumentFees) ?></td>
                <td><?= htmlspecialchars($totalInsuranceFees) ?></td>
            </tr>
        </tbody>
    </table>
    <?php
                    
                    // Calculate total collection for the center
                    $totalCollection = $plotData['plot'] + $totalPayments + $totalDocumentFees+ $totalInsuranceFees - $totalLoanAmount;
            
                    $Balance = ($totalCollection );
                    // Add plot amount if available
                    if ($plotData) {
                        $totalCollection += $plotData['plot'];
                    }
                    ?>
<h2>
Balance:Rs. <?= htmlspecialchars($Balance) ?>.00
</h2>

<?php if ($successMessage): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

<form id="paymentForm" action="" method="post">
        <!-- Your form fields here -->
        <input type="hidden" name="date" value="<?= htmlspecialchars($selectedDate) ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($executiveName) ?>">
        <input type="hidden" name="totalpayments" value="<?= htmlspecialchars($totalPayments) ?>">
        <input type="hidden" name="totalloans" value="<?= htmlspecialchars($totalLoanAmount) ?>">
        <input type="hidden" name="balance" value="<?= htmlspecialchars($totalCollection - $center['total_loan_amount']) ?>">
        <input type="submit" name="submit" value="Submit">
    </form>


    <!-- Hidden Form for Downloading -->
    <form action="" method="post">
        <input type="hidden" name="selected_date" value="<?= htmlspecialchars($selectedDate) ?>">
        <input type="hidden" name="executive_name" value="<?= htmlspecialchars($executiveName) ?>">
        <input type="submit" name="download_csv" value="Download CSV">
    </form>
<?php elseif($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['view_centers'])): ?>
    <p>No centers found for the selected date and executive.</p>
<?php endif; ?>
<?php if ($successMessage): ?>
        <div class="success-message">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php elseif ($errorMessage): ?>
        <div class="error-message">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>


        </div>
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

    document.getElementById('paymentForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            var formData = new FormData(this);

            // Send AJAX request
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    // Display success or error message
                    console.log(this.responseText);
                    // You can update the page or display a message here
                }
            };
            xhr.open('POST', window.location.href, true);
            xhr.send(formData);
        });
  </script>
</body>
</html>
