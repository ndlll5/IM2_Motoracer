<?php
session_start();
include '../db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['item_id'])) {
    $itemId = filter_var($_POST['item_id'], FILTER_SANITIZE_NUMBER_INT);

    if (isset($_SESSION['cart'])) {
        $itemFound = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['item_id'] == $itemId) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                $itemFound = true;
                break;
            }
        }

        if ($itemFound) {
            echo json_encode(['status' => 'success', 'message' => 'Item removed from cart.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item not found in cart.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No item ID provided.']);
}
?>
