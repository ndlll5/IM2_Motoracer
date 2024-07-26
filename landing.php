<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="assets/logo.png">
    <title>Motoracer</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Titillium Web', sans-serif;
            background: linear-gradient(135deg, #000000, #333333);
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            background: rgba(0, 0, 0, 0.8);
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
        }
        .btn-custom {
            background-color: #ff0000;
            color: #fff;
            border: none;
            padding: 12px 25px;
            margin: 20px 10px;
            font-size: 20px;
            border-radius: 30px;
            transition: background-color 0.3s, transform 0.3s;
        }
        .btn-custom:hover {
            background-color: darkred;
            transform: scale(1.05);
        }
        .logo {
            width: 150px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="assets/logo.png" alt="Motoracer Logo" class="logo">
        <h1>Welcome to Motoracer</h1>
        <p>Innovative solutions for modern businesses.</p>
        <a href="login.php" class="btn btn-custom">Login</a>
        <a href="register.php" class="btn btn-custom">Register</a>
    </div>
</body>
</html>