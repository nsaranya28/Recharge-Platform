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
    <title>Login - SmartRecharge</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0b0f19;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 0.4) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(263, 40%, 25%, 0.3) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 0.3) 0, transparent 50%);
            min-height: 100vh;
            color: #f8fafc;
        }

        /* Set header layout styles specifically for login/register to align with new dark theme */
        .navbar {
            background: rgba(11, 15, 25, 0.7) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .logo-text {
            color: #f8fafc !important;
        }
        .nav-link {
            color: #94a3b8 !important;
        }
        .nav-link:hover, .nav-link.active {
            color: #8b5cf6 !important;
            background: rgba(139, 92, 246, 0.1) !important;
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
            opacity: 0.7;
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
            background: linear-gradient(135deg, #7c3aed, #db2777);
        }

        .blob-2 {
            width: 280px;
            height: 280px;
            bottom: 5%;
            right: 10%;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            animation-delay: -5s;
            animation-duration: 25s;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(40px, -60px) scale(1.15); }
            100% { transform: translate(-20px, 30px) scale(0.9); }
        }

        .auth-card {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5),
                        inset 0 1px 1px rgba(255, 255, 255, 0.1);
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
            background: linear-gradient(135deg, #ffffff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .auth-header p {
            color: #94a3b8;
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Form elements */
        .input-group-custom {
            position: relative;
            margin-bottom: 1.75rem;
        }

        .input-group-custom label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #94a3b8;
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
            color: #64748b;
            pointer-events: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-custom {
            width: 100%;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 1rem 1.25rem 1rem 3.25rem;
            color: #f8fafc;
            font-size: 0.95rem;
            font-family: inherit;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-custom::placeholder {
            color: #475569;
        }

        .input-custom:focus {
            border-color: #8b5cf6;
            background: rgba(15, 23, 42, 0.6);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15),
                        0 4px 20px -2px rgba(139, 92, 246, 0.2);
        }

        .input-custom:focus + .input-icon-left {
            color: #8b5cf6;
        }

        .password-toggle-btn {
            position: absolute;
            right: 1.25rem;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .password-toggle-btn:hover {
            color: #f8fafc;
        }

        .btn-auth-gradient {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #7c3aed, #ec4899);
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.4);
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-auth-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(124, 58, 237, 0.6),
                        0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .demo-credentials-card {
            background: rgba(124, 58, 237, 0.08);
            border: 1px dashed rgba(139, 92, 246, 0.3);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 2rem;
            font-size: 0.85rem;
            line-height: 1.5;
            color: #cbd5e1;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .demo-credentials-card strong {
            color: #c084fc;
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
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .auth-alert-success {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: #a7f3d0;
        }

        .auth-footer-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #94a3b8;
        }

        .auth-footer-text a {
            color: #a78bfa;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .auth-footer-text a:hover {
            color: #c084fc;
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
            <h2>Welcome Back</h2>
            <p>Login to manage your recharges & bills</p>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['auth_message'])): ?>
            <div class="auth-alert auth-alert-<?php echo $_SESSION['auth_message_type'] === 'success' ? 'success' : 'danger'; ?>">
                <i data-lucide="<?php echo $_SESSION['auth_message_type'] === 'success' ? 'check-circle' : 'alert-circle'; ?>" style="flex-shrink: 0; width: 20px; height: 20px;"></i>
                <div>
                    <?php 
                    echo $_SESSION['auth_message']; 
                    unset($_SESSION['auth_message']);
                    unset($_SESSION['auth_message_type']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="auth-alert auth-alert-danger">
                <i data-lucide="alert-circle" style="flex-shrink: 0; width: 20px; height: 20px;"></i>
                <div><?php echo $error; ?></div>
            </div>
        <?php endif; ?>

        <!-- Demo credentials helper -->
        <div class="demo-credentials-card">
            <div>💡 <strong>Demo Credentials:</strong></div>
            <div>Email: <strong style="user-select: all;">test@example.com</strong></div>
            <div>Password: <strong style="user-select: all;">123456</strong></div>
        </div>

        <form method="POST">
            <div class="input-group-custom">
                <label>Email or Mobile Number</label>
                <div class="input-wrapper">
                    <input type="text" name="identifier" class="input-custom" value="<?php echo htmlspecialchars($prefilled_email); ?>" placeholder="Enter email or mobile" required autocomplete="username">
                    <span class="input-icon-left">
                        <i data-lucide="user" style="width: 18px; height: 18px;"></i>
                    </span>
                </div>
            </div>
            
            <div class="input-group-custom">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password-field" name="password" class="input-custom" placeholder="••••••••" required autocomplete="current-password">
                    <span class="input-icon-left">
                        <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                    </span>
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()">
                        <i id="password-toggle-icon" data-lucide="eye" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-auth-gradient">
                <span>Login</span>
                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
            </button>
            
            <p class="auth-footer-text">
                Don't have an account? <a href="register.php">Register</a>
            </p>
        </form>
    </div>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // Password visibility toggle
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('password-field');
        const toggleIcon = document.getElementById('password-toggle-icon');
        
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
