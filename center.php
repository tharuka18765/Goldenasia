<?php
session_start();
require "connecton.php";

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$status = $user['status'];
$bname = $user['bname'];
$name = $user['name'];
$search_input = '';
$selected_branch = '';
$selected_center = '';
$customer_results = [];
$branches = [];

$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
}

// Fetch branches for dropdown if user is admin
if ($status == 'admin') {
    $branchQuery = "SELECT DISTINCT bname FROM branch";
    if ($stmt = $con->prepare($branchQuery)) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $branches[] = $row['bname'];
        }
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $search_input = $_POST['search_input'];
    $selected_branch = $_POST['branch'] ?? '';
    $selected_center = $_POST['center'] ?? '';

    // Modify query to filter based on the selected branch and search input
    $sql_customer = "SELECT * FROM customer WHERE (center LIKE ? OR ccode LIKE ? AND loan_balance != 0 )";
    if ($status == 'branch_manager') {
        $sql_customer .= " AND bname = ?";
    } elseif ($status == 'executive') {
        $sql_customer .= " AND name = ?";
    } elseif ($status == 'admin' && $selected_branch) {
        $sql_customer .= " AND bname = ?";
    }
    if (!empty($selected_center)) {
        $sql_customer .= " AND center = ?";
    }

    // Prepare and execute the SQL query
    if ($stmt_customer = $con->prepare($sql_customer)) {
        $input_param = "%{$search_input}%";
        if ($status == 'branch_manager') {
            $stmt_customer->bind_param("sss", $input_param, $input_param, $bname);
        } elseif ($status == 'executive') {
            $stmt_customer->bind_param("sss", $input_param, $input_param, $name);
        } elseif ($status == 'admin' && $selected_branch) {
            $params = [$input_param, $input_param, $selected_branch];
            if (!empty($selected_center)) {
                $params[] = $selected_center;
            }
            $stmt_customer->bind_param(str_repeat('s', count($params)), ...$params);
        } else {
            $stmt_customer->bind_param("ss", $input_param, $input_param);
        }

        $stmt_customer->execute();
        $result_customer = $stmt_customer->get_result();

        while ($row = $result_customer->fetch_assoc()) {
            // Only add customers with a non-zero loan_balance
            if ($row['loan_balance'] != 0) {
                $customer_results[] = $row;
            }
        }

        $stmt_customer->close();
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Search</title>
    <link rel="stylesheet" href="styledash.css">
     <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
 <style>
        .card {
            margin-top: 20px;
            width: 100%;
            padding: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        body > div {
            margin: 30px;
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
        input[type="submit"]:hover {
            background-color: black;
            color: white;
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
        th, {
            background-color: #FAA300;
            color: white;
        }
        body > div > section > div.card > table > thead{
             background-color: #FAA300;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        body > div > section > div.card{
            padding:25px;
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
            input[type="text"],
            input[type="submit"] {
                margin: 5px 0; /* Stack inputs vertically with space between */
                width: calc(100% - 20px); /* Adjust width for smaller padding */
            }
            table, th, td {
                font-size: 14px; /* Smaller font size for table content */
            }
            body > div > section > div.card > table > thead > tr > th {
                color:white;
                background-color: #FAA300;
            
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
            <?php if ($status == 'admin'): ?>
                <div class="form-group">
                    <label for="branch">Select Branch:</label>
                    <select id="branch" name="branch" class="form-control" required>
                        <option value="">--Select Branch--</option>
                        <?php foreach ($branches as $branchName): ?>
                            <option value="<?= htmlspecialchars($branchName) ?>" <?= ($branchName == $selected_branch) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($branchName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="center">Select Center:</label>
                    <select id="center" name="center" class="form-control">
                        <option value="">--Select Center--</option>
                        <!-- Centers will be populated dynamically using JavaScript -->
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="search_input">Search by Center Name or Center Code:</label>
                <input type="text" id="search_input" name="search_input" class="form-control" value="<?= htmlspecialchars($search_input) ?>" >
            </div>

            <div class="form-group">
                <input type="submit" name="search" value="Search" class="btn btn-primary">
            </div>
        </form>
    </div>

    <?php if (!empty($customer_results)): ?>
        <div class="card">
            <h2>Customers</h2>
            <?php
            $grouped_results = [];
            foreach ($customer_results as $row) {
                $grouped_results[$row['group']][] = $row;
            }

            foreach ($grouped_results as $group_name => $customers): ?>
                <h5>Group: <?= htmlspecialchars($group_name) ?></h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Customer Code</th>
                            <th>Name</th>
                            <th>NIC</th>
                            <th>Address</th>
                            <th>Phone 1</th>
                            <th>Payments</th>
                            <th>Loan Amount</th>
                            <th>Loan Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?= htmlspecialchars($customer['customer_code']) ?></td>
                                <td><?= htmlspecialchars($customer['cname']) ?></td>
                                <td><?= htmlspecialchars($customer['nic']) ?></td>
                                <td><?= htmlspecialchars($customer['address']) ?></td>
                                <td><?= htmlspecialchars($customer['phone1']) ?></td>
                                <td><?= htmlspecialchars($customer['payment']) ?></td>
                                <td><?= htmlspecialchars($customer['loan_amount']) ?></td>
                                <td><?= htmlspecialchars($customer['loan_balance']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])): ?>
        <p>No results found for the specified input.</p>
    <?php endif; ?>
</div>

<script>
// When a branch is selected, fetch the centers for that branch
$(document).ready(function() {
    $('#branch').change(function() {
        var branchName = $(this).val();
        if (branchName !== '') {
            $.ajax({
                url: 'get_center.php',
                method: 'GET',
                data: { bname: branchName },
                success: function(data) {
                    $('#center').html(data);
                }
            });
        } else {
            $('#center').html('<option value="">--Select Center--</option>');
        }
    });
});

let arrow = document.querySelectorAll(".arrow");
for (var i = 0; i < arrow.length; i++) {
    arrow[i].addEventListener("click", (e) => {
        let arrowParent = e.target.parentElement.parentElement;
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
