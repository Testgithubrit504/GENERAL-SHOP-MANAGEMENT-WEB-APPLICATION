<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "products";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is set
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Fetch product by ID
    $sql = "SELECT * FROM products WHERE id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
} else {
    die("Invalid product ID.");
}

// Update product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    $suppliers = $_POST['suppliers'];
    $price=$_POST['price'] ;   
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Define the target directory and file
        $target_dir = "assets/img/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);

        // Ensure directory exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file; // Set image path for database update
        } else {
            echo "Error uploading image.";
            exit;
        }
    } else {
        // If no new image is uploaded, use the existing image
        $image = $row['image']; // Existing image
    }

    // Update product in the database
    $updateSQL = "UPDATE products SET 
                    name = '$name', 
                    stock = '$stock', 
                    description = '$description',
                    suppliers = '$suppliers',
                    image = '$image',
                    price='$price',
                     `updated at` = NOW() 
                 WHERE id = $id";

    if ($conn->query($updateSQL) === TRUE) {
        echo "Product updated successfully!";
        header("Location: product view.php"); // Redirect after successful update
        exit();
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        .currency-input {
            position: relative;
            width: 200px;
        }

        .currency-input input {
            padding-left: 25px; /* Space for the rupee symbol */
            width: 100%;
            padding-right: 10px;
        }

        .currency-input .rupee-symbol {
            position: absolute;
            left: 5px;
            top: 30%;
            transform: translateY(-50%);
            font-size: 18px;
        }
    </style>
</head>
<body>

<h1>Edit Product</h1>

<form method="post" enctype="multipart/form-data">
    <label>Product Name:</label><br>
    <input type="text" name="name" value="<?php echo $row['name']; ?>" required><br><br>

    <label>Stock:</label><br>
    <input type="number" name="stock" value="<?php echo $row['stock']; ?>" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required><?php echo $row['description']; ?></textarea><br><br>

    <label>Suppliers (comma-separated):</label><br>
    <input type="text" name="suppliers" value="<?php echo $row['suppliers']; ?>" required><br><br>

    <label>Current Image:</label><br>
    <?php if (!empty($row['image'])): ?>
        <img src="<?php echo $row['image']; ?>" alt="Product Image" width="150"><br><br>
    <?php else: ?>
        <p>No image available.</p><br><br>
    <?php endif; ?>

    <label>Upload New Image (optional):</label><br>
    <input type="file" name="image"><br><br>
    <label for="price">Price: </label>
    <div class="currency-input">
            <span class="rupee-symbol">₹</span>
    <input type="number" id="price" name="price" step="1" min="0" placeholder="Enter product price" required>


    <input type="submit" value="Update Product">
</form>

</body>
</html>
