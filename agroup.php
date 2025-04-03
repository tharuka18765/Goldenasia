<?php
session_start();
require "connecton.php"; // Ensure this is the correct path to your connection file



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

$groupAddedSuccess = false;
$errorMessage = ''; 

if (isset($_POST['submit'])) {
    $bname = mysqli_real_escape_string($con, $_POST['bname']);
    $bcode = mysqli_real_escape_string($con, $_POST['bcode']);
    $center = mysqli_real_escape_string($con, $_POST['center']);
    $centerCode = mysqli_real_escape_string($con, $_POST['ccode']);
    $groupName = mysqli_real_escape_string($con, $_POST['groupName']);

    // Check for duplicate group number in the same center
    $duplicateCheckQuery = "SELECT * FROM branch WHERE center = ? AND `group` = ?";
    $stmt = mysqli_prepare($con, $duplicateCheckQuery);
    mysqli_stmt_bind_param($stmt, "ss", $center, $groupName);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $errorMessage = "A group with this number already exists in the center.";
    } else {
        // Proceed with inserting the new group
        $insertQuery = "INSERT INTO branch (bname, bcode, center, ccode, `group`) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $insertQuery);
        mysqli_stmt_bind_param($stmt, 'sssss', $bname, $bcode, $center, $centerCode, $groupName);


        if (mysqli_stmt_execute($stmt)) {
            $groupAddedSuccess = true;
        } else {
            $errorMessage = "Error: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Dropdown with AJAX</title>
    <link rel="stylesheet" href="styledash.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    background: url('path-to-your-background.jpg') no-repeat center center fixed;
    background-size: cover;
   
}

form {
    width:1000px;
    background:transparent;
    margin-top:35px;
    border-radius: 8px;
    margin-left:140px;
    
}
body > div > section > hr {
    margin-left:10px;
    width: 85%;
    border: 1px solid #FAA300; /* Set the height and color of the hr line */
     /* Center the hr element within its container */
}
body > div > section > div.header-container > h2{
    margin-left:50px;
}
body > h2{
    margin-left:340px;
}
h3 {
    color: #333;
}

input[type="text"],
input[type="tel"],
input[type="number"],
input[type="email"],
input[type="password"],
input[type="date"],
select,
input[type="file"] {
    width: 100%;
    padding: 8px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

input[type="submit"] {
    margin-left:100px;
    width:60%;
    font-weight:500;
    text-transform:uppercase;
   
    padding: 10px;
    background-color: #FAA300;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

input[type="submit"]:hover {
    background-color: black;
    color:white;
}


.user-details p {
    color: #333;
}
select {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    color: #333;
    font-size: 16px;
    cursor: pointer;
}
.form-row {
    display: flex;
    justify-content: space-between; /* This separates the two dropdowns */
    margin-bottom: 20px; /* Adds some space below the row */
}

.form-group {
    flex: 1; /* Allows each .form-group to take up equal space */
    margin-right: 10px; /* Adds some space between the dropdowns */
}

.form-group:last-child {
    margin-right: 0; /* Removes margin from the last .form-group */
}


body > form > div.user-details{
    font-size:20px;
}
.header-container {
    display: flex; /* Use flexbox for horizontal layout */
    align-items: center; /* Center items vertically */
    justify-content: space-between; /* Space out the header and user details */
}

.header-container h2 {
    margin-right: 20px; /* Add space between the header and user details */
}
.user-details {
            display: flex; /* Align items in a row */
            justify-content: start; /* Start aligning items from the start */
            align-items: center; /* Center items vertically */
    
            background:transparent; /* Light grey background */
            /* Rounded corners */
            margin: 10px 0; 
            font-size: 22px;/* Margin for spacing */
            /* Soft shadow for depth */
        }
        
       .user-details span, .user-details input[type="text"] {
        background:transparent;
            margin-right: 20px; /* Space between elements */
            font-size: 20px; /* Uniform font size */
        }

        .user-details .branch-name, .user-details .branch-code {
            font-weight: bold; /* Make branch name and code bold */
        }
        #currentDate{
            border:none;
        }
.branch-name, .branch-code {
    color: #333; /* Dark text for readability */
    font-weight: bold; /* Make text bold */
   /* Appropriate text size */
}

.branch-code {
    background:transparent; /* Blue background for the code */
    color: black; /* White text for contrast */
    padding: 2px 6px; /* Padding around the text */
     /* Rounded corners */
}
.message-container {
        text-align: center;
        margin-top: 20px;
    }

    .success-message {
        color: #fff;
        background-color: #4CAF50;
        padding: 15px;
        border-radius: 5px;
        width: 60%;
        margin: auto;
    }

    .error-message {
        color: #fff;
        background-color: #f44336;
        padding: 15px;
        border-radius: 5px;
        width: 60%;
        margin: auto;
    }
    .custom-file-upload {
    display: inline-block;
    padding: 10px 20px;
    cursor: pointer;
    background-color: #4CAF50;
    color: white;
    border-radius: 5px;
    margin-right: 10px;
    margin-bottom:15px;
}

#file-chosen {
    border:1px solid black;
    margin-left: 10px;
    padding: 10px;
    background-color: #fff;
    border-radius: 5px;
    color: #333;
}
.logo-details a {
  text-decoration: none; /* Remove underline */
  color: inherit; /* Use the default color */
}

@media (max-width: 768px) {
    form {
        width: 95%;
        margin-top: 20px;
    }

    .form-row {
        flex-direction: column;
    }

    .form-group {
        margin-right: 0;
        margin-bottom: 10px;
    }

    .input-box i {
        top: 50%;
    }

    h2, h3 {
        font-size: 16px; /* Further reduced font size for smaller headers */
    }
}

@media (max-width: 480px) {

    body > div > section > form > div{
        margin-left:10px;
        width:230px;
    }
    input, select {
        padding: 6px; /* Even smaller padding */
        font-size: 14px; /* Smaller font size */
    }

    input[type="submit"] {
        font-size: 14px;
        padding: 8px;
    }

    .custom-file-upload, #file-chosen {
        width: 100%; /* Full width for smaller devices */
        margin-bottom: 10px;
        padding: 4px 8px; /* Reduced padding for smaller devices */
    }

    .user-details span, .user-details input[type="text"] {
        font-size: 14px; /* Reduced font size for user details */
    }
}
body > div > section > div.header-container > div > div{
    display: flex; 
    
}
body > div > section > div.header-container > div > div > label{
    margin-top:15px;
    margin-right:20px;
}
body > div > section > div.header-container > div > span:nth-child(4){
    margin-right:100px;
}
</style>
</head>
<body>
<?php if ($groupAddedSuccess): ?>
    <div class="success-message">Group added successfully!</div>
