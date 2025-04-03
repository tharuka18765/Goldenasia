<?php
session_start();
require "connecton.php";

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if the ID is provided
    if (isset($input['id'])) {
        $id = $input['id'];

        // Prepare the DELETE statement
        $sql = "DELETE FROM branch WHERE id = ?";

        if ($stmt = $con->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                // Send a success response
                echo json_encode(['status' => 'success', 'message' => 'Row deleted successfully']);
            } else {
                // Send an error response if the execution failed
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete row']);
            }
            $stmt->close();
        } else {
            // Send an error response if the statement preparation failed
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
        }
    } else {
        // Send an error response if the ID is not provided
        echo json_encode(['status' => 'error', 'message' => 'No ID provided']);
    }

    $con->close();
} else {
    // Send an error response if the request method is not POST
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
