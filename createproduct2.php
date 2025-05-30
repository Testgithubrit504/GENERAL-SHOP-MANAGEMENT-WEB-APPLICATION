<?php
include 'database.php';

$errors = [];
$success = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $suppliers = $_POST['suppliers'] ?? '';
    $price = $_POST['price'] ?? '';

    // Validate price is a valid number
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Invalid price value. Please enter a valid number.";
    }

    // File upload handling
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $target_dir = "assets/img/";
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);

        // Ensure directory exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $target_file)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO products (name, description, suppliers, image, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssd", $product_name, $description, $suppliers, $target_file, $price);

            if ($stmt->execute()) {
                $success = "Product created successfully!";
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Error uploading file.";
        }
    } else {
        $errors[] = "No file uploaded or file upload error.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
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

        .form-container {
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
        textarea,
        select,
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus,
        input[type="number"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(67, 97, 238, 0.3);
        }

        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: white;
        }

        input[type="file"]:hover {
            border-color: var(--primary-color);
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

<div class="form-container">
    <h3><i class="fas fa-box-open"></i> Create Product</h3>

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

    <form action="createproduct2.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="product_name"><i class="fas fa-tag"></i> Product Name</label>
            <input type="text" name="product_name" required>
        </div>

        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Description</label>
            <textarea name="description" rows="3" required></textarea>
        </div>

        <div class="form-group">
            <label for="suppliers"><i class="fas fa-truck"></i> Suppliers</label>
            <select name="suppliers" required>
                <option value="">Select Supplier</option>
                <option value="Robinson">Robinson</option>
                <option value="Nestle">Nestle</option>
            </select>
        </div>

        <div class="form-group">
            <label for="price"><i class="fas fa-rupee-sign"></i> Price</label>
            <input type="number" name="price" step="0.01" min="0" required>
        </div>

        <div class="form-group">
            <label for="product_image"><i class="fas fa-image"></i> Product Image</label>
            <input type="file" name="product_image" required>
        </div>

        <button type="submit"><i class="fas fa-save"></i> Save Product</button>
    </form>

    <a href="dashboard.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
