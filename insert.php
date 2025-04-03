<?php

require "connecton.php";


// Check if the required POST keys are set and not empty
if (isset($_POST['loan_amount'], $_POST['payment']) && $_POST['loan_amount'] !== '' && $_POST['payment'] !== '') {
    $loanAmount = mysqli_real_escape_string($con, $_POST['loan_amount']);
    

    // Prepare the SQL statement
    $insertPaymentStmt = $con->prepare("INSERT INTO payments (loan_code, payment_date) VALUES (?, CURDATE())");

    // Check if the statement was prepared successfully
    if ($insertPaymentStmt) {
        // Bind the parameters to the prepared statement
        $insertPaymentStmt->bind_param("s", $loanCode, );

        // Execute the prepared statement
        if ($insertPaymentStmt->execute()) {
            // Success
            echo "Payment was successfully recorded.";
        } else {
            // Handle execution error
            echo "Error: " . $con->error;
        }

        // Close the prepared statement
        $insertPaymentStmt->close();
    } else {
        // Handle preparation error
        echo "Error: " . $con->error;
    }
} 