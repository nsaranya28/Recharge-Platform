<?php
session_start();
require_once 'db.php';

// Get parameters
$mobile = $_GET['mobile'] ?? '';
$plan_id = $_GET['plan_id'] ?? '';

// Fetch plan details if needed
$plan = null;
if ($plan_id) {
    $stmt = $pdo->prepare('SELECT * FROM plans WHERE id = ?');
    $stmt->execute([$plan_id]);
    $plan = $stmt->fetch();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input_otp = $_POST['otp'] ?? '';
    if (isset($_SESSION['otp']) && $input_otp === $_SESSION['otp'] && $mobile === ($_SESSION['otp_mobile'] ?? '')) {
        // OTP verified, proceed to success page
        unset($_SESSION['otp'], $_SESSION['otp_mobile'], $_SESSION['otp_plan_id']);
        $price = $plan['price'] ?? '';
        header("Location: success.php?mobile=" . urlencode($mobile) . "&price=" . urlencode($price));
        exit();
    } else {
        $error = 'Invalid OTP. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Enter OTP</title>
    <link rel=\"stylesheet\" href=\"style.css\">
    <style>
        .otp-container { max-width: 400px; margin: 5rem auto; padding: 2rem; background: white; border-radius: var(--radius); box-shadow: var(--shadow); }
        .error { color: var(--accent-airtel); background: #fee2e2; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"otp-container\">
            <h2 style=\"color: var(--primary); text-align:center; margin-bottom:1rem;\">OTP Verification</h2>
            <?php if ($error): ?>
                <div class=\"error\"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <p>We have sent an OTP to your mobile number: <strong>+91 <?php echo htmlspecialchars($mobile); ?></strong></p>
            <?php if (isset($_SESSION['otp'])): ?>
                <p><em>For demo purposes, your OTP is: <strong><?php echo htmlspecialchars($_SESSION['otp']); ?></strong></em></p>
            <?php endif; ?>
            <form method=\"POST\">
                <div class=\"filter-group\">
                    <label>Enter OTP</label>
                    <input type=\"text\" name=\"otp\" maxlength=\"6\" pattern=\"[0-9]{6}\" required placeholder=\"6-digit OTP\" />
                </div>
                <button type=\"submit\" class=\"btn-apply\" style=\"margin-top:1rem;\">Verify OTP</button>
            </form>
        </div>
    </div>
</body>
</html>
