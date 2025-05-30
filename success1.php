<?php
session_start();
ob_start(); // Start output buffering

require 'vendor/autoload.php';
require 'database.php';
require 'C:\xampp\htdocs\phpinsert\tcpdf\tcpdf.php';

use Stripe\Stripe;
use Stripe\Checkout\Session;

// Check Database Connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set Stripe API Key
Stripe::setApiKey('sk_test_51Qh8cWRtFIaJbj5jR5VgIMZer0BiwLZa8qMPLsRMa9oK6qiaAKMU8cg7KiYOodstyfl18z435xhMSKGfPpJucBYJ00YptHyObo');

if (!isset($_GET['session_id']) || !isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("Invalid request! Missing or invalid session ID or order ID.");
}

$session_id = $_GET['session_id'];
$order_id = (int) $_GET['order_id'];

try {
    $session = Session::retrieve($session_id);

    // Fetch Order Details
    $sql = "SELECT id, customer_name, customer_email, total_amount, delivery_address FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    if (!$order) {
        die("Order not found! Please check the order ID.");
    }

    $customerName = $order['customer_name'];
    $customerEmail = $order['customer_email'];
    $totalAmount = $order['total_amount'];
    $deliveryAddress = $order['delivery_address'];

    if ($session->payment_status === 'paid' && isset($_GET['generate_receipt'])) {
        $orderDate = date('Y-m-d H:i:s');
        ob_end_clean(); // Clean the output buffer before generating the PDF

        $pdf = new TCPDF();
        $pdf->SetCreator('MyStore');
        $pdf->SetAuthor('MyStore');
        $pdf->SetTitle('Payment Receipt');
        $pdf->SetSubject('Receipt');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Set Unicode Font for Rupees Symbol
        $pdf->SetFont('freeserif', '', 12); // Use freeserif for Unicode support

        // Header
        $pdf->SetFont('freeserif', 'B', 18);
        $pdf->SetTextColor(0, 102, 204); // Blue color
        $pdf->Cell(190, 10, 'Payment Receipt', 0, 1, 'C');
        $pdf->SetFont('freeserif', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Black color
        $pdf->Ln(5);
        $pdf->Cell(190, 10, "Order ID: $order_id", 0, 1);
        $pdf->Cell(190, 10, "Customer: $customerName", 0, 1);
        $pdf->Cell(190, 10, "Email: $customerEmail", 0, 1);
        $pdf->Cell(190, 10, "Order Date: $orderDate", 0, 1);
        $pdf->Cell(190, 10, "Delivery Address: $deliveryAddress", 0, 1);
        $pdf->Ln(10);

        // Table Header
        $pdf->SetFont('freeserif', 'B', 12);
        $pdf->SetFillColor(0, 102, 204); // Blue background
        $pdf->SetTextColor(255, 255, 255); // White text
        $pdf->Cell(80, 10, 'Product', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Quantity', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'Total', 1, 1, 'C', true);

        $pdf->SetFont('freeserif', '', 12);
        $pdf->SetTextColor(0, 0, 0); // Black text
        $grandTotal = 0;

        // Check if cart exists and is not empty
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $grandTotal += $itemTotal;

                $pdf->Cell(80, 10, $item['name'], 1, 0, 'C');
                $pdf->Cell(30, 10, "₹ " . number_format($item['price'], 2), 1, 0, 'C'); // Rupees symbol added
                $pdf->Cell(30, 10, $item['quantity'], 1, 0, 'C');
                $pdf->Cell(50, 10, "₹ " . number_format($itemTotal, 2), 1, 1, 'C'); // Rupees symbol added
            }
        } else {
            $pdf->Cell(190, 10, 'No items in the cart.', 1, 1, 'C');
        }

        // Total
        $pdf->SetFont('freeserif', 'B', 12);
        $pdf->SetFillColor(0, 102, 204); // Blue background
        $pdf->SetTextColor(255, 255, 255); // White text
        $pdf->Cell(140, 10, 'Total Amount', 1, 0, 'C', true);
        $pdf->Cell(50, 10, "₹ " . number_format($grandTotal, 2), 1, 1, 'C', true); // Rupees symbol added

        $pdf->Ln(10);
        $pdf->SetFont('freeserif', 'I', 10);
        $pdf->SetTextColor(0, 102, 204); // Blue color
        $pdf->Cell(0, 10, 'Thank you for your purchase!', 0, 1, 'C');
        unset($_SESSION['cart']); // Clear the cart after generating the receipt

        $pdf->Output("Receipt_Order_$order_id.pdf", 'D');
        exit();
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "Error retrieving payment details: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Poppins', sans-serif;
            color: #333;
        }
        .container {
            margin-top: 50px;
            text-align: center;
        }
        .card {
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        .btn-download {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-download:hover {
            background: linear-gradient(135deg, #218838, #1e7e34);
            transform: translateY(-2px);
        }
        .table-container {
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .table {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .table th {
            background: linear-gradient(135deg, #28a745, #218838);
            color: white;
            font-weight: bold;
            padding: 14px;
        }
        .table td {
            background: #ffffff;
            padding: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .table tbody tr:hover {
            background: #f1f1f1;
            transform: scale(1.02);
        }
        h2 {
            color: #28a745;
            font-weight: 700;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2 class="text-success fw-bold">Payment Successful!</h2>
        <p class="text-muted">Thank you for your order.</p>

        <div class="table-container">
            <table class="table table-hover table-striped table-bordered">
                <tbody>
                    <tr><th>Order ID</th><td><?= htmlspecialchars($order_id) ?></td></tr>
                    <tr><th>Customer Name</th><td><?= htmlspecialchars($customerName) ?></td></tr>
                    <tr><th>Customer Email</th><td><?= htmlspecialchars($customerEmail) ?></td></tr>
                    <tr><th>Delivery Address</th><td><?= htmlspecialchars($deliveryAddress) ?></td></tr>
                    <tr><th>Total Paid</th><td>₹<?= number_format($totalAmount, 2) ?></td></tr>
                </tbody>
            </table>
        </div>

        <a href="success1.php?session_id=<?= htmlspecialchars($session_id) ?>&order_id=<?= htmlspecialchars($order_id) ?>&generate_receipt=1" class="btn-download">
            Download Receipt
        </a>
    </div>
</div>

</body>
</html>