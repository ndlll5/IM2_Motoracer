<?php include 'header.php'; ?>
<?php include 'db_connect.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error); // Debugging line
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];  // Store role in session

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: shop/shop.php");
        }
        exit();
    } else {
        echo "<div class='alert alert-danger'>Invalid email or password</div>";
    }
}
?>

<h2>Login</h2>
<form action="login.php" method="post">
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-custom">Login</button>
</form>

<?php include 'footer.php'; ?>
