<?php
require_once 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']); // Email or Mobile
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = ? OR mobile = ?");
    $stmt->execute([$identifier, $identifier]);
    $users = $stmt->fetchAll();

    $authenticated = false;
    foreach ($users as $user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $authenticated = true;
            break;
        }
    }

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
            <h2>Welcome Back</h2>
            <p style="color: var(--text-muted);">Login to manage your recharges</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="filter-group">
                <label>Email or Mobile Number</label>
                <input type="text" name="identifier" placeholder="Enter your email or mobile" required>
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
