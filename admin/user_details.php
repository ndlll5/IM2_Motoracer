<?php
include '../db_connect.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    // Fetch user details
    $user_sql = "SELECT * FROM user WHERE user_id = $user_id";
    $user_result = $conn->query($user_sql);
    $user = $user_result->fetch_assoc();
    
    // Fetch user orders
    $orders_sql = "SELECT * FROM shop_order WHERE user_id = $user_id";
    $orders_result = $conn->query($orders_sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #343a40; /* Dark gray background */
            color: white; /* Light text color */
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #212529; /* Darker background */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Light shadow */
        }
        .main-title {
            color: white; /* Red color for titles */
            text-align: center;
            margin-bottom: 20px;
        }
        .sub-title {
            color: white; /* Yellow color for subtitles */
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #444; /* Dark border color */
            padding: 8px;
            text-align: left;
            background-color: #343a40; /* Dark gray background */
            color: white; /* Light text color */
        }
        .data-table th {
            background-color: #555; /* Darker gray background for table headers */
        }
        .btn {
            display: inline-block;
            padding: 6px 12px;
            color: white;
            background-color: #007bff; /* Blue button background */
            text-decoration: none;
            border-radius: 4px;
        }
        .btn:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        .user-info {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Light shadow */
            background-color: #343a40; /* Dark gray background */
        }
        .user-info p {
            margin: 5px 0;
            color: white; /* Light text color */
        }
    </style>
</head>
<body class = "bg-dark">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="main-title">User Details</h1>
        <div class="user-info">
            <p><strong>Name:</strong> <?php echo $user['firstname'] . ' ' . $user['lastname']; ?></p>
            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
            <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
        </div>
        
        <h2 class="sub-title">Orders</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Order Total</th>
                    <th>Order Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $orders_result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $order['shop_order_id']; ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                        <td><?php echo $order['order_total']; ?></td>
                        <td><?php echo $order['order_status']; ?></td>
                        <td><a href="order_details.php?order_id=<?php echo $order['shop_order_id']; ?>" class="btn">View Order</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
