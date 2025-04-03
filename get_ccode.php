<?php
include 'connecton.php';

$center_id = $_GET['center_id'];
$query = "SELECT DISTINCT ccode FROM branch WHERE center = '$center_id'";
$result = $con->query($query);

$options = "<option value=''>Select CCode</option>";
while ($row = $result->fetch_assoc()) {
    $options .= "<option value='{$row['ccode']}'>{$row['ccode']}</option>";
}

echo $options;

$con->close();
?>
