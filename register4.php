<?php
session_start();
include 'database.php';

$error = '';
$success = '';

if (isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);

    if (!preg_match("/^[789]\d{9}$/", $contact)) {
        $error = 'Invalid mobile number! It should be 10 digits and start with 7, 8, or 9.';
    } else {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, contact) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $contact);

        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $success = 'Registration Successful! Redirecting...';
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'productmanage1.php';
                    }, 1500);
                  </script>";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            width: 100%;
            max-width: 480px;
            transition: all 0.3s ease;
        }

        h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fca5a5;
        }

        .alert-success {
            background: #dcfce7;
            color: #16a34a;
            border: 1px solid #86efac;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4b5563;
            font-size: 0.9rem;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.875rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        button:hover {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: #64748b;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: #3b82f6;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>👤 Customer Registration</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required placeholder="john@example.com">
            </div>

            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" id="contact" name="contact" 
                    pattern="^[789]\d{9}$" 
                    title="Mobile number should be 10 digits and start with 7, 8, or 9"
                    placeholder="9876543210"
                    required>
            </div>

            <button type="submit" name="submit">Register Now</button>
        </form>

        <div class="back-link">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
</body>
</html>