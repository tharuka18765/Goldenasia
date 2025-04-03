<?php
require 'connecton.php';

// Get the center_id from the GET parameters
$center_id = $_GET['center_id'] ?? '';

// Check if center_id is set and not empty
if (!empty($center_id)) {
    // Prepare and execute the query using prepared statements to prevent SQL injection
    $stmt = $con->prepare("SELECT DISTINCT `group` FROM branch WHERE ccode = ?");
    $stmt->bind_param("s", $center_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to store groups
    $groups = [];

    // Fetch and append each result as an associative array
    while ($row = $result->fetch_assoc()) {
        $groups[] = ['group' => $row['group']];
    }

    // Prepare JSON response
    $response = [
        'groups' => $groups,
        'ccode' => $center_id // Assuming ccode is directly the center_id
    ];

    // Output JSON response
    echo json_encode($response);
} else {
    // Output error JSON if center_id is not provided
    echo json_encode(['error' => 'Invalid center_id']);
}

// Close the statement
$stmt->close();

// Close the database connection
$con->close();
?>
