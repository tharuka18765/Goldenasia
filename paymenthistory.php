<?php
session_start();
require "connecton.php"; // Ensure this path is correct

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user = $_SESSION['user'];
$branchName = $user['bname'];
$status = $user['status'];
$link = 'newloan.php';
if ($status == 'admin') {
    $link = 'aloan.php';
}
function getCustomerDetailsByNIC($con, $nic) {
    $stmt = $con->prepare("
        SELECT c.cname, c.nic, c.loan_code, c.loan_amount, c.loan_balance, pd.due_date_weekly, c.image, pd.payment, pd.week_number,pd.name , pd.payment_date
        FROM customer AS c
        JOIN paymentdates AS pd ON c.nic = pd.nic
        WHERE c.nic = ? OR c.customer_code = ?
    ");
    $stmt->bind_param("ss", $nic,$nic);
    $stmt->execute();
    $result = $stmt->get_result();
    $customerDetails = [];
    while ($row = $result->fetch_assoc()) {
        $customerDetails[] = $row;
    }
    $stmt->close();
    return $customerDetails;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'searchCustomer') {
    $nic = $_POST['nic'];
    if (!empty($nic)) {
        $customerDetails = getCustomerDetailsByNIC($con, $nic);
        header('Content-Type: application/json');
        echo json_encode($customerDetails ?: ['error' => 'No customer found for this NIC']);
    } else {
        echo json_encode(['error' => 'NIC is required']);
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Payment</title>
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
            color: black;
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
    .customer-info {
        margin-top:40px;
        margin-left:100px;
    background:transparent;
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 15px;
    border-radius:8px;
    
}

.customer-info h3 {
    color: #333;
    font-size: 24px;
    margin: 0 0 10px 0;
}

.customer-info p {
    margin-top:10px;
}

.customer-info ul {
    margin-top:10px;
    list-style-type: none;
    padding: 0;
}
#search-results > div > div{
    margin-top:10px;
}
.customer-info ul li {
    background-color: #fff;
    border: 1px solid #eee;
    margin-bottom: 5px;
    padding: 5px;
}
#search-results > div > p{
    font-size:20px;
    font-weight:bold;
}
#search-results > div > div:nth-child(1) > div > p{
    font-size:20px;
    font-weight:bold;
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
    <h3><?php echo htmlspecialchars($branchName); ?> Branch - Single Payment</h3>
    <br>
    <div>
    <div class="search-container">
        <input type="text" id="nic-search" placeholder="Search by NIC...">
        <button id="search-btn">Search</button>
    </div>
</div>
    <div id="search-results">
        
    
    <!-- The customer payment details will be loaded here -->
</div>

    <script>
  $(document).ready(function() {
    $('#search-btn').click(function() {
        var nic = $('#nic-search').val();
        if (nic) {
            $.ajax({
    type: 'POST',
    url: 'paymenthistory.php',
    data: {
        action: 'searchCustomer',
        nic: nic
    },
    dataType: 'json',
    success: function(data) {
    if (!data || data.error) {
        $('#search-results').html(`<p>${data.error || 'Error fetching data'}</p>`);
    } else {
        var output = '<div class="customer-info">';
        output += `<div style="display: flex; align-items: center;"><img src="customer_images/${data[0].image}" alt="Customer Image" style="width: 100px; height: 100px; border-radius: 50%; margin-right: 20px;">
                   <div><h3>${data[0].cname} - ${data[0].loan_code}</h3>
                   <p>Loan Amount: ${parseFloat(data[0].loan_amount).toFixed(2)}</p></div></div>`;

        data.forEach(function(payment) {
            var paymentAmount = payment.payment ? parseFloat(payment.payment).toFixed(2) : "0.00";
            output += `<div>Week ${payment.week_number} - Due Date: ${payment.due_date_weekly} - Payment: ${paymentAmount} - Executive : ${payment.name} - Payment Date: ${payment.payment_date} </div>`;
        });

        output += `<p>Current Balance: ${parseFloat(data[0].loan_balance).toFixed(2)}</p></div>`;
        $('#search-results').html(output);
    }
},
    error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
    }
});

        } else {
            alert('Please enter a NIC.');
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




</script>
</body>
</html>