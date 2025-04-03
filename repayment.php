<?php
session_start();
require "connecton.php";

if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}

$user = $_SESSION['user'];
$status = $user['status'];
$branch = $user['bname']; // Assuming branch name is stored in the session
$username = $user['name']; // Assuming the executive's name is stored in the session

$center_name = '';
$selected_date = '';
$selected_branch = '';
$results = [];

// Fetch branches for the dropdown based on user status
$branches = [];
$centers = [];

if ($status == 'admin') {
    $branchQuery = "SELECT DISTINCT bname FROM branch";
} elseif ($status == 'branch_manager') {
    $branchQuery = "SELECT bname FROM branch WHERE bname = ?";
} elseif ($status == 'executive') {
    $branchQuery = "SELECT bname, center FROM branch WHERE name = ?";
}

// Execute the branch query based on status
if ($status == 'admin' || $status == 'branch_manager' || $status == 'executive') {
    if ($stmt = $con->prepare($branchQuery)) {
        if ($status == 'branch_manager') {
            $stmt->bind_param("s", $branch);
        } elseif ($status == 'executive') {
            $stmt->bind_param("s", $username);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($status == 'executive') {
                $branches[] = $row['bname'];
                $centers[] = $row['center']; // Get centers associated with the executive's branch
            } else {
                $branches[] = $row['bname'];
            }
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $center_name = $_POST['center_name'];
    $selected_date = $_POST['selected_date'];
    $selected_branch = $_POST['branch'] ?? ''; // For admin or branch_manager

    // Base SQL query to fetch customers
    $sql = "SELECT customer_code, loan_amount, loan_balance, center, ccode, cname, week_payment
            FROM customer 
            WHERE (center = ? OR ccode = ?) 
            AND loan_date <= ? AND loan_balance != 0 ";

    // Add branch condition for branch_manager and admin
    if ($status == 'admin' && !empty($selected_branch)) {
        $sql .= " AND bname = ?";
    } elseif ($status == 'branch_manager') {
        $sql .= " AND bname = ?";
    } elseif ($status == 'executive') {
        $sql .= " AND bname = ?";
    }

    // Prepare and execute the statement
    if ($stmt = $con->prepare($sql)) {
        if ($status == 'admin' && !empty($selected_branch)) {
            $stmt->bind_param("ssss", $center_name, $center_name, $selected_date, $selected_branch);
        } elseif ($status == 'branch_manager') {
            $stmt->bind_param("ssss", $center_name, $center_name, $selected_date, $branch);
        } elseif ($status == 'executive') {
            $stmt->bind_param("ssss", $center_name, $center_name, $selected_date, $branches[0]); // Use the executive's branch
        } else {
            $stmt->bind_param("sss", $center_name, $center_name, $selected_date);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }

        $stmt->close();
    }
}

$con->close();

function formatCustomerNumber($customer_code) {
    $parts = explode('/', $customer_code);
    return $parts[1] . '/' . $parts[3];
}

function formatCustomerName($cname) {
    $name_parts = explode(' ', $cname);
    $num_parts = count($name_parts);

    if ($num_parts == 1) {
        return $name_parts[0];
    }

    $initials = array_map(function($part) {
        return substr($part, 0, 1) . '.';
    }, array_slice($name_parts, 0, $num_parts - 1));

    return implode('', $initials) . end($name_parts);
}

function getGroupNumber($customer_code) {
    $parts = explode('/', $customer_code);
    return $parts[2];
}

function addEmptyRows($currentRowCount, $targetRowCount) {
    $emptyRows = $targetRowCount - $currentRowCount;
    for ($i = 0; $i < $emptyRows; $i++) {
        echo "<tr class='empty-row'>";
        for ($j = 0; $j < 12; $j++) {
            echo "<td class='fixed-width'></td>";
        }
        echo "</tr>";
    }
}

function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Repayment Table</title>
    <style>
        .card {
            width: 90%;
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
        input[type="text"], input[type="date"] {
            width: 60%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type="submit"] {
            width: 60%;
            padding: 10px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        body > div:nth-child(2) > button:hover {
            background-color: black;
            color: white;
        }
        input[type="submit"]:hover {
            background-color: black;
            color: white;
        }
        table {
            margin-left:15px;
            padding:15px;
            border: 1px solid black;
            border-collapse: collapse;
            width: 98%;
        }
        table, th, td {
            border: 1px solid black;
            font-size: 10px;
            border-collapse: collapse;
        }
        #printableArea > table > tbody > tr > th {
            width: 5%;
        }
        th, td {
            padding: 1px;
            text-align: left;
            font-size: 10px;
            height: 11px;
            width: 7%; /* Adjusted column width */
        }
        .empty-row {
            height: 15px; /* Ensure consistent height for empty rows */
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #printableArea, #printableArea * {
                visibility: visible;
            }
            #printableArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 98%;
            }
        }
        body > div:nth-child(2) > button {
            width: 40%;
            padding: 10px;
            background-color: #FAA300;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }#branch{
            width:200px;
            height:35px;
            padding:5px;
            border-radius:5px;
        }
    </style>
    <script>
        function printDiv() {
            window.print();
        }
    </script>
</head>
<body>
<div class="card">
    <h2>Search Repayment Details</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <?php if ($status == 'admin' || $status == 'branch_manager'): ?>
            <div class="form-group">
                <label for="branch">Branch:</label>
                <select id="branch" name="branch" required>
                    <?php foreach ($branches as $branchName): ?>
                        <option value="<?php echo htmlspecialchars($branchName); ?>" 
                            <?php echo ($branchName == $selected_branch) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($branchName); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label for="center_name">Center Name or Code:</label>
            <input type="text" id="center_name" name="center_name" value="<?php echo htmlspecialchars($center_name); ?>" required>
        </div>
        <div class="form-group">
            <input type="hidden" id="selected_date" name="selected_date" value="<?php echo htmlspecialchars(date('Y-m-d')); ?>">
        </div>
        <div class="form-group">
            <input type="submit" name="submit" value="Search">
        </div>
    </form>
</div>
<div>

<?php if (!empty($results)): ?>
    <h2>Repayment Table</h2>
    <button onclick="printDiv()">Print</button>
    <div id="printableArea">
        <?php 
        // Display center details
        if (!empty($results)) {
            $center = htmlspecialchars($results[0]['center']);
            $center_number = htmlspecialchars($results[0]['ccode']);
        ?>
            <h4>Center Number: <?php echo $center_number; ?> | Center: <?php echo $center; ?></h4>
        <?php 
        }
        ?>

        <?php 
        // Group results by group number
        $grouped_results = [];
        foreach ($results as $row) {
            $group_number = getGroupNumber($row['customer_code']);
            $grouped_results[$group_number][] = $row;
        }

        // Ensure at least 5 groups
        $group_count = count($grouped_results);
        $total_groups = 5; // Desired total number of groups
        for ($i = $group_count + 1; $i <= $total_groups; $i++) {
            $grouped_results[str_pad($i, 3, '0', STR_PAD_LEFT)] = [];
        }

        // Initialize totals for the page
        $page_total_loan_amount = 0;
        $page_total_due_amount = 0;
        $page_total_balance = 0;
        ?>

        <table>
            <tr class="empty-row">
                <th class="fixed-width">Customer ID</th>
                <th class="fixed-width">Customer Name</th>
                <th class="fixed-width">Loan Amount</th>
                <th class="fixed-width">Due Amount</th>
                <th class="fixed-width">Balance</th>
                <?php for ($i = 5; $i < 12; $i++) echo "<th class='fixed-width'></th>"; ?>
            </tr>
            <?php 
            $currentRowCount = 1; // Counting the header row
            foreach ($grouped_results as $group_number => $group_rows): 
                $total_loan_amount = 0;
                $total_due_amount = 0;
                $total_balance = 0;
                $currentRowCount++; // Counting the group number row
            ?>
                <tr class="empty-row">
                    <td class="fixed-width" colspan="12"><strong>Group No: <?php echo htmlspecialchars($group_number); ?></strong></td>
                </tr>
                <?php foreach ($group_rows as $row): 
                    $loan_amount = $row['loan_amount'];
                    $due_amount = $row['week_payment'];
                    $balance = ($row['loan_balance']) ;
                    
                    $total_loan_amount += $loan_amount;
                    $total_due_amount += $due_amount;
                    $total_balance += $balance;

                    $page_total_loan_amount += $loan_amount;
                    $page_total_due_amount += $due_amount;
                    $page_total_balance += $balance;

                    $currentRowCount++; // Counting the detailed row
                ?>
                    <tr class="empty-row">
                        <td class="fixed-width"><?php echo htmlspecialchars(formatCustomerNumber($row['customer_code'])); ?></td>
                        <td class="fixed-width"><?php echo htmlspecialchars(formatCustomerName($row['cname'])); ?></td>
                        <td class="fixed-width"><?php echo htmlspecialchars($loan_amount); ?></td>
                        <td class="fixed-width"><?php echo htmlspecialchars($due_amount); ?></td>
                        <td class="fixed-width"><?php echo htmlspecialchars($balance); ?></td>
                        <?php for ($i = 5; $i < 12; $i++) echo "<td class='fixed-width'></td>"; ?>
                    </tr>
                <?php endforeach; ?>
                <?php 
                $rows_in_group = count($group_rows);
                if ($rows_in_group < 5) {
                    for ($i = $rows_in_group; $i < 6; $i++) {
                        $currentRowCount++;
                ?>
                        <tr class="empty-row">
                            <?php for ($j = 0; $j < 12; $j++) echo "<td class='fixed-width'></td>"; ?>
                        </tr>
                <?php 
                    }
                }
                ?>
                <tr class="empty-row">
                    <td class="fixed-width"><strong>Totals</strong></td>
                    <td class="fixed-width"></td>
                    <td class="fixed-width"><strong><?php echo htmlspecialchars($total_loan_amount); ?></strong></td>
                    <td class="fixed-width"><strong><?php echo htmlspecialchars($total_due_amount); ?></strong></td>
                    <td class="fixed-width"><strong><?php echo htmlspecialchars($total_balance); ?></strong></td>
                    <?php for ($i = 5; $i < 12; $i++) echo "<td class='fixed-width'></td>"; ?>
                </tr>
                <?php 
                $currentRowCount++; // Counting the total row
            endforeach; 
            addEmptyRows($currentRowCount, 43); // Adjust targetRowCount for signatures
            ?>
            <tr class="empty-row">
                <td class="fixed-width"><strong>Page Due</strong></td>
                <td class="fixed-width"></td>
                <td class="fixed-width"><strong><?php echo htmlspecialchars($page_total_loan_amount); ?></strong></td>
                <td class="fixed-width"><strong><?php echo htmlspecialchars($page_total_due_amount); ?></strong></td>
                <td class="fixed-width"><strong><?php echo htmlspecialchars($page_total_balance); ?></strong></td>
                <?php for ($i = 5; $i < 12; $i++) echo "<td class='fixed-width'></td>"; ?>
            </tr>
            <tr class="empty-row">
                <td class="fixed-width"><strong>Executive</strong></td>
                <td class="fixed-width" colspan="4"></td>
                <?php for ($i = 5; $i < 12; $i++) echo "<td class='fixed-width'></td>"; ?>
            </tr>
            <tr class="empty-row">
                <td class="fixed-width"><strong>Feild Manager</strong></td>
                <td class="fixed-width" colspan="4"></td>
                <?php for ($i = 5; $i < 12; $i++) echo "<td class='fixed-width'></td>"; ?>
            </tr>
            <tr class="empty-row">
                <td class="fixed-width"><strong>Finance Officer</strong></td>
                <td class="fixed-width" colspan="4" ></td>
                <?php for ($i = 5; $i < 12; $i++) echo "<td class='fixed-width'></td>"; ?>
            </tr>
        </table>
    </div>
<?php endif; ?>

</div>
</body>
</html>
