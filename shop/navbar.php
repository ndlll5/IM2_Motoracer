<?php
    session_start();
    include '../db_connect.php';
    $current_page = basename($_SERVER['PHP_SELF']);
    $pages_without_search = ['contact.php', 'profile.php','cart.php','success.php'];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../assets/logo.png">
    <title>Motoracer</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-custom {
            background-color: #000; /* Black background */
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link,
        .navbar-custom .form-inline .btn {
            color: #fff; /* White text */
        }
        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link:focus,
        .navbar-custom .form-inline .btn:hover,
        .navbar-custom .form-inline .btn:focus {
            color: #ff0000; /* Red text on hover/focus */
        }
        .navbar-custom .form-inline .btn-custom {
            color: #fff; /* Red text */
            background-color: #ff0000; /* Red Background */
        }
        .navbar-custom .form-inline .btn-custom:hover {
            background-color: darkred; /* Red background on hover */
            color: #fff; /* White text on hover */
        }
        .text-light:hover {
            color: #FFC107; 
        }
 
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
    <a class="navbar-brand" href="shop.php">
        <img src="../assets/logo.png" height="55" class="d-inline-block align-top" alt="">
    </a>
    <!-- <a class="navbar-brand" href="#">Motoracer</a> -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- <?php if (!in_array($current_page, $pages_without_search)): ?>
            <div class="flex-grow-1 d-flex">
                <form class="form-inline flex-nowrap mx-0 mx-lg-auto rounded p-1" action="shop.php" method="GET">
                    <input class="form-control mr-sm-2" type="search" placeholder="Search" name="search_query">
                    <button class="btn btn-custom" type="submit">Search</button>
                </form>
            </div>
        <?php endif; ?> -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="contact.php">Contact<span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">Cart</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">Account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-2 flex-grow-1">
