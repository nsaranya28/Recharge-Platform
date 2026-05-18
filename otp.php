<?php
require_once 'db.php';
session_start();

// Redirect if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Redirect back to registration if no temporary user session exists
if (!isset($_SESSION['temp_user'])) {
    header("Location: register.php");
    exit();
}

$temp_user = $_SESSION['temp_user'];
$email = $temp_user['email'];

$error = '';
$success = '';

// Handle Resend OTP Request
if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    // Generate new OTP
    $new_otp = mt_rand(100000, 999999);
    
    // Update session
    $_SESSION['temp_user']['otp'] = $new_otp;
    $_SESSION['temp_user']['expires'] = time() + 600; // Reset validity to 10 mins
    
    // Send email
    require_once 'gmail_helper.php';
    $mailResult = sendGmailOTP($email, $new_otp);
    
    if ($mailResult['status'] === 'success') {
        $success = "A new verification code has been sent to <strong>" . htmlspecialchars($email) . "</strong>!";
    } else {
        $error = "Failed to resend OTP. " . $mailResult['message'];
    }
}

// Handle OTP Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp'] ?? '');
    
    // Validate length and digits
    if (strlen($entered_otp) !== 6 || !is_numeric($entered_otp)) {
        $error = "Please enter a valid 6-digit verification code.";
    } else if (time() > $temp_user['expires']) {
        $error = "The verification code has expired. Please request a new one.";
    } else if (intval($entered_otp) !== intval($temp_user['otp'])) {
        $error = "Incorrect verification code. Please try again.";
    } else {
        // OTP matches and is valid! Insert user into database
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([
                $temp_user['name'],
                $temp_user['email'],
                $temp_user['mobile'],
                $temp_user['password']
            ])) {
                // Log the user in
                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['user_name'] = $temp_user['name'];
                
                // Clear temporary registration session
                unset($_SESSION['temp_user']);
                
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed database insertion. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - Smart Recharge</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .otp-container {
            max-width: 450px;
            margin: 5rem auto;
            background: white;
            padding: 3rem 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
        }
        
        .otp-header {
            margin-bottom: 2rem;
        }

        .otp-icon {
            width: 70px;
            height: 70px;
            background: #f5f3ff;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1.5rem;
            border: 2px solid rgba(139, 92, 246, 0.15);
        }

        .otp-input-field {
            letter-spacing: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
            padding: 0.75rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            width: 80%;
            outline: none;
            transition: all 0.3s ease;
            color: var(--text-main);
            margin: 1.5rem auto;
            display: block;
        }

        .otp-input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .error-msg {
            background: #fee2e2;
            color: #ef4444;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: left;
            border-left: 4px solid #ef4444;
            line-height: 1.5;
        }

        .success-msg {
            background: #ecfdf5;
            color: #10b981;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            text-align: left;
            border-left: 4px solid #10b981;
            line-height: 1.5;
        }

        .resend-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .resend-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <div class="otp-container">
        <div class="otp-header">
            <div class="otp-icon">🔒</div>
            <h2>Verify Your Email</h2>
            <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 0.95rem;">
                We have sended a 6-digit verification OTP code to your Gmail address:<br>
                <strong style="color: var(--text-main);"><?php echo htmlspecialchars($email); ?></strong>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="filter-group" style="margin-bottom: 2rem;">
                <label style="font-weight: 600; color: var(--text-main);">Enter 6-Digit OTP Code</label>
                <input type="text" 
                       name="otp" 
                       class="otp-input-field" 
                       placeholder="000000" 
                       maxlength="6" 
                       pattern="[0-9]{6}" 
                       required 
                       autocomplete="off" 
                       oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <button type="submit" class="btn-apply" style="font-size: 1rem; padding: 0.85rem;">
                Verify & Create Account
            </button>
            
            <div style="margin-top: 2rem; font-size: 0.9rem; color: var(--text-muted);">
                Didn't receive the email? <br>
                <a href="otp.php?action=resend" class="resend-link">Resend Code</a>
            </div>

            <div style="margin-top: 1.5rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                <a href="register.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">
                    &larr; Go back and edit details
                </a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
