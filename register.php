<?php
include 'database.php';

$errors = [];
$success = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $contact = $_POST['contact'] ?? '';

    // Validate the mobile number (10 digits starting with 7, 8, or 9)
    if (!preg_match("/^[789]\d{9}$/", $contact)) {
        $errors[] = "Invalid mobile number! It should be 10 digits and start with 7, 8, or 9.";
    } else {
        // Insert data into database
        $stmt = $conn->prepare("INSERT INTO customers (name, email, contact) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $contact);

        if ($stmt->execute()) {
            $success = "Registration Successful!";
        } else {
            $errors[] = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --danger-color: #ff4d4d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: var(--light-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: white;
            padding: 30px;
            width: 450px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 24px;
        }

        .error {
            background: var(--danger-color);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .success {
            background: var(--success-color);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: var(--dark-color);
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(67, 97, 238, 0.3);
        }

        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        button:hover {
            background-color: var(--secondary-color);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: var(--primary-color);
            font-size: 14px;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="container">
    <h3><i class="fas fa-user-plus"></i> Customer Registration</h3>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success">
            <p><?php echo $success; ?></p>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="name"><i class="fas fa-user"></i> Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="contact"><i class="fas fa-phone"></i> Contact Number</label>
            <input type="text" name="contact" pattern="^[789]\d{9}$" title="Mobile number should be 10 digits and start with 7, 8, or 9" required>
        </div>

        <button type="submit" name="submit"><i class="fas fa-save"></i> Register</button>
    </form>

    <a href="dashboard.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
