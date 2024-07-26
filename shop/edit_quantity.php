<?php
session_start();
include '../db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
    $itemId = filter_var($_POST['item_id'], FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);

    if ($quantity < 1) {
        echo json_encode(['status' => 'error', 'message' => 'Quantity must be at least 1.']);
        exit();
    }

    if (isset($_SESSION['cart'])) {
        $itemFound = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['item_id'] == $itemId) {
                $item['quantity'] = $quantity;
                $itemFound = true;
                break;
            }
        }

        if ($itemFound) {
            echo json_encode(['status' => 'success', 'message' => 'Quantity updated.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No item ID or quantity provided.']);
}
?>
