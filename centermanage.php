<?php
session_start();
require "connecton.php";

$user = $_SESSION['user'];
$status = $user['status'];
$bname = $user['bname'];
$name = $user['name'];
$results_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare SQL query based on user status
$sql = "SELECT * FROM branch";
$params = [];
$types = '';

if ($status == 'branch_manager') {
    $sql .= " WHERE bname = ?";
    $params[] = $bname;
    $types .= 's';
} elseif ($status == 'executive') {
    $sql .= " WHERE name = ?";
    $params[] = $name;
    $types .= 's';
}

if (!empty($search_query)) {
    $search_query = "$search_query";
    if ($status == 'admin') {
        $sql .= " WHERE (center LIKE ? OR ccode LIKE ?)";
    } else {
        $sql .= " AND (center LIKE ? OR ccode LIKE ?)";
    }
    $params[] = $search_query;
    $params[] = $search_query;
    $types .= 'ss';
}

$start = ($page - 1) * $results_per_page;
$sql .= " ORDER BY bname, id LIMIT ?, ?";
$params[] = $start;
$params[] = $results_per_page;
$types .= 'ii';

// Prepare and execute the statement
if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();
}

$total_sql = "SELECT COUNT(*) as total FROM branch";
$total_stmt = $con->prepare($total_sql);
$total_stmt->execute();
$total_result = $total_stmt->get_result()->fetch_assoc();
$total_pages = ceil($total_result['total'] / $results_per_page);

$con->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Branch Details</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
    <script>
        function saveChanges(rowId) {
            var row = document.querySelector(`tr[data-id='${rowId}']`);
            var cols = row.querySelectorAll("td");
            var data = {
                id: rowId,
                bname: cols[0].innerText,
                bcode: cols[1].innerText,
                day: cols[2].innerText,
                center: cols[3].innerText,
                ccode: cols[4].innerText,
                group: cols[5].innerText,
                name: cols[6].innerText
            };

            // Send the updated data to the server via AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_branch.php", true);
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    alert("Changes saved successfully!");
                }
            };
            xhr.send(JSON.stringify(data));
        }

        function deleteRow(rowId) {
            if (confirm("Are you sure you want to delete this row?")) {
                // Send the delete request to the server via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_branch.php", true);
                xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert("Row deleted successfully!");
                        document.querySelector(`tr[data-id='${rowId}']`).remove();
                    }
                };
                xhr.send(JSON.stringify({ id: rowId }));
            }
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
        body > div > section > form{
            margin-top:30px;
            margin-bottom:20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 40%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        body > div > section > div:nth-child(6) > button,#deleteButton {
            width: 299px;
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
            width: 30%;
            padding: 10px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        body > div > section > table > tbody > tr > td > button{
            width: 70px;
            padding: 5px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            color:white;
        }
        #deleteButton,body > div > section > div> button,body > div > section > table > tbody > tr > td > button:hover {
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
            padding:10px;
            margin-top:20px;
            border: 1px solid black;
            border-collapse: collapse;
            width: 100%;
        }
        table, th {
            height:60px;
        }
        table, th, td {
            padding:10px;
            border: 1px solid black;
            border-collapse: collapse;
        }
        table, th, td {
            padding:10px;
            border: 1px solid #dee2e6;
        }
        th, td {
            padding:10px;
            text-align: left;
            padding: 8px;
        }
        th {
            padding:10px;
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
            padding: 10px;
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
        .pagination {
            display: flex;
            justify-content: right;
            align-items: right;
            list-style: none;
            padding: 0;
            margin-right:30px;
            margin-bottom:30px;
            margin-top:20px;
        }

        .pagination li {
            margin: 0 5px;
        }
body > div > section > ul{
    margin-bottom:30px;
}
        .pagination a {
            display: block;
            padding: 8px 16px;
            text-decoration: none;
            color: #FAA300;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: background-color 0.3s, color 0.3s;
        }

        .pagination a:hover {
            background-color: black;
            color: #fff;
        }

        .pagination a.active {
            background-color: #FAA300;
            color: #fff;
            
        }

        .pagination .disabled {
            pointer-events: none;
            opacity: 0.6;
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

<h2>Branch Details</h2><div class="user-details">
<hr>

</div>
</div>
<hr>
   
    <form method="get" action="">
        <input type="text" name="search" placeholder="Search by center or ccode" value="<?php echo htmlspecialchars($search_query); ?>">
        <input type="submit" value="Search">
    </form>
    <table>
        <tr>
            <th>Branch Name</th>
            <th>Branch Code</th>
            <th>Day</th>
            <th>Center</th>
            <th>Center Code</th>
            <th>Group</th>
            <th>Name</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($results as $row): ?>
            <tr data-id="<?php echo htmlspecialchars($row['id']); ?>">
                <td><?php echo htmlspecialchars($row['bname']); ?></td>
                <td><?php echo htmlspecialchars($row['bcode']); ?></td>
                <td contenteditable="true"><?php echo htmlspecialchars($row['day']); ?></td>
                <td contenteditable="true"><?php echo htmlspecialchars($row['center']); ?></td>
                <td contenteditable="true"><?php echo htmlspecialchars($row['ccode']); ?></td>
                <td contenteditable="true"><?php echo htmlspecialchars($row['group']); ?></td>
                <td contenteditable="true"><?php echo htmlspecialchars($row['name']); ?></td>
                <td>
                    <button onclick="saveChanges('<?php echo htmlspecialchars($row['id']); ?>')">Save</button>
                    <button onclick="deleteRow('<?php echo htmlspecialchars($row['id']); ?>')">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <ul class="pagination">
        <?php if ($page > 1): ?>
            <li><a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a></li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li><a class="<?php echo $i == $page ? 'active' : ''; ?>" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <li><a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a></li>
        <?php endif; ?>
    </ul>
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
