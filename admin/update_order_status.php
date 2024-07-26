<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = $_POST['shop_order_id'];
    $status = $_POST['order_status'];
    $paymentDate = isset($_POST['payment_date']) ? $_POST['payment_date'] : null;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE shop_order SET order_status = ?, payment_date = ? WHERE shop_order_id = ?");
        if ($stmt === false) {
            throw new Exception("Error in SQL query: " . $conn->error);
        }

        $stmt->bind_param("ssi", $status, $paymentDate, $orderId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            if ($status === 'Cancelled') {
                // Get the order line items
                $lineItemsQuery = $conn->prepare("SELECT product_item_id, qty FROM orderline WHERE order_id = ?");
                if ($lineItemsQuery === false) {
                    throw new Exception("Error in SQL query: " . $conn->error);
                }
                $lineItemsQuery->bind_param("i", $orderId);
                $lineItemsQuery->execute();
                $lineItemsResult = $lineItemsQuery->get_result();

                // Add the item quantities back to the inventory
                while ($item = $lineItemsResult->fetch_assoc()) {
                    $updateQuantityQuery = $conn->prepare("UPDATE product_item SET stock_qty = stock_qty + ? WHERE item_id = ?");
                    if ($updateQuantityQuery === false) {
                        throw new Exception("Error in SQL query: " . $conn->error);
                    }
                    $updateQuantityQuery->bind_param("ii", $item['qty'], $item['product_item_id']);
                    if (!$updateQuantityQuery->execute()) {
                        throw new Exception("Error updating item quantity: " . $updateQuantityQuery->error);
                    }
                }
            }

            $conn->commit();

            // Redirect to order details page
            header("Location: order_details.php?shop_order_id=" . urlencode($orderId));
            exit();
        } else {
            throw new Exception("Failed to update order status.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo $e->getMessage();
    }

    $stmt->close();
    $conn->close();
}
?>
