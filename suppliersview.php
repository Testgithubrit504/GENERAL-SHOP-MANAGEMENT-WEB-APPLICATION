<?php
include 'database.php';

// SQL query to fetch supplier data
$sql = "SELECT * FROM suppliers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
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
            max-width: 1000px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 24px;
        }

        .supplier-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            text-align: left;
        }

        .supplier-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 12px;
            border: 1px solid var(--primary-color);
        }

        .supplier-table td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        .supplier-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .supplier-table tr:hover {
            background-color: #f1f1f1;
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
    <h2><i class="fas fa-list"></i> Supplier List</h2>

    <table class="supplier-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Supplier Name</th>
                <th>Location</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row["id"] ?></td>
                        <td><?= htmlspecialchars($row["name"]) ?></td>
                        <td><?= htmlspecialchars($row["location"]) ?></td>
                        <td><?= htmlspecialchars($row["contact"]) ?></td>
                        <td><?= htmlspecialchars($row["email"]) ?></td>
                        <td><?= htmlspecialchars($row["created_at"]) ?></td>
                        <td class="action-buttons">
                            <a href="edit3.php?id=<?= $row["id"] ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete2.php?id=<?= $row["id"] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this supplier?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7" class="no-data">No suppliers found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>

</body>
</html>
