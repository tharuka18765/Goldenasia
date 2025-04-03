<?php
session_start();
require "connecton.php";

$data = json_decode(file_get_contents("php://input"), true);

$sql = "UPDATE branch SET bname=?, bcode=?, day=?, center=?, ccode=?, `group`=?, name=? WHERE id=?";
if ($stmt = $con->prepare($sql)) {
    $stmt->bind_param("sssssssi", $data['bname'], $data['bcode'], $data['day'], $data['center'], $data['ccode'], $data['group'], $data['name'], $data['id']);
    $stmt->execute();
    $stmt->close();
}

$con->close();
?>
