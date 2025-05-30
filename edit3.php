<?php
include 'database.php';

// Get supplier ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$result = $conn->query("SELECT * FROM suppliers WHERE id = $id");

// Check if supplier exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    // Redirect or show error if supplier not found
    echo "<p style='color: red;'>Supplier not found. <a href='supplierslist.php'>Go back</a></p>";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $created_at = $_POST['created_at'];

    $update_query = "UPDATE suppliers SET 
        name = '$name', 
        location = '$location',
        email = '$email', 
        contact = '$contact', 
        created_at = '$created_at' 
        WHERE id = $id";

    if ($conn->query($update_query) === TRUE) {
        header("Location: suppliersview.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        form { max-width: 600px; margin: auto; }
        label { display: block; margin: 15px 0 5px; }
        input, button { width: 100%; padding: 10px; margin-bottom: 20px; }
        button { background-color: #007BFF; color: white; border: none; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h2>Edit Supplier</h2>

<form method="POST">
    <label>Supplier ID:</label>
    <input type="text" name="id" value="<?php echo htmlspecialchars($row['id']); ?>" readonly>

    <label>Supplier Name:</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>

    <label>Supplier Location:</label>
    <input type="text" name="location" value="<?php echo htmlspecialchars($row['location']); ?>" required>

    <label>Contact Details:</label>
    <input type="text" name="contact" value="<?php echo htmlspecialchars($row['contact']); ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

    <label>Created At:</label>
    <input type="datetime-local" name="created_at" value="<?php echo date('Y-m-d\TH:i', strtotime($row['created_at'])); ?>" required>

    <button type="submit">Update Supplier</button>
</form>

</body>
</html>
