<?php
require_once 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$prefilled_email = '';
if (isset($_SESSION['registered_email'])) {
    $prefilled_email = $_SESSION['registered_email'];
    unset($_SESSION['registered_email']);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']); // Email or Mobile
    $form_password = $_POST['password'];

    // Initialize MySQLi connection
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch user matching either email or mobile
    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ? OR mobile = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    $authenticated = false;
    while ($user = $result->fetch_assoc()) {
        if (password_verify($form_password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $authenticated = true;
            break;
        }
    }
    $stmt->close();
    $conn->close();

    if ($authenticated) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email/mobile or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Recharge</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 6rem auto;
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
            <h2>Welcome Back</h2>
            <p style="color: var(--text-muted);">Login to manage your recharges</p>
        </div>

        <!-- Bootstrap Alert Messages from Redirect or Authentication Errors -->
        <?php if (isset($_SESSION['auth_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['auth_message_type']; ?>">
                <?php 
                echo $_SESSION['auth_message']; 
                unset($_SESSION['auth_message']);
                unset($_SESSION['auth_message_type']);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="filter-group">
                <label>Email or Mobile Number</label>
                <input type="text" name="identifier" value="<?php echo htmlspecialchars($prefilled_email); ?>" placeholder="Enter your email or mobile" required>
            </div>
            <div class="filter-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-apply" style="margin-top: 1rem;">Login</button>
            
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem; color: var(--text-muted);">
                Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600;">Register</a>
            </p>
        </form>
    </div>
</div>
</body>
</html>
