<?php
include '../db_connect.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch the latest order details
$query = $conn->prepare("SELECT * FROM shop_order WHERE user_id = ? ORDER BY shop_order_id DESC LIMIT 1");
if ($query === false) {
    die("Error in SQL query: " . $conn->error);
}
$query->bind_param("i", $userId);
$query->execute();
$orderResult = $query->get_result();
$order = $orderResult->fetch_assoc();

$orderId = $order['shop_order_id'];

// Fetch order line items
$lineItemsQuery = $conn->prepare("SELECT ol.*, pi.name FROM orderline ol INNER JOIN product_item pi ON ol.product_item_id = pi.item_id WHERE ol.order_id = ?");
if ($lineItemsQuery === false) {
    die("Error in SQL query: " . $conn->error);
}
$lineItemsQuery->bind_param("i", $orderId);
$lineItemsQuery->execute();
$lineItemsResult = $lineItemsQuery->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Order Confirmation</h2>
        <div class="alert alert-success" role="alert">
            Thank you! Your order has been placed successfully.
        </div>
        <h4>Order Summary</h4>
        <p><strong>Order ID:</strong> <?php echo $orderId; ?></p>
        <p><strong>Order Date:</strong> <?php echo $order['order_date']; ?></p>
        <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['order_total'], 2); ?></p>
        <p><strong>Order Status:</strong> <?php echo $order['order_status']; ?></p>
        <?php if ($order['fulfillment_method_id'] == 2): // Assuming 2 is for delivery ?>
            <p><strong>Shipping Address:</strong> <?php echo $order['shipping_address']; ?></p>
        <?php endif; ?>
        <h5>Items:</h5>
        <table class="table table-bordered text-white">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $lineItemsResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['qty']; ?></td>
                        <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <a href="shop.php" class="btn btn-custom">Continue Shopping</a>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
