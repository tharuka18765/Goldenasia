<?php
session_start();
require "connecton.php";

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

$branch = '';
$selected_date_from = '';
$selected_date_to = '';
$results = [];

// Get today's date in the format yyyy-mm-dd
$today_date = date("Y-m-d");

// Set the default date range
$default_date_from = date("Y-m-d", strtotime("-30 days"));
$default_date_to = $today_date;

// Fetch users and branches for the dropdown if the user is an admin or branch manager
$users = [];
$branches = [];
if ($status == 'branch_manager' || $status == 'admin') {
    $sql = "SELECT name FROM usertable";
    if ($status == 'branch_manager') {
        $sql .= " WHERE bname = ?";
    }
    if ($stmt = $con->prepare($sql)) {
        if ($status == 'branch_manager') {
            $stmt->bind_param("s", $user['bname']);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row['name'];
        }
        $stmt->close();
    }

    $sql = "SELECT DISTINCT bname FROM customer";
    if ($status == 'branch_manager') {
        $sql .= " WHERE bname = ?";
    }
    if ($stmt = $con->prepare($sql)) {
        if ($status == 'branch_manager') {
            $stmt->bind_param("s", $user['bname']);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $branches[] = $row['bname'];
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $branch = $_POST['branch'] ?? '';
    $name = $_POST['name'] ?? '';
    $selected_date_from = $_POST['date_from'] ?? $default_date_from;
    $selected_date_to = $_POST['date_to'] ?? $default_date_to;

    // Prepare SQL query to get report details
    $sql = "SELECT center, bname,
                   COUNT(CASE WHEN type = 'newloan' AND loan_date BETWEEN ? AND ? THEN 1 END) AS new_loans,
                   SUM(CASE WHEN type = 'newloan' AND loan_date BETWEEN ? AND ? THEN loan_amount ELSE 0 END) AS new_loan_amount,
                   COUNT(CASE WHEN type = 'renewloan' AND loan_date BETWEEN ? AND ? THEN 1 END) AS renewed_loans,
                   SUM(CASE WHEN type = 'renewloan' AND loan_date BETWEEN ? AND ? THEN loan_amount ELSE 0 END) AS renewed_loan_amount,
                   COUNT(CASE WHEN type = 'oldloan' AND loan_date BETWEEN ? AND ? THEN 1 END) AS old_loans,
                   SUM(CASE WHEN type = 'oldloan' AND loan_date BETWEEN ? AND ? THEN loan_amount ELSE 0 END) AS old_loan_amount
            FROM customer WHERE loan_balance != 0";

    // Add conditions based on the branch and name
    $conditions = [];
    $params = [$selected_date_from, $selected_date_to, $selected_date_from, $selected_date_to, 
               $selected_date_from, $selected_date_to, $selected_date_from, $selected_date_to, 
               $selected_date_from, $selected_date_to, $selected_date_from, $selected_date_to];

    if ($branch) {
        $conditions[] = "bname = ?";
        $params[] = $branch;
    }
    if ($name) {
        $conditions[] = "name = ?";
        $params[] = $name;
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // Group by center
    $sql .= " GROUP BY center";

    // Prepare and execute the statement
    if ($stmt = $con->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        $stmt->close();
    }

    $con->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lending Reports</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function downloadCSV(csv, filename) {
            var csvFile;
            var downloadLink;

            csvFile = new Blob([csv], {type: "text/csv"});

            downloadLink = document.createElement("a");

            downloadLink.download = filename;

            downloadLink.href = window.URL.createObjectURL(csvFile);

            downloadLink.style.display = "none";

            document.body.appendChild(downloadLink);

            downloadLink.click();
        }

        function exportTableToCSV(filename) {
            var csv = [];
            var rows = document.querySelectorAll("table tr");

            for (var i = 0; i < rows.length; i++) {
                var row = [], cols = rows[i].querySelectorAll("td, th");

                for (var j = 0; j < cols.length; j++) {
                    let cellValue = cols[j].innerText;
                    // Check if the cell contains a date or a long number and wrap it in double quotes
                    if (cols[j].innerText && (isNaN(cols[j].innerText) == false || isNaN(Date.parse(cols[j].innerText)) == false)) {
                        cellValue = `"${cols[j].innerText}"`; // Wrap in quotes to preserve formatting
                    }
                    row.push(cellValue);
                }

                csv.push(row.join(","));
            }

            downloadCSV(csv.join("\n"), filename);
        }
    </script>
    <style>
        .card {
            margin-top:20px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        body > div {
            margin: 30px 30px 30px 30px;
        }
        .card h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 60%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        body > div > section > div:nth-child(6) > button,#deleteButton {
            width: 20%;
            padding: 10px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #deleteButton {
            margin-top:20px;
            margin-bottom:20px;
        }
        input[type="submit"],body > div > section > div > button {
            width: 40%;
            padding: 10px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #deleteButton,body > div > section > div> button:hover {
            background-color: black;
            color: white;
        }
        body > div:nth-child(2) > button, body > div > section > div:nth-child(6) > button {
            background-color: black;
            color: white;
        }
        input[type="submit"]:hover {
            background-color: black;
            color: white;
        }
        table {
            border: 1px solid black;
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
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
        th, td {
            padding: 4px;
            text-align: left;
            font-size: 16px;
        }
        #branch,#name,#date_from,#date_to{
            width: 300px;
           
            
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
    font-size: 16px;
    cursor: pointer;
        }body > div > section > div > form{
padding:20px;
        }body > div > section > div:nth-child(5) > button{
            margin-top:10px;
            margin-bottom:15px;
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

<h2>Lending Details</h2><div class="user-details">
<hr>

</div>
</div>
<hr>
<div>
    
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <div>
            <?php if ($user['status'] == 'admin' || $user['status'] == 'branch_manager'): ?>
                <label for="branch">Branch:</label>
                <select name="branch" id="branch">
                    <?php if ($user['status'] == 'admin'): ?>
                        <option value="">-- All Branches --</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?php echo htmlspecialchars($branch); ?>"><?php echo htmlspecialchars($branch); ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="<?php echo htmlspecialchars($user['bname']); ?>"><?php echo htmlspecialchars($user['bname']); ?></option>
                    <?php endif; ?>
                </select>
                <br>
                <label for="name">User:</label>
                <select name="name" id="name">
                    <option value="">-- All Users --</option>
                    <?php foreach ($users as $username): ?>
                        <option value="<?php echo htmlspecialchars($username); ?>"><?php echo htmlspecialchars($username); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="hidden" name="branch" value="<?php echo htmlspecialchars($user['bname']); ?>">
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
            <?php endif; ?>
            <br>
            <label for="date_from">From:</label>
            <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($default_date_from); ?>" required>
            <label for="date_to">To:</label>
            <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($default_date_to); ?>" max="<?php echo htmlspecialchars($today_date); ?>" required>
            <br>
            <input type="submit" name="submit" value="Generate">
        </div>
    </form>
</div>
<div>
<?php if (!empty($results)): ?>
    <h2>Lending Details</h2>
    <table border="1">
        <tr>
            <th>Center Name</th>
            <th>Number of New Loans</th>
            <th>New Loan Total Amounts</th>
            <th>Number of Renewed Loans</th>
            <th>Renewed Loan Amounts</th>
      
        </tr>
        <?php
        // Initialize totals
        $total_new_loans = 0;
        $total_new_loan_amount = 0;
        $total_renewed_loans = 0;
        $total_renewed_loan_amount = 0;
        $total_old_loans = 0;
        $total_old_loan_amount = 0;

        foreach ($results as $row):
            $total_new_loans += $row['new_loans'];
            $total_new_loan_amount += $row['new_loan_amount'];
            $total_renewed_loans += $row['renewed_loans'];
            $total_renewed_loan_amount += $row['renewed_loan_amount'];
            $total_old_loans += $row['old_loans'];
            $total_old_loan_amount += $row['old_loan_amount'];
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['center']); ?></td>
                <td><?php echo htmlspecialchars($row['new_loans']); ?></td>
                <td><?php echo htmlspecialchars($row['new_loan_amount']); ?></td>
                <td><?php echo htmlspecialchars($row['renewed_loans']); ?></td>
                <td><?php echo htmlspecialchars($row['renewed_loan_amount']); ?></td>
             
            </tr>
        <?php endforeach; ?>
        <tr>
            <td><strong>Totals</strong></td>
            <td><strong><?php echo htmlspecialchars($total_new_loans); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($total_new_loan_amount); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($total_renewed_loans); ?></strong></td>
            <td><strong><?php echo htmlspecialchars($total_renewed_loan_amount); ?></strong></td>
          
        </tr>
    </table>
    <button onclick="exportTableToCSV('center_repayment_details.csv')">Download CSV</button>
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
