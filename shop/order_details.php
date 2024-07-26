<?php
include '../db_connect.php';

// Ensure shop_order_id is provided and valid
if (!isset($_GET['shop_order_id']) || !is_numeric($_GET['shop_order_id'])) {
    header("Location: orders.php");
    exit();
}

$orderId = $_GET['shop_order_id'];

// Fetch order details including user information
$sql = "SELECT so.*, u.firstname, u.lastname, u.username 
        FROM shop_order so 
        JOIN user u ON so.user_id = u.user_id 
        WHERE so.shop_order_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();
} else {
    echo "Order not found";
    exit();
}

// Fetch order items
$sqlItems = "SELECT ol.*, pi.name AS product_name 
             FROM orderline ol 
             JOIN product_item pi ON ol.product_item_id = pi.item_id 
             WHERE ol.order_id = ?";
$stmtItems = $conn->prepare($sqlItems);

if (!$stmtItems) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmtItems->bind_param("i", $orderId);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();

if ($resultItems->num_rows > 0) {
    $orderItems = $resultItems->fetch_all(MYSQLI_ASSOC);
} else {
    $orderItems = [];
}

// Handle confirmation of delivery
if (isset($_POST['confirm_delivery'])) {
    $updateStatusSql = "UPDATE shop_order SET order_status = 'Completed' WHERE shop_order_id = ?";
    $stmtUpdate = $conn->prepare($updateStatusSql);
    if (!$stmtUpdate) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }
    $stmtUpdate->bind_param("i", $orderId);
    $stmtUpdate->execute();

    if ($stmtUpdate->affected_rows > 0) {
        $order['order_status'] = 'Completed';
    } else {
        echo "Failed to update order status";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .card-header {
            background-color: #FFC107 !important; /* Set your desired background color here */
            color: #343A40; /* Optionally change text color for contrast */
        }

        .bg-dark2 {
            background-color: #202124 !important;
        }
    </style>
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Order Details</h1>
            </div>
            <div class="card-body bg-dark2 text-white">
                <h3 class="card-subtitle mb-2 text-white">Order ID: <?php echo htmlspecialchars($order['shop_order_id']); ?></h3>
                <p><strong>User Name:</strong> <?php echo htmlspecialchars($order['firstname'] . " " . $order['lastname'] . " (" . $order['username'] . ")"); ?></p>
                <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['order_total'], 2); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['order_status']); ?></p>
                <?php if ($order['order_status'] == 'Delivered'): ?>
                    <form method="POST">
                        <button type="submit" name="confirm_delivery" class="btn btn-success mt-3">Confirm Delivery</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-5">
            <h3 class="text-white">Order Items</h3>
            <table class="table table-dark table-striped">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_item_id']); ?></td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['qty']); ?></td>
                        <td>₱<?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="profile.php" class="btn btn-primary mt-3">Back to Account</a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
