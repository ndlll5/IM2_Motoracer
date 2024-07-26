<?php
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category_id = (int) $_POST['category_id'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($product_image);

    // Validate file type
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_extensions = array("jpg", "jpeg", "png", "gif");
    if (!in_array($imageFileType, $valid_extensions)) {
        echo "Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.";
        exit();
    }

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $query = "INSERT INTO product (category_id, name, description, product_image) VALUES ('$category_id', '$name', '$description', '$product_image')";
        if (mysqli_query($conn, $query)) {
            header("Location: products.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading file.";
    }
}
?>

<?php include 'header.php'; ?>

<h1 class="mt-5">Add Product</h1>
<form action="add_product.php" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="form-group">
        <label for="category_id">Category</label>
        <input type="number" class="form-control" id="category_id" name="category_id" required>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" required></textarea>
    </div>
    <div class="form-group">
        <label for="product_image">Product Image</label>
        <input type="file" class="form-control-file" id="product_image" name="product_image" required>
    </div>
    <button type="submit" class="btn">Add Product</button>
</form>

<?php include 'footer.php'; ?>
