<?php
include 'database.php';

$errors = [];
$success = "";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier_name = trim($_POST['supplier_name']);
    $location = trim($_POST['location']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $created_at = date('Y-m-d H:i:s');

    // Validation
    if (empty($supplier_name) || empty($location) || empty($contact) || empty($email)) {
        $errors[] = "All fields are required.";
    }

    // Contact number validation
    if (!preg_match('/^[789]\d{9}$/', $contact)) {
        $errors[] = "Contact number must be exactly 10 digits and start with 7, 8, or 9.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !strpos($email, '@')) {
        $errors[] = "Invalid email format. Email must contain '@' sign and be valid.";
    }

    // If no errors, insert or update the data
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO suppliers (name, location, contact, email, created_at) VALUES (?, ?, ?, ?, ?)
                                ON DUPLICATE KEY UPDATE location = VALUES(location), contact = VALUES(contact), email = VALUES(email), created_at = VALUES(created_at)");
        $stmt->bind_param("sssss", $supplier_name, $location, $contact, $email, $created_at);

        if ($stmt->execute()) {
            $success = "Supplier created successfully";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Supplier</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --error-color: #ff4d4d;
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
        }

        .form-container {
            background-color: white;
            padding: 30px;
            width: 450px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
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

        .error {
            background: var(--error-color);
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

<div class="form-container">
    <h1><i class="fas fa-user-plus"></i> Add Supplier</h1>
    
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

    <form action="addsupplier.php" method="POST">
        <div class="form-group">
            <label for="supplier_name"><i class="fas fa-user"></i> Supplier Name</label>
            <input type="text" name="supplier_name" id="supplier_name" placeholder="Enter supplier name" required>
        </div>
        <div class="form-group">
            <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
            <input type="text" name="location" id="location" placeholder="Enter supplier location" required>
        </div>
        <div class="form-group">
            <label for="contact"><i class="fas fa-phone"></i> Contact Number</label>
            <input type="text" name="contact" id="contact" maxlength="10" placeholder="Enter 10-digit contact number" required pattern="[789][0-9]{9}" title="Contact must start with 7, 8, or 9 and be 10 digits long.">
        </div>
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" name="email" id="email" placeholder="Enter supplier email" required>
        </div>
        <button type="submit"><i class="fas fa-save"></i> Save Supplier</button>
    </form>

    <a href="dashboard.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>

