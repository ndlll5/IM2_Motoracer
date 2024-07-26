<?php
session_start();
include '../db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['shop_order_id'];
    $paymentStatus = $_POST['payment_status'];
    $paymentDate = isset($_POST['payment_date']) ? $_POST['payment_date'] : null;

    // Prepare the SQL update query
    $sql = "UPDATE shop_order SET payment_status = ?";
    if ($paymentDate) {
        $sql .= ", payment_date = ?";
    }
    $sql .= " WHERE shop_order_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error in SQL query: " . $conn->error);
    }

    if ($paymentDate) {
        $stmt->bind_param("ssi", $paymentStatus, $paymentDate, $orderId);
    } else {
        $stmt->bind_param("si", $paymentStatus, $orderId);
    }

    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        // Redirect to payment details page
        header("Location: payment_details.php?shop_order_id=$orderId");
        exit();
    } else {
        echo "Failed to update payment status.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
?>
