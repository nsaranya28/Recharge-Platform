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
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if mobile already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ?");
        $stmt->execute([$mobile]);
        if ($stmt->fetch()) {
            $error = "This Mobile number is already registered!";
        } else {
            // Check if email already exists (since email has a UNIQUE constraint in users table)
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "This Email address is already registered!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $mobile, $hashed_password])) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['user_name'] = $name;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
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
        .error-msg {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: center;
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
            <div class="error-msg"><?php echo $error; ?></div>
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
