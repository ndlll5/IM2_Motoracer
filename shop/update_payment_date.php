<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $orderId = filter_input(INPUT_POST, 'shop_order_id', FILTER_SANITIZE_NUMBER_INT);
    $paymentStatus = filter_input(INPUT_POST, 'payment_status', FILTER_SANITIZE_STRING);
    $paymentDate = filter_input(INPUT_POST, 'payment_date', FILTER_SANITIZE_STRING);

    // Debugging: Log received data
    error_log("Order ID: $orderId");
    error_log("Payment Status: $paymentStatus");
    error_log("Payment Date: $paymentDate");

    // Prepare the SQL statement based on payment status
    if ($paymentStatus == 'Paid') {
        $stmt = $conn->prepare("UPDATE shop_order SET payment_status = ?, payment_date = ? WHERE shop_order_id = ?");
        $stmt->bind_param("ssi", $paymentStatus, $paymentDate, $orderId);
    } else {
        $stmt = $conn->prepare("UPDATE shop_order SET payment_status = ? WHERE shop_order_id = ?");
        $stmt->bind_param("si", $paymentStatus, $orderId);
    }

    // Execute and check for errors
    if ($stmt->execute()) {
        echo "Payment status updated successfully.";
    } else {
        error_log("Error updating payment status: " . $stmt->error);
        echo "Error updating payment status. Please check the logs for details.";
    }

    $stmt->close();
}
?>
