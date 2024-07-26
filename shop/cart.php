<?php
include '../db_connect.php';
include 'navbar.php';

function getItemDetails($conn, $itemId) {
    $query = $conn->prepare("SELECT * FROM product_item WHERE item_id = ?");
    if ($query === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $query->bind_param("i", $itemId);
    $query->execute();
    $result = $query->get_result();
    return $result->fetch_assoc();
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$totalAmount = 0;
foreach ($cartItems as $item) {
    $itemDetails = getItemDetails($conn, $item['item_id']);
    $totalAmount += $itemDetails['price'] * $item['quantity'];
}

function isValidPhoneNumber($phoneNumber) {
    return preg_match('/^09[0-9]{9}$/', $phoneNumber);
}

function getUserFullName($conn, $userId) {
    $query = $conn->prepare("SELECT firstname, lastname FROM user WHERE user_id = ?");
    if ($query === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $query->bind_param("i", $userId);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();
    return $row['firstname'] . ' ' . $row['lastname'];
}

$userId = $_SESSION['user_id'];
$userFullName = getUserFullName($conn, $userId);

$payment_type_query = "SELECT payment_type_id, value FROM payment_type";
$payment_type_result = mysqli_query($conn, $payment_type_query);
if ($payment_type_result) {
    $payment_type = mysqli_fetch_all($payment_type_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching payment types: " . mysqli_error($conn);
    $payment_type = [];
}

if (isset($_POST['checkout'])) {
    $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    if (empty($cartItems)) {
        echo "<script>alert('Your cart is empty. Cannot proceed to checkout.'); window.location.href='cart.php';</script>";
        exit();
    }

    $paymentMethodId = $_POST['payment_method'];
    $fulfillmentMethodId = $_POST['fulfillment_method'];
    $orderStatus = 'Processing';
    $phoneNumber = $_POST['phone_number'];
    $shippingAddress = $fulfillmentMethodId == 2 ? $_POST['shipping_address'] : '';
    $instructions = $_POST['instructions'];

    $insertOrderQuery = $conn->prepare("INSERT INTO shop_order (user_id, order_date, payment_method_id, fulfillment_method_id, order_status, order_total, shipping_address, phone_number, instructions)
                                       VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");
    if ($insertOrderQuery === false) {
        die("Error in SQL query: " . $conn->error);
    }
    $insertOrderQuery->bind_param("iiisisss", $userId, $paymentMethodId, $fulfillmentMethodId, $orderStatus, $totalAmount, $shippingAddress, $phoneNumber, $instructions);
    $success = $insertOrderQuery->execute();

    if ($success) {
        $orderId = $insertOrderQuery->insert_id;

        foreach ($cartItems as $item) {
            $itemDetails = getItemDetails($conn, $item['item_id']);
            $insertOrderlineQuery = $conn->prepare("INSERT INTO orderline (product_item_id, order_id, qty, subtotal) VALUES (?, ?, ?, ?)");
            if ($insertOrderlineQuery === false) {
                die("Error in SQL query: " . $conn->error);
            }
            $subtotal = $itemDetails['price'] * $item['quantity'];
            $insertOrderlineQuery->bind_param("iiid", $item['item_id'], $orderId, $item['quantity'], $subtotal);
            $insertOrderlineSuccess = $insertOrderlineQuery->execute();
            if (!$insertOrderlineSuccess) {
                echo "Error inserting order line: " . $insertOrderlineQuery->error;
            } else {
                // Subtract item quantity from inventory
                $updateQuantityQuery = $conn->prepare("UPDATE product_item SET stock_qty = stock_qty - ? WHERE item_id = ?");
                if ($updateQuantityQuery === false) {
                    die("Error in SQL query: " . $conn->error);
                }
                $updateQuantityQuery->bind_param("ii", $item['quantity'], $item['item_id']);
                if (!$updateQuantityQuery->execute()) {
                    echo "Error updating item quantity: " . $updateQuantityQuery->error;
                }
            }
        }

        $_SESSION['cart'] = [];

        // Redirect based on payment method
        if ($paymentMethodId == 5) {
            header("Location: success.php");
        } else {
            header("Location: payment.php");
        }
        exit();
    } else {
        echo "Error: " . $insertOrderQuery->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Motoracer Cart</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Shopping Cart</h2>
        <table class="table table-dark table-bordered">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($cartItems as $item) {
                    $itemDetails = getItemDetails($conn, $item['item_id']);
                    $itemTotal = $itemDetails['price'] * $item['quantity'];
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($itemDetails['name']); ?></td>
                        <td>₱<?php echo htmlspecialchars(number_format($itemDetails['price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>₱<?php echo htmlspecialchars(number_format($itemTotal, 2)); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-quantity-btn" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>" data-quantity="<?php echo htmlspecialchars($item['quantity']); ?>" data-toggle="modal" data-target="#editQuantityModal">Edit Quantity</button>
                            <button class="btn btn-danger btn-sm remove-item-btn" data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">Remove</button>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <div class="float-right">
            <h3>Total: ₱<?php echo htmlspecialchars(number_format($totalAmount, 2)); ?></h3>
            <button type="button" class="btn btn-success btn-lg" id="checkoutButton" data-toggle="modal" data-target="#checkoutModal">Checkout</button>
        </div>
    </div>

    <!-- Edit Quantity Modal -->
    <div class="modal fade" id="editQuantityModal" tabindex="-1" aria-labelledby="editQuantityModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="editQuantityModalLabel">Edit Quantity</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editQuantityForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_item_id" name="item_id">
                        <div class="form-group">
                            <label for="edit_quantity">New Quantity:</label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Checkout</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="customer_name">Customer Name:</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($userFullName); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method:</label>
                            <select class="form-control" id="payment_method" name="payment_method">
                                <?php foreach ($payment_type as $payment) { ?>
                                    <option value="<?php echo htmlspecialchars($payment['payment_type_id']); ?>"><?php echo htmlspecialchars($payment['value']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fulfillment_method">Fulfillment Method:</label>
                            <select class="form-control" id="fulfillment_method" name="fulfillment_method">
                                <option value="1">Pickup</option>
                                <option value="2">Deliver</option>
                            </select>
                        </div>
                        <div class="form-group" id="shipping_address_group" style="display: none;">
                            <label for="shipping_address">Shipping Address:</label>
                            <input type="text" class="form-control" id="shipping_address" name="shipping_address">
                        </div>
                        <div class="form-group">
                            <label for="phone_number">Phone Number:</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="instructions">Instructions:</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="checkout" class="btn btn-primary">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.edit-quantity-btn').click(function() {
            var itemId = $(this).data('item-id');
            var quantity = $(this).data('quantity');
            $('#edit_item_id').val(itemId);
            $('#edit_quantity').val(quantity);
        });

        $('#editQuantityForm').submit(function(event) {
            event.preventDefault();
            var itemId = $('#edit_item_id').val();
            var quantity = $('#edit_quantity').val();
            $.ajax({
                url: 'update_cart_quantity.php',
                type: 'POST',
                data: {
                    item_id: itemId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response == 'success') {
                        window.location.reload();
                    } else if (response == 'exceeds_stock') {
                        alert('The requested quantity exceeds the available stock.');
                    } else {
                        alert('Failed to update quantity.');
                    }
                }
            });
        });

        $('#fulfillment_method').change(function() {
            if ($(this).val() == '2') {
                $('#shipping_address_group').show();
            } else {
                $('#shipping_address_group').hide();
            }
        });

        $('#checkoutModal').on('hidden.bs.modal', function () {
            $(this).find('form').trigger('reset');
            $('#shipping_address_group').hide();
        });

        $('.remove-item-btn').click(function() {
            var itemId = $(this).data('item-id');
            if (confirm('Are you sure you want to remove this item from the cart?')) {
                $.ajax({
                    url: 'remove_from_cart.php',
                    type: 'POST',
                    data: { item_id: itemId },
                    success: function(response) {
                        if (response == 'success') {
                            window.location.reload();
                        } else {
                            alert('Failed to remove item from cart.');
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>
