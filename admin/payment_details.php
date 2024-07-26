<?php
include '../db_connect.php';

// Ensure shop_order_id is provided and valid
if (!isset($_GET['shop_order_id']) || !is_numeric($_GET['shop_order_id'])) {
    header("Location: payment.php");
    exit();
}

$orderId = $_GET['shop_order_id'];

// Fetch payment details including user information
$sql = "SELECT shop_order.*, CONCAT(user.firstname, ' ', user.lastname) AS full_name, 
        payment_type.value AS payment_type, user.username as username
        FROM shop_order 
        JOIN user ON shop_order.user_id = user.user_id 
        JOIN payment_type ON payment_type.payment_type_id = shop_order.payment_method_id
        WHERE shop_order.shop_order_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    exit();
}

$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $payment = $result->fetch_assoc();
} else {
    echo "Payment not found";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .card-header {
            background-color: #FFC107 !important; /* Set your desired background color here */
            color: #343A40; /* Optionally change text color for contrast */
        }

        .card-footer {
            background-color: #343A40 !important;
            color: #FFC107;
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
                <h1 class="card-title">Payment Details</h1>
            </div>
            <div class="card-body bg-dark2 text-white">
                <h3 class="card-subtitle mb-2 text-white">Order ID: <?php echo $payment['shop_order_id']; ?></h3>
                <p><strong>User Name:</strong> <?php echo $payment['username']; ?></p>
                <p><strong>Full Name:</strong> <?php echo $payment['full_name']; ?></p>
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($payment['order_total'], 2); ?></p>
                <p><strong>Payment Status:</strong> 
                    <form method="POST" action="update_payment_status.php" class="d-inline">
                        <input type="hidden" name="shop_order_id" value="<?php echo $payment['shop_order_id']; ?>">
                        <select name="payment_status" class="custom-select custom-select-sm bg-secondary text-white" onchange="this.form.submit()">
                            <option value="Paid" <?php echo $payment['payment_status'] == 'Paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="Pending" <?php echo $payment['payment_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="Failed" <?php echo $payment['payment_status'] == 'Failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="Cancelled" <?php echo $payment['payment_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="Refunded" <?php echo $payment['payment_status'] == 'Refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </form>
                </p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($payment['payment_type']); ?></p>
                <p><strong>Payment Date:</strong> <?php echo $payment['payment_date'] ? $payment['payment_date'] : 'N/A'; ?></p>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($payment['shipping_address'] ?: 'N/A'); ?></p>
            </div>
            <div class="card-footer">
                <a href="payment.php" class="btn btn-light">Back to Payments</a>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
