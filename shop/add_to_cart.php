<?php
session_start();

if (isset($_POST['product_id']) && isset($_POST['item_id']) && isset($_POST['quantity'])) {
    $productId = $_POST['product_id'];
    $itemId = $_POST['item_id'];
    $quantity = $_POST['quantity'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$itemId])) {
        $_SESSION['cart'][$itemId]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$itemId] = [
            'product_id' => $productId,
            'item_id' => $itemId,
            'quantity' => $quantity
        ];
    }

    echo json_encode(['status' => 'success', 'message' => 'Item added to cart']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
