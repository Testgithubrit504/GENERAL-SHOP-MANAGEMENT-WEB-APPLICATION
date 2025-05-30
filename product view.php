<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "products";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch products
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
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
            padding: 20px;
            width: 90%;
            max-width: 1100px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            border: 1px solid var(--primary-color);
        }

        td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-btn,
        .delete-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            display: inline-block;
        }

        .edit-btn {
            background-color: var(--accent-color);
        }

        .edit-btn:hover {
            background-color: var(--secondary-color);
        }

        .delete-btn {
            background-color: var(--danger-color);
        }

        .delete-btn:hover {
            background-color: #d43f3f;
        }

        .no-data {
            text-align: center;
            font-size: 16px;
            color: var(--dark-color);
            padding: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-box-open"></i> Product List</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>IMAGE</th>
                <th>PRODUCT NAME</th>
                <th>STOCK</th>
                <th>DESCRIPTION</th>
                <th>SUPPLIERS</th>
                <th>CREATED BY</th>
                <th>CREATED AT</th>
                <th>UPDATED AT</th>
                <th>PRICE</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php $counter = 1; ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $counter ?></td>
                        <td><img src="<?= htmlspecialchars($row['image']) ?>" alt="Product Image" class="product-image"></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['stock']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td><?= str_replace(',', '<br>• ', htmlspecialchars($row['suppliers'])) ?></td>
                        <td><?= htmlspecialchars($row['created by'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['created at']) ?></td>
                        <td><?= htmlspecialchars($row['updated at']) ?></td>
                        <td>₹<?= number_format($row['price'], 2) ?></td>
                        <td class="action-buttons">
                            <a href="product edit2.php?id=<?= $row['id'] ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete1.php?id=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                    <?php $counter++; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="11" class="no-data">No products found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>

</body>
</html>
