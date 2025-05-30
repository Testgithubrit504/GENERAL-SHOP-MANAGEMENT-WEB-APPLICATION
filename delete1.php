<?php
// Include database connection
$database_file = 'database.php';
if (file_exists($database_file)) {
    include($database_file);
} else {
    die("Database connection file not found.");
}

// Check if 'id' is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete query
    $sql = "DELETE FROM products WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        echo "Product deleted successfully!";
        header("Location: product view.php");
        exit();
    } else {
        echo "Error deleting product: " . $conn->error;
    }
} else {
    die("No product ID provided.");
}
?>