<?php elseif (!empty($errorMessage)): ?>
    <div class="error-message"><?php echo $errorMessage; ?></div>
<?php endif; ?>
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

<h2>Customer Registartion</h2> <div class="user-details">
    <hr>

</div>
</div>
<hr>

<form action="agroup.php" method="post">
<input type="hidden" name="executive_name" value="<?php echo htmlspecialchars($user['name']); ?>">


<input type="hidden" name="type" value="newloan">


<div class="form-row">
        <div class="form-group">
            <label for="branch">Branch:</label>
            <select id="branch" name="bname">
            <option value="">Select Branch</option>
                <!-- Branch options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="bcode">BCode:</label>
            <input type="text" id="bcode" name="bcode" readonly>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="center">Center:</label>
            <select id="center" name="center" disabled>
                <option value="">Select Center</option>
                <!-- Center options will be populated here -->
            </select>
        </div>
        
        <div class="form-group">
            <label for="ccode">CCode:</label>
            <input type="text" id="ccode" name="ccode" readonly>
        </div>
    </div>
    <div class="form-group">
    <label for="groupName">Group Number:</label>
<select name="groupName" required>
    <?php
    $nextGroup = isset($nextGroup) ? $nextGroup : "001"; // Assuming $nextGroup holds the current value or default to "001"
    for ($i = 1; $i <= 20; $i++) {
        $formattedNumber = sprintf("%03d", $i); // Format the number with leading zeros
        echo "<option value=\"$formattedNumber\"" . ($nextGroup == $formattedNumber ? " selected" : "") . ">$formattedNumber</option>";
    }
    ?>
</select>
</div>
        
        
    

    <input type="submit" name="submit" value="ADD">
</form>
            </div>

<script>
 $(document).ready(function() {
            // Load branches on page load
            $.ajax({
                url: 'get_branch.php',
                type: 'GET',
                success: function(data) {
                    $('#branch').append(data); // Append branch options to the existing "Select Branch" option
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching branches:', error);
                }
            });

            // Event listener for branch selection
            $('#branch').change(function() {
                let selectedOption = $(this).find('option:selected');
                let branchName = selectedOption.val();
                let bcode = selectedOption.data('bcode'); // Get bcode from data attribute

                if (branchName) {
                    // Set bcode value
                    $('#bcode').val(bcode);

                    // Fetch centers based on selected branch
                    $.ajax({
                        url: 'get_center.php',
                        type: 'GET',
                        data: { bname: branchName },
                        success: function(data) {
                            $('#center').html('<option value="">Select Center</option>' + data); // Update center dropdown with options
                            $('#center').removeAttr('disabled'); // Enable the center dropdown
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching centers:', error);
                        }
                    });
                } else {
                    $('#bcode').val('');
                    $('#center').html('<option value="">Select Center</option>').attr('disabled', 'disabled');
                    $('#ccode').val('');
                    $('#group').html('<option value="">Select Group</option>').attr('disabled', 'disabled');
                }
            });
            $('#center').change(function() {
        let selectedOption = $(this).find('option:selected');
        let center = selectedOption.val(); // Assuming center is the value of the option
        let ccode = selectedOption.data('ccode'); // Get ccode from data attribute

        if (ccode) {
            // Set ccode value
            $('#ccode').val(ccode);
            $('#center_name').val(center); // Assuming you have an input field with id 'center_name'

            // Fetch groups based on selected center
            $.ajax({
                url: 'get_group.php',
                type: 'GET',
                data: { center_id: ccode },
                dataType: 'json', // Expect JSON response
                success: function(data) {
                    $('#group').html(''); // Clear existing options
                    // Iterate through groups and append options to dropdown
                    $.each(data.groups, function(index, group) {
                        $('#group').append(`<option value="${group.group}">${group.group}</option>`);
                    });
                    // Enable the group dropdown
                    $('#group').removeAttr('disabled');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching groups:', error);
                }
            });
        } else {
            $('#ccode').val('');
            $('#group').html('<option value="">Select Group</option>').attr('disabled', 'disabled');
        }
    });

    
});


</script>
</body>
</html>
