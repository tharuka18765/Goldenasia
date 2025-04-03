<?php
// fetch_centers.php
include 'connecton.php';

$sql = "SELECT DISTINCT center FROM branch";
$result = $con->query($sql);

$centers = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $centers[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $center = $_POST['center'];
    $selected_date = $_POST['date'];
    $holiday_name = "ravidu"; // Fixed holiday name

    if (!empty($center) && !empty($selected_date)) {
        // Fetch all due_date entries from the customer table for the selected center
        $stmt = $con->prepare("SELECT id, due_date FROM customer WHERE center = ?");
        $stmt->bind_param("s", $center);
        $stmt->execute();
        $stmt->bind_result($customer_id, $current_due_date);

        $customers = [];
        while ($stmt->fetch()) {
            $customers[] = ['id' => $customer_id, 'due_date' => $current_due_date];
        }
        $stmt->close();

        if (!empty($customers)) {
            foreach ($customers as $customer) {
                // Calculate the new due date by adding 7 days to the current due date
                $new_due_date = date('Y-m-d', strtotime($customer['due_date'] . ' + 7 days'));

                // Update the due date for the customer
                $update_stmt = $con->prepare("UPDATE customer SET due_date = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_due_date, $customer['id']);

                // Execute the statement
                if (!$update_stmt->execute()) {
                    echo "Error updating customer table for customer ID {$customer['id']}: " . $update_stmt->error . "<br>";
                }

                // Close the update statement
                $update_stmt->close();
            }

            echo "Customer table updated successfully.<br>";
        } else {
            echo "No customer found for the selected center.<br>";
        }

        // Fetch all due_date_weekly entries from the paymentdates table for the selected center from the selected date onwards
        $stmt = $con->prepare("
        SELECT id, due_date_weekly 
        FROM paymentdates 
        WHERE center = ? AND due_date_weekly = ?
        OR (due_date_weekly > ? AND MOD(DATEDIFF(due_date_weekly, ?), 7) = 0)
    ");
    $stmt->bind_param("ssss", $center, $selected_date, $selected_date, $selected_date);
    $stmt->execute();
    $stmt->bind_result($payment_id, $current_due_date_weekly);

        $paymentdates = [];
        while ($stmt->fetch()) {
            $paymentdates[] = ['id' => $payment_id, 'due_date_weekly' => $current_due_date_weekly];
        }
        $stmt->close();

        if (!empty($paymentdates)) {
            foreach ($paymentdates as $payment) {
                // Calculate the new due date by adding 7 days to the current due date
                $new_due_date_weekly = date('Y-m-d', strtotime($payment['due_date_weekly'] . ' + 7 days'));

                // Update the due date for the payment
                $update_payment_stmt = $con->prepare("UPDATE paymentdates SET due_date_weekly = ? WHERE id = ?");
                $update_payment_stmt->bind_param("si", $new_due_date_weekly, $payment['id']);

                // Execute the statement
                if (!$update_payment_stmt->execute()) {
                    echo "Error updating paymentdates table for payment ID {$payment['id']}: " . $update_payment_stmt->error . "<br>";
                }

                // Close the update statement
                $update_payment_stmt->close();
            }

            echo "Paymentdates table updated successfully.<br>";
        } else {
            echo "No payment dates found for the selected center and date.<br>";
        }

        // Insert the holiday into the holiday table
        $insert_holiday_stmt = $con->prepare("INSERT INTO holiday (name, date, cname) VALUES (?, ?, ?)");
        $insert_holiday_stmt->bind_param("sss", $holiday_name, $selected_date, $center);

        // Execute the statement
        if ($insert_holiday_stmt->execute()) {
            echo "Holiday added successfully.<br>";
        } else {
            echo "Error adding holiday: " . $insert_holiday_stmt->error . "<br>";
        }

        // Close the insert statement
        $insert_holiday_stmt->close();
    } else {
        echo "Center and date are required.<br>";
    }
}

// Close the connection
$con->close();
?>
