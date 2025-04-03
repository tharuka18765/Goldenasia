<?php
session_start();
require "connecton.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $deletedCustomers = [];
    $deletedPayments = [];

    // Delete from customer table
    if (!empty($_POST['customer_ids'])) {
        $customer_ids = $_POST['customer_ids'];
        $placeholders = rtrim(str_repeat('?,', count($customer_ids)), ',');
        $sql_customer_delete = "DELETE FROM customer WHERE customer_code IN ($placeholders)";
        
        if ($stmt_customer_delete = $con->prepare($sql_customer_delete)) {
            $stmt_customer_delete->bind_param(str_repeat('s', count($customer_ids)), ...$customer_ids);
            if ($stmt_customer_delete->execute()) {
                $deletedCustomers = $customer_ids; // Track deleted customer IDs
            } else {
                error_log("Customer delete execution error: " . $stmt_customer_delete->error);
            }
            $stmt_customer_delete->close();
        } else {
            error_log("Customer delete prepare error: " . $con->error);
        }
    }

    // Delete from paymentdates table
    if (!empty($_POST['payment_ids'])) {
        $payment_ids = $_POST['payment_ids'];
        $placeholders = rtrim(str_repeat('?,', count($payment_ids)), ',');
        $sql_payment_delete = "DELETE FROM paymentdates WHERE id IN ($placeholders)";
        
        if ($stmt_payment_delete = $con->prepare($sql_payment_delete)) {
            $stmt_payment_delete->bind_param(str_repeat('s', count($payment_ids)), ...$payment_ids);
            if ($stmt_payment_delete->execute()) {
                $deletedPayments = $payment_ids; // Track deleted payment IDs
            } else {
                error_log("Payment delete execution error: " . $stmt_payment_delete->error);
            }
            $stmt_payment_delete->close();
        } else {
            error_log("Payment delete prepare error: " . $con->error);
        }
    }

    $con->close();

    // Redirect to managecus.php with a confirmation message
    $deletedCustomersMsg = !empty($deletedCustomers) ? "Deleted Customer IDs: " . implode(', ', $deletedCustomers) : "";
    $deletedPaymentsMsg = !empty($deletedPayments) ? "Deleted Payment IDs: " . implode(', ', $deletedPayments) : "";
    $confirmationMessage = $deletedCustomersMsg . " " . $deletedPaymentsMsg;
    $_SESSION['confirmation_message'] = $confirmationMessage;

    header("Location: managecus.php");
    exit();
} else {
    // Handle cases where delete action was not properly requested
    header("Location: managecus.php"); // Redirect back to managecus.php if delete was not properly requested
    exit();
}
?>
