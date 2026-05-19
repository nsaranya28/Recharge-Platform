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

            // Register user and redirect to login (allowing same email multiple times)
            $hashed_password = password_hash($form_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $mobile, $hashed_password);
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                $_SESSION['auth_message'] = "Registration successful. Please login.";
                $_SESSION['auth_message_type'] = "success";
                $_SESSION['registered_email'] = $email;
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
                $stmt->close();
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
    <title>Register - SmartRecharge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(245, 243, 255, 0.9) 0, transparent 50%), 
                radial-gradient(at 50% 0%, rgba(237, 233, 254, 0.8) 0, transparent 50%), 
                radial-gradient(at 100% 0%, rgba(243, 232, 255, 0.9) 0, transparent 50%);
            min-height: 100vh;
            color: #1e293b;
        }

        /* Set header layout styles specifically for login/register to align with white/violet theme */
        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #edf2f7 !important;
        }
        .logo-text {
            color: #6d28d9 !important;
        }
        .nav-link {
            color: #4b5563 !important;
        }
        .nav-link:hover, .nav-link.active {
            color: #6d28d9 !important;
            background: rgba(109, 40, 217, 0.05) !important;
        }

        .auth-page-wrapper {
            position: relative;
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 1rem;
            z-index: 1;
        }

        /* Glowing background blobs */
        .auth-bg-blobs {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 600px;
            height: 100%;
            max-height: 600px;
            pointer-events: none;
            z-index: -1;
            filter: blur(100px);
            opacity: 0.5;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: floatBlob 20s infinite alternate;
        }

        .blob-1 {
            width: 320px;
            height: 320px;
            top: 5%;
            left: 10%;
            background: linear-gradient(135deg, #a78bfa, #c084fc);
        }

        .blob-2 {
            width: 280px;
            height: 280px;
            bottom: 5%;
            right: 10%;
            background: linear-gradient(135deg, #c084fc, #818cf8);
            animation-delay: -5s;
            animation-duration: 25s;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, -60px) scale(1.15); }
            100% { transform: translate(-20px, 30px) scale(0.9); }
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(109, 40, 217, 0.08);
            border-radius: 28px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 40px -15px rgba(109, 40, 217, 0.1),
                        inset 0 1px 0 rgba(255, 255, 255, 0.6);
            position: relative;
            z-index: 10;
            animation: cardFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .auth-header h2 {
            font-size: 2.25rem;
            font-weight: 800;
            background: linear-gradient(135deg, #4c1d95, #6d28d9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .auth-header p {
            color: #6b7280;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Form elements */
        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-custom label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #4c1d95;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 1.25rem;
            color: #8b5cf6;
            pointer-events: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-custom {
            width: 100%;
            background: #ffffff;
            border: 1px solid #ddd6fe;
            border-radius: 14px;
            padding: 1rem 1.25rem 1rem 3.25rem;
            color: #1e293b;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-custom::placeholder {
            color: #94a3b8;
        }

        .input-custom:focus {
            border-color: #6d28d9;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(109, 40, 217, 0.1),
                        0 4px 20px -2px rgba(109, 40, 217, 0.08);
        }

        .input-custom:focus + .input-icon-left {
            color: #6d28d9;
        }

        .password-toggle-btn {
            position: absolute;
            right: 1.25rem;
            background: none;
            border: none;
            color: #8b5cf6;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: #6d28d9;
        }

        .btn-auth-gradient {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6d28d9, #8b5cf6);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -5px rgba(109, 40, 217, 0.3);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-auth-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(109, 40, 217, 0.4),
                        0 0 0 4px rgba(109, 40, 217, 0.1);
        }

        /* Alert overrides */
        .auth-alert {
            padding: 1rem;
            border-radius: 14px;
            margin-bottom: 1.75rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid transparent;
        }

        .auth-alert-danger {
            background: #fef2f2;
            border-color: #fee2e2;
            color: #ef4444;
        }

        .auth-footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .auth-footer-text a {
            color: #6d28d9;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-footer-text a:hover {
            color: #5b21b6;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="auth-page-wrapper">
    <div class="auth-bg-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="auth-card">
        <div class="auth-header">
            <h2>Create Account</h2>
            <p>Join SmartRecharge today</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-alert auth-alert-danger">
                <i data-lucide="alert-circle" style="flex-shrink: 0; width: 20px; height: 20px;"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group-custom">
                <label>Full Name</label>
                <div class="input-wrapper">
                    <input type="text" name="name" class="input-custom" placeholder="John Doe" required autocomplete="name">
                    <span class="input-icon-left">
                        <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                    </span>
                </div>
            </div>

            <div class="input-group-custom">
                <label>Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" class="input-custom" placeholder="john@example.com" required autocomplete="email">
                    <span class="input-icon-left">
                        <i data-lucide="mail" style="width: 18px; height: 18px;"></i>
                    </span>
                </div>
            </div>

            <div class="input-group-custom">
                <label>Mobile Number</label>
                <div class="input-wrapper">
                    <input type="tel" name="mobile" class="input-custom" placeholder="10-digit number" pattern="[0-9]{10}" required autocomplete="tel">
                    <span class="input-icon-left">
                        <i data-lucide="phone" style="width: 18px; height: 18px;"></i>
                    </span>
                </div>
            </div>

            <div class="input-group-custom">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password-field" name="password" class="input-custom" placeholder="••••••••" required autocomplete="new-password">
                    <span class="input-icon-left">
                        <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                    </span>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password-field', 'password-toggle-icon')">
                        <i id="password-toggle-icon" data-lucide="eye" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>

            <div class="input-group-custom">
                <label>Confirm Password</label>
                <div class="input-wrapper">
                    <input type="password" id="confirm-password-field" name="confirm_password" class="input-custom" placeholder="••••••••" required autocomplete="new-password">
                    <span class="input-icon-left">
                        <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
                    </span>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('confirm-password-field', 'confirm-toggle-icon')">
                        <i id="confirm-toggle-icon" data-lucide="eye" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth-gradient">
                <span>Register Now</span>
                <i data-lucide="user-plus" style="width: 18px; height: 18px;"></i>
            </button>
            
            <p class="auth-footer-text">
                Already have an account? <a href="login.php">Login</a>
            </p>
        </form>
    </div>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // Password visibility toggle
    function togglePasswordVisibility(fieldId, iconId) {
        const passwordField = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.setAttribute('data-lucide', 'eye-off');
        } else {
            passwordField.type = 'password';
            toggleIcon.setAttribute('data-lucide', 'eye');
        }
        lucide.createIcons();
    }
</script>
</body>
</html>
