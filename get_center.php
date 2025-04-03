<?php
require "connecton.php";

// Get the branch name from the query parameters
$branch_name = $_GET['bname'] ?? '';

// Check if the branch name is not empty
if (!empty($branch_name)) {
    // Prepare the SQL statement to fetch centers based on the branch name
    $stmt = $con->prepare("SELECT DISTINCT ccode, center FROM branch WHERE bname = ?");
    $stmt->bind_param("s", $branch_name);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = "<option value=''>Select Center</option>";
    while ($row = $result->fetch_assoc()) {
        $options .= "<option value='{$row['center']}' data-ccode='{$row['ccode']}'>{$row['center']}</option>";
    }
    echo $options;
} else {
    echo "<option value=''>Select Center</option>";
}

// Close the database connection
$con->close();
?>
