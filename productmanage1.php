<?php
session_start();
// Database Connection
include 'database.php';
require __DIR__ . '/vendor/autoload.php';
require 'fpdf186/fpdf.php';

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
$name = $_SESSION['name'];
$email = $_SESSION['email'];

Stripe::setApiKey('sk_test_51Qh8cWRtFIaJbj5jR5VgIMZer0BiwLZa8qMPLsRMa9oK6qiaAKMU8cg7KiYOodstyfl18z435xhMSKGfPpJucBYJ00YptHyObo');
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $productName = $_POST['product_name'];
    $productPrice = $_POST['product_price'];
    $quantity = $_POST['quantity'];

    // Validate quantity
    if (!is_numeric($quantity) || $quantity <= 0) {
        echo "<script>alert('Invalid quantity. Please try again.');</script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Add to session cart
    $_SESSION['cart'][] = [
        'id' => $productId,
        'name' => $productName,
        'price' => $productPrice,
        'quantity' => (int)$quantity, // Ensure quantity is treated as an integer
    ];

    echo "<script>alert('Product added to cart!');</script>";
    header("Location: " . $_SERVER['PHP_SELF']); // Refresh page
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $paymentMethod = $_POST['payment_method'];
    $totalAmount = array_reduce($_SESSION['cart'], function ($total, $item) {
        return $total + ($item['price'] * $item['quantity']);
    }, 0);
    $orderDate = date('Y-m-d H:i:s');
    $deliveryAddress = $_POST['delivery_address']; // Get delivery address from form

    // Validate delivery address
    if (empty($deliveryAddress)) {
        echo "<script>alert('Please enter a delivery address.');</script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    try {
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, order_date, total_amount, delivery_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $name, $email, $orderDate, $totalAmount, $deliveryAddress);
        
        if ($stmt->execute()) {
            $orderId = $conn->insert_id;
            $stmt->close();

            if ($paymentMethod === 'COD') {
                echo "<script>alert('Order placed successfully! Pay on delivery. Total: ₹$totalAmount');</script>";
                $_SESSION['cart'] = []; // Clear cart
            } elseif ($paymentMethod === 'Online') {
                // Create Stripe checkout session
                $lineItems = array_map(function ($item) {
                    return [
                        'price_data' => [
                            'currency' => 'inr',
                            'product_data' => [
                                'name' => $item['name'],
                            ],
                            'unit_amount' => $item['price'] * 100, // Stripe requires amount in cents
                        ],
                        'quantity' => $item['quantity'],
                    ];
                }, $_SESSION['cart']);

                $checkoutSession = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'metadata' => [
                        'order_id' => strval($orderId) // Pass order ID dynamically
                    ],
                    'success_url' => 'http://localhost/phpinsert/success1.php?session_id={CHECKOUT_SESSION_ID}&order_id=' . $orderId,
                    'cancel_url' => 'http://yourdomain.com/cancel.php',
                ]);

                // Redirect to Stripe Checkout
                header('Location: ' . $checkoutSession->url);
                exit();
            }
        } else {
            throw new Exception("Error inserting order: " . $stmt->error);
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// Fetch logged-in customer details
$name = $_SESSION['name'] ?? 'Guest';
$email = $_SESSION['email'] ?? 'guest@example.com';

// Fetch customer profile from database
$stmt = $conn->prepare("SELECT * FROM customers WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$customerResult = $stmt->get_result();
$customer = $customerResult->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName = $_POST['name'];
    $newEmail = $_POST['email'];

    // Validate input
    if (empty($newName) || empty($newEmail)) {
        $_SESSION['error'] = 'Please fill in all fields';
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }

    // Update database
    $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ? WHERE email = ?");
    $stmt->bind_param("sss", $newName, $newEmail, $email);
    if ($stmt->execute()) {
        $_SESSION['name'] = $newName;
        $_SESSION['email'] = $newEmail;
        $name = $newName;
        $email = $newEmail;
        $_SESSION['success'] = 'Profile updated successfully!';
    } else {
        $_SESSION['error'] = 'Error updating profile: '.$stmt->error;
    }
    $stmt->close();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch previous addresses
$previousAddresses = [];
$addressStmt = $conn->prepare("SELECT DISTINCT delivery_address FROM orders WHERE customer_email = ? ORDER BY order_date DESC");
$addressStmt->bind_param("s", $email);
$addressStmt->execute();
$addressResult = $addressStmt->get_result();
while ($row = $addressResult->fetch_assoc()) {
    $previousAddresses[] = $row['delivery_address'];
}
$addressStmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Add this in the head section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  
    <style>
        /* Add these new styles */
        :root {
            --primary-color: #ff5a5f;
            --secondary-color: #00a699;
            --dark-color: #2d333f;
            --light-color: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-color);
            position: relative;
            min-height: 100vh;
        }

        /* Enhanced Header */
        .header {
            background: linear-gradient(135deg, var(--dark-color) 0%, #1a1a1a 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: rotate(15deg);
        }

        .nav-controls {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .search-bar {
            flex: 1;
            max-width: 600px;
            margin: 0 2rem;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 0.8rem 2.5rem;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            background: rgba(255,255,255,0.9);
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            box-shadow: 0 0 15px rgba(255,90,95,0.3);
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--dark-color);
        }

        .cart-icon {
            position: relative;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            background: rgba(255,255,255,0.1);
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 50%;
            font-size: 0.8rem;
        }

        /* Modern Product Grid */
        .product-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.12);
        }

        .product-badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--primary-color);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .product-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-bottom: 2px solid var(--light-color);
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-details {
            padding: 1.5rem;
            position: relative;
        }

        .product-title {
            font-size: 1.1rem;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            color: var(--primary-color);
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .add-to-cart-btn {
            width: 100%;
            padding: 0.8rem;
            background: linear-gradient(135deg, var(--primary-color) 0%, #ff3b3f 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .add-to-cart-btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        /* Enhanced Cart Modal */
        .cart-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            width: 90%;
            max-width: 600px;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            z-index: 1001;
            max-height: 100vh;
            overflow: hidden;
        }

        .cart-header {
            padding: 1.5rem;
            background: var(--dark-color);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-items {
            padding: 1.5rem;
            max-height: 50vh;
            overflow-y: auto;
        }

        .cart-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .cart-item-price {
            color: var(--primary-color);
            font-weight: 700;
        }

        .cart-total {
            padding: 1.5rem;
            background: #f9f9f9;
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--secondary-color);
            border: none;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkout-btn:hover {
            opacity: 0.9;
        }
        .delivery-address {
    padding: 1rem 1.5rem;
}

.delivery-address textarea {
    width: 100%;
    padding: 0.8rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-top: 0.5rem;
    resize: vertical;
}

        /* Overlay and Animations */
        .cart-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            backdrop-filter: blur(3px);
        }

        @keyframes slideIn {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            background: var(--secondary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 8px;
            display: none;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .generaterecipt-btn {
            width: 100%;
            padding: 1rem;
            background: var(--secondary-color);
            border: none;
            color: pink;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }


        /* Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-wrap: wrap;
                gap: 1rem;
                padding: 1rem;
            }

            .search-bar {
                order: 3;
                width: 100%;
                margin: 0;
            }

            .product-container {
                grid-template-columns: 1fr;
                padding: 1rem;
            }

            .cart-modal {
                width: 95%;
            }
            footer {
            background-color: #232f3e;
            color: white;
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            font-size: 14px;
        }
        }
        .user-info {
            position: relative;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        .user-info:hover {
            background: rgba(255,255,255,0.1);
        }
        .edit-profile-btn {
            margin-left: 10px;
            background: none;
            border: none;
            color: #fff;
            cursor: pointer;
        }
        .profile-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 10px;
            z-index: 1002;
            width: 90%;
            max-width: 400px;
        }
        .address-selector {
            margin-bottom: 1rem;
        }
        .address-selector select {
            width: 100%;
            padding: 0.5rem;
            border-radius: 5px;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <!-- Updated Header Section -->
    <header class="header">
        <div class="logo-container" onclick="toggleProfileModal()">
            <img src="assets/img/admin1.png" alt="CUSTOMER" class="logo">
 <div class="user-info">
                <span class="username"><?php echo htmlspecialchars($name); ?></span>
                <span class="email"><?php echo htmlspecialchars($email); ?></span>
                <button class="edit-profile-btn" onclick="toggleProfileModal()"><i class="fas fa-edit"></i></button>
            </div>
        </div>
        <div class="search-bar">
            <input type="text" id="search" class="search-input" placeholder="Search products..." onkeyup="filterProducts()">
        </div>
        <div class="nav-controls">
            <div class="cart-icon" onclick="toggleCartModal()">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-count"><?php echo count($_SESSION['cart']); ?></span>
            </div>
        </div>
    </header>

    <!-- Profile Modal -->
    <div class="profile-modal" id="profileModal">
        <h3>Edit Profile</h3>
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
            <button type="submit" name="update_profile">Update Profile</button>
            <button type="button" onclick="toggleProfileModal()">Cancel</button>
        </form>
    </div>
    

    <!-- Product Grid -->
    <main class="product-container" id="productContainer">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <article class="product-card" data-name="<?php echo strtolower($row['name']); ?>">
                    <div class="product-badge"></div>
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-image">
                    <div class="product-details">
                        <h3 class="product-title"><?php echo $row['name']; ?></h3>
                        <p class="product-description"><?php echo $row['description']; ?></p>
                        <div class="product-price">₹<?php echo number_format($row['price'], 2); ?></div>
                        <form method="POST" onsubmit="showToast('Added to cart!')">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo $row['name']; ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <div class="quantity-control">
                                <button type="button" class="quantity-btn" onclick="adjustQuantity(this, -1)">-</button>
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['stock']; ?>" class="quantity-input">
                                <button type="button" class="quantity-btn" onclick="adjustQuantity(this, 1)">+</button>
                            </div>
                            <button type="submit" name="add_to_cart" class="add-to-cart-btn">
                                <i class="fas fa-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h2>No Products Found</h2>
            </div>
        <?php endif; ?>
    </main>

    <!-- Enhanced Cart Modal -->
    <div class="cart-overlay" id="cartOverlay" onclick="closeCartModal()"></div>
    <div class="cart-modal" id="cartModal">
        <div class="cart-header">
            <h3>Your Shopping Cart</h3>
            <button class="close-btn" onclick="closeCartModal()">&times;</button>
        </div>
        <div class="cart-items">
            <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo ($item['id']); ?>" class="cart-item-image" alt="<?php echo $item['name']; ?>">
                        <div class="cart-item-details">
                            <h4 class="cart-item-title"><?php echo $item['name']; ?></h4>
                            <div class="cart-item-info">
                                <span class="cart-item-quantity">Qty: <?php echo $item['quantity']; ?></span>
                                <span class="cart-item-price">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                            <button class="remove-item" onclick="removeCartItem(<?php echo $index; ?>)">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <p>Your cart is empty</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="cart-total">
            <span>Total:</span>
            <span>₹<?php echo number_format(array_reduce($_SESSION['cart'], function($total, $item) {
                return $total + ($item['price'] * $item['quantity']);
            }, 0), 2); ?></span>
        </div>
        <?php if (!empty($_SESSION['cart'])): ?>
            <form method="POST" class="checkout-form" onsubmit="return validateCheckout()">
                <div class="delivery-address">
                    <h4>Delivery Address</h4>
                    <select id="previousAddresses" onchange="fillAddress(this.value)">
                        <option value="">Select a previous address</option>
                        <?php foreach ($previousAddresses as $address): ?>
                            <option value="<?php echo htmlspecialchars($address); ?>"><?php echo htmlspecialchars($address); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <textarea name="delivery_address" id="deliveryAddress" placeholder="Enter your full delivery address"></textarea>
                </div>
                <div class="payment-methods">
                    <label>
                        <input type="radio" name="payment_method" value="COD" checked>
                        <i class="fas fa-money-bill-wave"></i> Cash on Delivery
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="Online">
                        <i class="fas fa-credit-card"></i> Online Payment
                    </label>
                </div>
                <button type="submit" name="confirm_payment" class="checkout-btn">
                    Proceed to Checkout
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="toast" id="toast"></div>

    <script>
        // Cart Modal Controls
        function toggleCartModal() {
            document.getElementById('cartModal').style.display = 'block';
            document.getElementById('cartOverlay').style.display = 'block';
        }

        function closeCartModal() {
            document.getElementById('cartModal').style.display = 'none';
            document.getElementById('cartOverlay').style.display = 'none';
        }

        // Profile Modal Controls
        function toggleProfileModal() {
            const profileModal = document.getElementById('profileModal');
            profileModal.style.display = profileModal.style.display === 'block' ? 'none' : 'block';
        }

        // Fill address in textarea
        function fillAddress(address) {
            document.getElementById('deliveryAddress').value = address;
        }

        // Toast Notification
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        // Quantity Controls
        function adjustQuantity(button, delta) {
            const input = button.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value) + delta;
            value = Math.max(input.min, Math.min(input.max, value));
            input.value = value;
        }

        // Dynamic Search
        function filterProducts() {
            let searchInput = document.getElementById('search').value.toLowerCase().trim();
            let products = document.querySelectorAll('.product-card');

            products.forEach(product => {
                let productName = product.querySelector('.product-title').textContent.toLowerCase();
                let productDesc = product.querySelector('.product-description').textContent.toLowerCase();

                if (productName.includes(searchInput) || productDesc.includes(searchInput)) {
                    product.style.display = "block"; // Show matching products
                } else {
                    product.style.display = "none"; // Hide non-matching products
                }
            });
        }
        function validateCheckout() {
    const deliveryAddress = document.getElementById('deliveryAddress').value.trim();

    if (!deliveryAddress) {
        alert('Please enter a delivery address.');
        return false; // Prevent form submission
    }

    // If everything is valid, allow the form to be submitted
    return true;
}

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeCartModal();
        });
    </script>
</body>
</html>