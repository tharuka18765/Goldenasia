
<?php
session_start();
require "connecton.php"; // Corrected the filename to "connection.php"

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}

// Get user information
$user = $_SESSION['user'];
$status = $user['status'];


$link1 = 'old.php';
if ($status == 'admin') {
    $link1 = 'aold.php';
}

// Fetch distinct branch names from the database
$sql = "SELECT DISTINCT bname FROM branch";
$result = $con->query($sql);

$branch_names = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $branch_names[] = $row;
    }
}

$holiday_sql = "SELECT name, date, cname, time FROM holiday";
$holiday_result = $con->query($holiday_sql);

$holidays = [];
if ($holiday_result->num_rows > 0) {
    while($row = $holiday_result->fetch_assoc()) {
        $holidays[] = $row;
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Center Form</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="styledash.css">
  <!-- Boxiocns CDN Link -->
  <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        /* Optional: Add custom styles here */
        
         body > div{
      margin-top:200px;
    }
        body > div > section > div > div{
          margin-top:800px;
          width:900px;
          margin-right:1000px;
        }
        body > div > div.container > h1{
          margin-top:10px;
        }body > div > section > div > div > h1{
          margin-right:400px;
        }
        h1 {
            
            margin-bottom: 14px;
            color: #343a40;
        }
        .form-container {
         
            width:1100px;
            padding: 30px;
            
        }
        .form-group label {
            font-weight: bold;
            font-size:19px;
        }
        
        .form-control {
            border-radius: 6px;
            height: 45px;
        }
        
        .btn-primary {
    font-weight: 500;
    text-transform: uppercase;
    width: 60%;
    padding: 10px;
    background-color: #FAA300;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

.btn-primary:hover {
    background-color: #333;
    color: white;
}
        .form-group {
            margin-bottom: 20px;
        }
        #center,#date,#bname{
          padding:10px;
          width:340px;
          margin-right:200px;
          font-size:15px;
          margin-left:20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #FAA300;
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
        
<div>
    <div class="container" >
    <h1> Add Holiday For Centers</h1>
    <hr>
        <div class="row ">
          
            <div class="col-md-6">
                <div class="form-container">
                    <form id="centerForm" method="POST" action="holidayn.php">
                        <div class="form-group">
                            <label for="center">Select Center:</label>
                            <select id="center" name="center" class="form-control" required>
                                <option value="">Select a center</option>
                                <?php
                                include 'holidayn.php';
                                foreach ($centers as $center) {
                                    echo "<option value='{$center['center']}'>{$center['center']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Select Date:</label>
                            <input type="date" id="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <h1>Add Holiday For Branches</h1>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <div class="form-container">
                    <form id="branchForm" method="POST" action="holidayb.php">
                        <div class="form-group">
                            <label for="bname">Select Branch:</label>
                            <select id="bname" name="bname" class="form-control" required>
                                <option value="">Select a branch</option>
                                <?php
                                include 'connecton.php';
                                $sql = "SELECT DISTINCT bname FROM branch";
                                $result = $con->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['bname']}'>{$row['bname']}</option>";
                                    }
                                }
                                $con->close();
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Select Date:</label>
                            <input type="date" id="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <h1>Add Holiday For All Branches</h1>
        <hr>
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="form-container">
                    <form id="branchForm" method="POST" action="holidayc.php">
                        <div class="form-group">
                            <label for="date">Select Date:</label>
                            <input type="date" id="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
                <h1>Holiday Details</h1>
                <hr>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Holiday Date</th>
                            <th>Center/Branch Name</th>
                            <th>selected Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($holidays as $holiday) {
                            echo "<tr>
                                    <td>{$holiday['name']}</td>
                                    <td>{$holiday['date']}</td>
                                    <td>{$holiday['cname']}</td>
                                    <td>{$holiday['time']}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
                             
        </div>

                              </div>
        
                              </section>
    <!-- Bootstrap JS (Optional) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
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
