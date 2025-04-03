<?php
require "connecton.php";

// Fetch distinct branch names to avoid duplicates
$query = "SELECT DISTINCT bname, bcode FROM branch";
$result = mysqli_query($con, $query);

$options = "";
while ($row = mysqli_fetch_assoc($result)) {
    $options .= "<option value='{$row['bname']}' data-bcode='{$row['bcode']}'>{$row['bname']}</option>";
}
echo $options;

$con->close();
?>
