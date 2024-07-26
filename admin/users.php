<?php
include '../db_connect.php';

// Fetch users
$sql = "SELECT user_id, CONCAT(firstname, ' ', lastname) AS name, email FROM user";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .main-title {
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }

        .data-table th {
            background-color: #343a40;
            color: #fff;
        }

        .btn {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            text-decoration: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .user-info {
            margin-bottom: 20px;
        }

        .user-info p {
            margin: 5px 0;
        }
    </style>

</head>
<body class="bg-dark text-white">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1 class="main-title">Users</h1>
        <table class="table table-dark table-striped data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><a href="user_details.php?user_id=<?php echo $row['user_id']; ?>" class="btn">View Details</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
