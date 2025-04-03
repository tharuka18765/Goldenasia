<?php
include 'connecton.php';

$branch_id = $_GET['branch_id'];
$query = "SELECT DISTINCT bcode FROM branch WHERE bname = '$branch_id'";
$result = $con->query($query);

$options = "<option value=''>Select BCode</option>";
while ($row = $result->fetch_assoc()) {
    $options .= "<option value='{$row['bcode']}'>{$row['bcode']}</option>";
}

echo $options;

$con->close();
?>
