<?php
session_start();
require "connecton.php";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}

// Get user information
$user = $_SESSION['user'];
$status = $user['status'];

$link = ($status == 'admin') ? 'aloan.php' : 'newloan.php';
$link1 = ($status == 'admin') ? 'aold.php' : 'old.php';

$report_data = []; // Array to store report data for CSV export

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Branch Report</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

h2{
  margin-left:40px;
  margin-bottom:10px;
}
        .form-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 80%;
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

.input-container label {
    margin-right: 15px;
}

input[type="date"],
        select#bname {
            padding: 8px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            appearance: none; /* Removes default appearance */
            background-color: white;
       
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 12px;
        }


.submit-button {
  margin-top:2px;
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

.submit-button:hover {
    background-color: black;
    color: white;
}
body > div > div:nth-child(2){
    margin-left:280px;
    width:auto;
}
table {
            width: 80%;
            margin-left:60px;
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
    body > div > div:nth-child(2) > h2:nth-child(3){
        margin-left:60px;
    }
    body > div > section > div.header-container > div > form > button{
      margin-left:70px;
      margin-top:20px;
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
<div class="header-container">
    <h2>Branch Report</h2>
</div>
    <hr>
    <form action="" method="post" class="form-container">
        <div class="input-container">
            <label for="selected_date">Select Date:</label>
            <input type="date" id="selected_date" name="selected_date" required><br>
        </div>
        <div class="input-container">
            <label for="bname">Select Branch:</label>
            <?php
            require_once "connecton.php";

            $query = "SELECT DISTINCT bname FROM plots";
            $result = $con->query($query);

            if ($result) {
                echo '<select id="bname" name="bname" required>';
                while ($row = $result->fetch_assoc()) {
                    $branch = $row['bname'];
                    echo "<option value='$branch'>$branch</option>";
                }
                echo '</select><br>';
            } else {
                echo "Error fetching branch names: " . $con->error;
            }
            ?>
        </div>
        <button type="submit" class="submit-button">View</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['selected_date']) && isset($_POST['bname'])) {
        $selected_date = $_POST['selected_date'];
        $bname = $_POST['bname'];

        // Prepare SQL statement to fetch records from the plots table
        $stmt = $con->prepare("SELECT name, plot, collection, newloan, balance FROM plots WHERE date = ? AND bname = ?");
        if (!$stmt) {
            die("Error preparing statement: " . $con->error);
        }

        $stmt->bind_param("ss", $selected_date, $bname);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<h2>Branch Report for $bname on $selected_date</h2>";
            echo "<table border='1'>";
            echo "<tr>
                    <th>Name</th>
                    <th>Plot</th>
                    <th>Collection</th>
                    <th>New Loan</th>
                    <th>Renewed Loan</th>
                    <th>Balance</th>
                  </tr>";

            $total_plot = $total_collection = $total_newloan = $total_renewloan = $total_balance = 0;

            while ($row = $result->fetch_assoc()) {
                $name = $row['name'];

                // Fetch new loan amounts from the customer table
                $stmt2 = $con->prepare("SELECT SUM(loan_amount) AS newloan_amount FROM customer WHERE name = ? AND loan_date = ? AND type = 'newloan'");
                $stmt2->bind_param("ss", $name, $selected_date);
                $stmt2->execute();
                $newloan_result = $stmt2->get_result();
                $newloan_amount = $newloan_result->fetch_assoc()['newloan_amount'] ?? 0;
                $stmt2->close();

                // Fetch renewed loan amounts from the customer table
                $stmt3 = $con->prepare("SELECT SUM(loan_amount) AS renewloan_amount FROM customer WHERE name = ? AND loan_date = ? AND type = 'renewloan'");
                $stmt3->bind_param("ss", $name, $selected_date);
                $stmt3->execute();
                $renewloan_result = $stmt3->get_result();
                $renewloan_amount = $renewloan_result->fetch_assoc()['renewloan_amount'] ?? 0;
                $stmt3->close();

                echo "<tr>";
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row['plot'] . "</td>";
                echo "<td>" . $row['collection'] . "</td>";
                echo "<td>" . $newloan_amount . "</td>";
                echo "<td>" . $renewloan_amount . "</td>";
                echo "<td>" . $row['balance'] . "</td>";
                echo "</tr>";

                // Accumulate totals
                $total_plot += $row['plot'];
                $total_collection += $row['collection'];
                $total_newloan += $newloan_amount;
                $total_renewloan += $renewloan_amount;
                $total_balance += $row['balance'];

                // Store data for CSV
                $report_data[] = [
                    $name,
                    $row['plot'],
                    $row['collection'],
                    $newloan_amount,
                    $renewloan_amount,
                    $row['balance']
                ];
            }

            // Display totals at the end of the table
            echo "<tr>
                    <td><strong>Total</strong></td>
                    <td><strong>$total_plot</strong></td>
                    <td><strong>$total_collection</strong></td>
                    <td><strong>$total_newloan</strong></td>
                    <td><strong>$total_renewloan</strong></td>
                    <td><strong>$total_balance</strong></td>
                  </tr>";
            echo "</table>";

            // Store totals in the CSV data
            $report_data[] = [
                'Total',
                $total_plot,
                $total_collection,
                $total_newloan,
                $total_renewloan,
                $total_balance
            ];
        } else {
            echo "No records found for the selected date and branch.";
        }

        $stmt->close();
        $con->close();
    }
    ?>

    <!-- Download as CSV button -->
    <form action="download_csv.php" method="post">
        <input type="hidden" name="report_data" value="<?php echo htmlentities(serialize($report_data)); ?>">
        <button type="submit" class="submit-button">Download as CSV</button>
    </form>

<script>
    let arrow = document.querySelectorAll(".arrow");
    for (var i = 0; i < arrow.length; i++) {
        arrow[i].addEventListener("click", (e) => {
            let arrowParent = e.target.parentElement.parentElement; // selecting main parent of arrow
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
