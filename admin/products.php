<?php
include '../db_connect.php';

// Handle form submission for adding/editing products
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($product_image);

    if ($product_image && move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $image_sql = ", product_image='$product_image'";
    } else {
        $image_sql = "";
    }

    if (isset($_POST['product_id']) && $_POST['product_id']) {
        // Update existing product
        $product_id = $_POST['product_id'];
        $query = "UPDATE product SET category_id='$category_id', name='$name', description='$description' $image_sql WHERE product_id='$product_id'";
    } else {
        // Insert new product
        $query = "INSERT INTO product (category_id, name, description, product_image) VALUES ('$category_id', '$name', '$description', '$product_image')";
    }

    if (mysqli_query($conn, $query)) {
        header("Location: products.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Fetch product items to delete orderlines
        $fetch_items_query = "SELECT item_id FROM product_item WHERE product_id='$product_id'";
        $items_result = mysqli_query($conn, $fetch_items_query);

        if (!$items_result) {
            throw new Exception("Error fetching product items: " . mysqli_error($conn));
        }

        $items = mysqli_fetch_all($items_result, MYSQLI_ASSOC);

        foreach ($items as $item) {
            $item_id = $item['item_id'];

            // Delete all orderlines associated with the product items
            $delete_orderlines_query = "DELETE FROM orderline WHERE product_item_id='$item_id'";
            if (!mysqli_query($conn, $delete_orderlines_query)) {
                throw new Exception("Error deleting orderlines: " . mysqli_error($conn));
            }
        }

        // Delete all items associated with the product
        $delete_items_query = "DELETE FROM product_item WHERE product_id='$product_id'";
        if (!mysqli_query($conn, $delete_items_query)) {
            throw new Exception("Error deleting product items: " . mysqli_error($conn));
        }

        // Delete the product
        $delete_product_query = "DELETE FROM product WHERE product_id='$product_id'";
        if (!mysqli_query($conn, $delete_product_query)) {
            throw new Exception("Error deleting product: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);
        header("Location: products.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        echo $e->getMessage();
        exit();
    }
}

// Pagination and search settings
$limit = 10; // Number of rows per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch products for display with search functionality
$product_query = "SELECT product.*, product_category.category_name AS category_name FROM product 
                  JOIN product_category ON product.category_id = product_category.category_id
                  WHERE product.name LIKE '%$search%'
                  LIMIT $limit OFFSET $offset";
$product_result = mysqli_query($conn, $product_query);
if ($product_result) {
    $products = mysqli_fetch_all($product_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching products: " . mysqli_error($conn);
    $products = [];
}

// Fetch total number of products for pagination
$total_query = "SELECT COUNT(*) as total FROM product WHERE name LIKE '%$search%'";
$total_result = mysqli_query($conn, $total_query);
$total_rows = mysqli_fetch_assoc($total_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch categories for the dropdown
$category_query = "SELECT category_id, category_name FROM product_category";
$category_result = mysqli_query($conn, $category_query);
if ($category_result) {
    $categories = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
} else {
    echo "Error fetching categories: " . mysqli_error($conn);
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #212529; /* Dark background color */
        }

        .table {
            background-color: #343a40; /* Darker background for the table */
        }


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
            background-color: #343a40; /* Dark background color for modal */
            color: #fff; /* White text color for modal */
        }

        .btn {
            background-color: #f00; /* Red background for buttons */
            color: #fff; /* White text color for buttons */
        }

        .btn:hover {
            background-color: #c00; /* Darker red on hover */
            color: #fff; /* White text color on hover */
        }
    </style>
</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="mt-5">Products</h1>

        <div class="row">
            <div class="col mb-3">
                <!-- Search form -->
                <form class="form-inline" method="GET" action="products.php">
                    <input class="form-control mr-2" type="search" name="search" placeholder="Search products" aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-outline-light btn-sm" type="submit">Search</button>
                </form>
            </div>
            <div class="col text-right">
                <!-- Button to open the Add Product modal -->
                <button type="button" class="btn btn-sm" data-toggle="modal" data-target="#productModal" onclick="clearProductForm();">
                    Add Product
                </button>
            </div>
        </div>

        <!-- Table to display existing products -->
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th class="text-nowrap pr-5">Product ID</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['product_id']; ?></td>
                        <td><?php echo $product['category_name']; ?></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><?php echo $product['description']; ?></td>
                        <td><img src="../uploads/<?php echo $product['product_image']; ?>" alt="Product Image" style="width: 50px; height: auto;"></td>
                        <td>
                            <div class="mb-2 text-nowrap">
                                <button class="btn btn-sm btn-primary" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>);">Edit</button>
                                <a href="products.php?delete=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this product and all its items?');">Delete</a>
                            </div>
                            <a href="items.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-secondary w-100">Manage Items</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination controls -->
        <nav>
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="products.php?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Previous</a></li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>"><a class="page-link" href="products.php?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item"><a class="page-link" href="products.php?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Next</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Modal for adding/editing products -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Add/Edit Product</h5>
                    <button type="button" class="close light" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="productForm" action="products.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="product_id" id="product_id">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="product_image">Product Image</label>
                            <input type="file" class="form-control-file" id="product_image" name="product_image">
                        </div>
                        <button type="submit" class="btn">Save Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function clearProductForm() {
            document.getElementById('product_id').value = '';
            document.getElementById('category_id').value = '';
            document.getElementById('name').value = '';
            document.getElementById('description').value = '';
        }

        function editProduct(product) {
            document.getElementById('product_id').value = product.product_id;
            document.getElementById('category_id').value = product.category_id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            $('#productModal').modal('show');
        }
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>

