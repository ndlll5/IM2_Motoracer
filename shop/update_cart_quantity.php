<?php
session_start();
include '../db_connect.php';

if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
    $itemId = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);

    // Fetch the current stock quantity for the item
    $query = $conn->prepare("SELECT stock_qty FROM product_item WHERE item_id = ?");
    if ($query === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $query->bind_param("i", $itemId);
    $query->execute();
    $result = $query->get_result();
    $item = $result->fetch_assoc();
    $availableStock = $item['stock_qty'];

    // Check if requested quantity exceeds available stock
    if ($quantity > $availableStock) {
        echo 'exceeds_stock';
        exit();
    }

    if ($quantity < 1) {
        echo 'error';
        exit();
    }

    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['item_id'] == $itemId) {
            $cartItem['quantity'] = $quantity;
            echo 'success';
            exit();
        }
    }

    echo 'error';
}
?>
