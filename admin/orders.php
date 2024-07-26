<?php
include '../db_connect.php';

// Pagination and search settings
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';

// Fetch orders for display with search and status filter functionality
$order_query = "SELECT shop_order.*, user.username, CONCAT(user.firstname, ' ', user.lastname) AS full_name 
                FROM shop_order 
                JOIN user ON shop_order.user_id = user.user_id 
                WHERE (user.username LIKE '%$search%' 
                OR CONCAT(user.firstname, ' ', user.lastname) LIKE '%$search%' 
                OR shop_order.order_status LIKE '%$search%')
                AND ('$filter_status' = '' OR shop_order.order_status = '$filter_status')
                ORDER BY CASE 
                    WHEN shop_order.order_status NOT IN ('Delivered', 'Cancelled', 'Resolved') THEN 1 
                    ELSE 2 
                END, shop_order.order_date DESC
                LIMIT $limit OFFSET $offset";
$order_result = mysqli_query($conn, $order_query);
if ($order_result) {
    $orders = mysqli_fetch_all($order_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching orders: " . mysqli_error($conn);
    $orders = [];
}

// Fetch total number of orders for pagination
$total_query = "SELECT COUNT(*) as total 
                FROM shop_order 
                JOIN user ON shop_order.user_id = user.user_id 
                WHERE (user.username LIKE '%$search%' 
                OR CONCAT(user.firstname, ' ', user.lastname) LIKE '%$search%' 
                OR shop_order.order_status LIKE '%$search%')
                AND ('$filter_status' = '' OR shop_order.order_status = '$filter_status')";
$total_result = mysqli_query($conn, $total_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .pagination .page-item .page-link {
            color: #f00; /* Red text color for page links */
            background-color: #343a40; /* Dark background for page links */
            border-color: #f00; /* Red border color */
        }

        .pagination .page-item.active .page-link {
            background-color: #f00; /* Red background for active page */
            color: #fff; /* White text color for active page */
            border-color: #f00; /* Red border color */
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d; /* Gray color for disabled page links */
            background-color: #343a40; /* Dark background for disabled page links */
            border-color: #343a40; /* Dark border color for disabled page links */
        }

        .btn-outline-light {
            color: #f00; /* Red text color */
            border-color: #f00; /* Red border color */
        }

        .btn-outline-light:hover {
            background-color: #f00; /* Red background color on hover */
            color: #fff; /* White text color on hover */
        }
        .modal-content {
            background-color: #343a40; /* Dark background color */
            color: white; /* White text color */
        }

        .bg-pagi {
            background-color: #333333;
        }
    </style>
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Orders</h1>

        <div class="row mb-3">
            <div class="col-md-6">
                <!-- Search form -->
                <form class="form-inline" method="GET" action="orders.php">
                    <input class="form-control mr-2" type="search" name="search" placeholder="Search orders" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-light btn-sm" type="submit">Search</button>
                </form>
            </div>
            <div class="col-md-6">
                <!-- Filter form -->
                <form class="form-inline justify-content-end" method="GET" action="orders.php">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <select class="form-control mr-2" name="filter_status" onchange="this.form.submit()">
                        <option value="">Status</option>
                        <option value="Delivered" <?php echo $filter_status == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo $filter_status == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="Resolved" <?php echo $filter_status == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="Returned" <?php echo $filter_status == 'Returned' ? 'selected' : ''; ?>>Returned</option>
                        <option value="In Transit" <?php echo $filter_status == 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                        <option value="Payment Due" <?php echo $filter_status == 'Payment Due' ? 'selected' : ''; ?>>Payment Due</option>
                        <option value="Processing" <?php echo $filter_status == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="Ready for Pickup" <?php echo $filter_status == 'Ready for Pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                        <option value="Problem" <?php echo $filter_status == 'Problem' ? 'selected' : ''; ?>>Problem</option>
                        <option value="Problem" <?php echo $filter_status == 'Problem' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Table to display existing orders -->
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo $order['shop_order_id']; ?></td>
                    <td><?php echo $order['username']; ?></td>
                    <td><?php echo $order['full_name']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td>₱<?php echo number_format($order['order_total'], 2); ?></td>
                    <td>
                        <form method="POST" action="update_order_status.php" class="d-inline">
                            <input type="hidden" name="shop_order_id" value="<?php echo $order['shop_order_id']; ?>">
                            <select name="order_status" class="custom-select custom-select-sm bg-secondary text-white" onchange="this.form.submit()">
                                <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Cancelled" <?php echo $order['order_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                <option value="Resolved" <?php echo $order['order_status'] == 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                <option value="Returned" <?php echo $order['order_status'] == 'Returned' ? 'selected' : ''; ?>>Returned</option>
                                <option value="In Transit" <?php echo $order['order_status'] == 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                                <option value="Payment Due" <?php echo $order['order_status'] == 'Payment Due' ? 'selected' : ''; ?>>Payment Due</option>
                                <option value="Processing" <?php echo $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Ready for Pickup" <?php echo $order['order_status'] == 'Ready for Pickup' ? 'selected' : ''; ?>>Ready for Pickup</option>
                                <option value="Problem" <?php echo $order['order_status'] == 'Problem' ? 'selected' : ''; ?>>Problem</option>
                                <option value="Completed" <?php echo $order['order_status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                            </select>
                        </form>
                    </td>
                    <td><a href="order_details.php?shop_order_id=<?php echo $order['shop_order_id']; ?>" class="btn btn-primary btn-sm">View Details</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination controls -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="orders.php?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&filter_status=<?php echo htmlspecialchars($filter_status); ?>">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="orders.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&filter_status=<?php echo htmlspecialchars($filter_status); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="orders.php?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&filter_status=<?php echo htmlspecialchars($filter_status); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
