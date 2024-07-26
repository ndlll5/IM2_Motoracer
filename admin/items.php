<?php
include '../db_connect.php';

// Fetch products for the dropdown
$product_query = "SELECT product_id, name FROM product";
$product_result = mysqli_query($conn, $product_query);
$products = mysqli_fetch_all($product_result, MYSQLI_ASSOC);

// Fetch product info for display
$product_info = null;
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $product_info_query = "
        SELECT p.*, c.category_name 
        FROM product p 
        JOIN product_category c ON p.category_id = c.category_id 
        WHERE p.product_id='$product_id'
    ";
    $product_info_result = mysqli_query($conn, $product_info_query);
    if ($product_info_result) {
        $product_info = mysqli_fetch_assoc($product_info_result);
    } else {
        echo "Error fetching product information: " . mysqli_error($conn);
    }
}

// Handle form submission for adding/editing items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $SKU = mysqli_real_escape_string($conn, $_POST['SKU']);
    $stock_qty = mysqli_real_escape_string($conn, $_POST['stock_qty']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($product_image);

    if ($product_image && move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $image_sql = ", product_image='$product_image'";
    } else {
        $image_sql = "";
    }

    if (isset($_POST['item_id']) && $_POST['item_id']) {
        // Update existing item
        $item_id = mysqli_real_escape_string($conn, $_POST['item_id']);
        $query = "UPDATE product_item SET product_id='$product_id', name='$name', SKU='$SKU', stock_qty='$stock_qty', price='$price' $image_sql WHERE item_id='$item_id'";
    } else {
        // Insert new item
        $query = "INSERT INTO product_item (product_id, name, SKU, stock_qty, price, product_image) VALUES ('$product_id', '$name', '$SKU', '$stock_qty', '$price', '$product_image')";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: items.php?product_id=$product_id");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Handle item deletion
if (isset($_GET['delete'])) {
    $item_id = mysqli_real_escape_string($conn, $_GET['delete']);
    $delete_query = "DELETE FROM product_item WHERE item_id='$item_id'";
    if (mysqli_query($conn, $delete_query)) {
        header("Location: items.php?product_id=$product_id");
        exit();
    } else {
        echo "Error deleting item: " . mysqli_error($conn);
    }
}

// Fetch existing items for display
if (isset($_GET['product_id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['product_id']);
    $item_query = "
        SELECT pi.*, p.name AS product_name 
        FROM product_item pi
        JOIN product p ON pi.product_id = p.product_id
        WHERE pi.product_id='$product_id'
    ";
} else {
    $item_query = "
        SELECT pi.*, p.name AS product_name 
        FROM product_item pi
        JOIN product p ON pi.product_id = p.product_id
    ";
}

$item_result = mysqli_query($conn, $item_query);
if ($item_result) {
    $items = mysqli_fetch_all($item_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching items: " . mysqli_error($conn);
    $items = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Items</h1>

        <!-- Display product information -->
        <div class="card bg-dark mb-3 p-4">
            <h5 class="card-title">Product Information</h5>
            <?php if ($product_info): ?>
                <div class="row">
                    <div class="col-md-2">
                        <img src="../uploads/<?php echo $product_info['product_image']; ?>" alt="Product Image" class="img-fluid">
                    </div>
                    <div class="col-md-10">
                        <h3><?php echo $product_info['name']; ?></h3>
                        <p><strong>Category:</strong> <?php echo $product_info['category_name']; ?></p>
                        <p><strong>Description:</strong> <?php echo $product_info['description']; ?></p>
                    </div>
                </div>
            <?php else: ?>
                <p>No product selected.</p>
            <?php endif; ?>
        </div>

        <!-- Button to open the Add Item modal -->
        <button type="button" class="btn mb-3" data-toggle="modal" data-target="#itemModal" onclick="clearItemForm(<?php echo isset($product_id) ? $product_id : 'null'; ?>);">
            Add Item
        </button>

        <!-- Table to display existing items -->
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Product</th>
                    <th>Item Name</th>
                    <th>SKU</th>
                    <th>Stock Qty</th>
                    <th>Price</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['item_id']; ?></td>
                        <td><?php echo $item['product_name']; ?></td>
                        <td><?php echo $item['name']; ?></td>
                        <td><?php echo $item['SKU']; ?></td>
                        <td><?php echo $item['stock_qty']; ?></td>
                        <td><?php echo $item['price']; ?></td>
                        <td><img src="../uploads/<?php echo $item['product_image']; ?>" alt="Product Image" style="width: 50px; height: auto;"></td>
                        <td>
                            <button class="btn btn-sm" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>);">Edit</button>
                            <a href="items.php?delete=<?php echo $item['item_id']; ?>&product_id=<?php echo $product_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a type="button" class="btn mb-3" href="products.php">
            Back to Products
                </a>
    </div>

    <!-- Modal for adding/editing items -->
    <div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-white">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalLabel">Add/Edit Item</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="itemForm" action="items.php?product_id=<?php echo isset($product_id) ? $product_id : ''; ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="item_id" id="item_id">
                        <input type="hidden" name="product_id" id="form_product_id" value="<?php echo isset($product_id) ? $product_id : ''; ?>">
                        <div class="form-group">
                            <label for="product_id">Product</label>
                            <select class="form-control bg-dark text-white" id="product_id" name="product_id" required>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>" <?php echo isset($product_id) && $product_id == $product['product_id'] ? 'selected' : ''; ?>><?php echo $product['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Item Name</label>
                            <input type="text" class="form-control bg-dark text-white" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="SKU">SKU</label>
                            <input type="text" class="form-control bg-dark text-white" id="SKU" name="SKU" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_qty">Stock Quantity</label>
                            <input type="number" class="form-control bg-dark text-white" id="stock_qty" name="stock_qty" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="text" class="form-control bg-dark text-white" id="price" name="price" required>
                        </div>
                        <div class="form-group">
                            <label for="product_image">Product Image</label>
                            <input type="file" class="form-control-file bg-dark text-white" id="product_image" name="product_image">
                        </div>
                        <button type="submit" class="btn">Save Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function clearItemForm(productId) {
            document.getElementById('item_id').value = '';
            document.getElementById('product_id').value = productId || '';
            document.getElementById('name').value = '';
            document.getElementById('SKU').value = '';
            document.getElementById('stock_qty').value = '';
            document.getElementById('price').value = '';
        }

        function editItem(item) {
            document.getElementById('item_id').value = item.item_id;
            document.getElementById('product_id').value = item.product_id;
            document.getElementById('name').value = item.name;
            document.getElementById('SKU').value = item.SKU;
            document.getElementById('stock_qty').value = item.stock_qty;
            document.getElementById('price').value = item.price;
            $('#itemModal').modal('show');
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>
