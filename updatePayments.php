<?php
// updatePayments.php

session_start();
require "connecton.php";  // Adjust path as needed

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('location: login-user.php');
    exit();
}

// Get the executive name from the session, after checking if user is set
$user = $_SESSION['user'];
$executiveName = $user['name'];

$responseData = [];  // Initialize an array to hold the response data

// Check if the request is a POST request and contains the expected 'data'
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty(file_get_contents('php://input'))) {
    // Decode the JSON input to get the payment data array
    $paymentsData = json_decode(file_get_contents('php://input'), true);

    // Begin a transaction
    $con->begin_transaction();
    try {
        foreach ($paymentsData as $paymentData) {
            $loanCode = $paymentData['loanCode'];
            $paymentAmount = floatval($paymentData['payment']);
            $executiveName = $user['name'];

            // Fetch the current loan details along with bname, center, and loan_date
            $stmt = $con->prepare("SELECT loan_amount, payment,full_loan, nic, bname, center, loan_date FROM customer WHERE loan_code = ?");
            $stmt->bind_param("s", $loanCode);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $loanAmount = $row['loan_amount'];
                $fulloan = $row['full_loan'];
                $currentPayment = $row['payment'];
                $nic = $row['nic'];
                $bname = $row['bname'];
                $center = $row['center'];
                $loanDate = $row['loan_date'];
                $executiveName = $user['name'];
                

                $currentWeekNumber = ceil((time() - strtotime($loanDate)) / (7 * 86400));
                $currentweek = $currentWeekNumber - 1;

                $fetchStmt = $con->prepare("SELECT week_payment FROM paymentdates WHERE loan_code = ? ORDER BY week_number DESC LIMIT 1");
$fetchStmt->bind_param("s", $loanCode);
$fetchStmt->execute();
$fetchResult = $fetchStmt->get_result();
if ($fetchRow = $fetchResult->fetch_assoc()) {
    $weekPayment = $fetchRow['week_payment'];
} else {
    $weekPayment = 0;  // Default to 0 if no payment history is found
}
$fetchStmt->close();


           

            $fetchStmt = $con->prepare("SELECT week_payment FROM paymentdates WHERE loan_code = ? ORDER BY week_number DESC LIMIT 1");
$fetchStmt->bind_param("s", $loanCode);
$fetchStmt->execute();
$fetchResult = $fetchStmt->get_result();
if ($fetchRow = $fetchResult->fetch_assoc()) {
    $weekPayment = $fetchRow['week_payment'];
} else {
    $weekPayment = 0;  // Default to 0 if no payment history is found
}
$fetchStmt->close();


$arriarseStmt = $con->prepare("SELECT arriarse FROM paymentdates WHERE loan_code = ? AND week_number = ?");
$arriarseStmt->bind_param("sd", $loanCode, $currentWeekNumber);
$arriarseStmt->execute();
$arriarseResult = $arriarseStmt->get_result();
if ($arriarseRow = $arriarseResult->fetch_assoc()) {
    $previousArriarse = $arriarseRow['arriarse'];
}



// Calculate new balance and updated payment
$newPayment = $currentPayment + $paymentAmount;
$newBalance = $fulloan - $newPayment;

$newArriarse = $weekPayment - $paymentAmount + $previousArriarse;


                // Update the customer table
                $updateStmt = $con->prepare("UPDATE customer SET payment = ?, loan_balance = ?, payment_date = CURDATE() WHERE loan_code = ?");
                $updateStmt->bind_param("dds", $newPayment, $newBalance, $loanCode);
                $updateStmt->execute();

                // Add data to response
                $responseData[] = ['loanCode' => $loanCode, 'newBalance' => $newBalance, 'payment' => $paymentAmount];
                

                if ($paymentAmount == 0) {
                    // If payment is 0, update the row with payment = 0 and other details
                    $updatePaymentDatesStmt = $con->prepare("UPDATE paymentdates SET payment = 0, arriarse = ?, name = ?, payment_date = CURDATE() WHERE loan_code = ? AND week_number = ?");
                    $updatePaymentDatesStmt->bind_param("dssd", $newArriarse, $executiveName, $loanCode, $currentweek );
                } else {
                    // If payment is not 0, update the row with the calculated payment amount and other details
                    $updatePaymentDatesStmt = $con->prepare("UPDATE paymentdates SET payment = ?, arriarse = ?, name = ?, payment_date = CURDATE() WHERE loan_code = ? AND week_number = ?");
                    $updatePaymentDatesStmt->bind_param("ddssd", $paymentAmount, $newArriarse, $executiveName, $loanCode, $currentweek );
                }
                if ($updatePaymentDatesStmt->execute() === false || $updatePaymentDatesStmt->affected_rows === 0) {
                    // If there is no existing record for the current week, insert a new one
                    
                }

                $updatePaymentDatesStmt->close();
            } else {
                throw new Exception("Loan code not found: $loanCode");
            }
        }
        // Commit the transaction
        $con->commit();
        echo json_encode(['success' => true, 'data' => $responseData]);
    } catch (Exception $e) {
        // Roll back the transaction on error
        $con->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Invalid request method or missing data']);
}
?>
