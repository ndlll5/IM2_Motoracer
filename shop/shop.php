<?php
include '../db_connect.php';
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Constants
$rowPerPage = 12;

// Function to fetch categories
function getCategories($conn) {
    $query = $conn->query("SELECT * FROM product_category");
    if (!$query) {
        die("Error retrieving categories: " . $conn->error);
    }
    return $query->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch products
function getProducts($conn, $filterCategory, $startRow, $rowPerPage) {
    $filterCondition = $filterCategory ? "WHERE category_id = $filterCategory" : '';
    $query = $conn->query("SELECT * FROM product $filterCondition LIMIT $startRow, $rowPerPage");
    if (!$query) {
        die("Error retrieving products: " . $conn->error);
    }
    return $query->fetch_all(MYSQLI_ASSOC);
}


// Function to count total products
function getTotalProducts($conn, $filterCategory) {
    $filterCondition = $filterCategory ? "WHERE category_id = $filterCategory" : '';
    $query = $conn->query("SELECT COUNT(*) AS total FROM product $filterCondition");
    if (!$query) {
        die("Error retrieving total number of products: " . $conn->error);
    }
    return $query->fetch_assoc()['total'];
}

$categories = getCategories($conn);

// Function to fetch product items based on product_id
function getProductItems($conn, $product_id) {
    $query = $conn->prepare("SELECT * FROM product_item WHERE product_id = ?");
    $query->bind_param("i", $product_id);
    $query->execute();
    $result = $query->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    return $items;
}

// Determine current page
$currentPage = isset($_GET['pageNum']) ? intval($_GET['pageNum']) : 1;
$startRow = ($currentPage - 1) * $rowPerPage;

// Filter products by category if a category is selected
$filterCategory = isset($_GET['category']) ? $_GET['category'] : '';
$products = getProducts($conn, $filterCategory, $startRow, $rowPerPage);

// Count total number of products
$totalProducts = getTotalProducts($conn, $filterCategory);
$totalPages = ceil($totalProducts / $rowPerPage);

// Function to get category name
function getCategoryName($categories, $categoryId) {
    foreach ($categories as $category) {
        if ($category['category_id'] == $categoryId) {
            return $category['category_name'];
        }
    }
    return 'All Categories';
}

// Determine selected category name
$selectedCategoryName = getCategoryName($categories, $filterCategory);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/logo.png">
    <title>Motoracer Shop</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <!-- Categories Card -->
            <div class="col-md-4 col-lg-3">
                <div class="category-card">
                    <h5 class="mt-4 mb-3">Filter by Category:</h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="shop.php">All Categories</a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="shop.php?category=<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main role="main" class="col-md-8 col-lg-9 ml-md-auto px-4">
                <div class="container mt-5">
                    <h2 class="mb-4">Products <?php echo ($selectedCategoryName != 'All Categories') ? 'in ' . $selectedCategoryName : ''; ?></h2>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-4 col-md-6 mb-2">
                                <div class="card">
                                    <img src="../uploads/<?php echo $product['product_image']; ?>" class="card-img-top bg-dark" alt="<?php echo $product['name']; ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                        <p class="card-text"><?php echo (strlen($product['description']) > 100) ? substr($product['description'], 0, 100) . '...' : $product['description']; ?></p>
                                        <button type="button" class="btn mb-3 view-details-btn" data-toggle="modal" data-target="#details_<?php echo $product['product_id']; ?>" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['name']; ?>">View Details</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Pagination -->
                    <nav aria-label="Page navigation example">
                        <ul class="pagination">
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=1<?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">First</a>
                            </li>
                            <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=<?php echo $currentPage - 1; ?><?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">Previous</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#"><?php echo $currentPage; ?></a>
                            </li>
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=<?php echo $currentPage + 1; ?><?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">Next</a>
                            </li>
                            <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?pageNum=<?php echo $totalPages; ?><?php echo ($filterCategory) ? '&category=' . $filterCategory : ''; ?>">Last</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </main>
        </div>
    </div>

   <!-- view more -->
<?php foreach ($products as $product): ?>
    <div class="modal fade" id="details_<?php echo $product['product_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="details_<?php echo $product['product_id']; ?>Label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content bg-dark">
                <div class="modal-header">
                    <h5 class="modal-title" id="details_<?php echo $product['product_id']; ?>Label"><?php echo $product['name']; ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body m-4">
                    <?php
                        $productItems = getProductItems($conn, $product['product_id']);
                    ?>
                    <div class="d-flex align-items-start">
                        <div class="carousel-box w-50" align="center">
                            <div id="carousel_<?php echo $product['product_id']; ?>" class="carousel slide" data-ride="carousel" data-interval="false">
                                <div class="carousel-inner">
                                    <?php $firstItem = true; ?>
                                    <?php foreach ($productItems as $index => $item): ?>
                                        <div class="carousel-item <?php echo $firstItem ? 'active' : ''; ?>" data-price="<?php echo $item['price']; ?>" data-stock="<?php echo $item['stock_qty']; ?>" data-name="<?php echo $item['name']; ?>" data-item-id="<?php echo $item['item_id']; ?>">
                                            <img src="../uploads/<?php echo $item['product_image']; ?>" class="d-block" id="display-image" alt="<?php echo $item['name']; ?>">
                                        </div>
                                        <?php $firstItem = false; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#carousel_<?php echo $product['product_id']; ?>" role="button" data-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden"></span>
                            </a>
                            <a class="carousel-control-next" href="#carousel_<?php echo $product['product_id']; ?>" role="button" data-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden"></span>
                            </a>
                        </div>

                        <div class="item-info w-50 ml-3 pt-3">
                            <h1 id="item-name"><?php echo $productItems[0]['name']; ?></h1>
                            <hr style="background-color: #f0f0f0; border-top: 1px solid #555555;" />
                            <p><?php echo (strlen($product['description']) > 100) ? substr($product['description'], 0, 100) . '...' : $product['description']; ?></p>
                            <h2 id="item-price">Price: ₱<?php echo $productItems[0]['price']; ?></h2>
                            <p id="item-stock">Stock: <?php echo $productItems[0]['stock_qty']; ?></p>
                            <div>
                                <form id="add-to-cart-form">
                                    <div class="form-group">
                                        <label for="quantity">Quantity:</label>
                                        <div class="input-group col-xs-12 col-md-6">
                                            <div class="input-group-prepend">
                                                <button type="button" class="btn quantity-button" data-type="minus">-</button>
                                            </div>
                                            <input type="number" id="quantity" name="quantity" class="form-control text-center quantity-input" value="1" min="1" max="<?php echo $productItems[0]['stock_qty']; ?>">
                                            <div class="input-group-append">
                                                <button type="button" class="btn quantity-button" data-type="plus">+</button>
                                            </div>
                                        </div>  
                                    </div>
                                    <input type="hidden" id="product-id" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <input type="hidden" id="item-id" name="item_id" value="<?php echo $productItems[0]['item_id']; ?>">
                                    <button type="button" class="btn add-to-cart-btn" data-product-id="<?php echo $product['product_id']; ?>" data-item-id="<?php echo $productItems[0]['item_id']; ?>">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <h5 class="text-light">Please select an item:</h5>
                            <ul class="list-inline text-center">
                                <?php foreach ($productItems as $index => $item): ?>
                                    <li class="list-inline-item">
                                        <a href="#carousel_<?php echo $product['product_id']; ?>" data-slide-to="<?php echo $index; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
                                            <img src="../uploads/<?php echo $item['product_image']; ?>" class="img-thumbnail" alt="<?php echo $item['name']; ?>">
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
$(document).ready(function() {
    // Function to update item info
    function updateItemInfo(activeItem) {
        var price = activeItem.data('price');
        var stock = activeItem.data('stock');
        var name = activeItem.data('name');
        var itemId = activeItem.data('item-id');
        var modal = activeItem.closest('.modal');
        modal.find('#item-price').text('Price: ₱' + price);
        modal.find('#item-stock').text('Stock: ' + stock);
        modal.find('#item-name').text(name);
        modal.find('#quantity').attr('max', stock);
        modal.find('#item-id').val(itemId);
    }

    // view details button
    $('.view-details-btn').click(function() {
        var productId = $(this).data('id');
        var productName = $(this).data('name');
        var modal = $('#details_' + productId);
        modal.find('.modal-title').text(productName);

        // Initialize the item info for the first active item
        var initialActiveItem = modal.find('.carousel-item.active');
        updateItemInfo(initialActiveItem);
    });

    // Listen to the carousel slide event
    $('.carousel').on('slid.bs.carousel', function () {
        var activeItem = $(this).find('.carousel-item.active');
        updateItemInfo(activeItem);
    });

    $('.quantity-button').click(function() {
        var type = $(this).data('type');
        var input = $(this).closest('.input-group').find('input[name="quantity"]');
        var currentVal = parseInt(input.val());
        if (type === 'minus' && currentVal > input.attr('min')) {
            input.val(currentVal - 1).change();
        } else if (type === 'plus' && currentVal < input.attr('max')) {
            input.val(currentVal + 1).change();
        }
    });

    $('input[name="quantity"]').change(function() {
        var valueCurrent = parseInt($(this).val());
        var minValue = parseInt($(this).attr('min'));
        var maxValue = parseInt($(this).attr('max'));
        if (valueCurrent >= minValue) {
            $(".quantity-button[data-type='minus']").removeAttr('disabled');
        } else {
            $(this).val(minValue);
        }
        if (valueCurrent <= maxValue) {
            $(".quantity-button[data-type='plus']").removeAttr('disabled');
        } else {
            $(this).val(maxValue);
        }
    });

    $('.add-to-cart-btn').click(function() {
        var form = $(this).closest('form');
        var productId = form.find('input[name="product_id"]').val();
        var itemId = form.find('input[name="item_id"]').val();
        var quantity = form.find('input[name="quantity"]').val();

        $.post('add_to_cart.php', {
            product_id: productId,
            item_id: itemId,
            quantity: quantity
        }, function(response) {
            var result = JSON.parse(response);
            if (result.status === 'success') {
                alert(result.message);
            } else {
                alert(result.message);
            }
        });
    });
});
</script>




    <?php
        include 'footer.php';
    ?>