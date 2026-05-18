<?php
require_once 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $form_password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($form_password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Initialize MySQLi connection
        $conn = new mysqli($host, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 1. Check if mobile already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->bind_param("s", $mobile);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "This Mobile number is already registered!";
            $stmt->close();
        } else {
            $stmt->close();
            
            // 2. Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                // Email already exists: Do not show error, set session message, and redirect to login
                $stmt->close();
                $conn->close();
                $_SESSION['auth_message'] = "Account already exists. Please login.";
                $_SESSION['auth_message_type'] = "info";
                header("Location: login.php");
                exit();
            } else {
                $stmt->close();

                // 3. Register user and redirect to login
                $hashed_password = password_hash($form_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $mobile, $hashed_password);
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();
                    $_SESSION['auth_message'] = "Registration successful. Please login.";
                    $_SESSION['auth_message_type'] = "success";
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                    $stmt->close();
                }
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Recharge</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 4rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        /* Bootstrap Alert Styling */
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 0.875rem;
            text-align: center;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Create Account</h2>
            <p style="color: var(--text-muted);">Join Smart Recharge today</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="filter-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="John Doe" required>
            </div>
            <div class="filter-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="john@example.com" required>
            </div>
            <div class="filter-group">
                <label>Mobile Number</label>
                <input type="tel" name="mobile" placeholder="10-digit number" pattern="[0-9]{10}" required>
            </div>
            <div class="filter-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="filter-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-apply" style="margin-top: 1rem;">Register Now</button>
            
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600;">Login</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>
