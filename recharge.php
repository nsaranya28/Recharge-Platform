<?php
require_once 'db.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$plan_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();

if (!$plan) {
    die("Plan not found!");
}

// Handle form submission
$error = '';

if (isset($_POST['process_recharge'])) {
    $mobile = $_POST['mobile_number'];
    if (strlen($mobile) == 10 && is_numeric($mobile)) {
        // Generate OTP and store in session
        $otp = strval(mt_rand(100000, 999999));
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_mobile'] = $mobile;
        $_SESSION['otp_plan_id'] = $plan['id'];
        // In production, send OTP via SMS gateway. For demo, we will display it on the OTP page.
        // Redirect to OTP verification page
        header("Location: otp.php?mobile=" . $mobile . "&plan_id=" . $plan['id']);
        exit();
    } else {
        $error = "Please enter a valid 10-digit mobile number.";
    }
}
?>
>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Recharge</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .checkout-container {
            max-width: 500px;
            margin: 4rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .plan-summary {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }
        .error-msg {
            color: var(--accent-airtel);
            background: #fee2e2;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="checkout-container">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Recharge Checkout</h2>
        
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="plan-summary">
            <h4 style="color: var(--text-muted); margin-bottom: 0.5rem;">Selected Plan</h4>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong><?php echo $plan['operator']; ?> - ₹<?php echo $plan['price']; ?></strong>
                <span><?php echo $plan['validity']; ?> Days</span>
            </div>
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.5rem;">
                <?php echo $plan['data_per_day']; ?> GB Data/Day
            </p>
        </div>

        <form method="POST">
            <div class="filter-group">
                <label>Mobile Number</label>
                <input type="tel" name="mobile_number" placeholder="Enter 10 digit number" maxlength="10" pattern="[0-9]{10}" title="Please enter a 10-digit mobile number" required oninput="this.value = this.value.replace(/[^0-9]/g, '');">
            </div>

            <div class="filter-group">
                <label>Payment Method</label>
                <select id="payment_method" name="payment_method" onchange="togglePaymentFields()" required>
                    <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                    <option value="card">Credit / Debit Card</option>
                    <option value="netbanking">Net Banking</option>
                </select>
            </div>

            <!-- UPI Details Section -->
            <div id="upi_fields" style="display: block; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div class="filter-group">
                    <label>Enter UPI ID</label>
                    <input type="text" placeholder="example@ybl, mobile@paytm" pattern="[a-zA-Z0-9.\-_]{2,256}@[a-zA-Z]{2,64}">
                </div>
            </div>

            <!-- Card Details Section -->
            <div id="card_fields" style="display: none; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div class="filter-group">
                    <label>Card Number</label>
                    <input type="text" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" oninput="this.value = this.value.replace(/[^0-9 ]/g, '');">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="filter-group">
                        <label>Expiry (MM/YY)</label>
                        <input type="text" placeholder="MM/YY" maxlength="5" oninput="this.value = this.value.replace(/[^0-9\/]/g, '');">
                    </div>
                    <div class="filter-group">
                        <label>CVV</label>
                        <input type="password" placeholder="***" maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
            </div>

            <!-- Net Banking Section -->
            <div id="bank_fields" style="display: none; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div class="filter-group">
                    <label>Select Bank</label>
                    <select>
                        <option>State Bank of India</option>
                        <option>HDFC Bank</option>
                        <option>ICICI Bank</option>
                        <option>Axis Bank</option>
                        <option>Other Bank</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="process_recharge" class="btn-apply" style="margin-top: 1rem; font-size: 1.1rem; height: 50px;">
                Proceed to Pay ₹<?php echo $plan['price']; ?>
            </button>
            
            <a href="index.php" style="display: block; text-align: center; margin-top: 1.5rem; color: var(--text-muted); text-decoration: none;">
                Cancel and go back
            </a>
        </form>
    </div>
</div>

<script>
function togglePaymentFields() {
    const method = document.getElementById('payment_method').value;
    const cardFields = document.getElementById('card_fields');
    const bankFields = document.getElementById('bank_fields');
    const upiFields = document.getElementById('upi_fields');

    if (cardFields) cardFields.style.display = 'none';
    if (bankFields) bankFields.style.display = 'none';
    if (upiFields) upiFields.style.display = 'none';

    if (method === 'card' && cardFields) {
        cardFields.style.display = 'block';
    } else if (method === 'netbanking' && bankFields) {
        bankFields.style.display = 'block';
    } else if (method === 'upi' && upiFields) {
        upiFields.style.display = 'block';
    }
}

// Ensure the correct fields are shown on page load
window.addEventListener('DOMContentLoaded', togglePaymentFields);
</script>

</body>
</html>
