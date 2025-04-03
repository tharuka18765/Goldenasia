<?php
session_start();
require "connecton.php";

if (!isset($_SESSION['user']) || $_SESSION['user']['status'] != 'admin') {
    header('location: login-user.php');
    exit();
}

$user = $_SESSION['user'];
$results = [];
$current_date = date("Y-m-d");

// Fetch loan balance and count data grouped by branch and center
$sql = "SELECT bname AS branch, center, SUM(loan_balance) AS total_amount, COUNT(*) AS total_count
        FROM customer
        WHERE loan_balance != 0 AND due_date <= ?
        GROUP BY bname, center
        ORDER BY bname, center";

if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param("s", $current_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $branch = $row['branch'];
        if (!isset($results[$branch])) {
            $results[$branch] = [];
        }
        $results[$branch][] = $row;
    }

    $stmt->close();
}

$con->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Report</title>
  
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #deleteButton:hover {
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
            padding:15px;
            margin-bottom:20px;
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
        .branch-section {
            margin-bottom: 30px;
        }
        .branch-title {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        }body > div > section > div.header-container > div > div > div > table > tbody > tr.total-row{
            background-color: #FAA300;
            color: white;
            font-size:15px;
            font-weight:bold;
        }
    </style>
    <script>
        function selectAllCheckboxes() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });

            enableDeleteButton();
        }

        function enableDeleteButton() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            var deleteButton = document.getElementById('deleteButton');
            var anyChecked = Array.from(checkboxes).some(function(cb) {
                return cb.checked;
            });
            deleteButton.disabled = !anyChecked;
        }

        function confirmDelete() {
            return confirm('Are you sure you want to delete selected records?');
        }
    </script>
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
  <li><a href="old.php">Old Loans</a></li>
  
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
  <li>
    <div class="iocn-link">
    <a href="dayendreport.php">
        <i class='bx bxs-report' ></i>
        <span class="link_name">Center Report</span>
      </a>
      
    </div>
</li>
<?php if ($status == 'admin'): ?>
<li>
    <div class="iocn-link">
    <a href="branchday.php">
        <i class='bx bxs-report' ></i>
        <span class="link_name">Branch Report</span>
      </a>
      
    </div>
</li>
<?php endif; ?>
  
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
  <li><a href="registeruser.php">New Executive</a></li>
  <li><a href="manage_ex.php">Manage Executive</a></li>
  <li><a href="addCenter.php">New Branch</a></li>
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

<h2>Stock Report</h2><div class="user-details">
<hr>

    <div class="container mt-5">
    
        <?php foreach ($results as $branch => $centers): ?>
            <?php
            // Initialize totals for the branch
            $branch_total_amount = 0;
            $branch_total_count = 0;
            foreach ($centers as $center) {
                $branch_total_amount += $center['total_amount'];
                $branch_total_count += $center['total_count'];
            }
            ?>
            <div class="branch-section">
                <div class="branch-title">Branch: <?= htmlspecialchars($branch) ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>Center</th>
                            <th>Amount</th>
                            <th>Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($centers as $center): ?>
                            <tr>
                                <td><?= htmlspecialchars($center['center']) ?></td>
                                <td><?= number_format(htmlspecialchars($center['total_amount']), 2) ?></td>
                                <td><?= htmlspecialchars($center['total_count']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Total row for the branch -->
                       <strong><tr class="total-row" >
                            <td>Total</td>
                            <td><?= number_format($branch_total_amount, 2) ?></td>
                            <td><?= $branch_total_count ?></td>
                        </tr></strong> 
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
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
