<?php
include 'database.php';

$id = $_GET['id'];

$sql = "DELETE FROM suppliers WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: suppliersview.php");
} else {
    echo "Error deleting record: " . $conn->error;
}
?>
