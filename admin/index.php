<?php 
include '../db_connect.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$importantStatuses = ['Processing', 'Cancelled', 'Delivered', 'Completed', 'Return', 'Problem'];
$counts = [];

foreach ($importantStatuses as $status) {
    $sql = "SELECT COUNT(*) as count FROM shop_order WHERE order_status = '$status'";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $counts[$status] = $row['count'];
    } else {
        echo "Error: " . $conn->error;
        exit();
    }
}

// Get total number of products
$sql = "SELECT COUNT(*) as total_products FROM product";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $totalProducts = $row['total_products'];
} else {
    echo "Error: " . $conn->error;
    exit();
}

// Get out of stock products
$sql = "SELECT COUNT(*) as out_of_stock FROM product_item WHERE stock_qty = 0";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $outOfStockProducts = $row['out_of_stock'];
} else {
    echo "Error: " . $conn->error;
    exit();
}

// Get top 5 most popular products
$sql = "SELECT p.product_id, p.name, SUM(ol.qty) as total_sold 
        FROM orderline ol 
        JOIN product_item pi ON ol.product_item_id = pi.item_id
        JOIN product p ON pi.product_id = p.product_id 
        GROUP BY p.product_id 
        ORDER BY total_sold DESC 
        LIMIT 5";
$result = $conn->query($sql);
$popularProducts = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $popularProducts[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
    exit();
}

// Fetch monthly sales data
$sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(order_total) as total_sales
        FROM shop_order
        WHERE order_status = 'Completed'
        GROUP BY month
        ORDER BY month DESC
        LIMIT 12"; // Last 12 months
$result = $conn->query($sql);
$salesData = [];
$months = [];
$sales = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $months[] = $row['month'];
        $sales[] = $row['total_sales'];
    }
} else {
    echo "Error: " . $conn->error;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #343a40 !important;
            color: #e0e0e0 !important;
        }

        .btn-secondary {
            background-color: #343a40 !important;
            color: white !important;
            border: none !important;
        }

        .card-custom {
            background-color: #ffc107 !important;
            border-radius: 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .card-custom:hover {
            transform: translateY(-5px);
        }
        .card-title {
            font-size: 1.1rem;
            color: #343a40 !important;
        }
        .card-text {
            font-size: 2rem;
            color: #343a40 !important;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        .border-bottom-custom {
            border-bottom: 2px solid white;
        }
        .hidden-card {
            display: none;
        }
        .order-card-container {
            background-color: #3c4045;
            border-radius: 1rem;
            padding: 20px;
            margin-bottom: 20px;
        }
        #toggleCards {
            background-color: #ffca2c;
            border-color: #ffca2c;
            color: #343a40;
            font-size: 0.875rem;
            padding: 5px 10px;
        }
        #toggleCards:hover {
            background-color: #ffc107;
            border-color: #ffc107;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom-custom">
            <h1 class="mt-2">Dashboard</h1>
        </div>

        <!-- Sales Analytics -->
        <div class="order-card-container">
            <h2 class="mb-3">Sales Analytics</h2>
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>

        <div class="order-card-container" id="orderCardContainer">
            <h2 class="mb-3">Order Status Overview</h2>
            <div class="dashboard-grid" id="orderCards">
                <?php 
                $visibleCount = 4; // Number of cards to show initially
                $i = 0;
                foreach ($counts as $status => $count): 
                    $hiddenClass = $i >= $visibleCount ? 'hidden-card' : '';
                ?>
                    <div class="card card-custom border-0 <?php echo $hiddenClass; ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo $status; ?></h5>
                            <h3 class="card-text"><?php echo $count; ?></h3>
                        </div>
                    </div>
                <?php 
                    $i++;
                endforeach; 
                ?>
            </div>
            <button class="btn btn-secondary mt-3" id="toggleCards" type="button">
                Show More
            </button>
        </div>

        <div class="order-card-container">
            <h2 class="mb-3">Product Analytics</h2>
            <div class="dashboard-grid">
                <div class="card card-custom border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">Total Products</h5>
                        <h3 class="card-text"><?php echo $totalProducts; ?></h3>
                    </div>
                </div>
                <div class="card card-custom border-0">
                    <div class="card-body text-center">
                        <h5 class="card-title">Out of Stock Products</h5>
                        <h3 class="card-text"><?php echo $outOfStockProducts; ?></h3>
                    </div>
                </div>
            </div>
            <h2 class="mb-3 mt-4">Most Popular Products</h2>
            <div class="dashboard-grid">
                <?php foreach ($popularProducts as $product): ?>
                    <div class="card card-custom border-0">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo $product['name']; ?></h5>
                            <h3 class="card-text"><?php echo $product['total_sold']; ?> Sold</h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('salesChart').getContext('2d');
            var salesChart = new Chart(ctx, {
                type: 'line', // Change to 'bar' for a bar chart, or any other type
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Monthly Sales',
                        data: <?php echo json_encode($sales); ?>,
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            document.querySelectorAll('.hidden-card').forEach(function(card) {
                card.style.display = 'none'; // Ensure cards are hidden by default
            });

            document.getElementById('toggleCards').addEventListener('click', function() {
                var hiddenCards = document.querySelectorAll('.hidden-card');
                hiddenCards.forEach(function(card) {
                    card.style.display = card.style.display === 'none' ? 'block' : 'none';
                });
                this.textContent = this.textContent === 'Show Less' ? 'Show More' : 'Show Less';
            });
        });
    </script>
</body>
</html>
