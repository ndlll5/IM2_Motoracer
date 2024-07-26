<?php
include 'navbar.php';
include '../db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Fetch user profile information
    $sql = "SELECT firstname, lastname, email FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
    } 
}
?>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['firstname']) . ' ' . htmlspecialchars($_SESSION['lastname']); ?>!</h2>
                </div>
                <div class="card-body">
                    <h4>Profile Information</h4>
                    <form method="POST" action="update_profile.php">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" class="form-control" id="firstname" name="firstname" value="<?php echo htmlspecialchars($_SESSION['firstname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" class="form-control" id="lastname" name="lastname" value="<?php echo htmlspecialchars($_SESSION['lastname']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4>Order History</h4>
                </div>
                <div class="card-body">
                    <!-- Display user's order history here -->
                    <?php
                    // Fetch user's order history from the database
                    $order_sql = "SELECT shop_order_id, order_date, order_total, order_status FROM shop_order WHERE user_id = ? 
                                  ORDER BY 
                                      CASE 
                                          WHEN order_status = 'Completed' THEN 1 
                                          ELSE 0 
                                      END, 
                                      order_date DESC, 
                                      shop_order_id DESC";
                    $order_stmt = $conn->prepare($order_sql);

                    if ($order_stmt === false) {
                        die('Prepare failed: ' . htmlspecialchars($conn->error));
                    }

                    $order_stmt->bind_param("i", $user_id);
                    $order_stmt->execute();
                    $order_result = $order_stmt->get_result();

                    if ($order_result->num_rows > 0) {
                        echo '<table class="table text-white">';
                        echo '<thead class="thead-dark">';
                        echo '<tr>';
                        echo '<th scope="col" class="text-nowrap">Order ID</th>';
                        echo '<th scope="col">Date</th>';
                        echo '<th scope="col">Total</th>';
                        echo '<th scope="col">Status</th>';
                        echo '<th scope="col">Action</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';
                        while ($order = $order_result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<th scope="row">' . htmlspecialchars($order['shop_order_id']) . '</th>';
                            echo '<td class="text-nowrap">' . htmlspecialchars($order['order_date']) . '</td>';
                            echo '<td>₱' . htmlspecialchars($order['order_total']) . '</td>';
                            echo '<td>' . htmlspecialchars($order['order_status']) . '</td>';
                            echo '<td><a href="order_details.php?shop_order_id=' . htmlspecialchars($order['shop_order_id']) . '" class="btn btn-info btn-sm text-nowrap">View Details</a></td>';
                            echo '</tr>';
                        }
                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<p>No order history available.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
include 'footer.php';
?>
