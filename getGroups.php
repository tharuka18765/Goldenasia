<?php
require "connecton.php"; // Ensure this points to your actual connection file name

if(isset($_POST['center'])) {
    $center = $_POST['center'];

    // Assuming 'center' is unique and 'ccode' is the same for all entries with the same center
    $query = "SELECT DISTINCT `group`, `ccode` FROM branch WHERE center = ? ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $center);
    $stmt->execute();
    $result = $stmt->get_result();

    $groupsHtml = '<option value="">Select Group</option>';
    $ccode = ''; // Initialize ccode
    while($row = $result->fetch_assoc()) {
        if (empty($ccode)) { // Just set ccode once
            $ccode = htmlspecialchars($row['ccode']);
        }
        $groupsHtml .= '<option value="'.htmlspecialchars($row['group']).'">'.htmlspecialchars($row['group']).'</option>';
    }
    
    // Encode both groups and ccode in a JSON object and return it
    echo json_encode([
        'groupsHtml' => $groupsHtml,
        'ccode' => $ccode,
    ]);
}
?>
