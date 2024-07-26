<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['shop_order_id']) && is_numeric($_POST['shop_order_id'])) {
    $orderId = $_POST['shop_order_id'];
    
    // Update order status to 'Completed'
    $sql = "UPDATE shop_order SET order_status = 'Completed' WHERE shop_order_id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $orderId);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'invalid';
}
?>
