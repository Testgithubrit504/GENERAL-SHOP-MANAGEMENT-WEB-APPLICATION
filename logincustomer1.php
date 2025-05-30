<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Shop</title>
    <!-- CSS Styling -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-image: url('../phpinsert/assets/img/store.jpg');
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid #ffffff;
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 30px;
            width: 350px;
            text-align: center;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .container h1 {
            font-size: 32px;
            color: #f85f73;
            margin-bottom: 20px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .container label {
            display: block;
            font-size: 14px;
            color: #ffffff;
            text-align: left;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            font-size: 14px;
            color: #333;
        }
        .container input::placeholder {
            color: #aaa;
        }
        .container button {
            background: #f85f73;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: 0.3s;
        }
        .container button:hover {
            background: #ff3c57;
        }
        .register-link {
            margin-top: 20px;
            font-size: 14px;
            color: #fff;
        }
        .register-link a {
            color: #f85f73;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .success-message {
            color: green;
            font-size: 14px;
            margin-top: 10px;
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Login Form -->
    <div class="container">
        <h1>SIGN IN TO ACCOUNT</h1>
        <p style="color: #fff; margin-bottom: 20px;">General Shop MANAGEMENT SYSTEM</p>
        <form method="POST" action="">
            <label for="name">NAME</label>
            <input type="text" id="name" name="name" placeholder="Name" required>
            
            <label for="email">EMAIL</label>
            <input type="email" id="email" name="email" placeholder="Email" required>
            
            <label for="contact">CONTACT NUMBER</label>
            <input type="text" id="contact" name="contact" placeholder="Contact Number" required>
            
            <button type="submit">Login</button>
        </form>
        <?php
        session_start();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Database connection
            $conn = new mysqli($servername = "localhost", $username = "root", $password = "", $dbname = "products");

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $name = $_POST['name'];
            $email = $_POST['email'];
            $contact = $_POST['contact'];

            // Query to fetch customer data including the name
            $sql = "SELECT name FROM customers WHERE email = ? AND contact = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $contact);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if customer exists
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $dbName = $row['name'];
                
                if (strtolower($name) === strtolower($dbName)) {
                    $_SESSION['name'] = $dbName;
                    $_SESSION['email'] = $email;
                    echo "<p class='success-message'>Welcome, $dbName! Login Successful! Redirecting...</p>";
                    header("Refresh: 2; URL=productmanage1.php");   // Redirect after 2 seconds
                } else {
                    echo "<p class='error-message'>Name does not match our records!</p>";
                }
            } else {
                echo "<p class='error-message'>Invalid email or contact number!</p>";
            }

            $stmt->close();
            $conn->close();
        }
        ?>
        <!-- Register Link -->
        <div class="register-link">
            <p>Don't have an account? <a href="register4.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
