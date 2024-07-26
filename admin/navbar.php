<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="logo.png">
    <title>Motoracer</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../assets/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700&display=swap" rel="stylesheet">
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
        .navbar-custom .form-inline .btn-outline-danger {
            color: #ff0000; /* Red text */
            border-color: #ff0000; /* Red border */
        }
        .navbar-custom .form-inline .btn-outline-danger:hover {
            background-color: #ff0000; /* Red background on hover */
            color: #fff; /* White text on hover */
        }
        body {
          font-family: 'Titillium Web';
}

.sidebar {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1;
  top: 0;
  left: 0;
  background-color: #111;
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 60px;
}

.sidebar a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 25px;
  color: #818181;
  display: block;
  transition: 0.3s;
}

.sidebar a:hover {
  color: #f1f1f1;
}

.sidebar .closebtn {
  position: absolute;
  top: 0;
  right: 25px;
  font-size: 36px;
  margin-left: 50px;
}

.openbtn {
  font-size: 20px;
  cursor: pointer;
  background-color: #111;
  color: white;
  padding: 10px 15px;
  border: none;
}

.openbtn:hover {
  background-color: #444;
}

#top {
  transition: margin-left .5s;
}

#main{
    transition: margin-left .5s;
    padding: 16px;
    min-height:100vh !important;
} 

#logout {
  margin-left: 90%;
}

/* On smaller screens, where height is less than 450px, change the style of the sidenav (less padding and a smaller font size) */
@media screen and (max-height: 450px) {
  .sidebar {padding-top: 15px;}
  .sidebar a {font-size: 18px;}
}

.btn:hover {
  background-color: darkred;
  color: #fff;
}

.btn{
  background-color: red;
  color: #fff;
  padding: 10px 20px;
  text-decoration: none;
  border-radius: 5px;
  transition: background-color 0.3s;
}
    </style>
</head>
<body class="d-flex flex-column vh-100">
    <div id="mySidebar" class="sidebar">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()"><</a>
        <a class="navbar-brand ml-2" href="#">
            <img src="../assets/logo.png" height="75" class="d-inline-block align-top" alt="">
        </a>
        <div class="side-bar-contents mt-3">
            <a class="side-bar-item text-nowrap" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a class="side-bar-item text-nowrap" href="products.php"><i class = "fas fa-box-open"></i> Products</a>
            <a class="side-bar-item text-nowrap" href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a class="side-bar-item text-nowrap" href="payment.php"><i class="fas fa-credit-card"></i> Payments</a>
            <a class="side-bar-item text-nowrap" href="users.php"><i class="fas fa-users"></i> Users</a>
        </div>
    </div>
    
    <div id="top">
        <nav class="navbar navbar-expand-lg navbar-custom pr-4">
            <button class="openbtn" onclick="openNav()">☰</button>
            <a class="btn btn-outline-danger text-nowrap" id="logout" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
        </nav>
    </div>
    <div id="main">
      