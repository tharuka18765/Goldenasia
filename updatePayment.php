<?php
session_start();
require "connecton.php"; // Adjust path as needed

$responseData = []; // Initialize an array to hold the response data

// Check if the request contains payment data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentsData = json_decode(file_get_contents('php://input'), true); // Decode the JSON data

    foreach ($paymentsData as $paymentData) {
        $loanCode = $paymentData['loanCode'];
        $paymentAmount = floatval($paymentData['payment']);
        
        

        // Fetch the current loan balance
        $stmt = $con->prepare("SELECT loan_amount, payment FROM customer WHERE loan_code = ?");
        $stmt->bind_param("s", $loanCode);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $loanAmount = $row['loan_amount'];
            $currentPayment = $row['payment'];

            

            // Calculate new balance
            $newPayment = $currentPayment + $paymentAmount;
            $newBalance = $loanAmount - $newPayment;

            // Update the database
            $updateStmt = $con->prepare("UPDATE customer SET payment = ?, loan_balance = ? , payment_date=  CURDATE()  WHERE loan_code = ?");
            $updateStmt->bind_param("dds", $newPayment, $newBalance, $loanCode);
            $updateStmt->execute();
          
            
           
            // Add the updated balance and loan code to the response data
            $responseData[] = ['loanCode' => $loanCode, 'newBalance' => $newBalance];

         
        }
    }
 

    // Return the response data as JSON
    echo json_encode(["success" => true, "data" => $responseData]);
} else {
    echo json_encode(["error" => "Invalid request method."]);
}


    
?>
