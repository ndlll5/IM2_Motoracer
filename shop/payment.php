<?php
session_start();
include '../db_connect.php';

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
$lineItemsQuery = $conn->prepare("SELECT ol.*, pi.name, pi.price FROM orderline ol INNER JOIN product_item pi ON ol.product_item_id = pi.item_id WHERE ol.order_id = ?");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        h4 {
            text-align: center;
        }
        .container{
            display: flex;
        }
        .payment_details {
            width: 50%;
            background-color: #ebe6da;
        }
        .order_summary{
            width: 50%;
            background-color: #e3e3e3;
        }
    </style>
</head>
<body class="m-5">
    <div class="container p-3">
        <div class="order_summary p-3 shadow p-3 mb-5 rounded mr-1">
            <h4>Order Summary</h4>
            <hr>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderId); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
            <p><strong>Total Amount:</strong> ₱<?php echo htmlspecialchars(number_format($order['order_total'], 2)); ?></p>
            <?php if ($order['fulfillment_method_id'] == 2): // Assuming 2 is for delivery ?>
                <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
            <?php endif; ?>
            <h5>Items:</h5>
            <table class="table table-bordered text-dark">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totalAmount = 0;
                    while ($item = $lineItemsResult->fetch_assoc()) { 
                        $itemTotal = $item['price'] * $item['qty'];
                        $totalAmount += $itemTotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>₱<?php echo htmlspecialchars(number_format($item['price'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($item['qty']); ?></td>
                            <td>₱<?php echo htmlspecialchars(number_format($itemTotal, 2)); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3">Total</th>
                        <th>₱<?php echo htmlspecialchars(number_format($totalAmount, 2)); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="payment_details p-3 shadow p-3 mb-5 rounded ml-1">
            <h4>Payment</h4>
            <hr>
            <?php
                if ($order['payment_method_id'] == 1) {
                    echo'
                    <small>Accepted Credit / Debit Cards:</small>
                    <div class="mt-1">
                        <img src="../uploads/visa.png" alt="visa" class="mr-1">
                        <img src="../uploads/master.png" alt="master" class="mr-1">
                        <img src="../uploads/jcb.png" alt="jcb">
                    </div>
                    <form id="paymentForm" class="mt-3">
                        <div class="row mb-4">
                            <div class="col">
                                <div data-mdb-input-init class="form-outline">
                                    <input type="text" id="formNameOnCard" class="form-control" placeholder="Cardholder\'s Name" required />
                                </div>
                            </div>
                            <div class="col">
                                <div data-mdb-input-init class="form-outline">
                                    <input type="text" id="formCardNumber" class="form-control" placeholder="Card Number" required />
                                </div>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-3">
                                <div data-mdb-input-init class="form-outline">
                                    <input type="text" id="formExpiration" class="form-control" placeholder="MM/YY" required />
                                </div>
                            </div>
                            <div class="col-3">
                                <div data-mdb-input-init class="form-outline">
                                    <input type="text" id="formCVV" class="form-control" placeholder="CVV" required />
                                </div>
                            </div>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-light" type="button" onclick="processPayment(1)">Pay Now</button>
                        </div>
                    </form>';
                    
                } elseif ($order['payment_method_id'] == 4) {
                    echo'
                    <p>Payment by bank transfer.</p>
                    <form id="bankTransferForm" class="mt-3">
                        <div class="form-group">
                            <input type="text" id="accountName" class="form-control" placeholder="Account Holder\'s Name" required />
                        </div>
                        <div class="form-group">
                            <input type="text" id="bankName" class="form-control" placeholder="Bank Name" required />
                        </div>
                        <div class="form-group">
                            <input type="text" id="accountNumber" class="form-control" placeholder="Account Number" required />
                        </div>
                        <div class="text-center">
                            <button class="btn btn-light" type="button" onclick="processPayment(4)">Submit</button>
                        </div>
                    </form>';
                }
            ?>
        </div>
    </div>
    
    <script>
function processPayment(paymentMethod) {
    var paymentSuccessful = true; // Simulate payment success

    if (paymentSuccessful) {
        var paymentStatus = (paymentMethod == 1) ? "Paid" : "Pending"; // Assuming 1 is Credit Card and 4 is Bank Transfer
        var paymentDate = (paymentMethod == 1) ? new Date().toISOString().slice(0, 19).replace('T', ' ') : null;

        console.log("Payment Method:", paymentMethod);
        console.log("Payment Status:", paymentStatus);
        console.log("Payment Date:", paymentDate);

        // Send payment status and date to server
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_payment_date.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log("Response from server:", xhr.responseText);
                window.location.href = 'success.php';
            }
        };
        xhr.send("shop_order_id=<?php echo $orderId; ?>&payment_status=" + paymentStatus + "&payment_date=" + paymentDate);
    } else {
        alert("Payment failed. Please try again.");
    }
}
</script>



</body>
</html>
