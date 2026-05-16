<?php
ob_start();
require_once 'db.php';
session_start();

// Get Plan ID from URL or POST data
$plan_id = $_GET['id'] ?? ($_POST['plan_id'] ?? null);

if (!$plan_id) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
$stmt->execute([$plan_id]);
$plan = $stmt->fetch();

if (!$plan) {
    die("Plan not found!");
}

// Handle form submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = trim($_POST['mobile_number'] ?? '');
    
    if (strlen($mobile) === 10 && is_numeric($mobile)) {
        $price = $plan['price'];
        $operator = $plan['operator'];
        $validity = $plan['validity'];
        $data = $plan['data_per_day'];
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
        $user_id = $_SESSION['user_id'];

        // --- DUPLICATE CHECK ---
        // Prevent duplicate recharges for the same number and plan within the last 1 minute
        $checkStmt = $pdo->prepare("SELECT id FROM recharge_history WHERE mobile_number = ? AND plan_id = ? AND recharge_date > (NOW() - INTERVAL 1 MINUTE)");
        $checkStmt->execute([$mobile, $plan_id]);
        if ($checkStmt->fetch()) {
            $error = "Duplicate request detected. Please wait a moment before trying again.";
        } else {
            // Calculate Expiry Date
            $expiry_date = date('Y-m-d', strtotime("+$validity days"));

            try {
                $pdo->beginTransaction();

                // 1. Insert into recharge_history
                $stmt = $pdo->prepare("INSERT INTO recharge_history (user_id, mobile_number, operator, plan_id, amount, expiry_date) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $mobile, $operator, $plan_id, $price, $expiry_date]);
                $recharge_id = $pdo->lastInsertId();

                // 2. Schedule Reminders (3 days, 1 day, and same day)
                $reminders = [
                    ['3_days_before', date('Y-m-d', strtotime("$expiry_date -3 days"))],
                    ['1_day_before', date('Y-m-d', strtotime("$expiry_date -1 days"))],
                    ['on_expiry', $expiry_date]
                ];

                $stmt = $pdo->prepare("INSERT INTO reminders (recharge_id, reminder_type, scheduled_date) VALUES (?, ?, ?)");
                foreach ($reminders as $r) {
                    // Only schedule if the date is in the future
                    if ($r[1] >= date('Y-m-d')) {
                        $stmt->execute([$recharge_id, $r[0], $r[1]]);
                    }
                }

                $pdo->commit();

                // --- Send Notifications (Wrapped in try-catch to avoid breaking success flow) ---
                $userName = $_SESSION['user_name'] ?? 'Customer';
                $txId = strtoupper(substr(md5(time() . $mobile), 0, 10)); // Generate Transaction ID
                $smsStatus = 'pending';

                try {
                    // 1. Send SMS via Fast2SMS
                    if (file_exists('fast2sms_helper.php')) {
                        require_once 'fast2sms_helper.php';
                        $smsResponse = sendFast2SMS($mobile, $price, $operator);
                        if (isset($smsResponse['return']) && $smsResponse['return'] === true) {
                            $smsStatus = 'sent';
                        } else {
                            $smsStatus = 'failed';
                        }
                    }
                } catch (Exception $e) {
                    $smsStatus = 'error';
                }

                try {
                    // 2. Send WhatsApp Notification via Twilio
                    if (file_exists('twilio_helper.php')) {
                        require_once 'twilio_helper.php';
                        sendWhatsAppRechargeSuccess($mobile, $userName, $operator, $price, $validity, $txId);
                    }
                } catch (Exception $e) {}

                header("Location: success.php?mobile=" . urlencode($mobile) . "&price=" . urlencode($price) . "&op=" . urlencode($operator) . "&val=" . urlencode($validity) . "&dat=" . urlencode($data) . "&txid=" . urlencode($txId) . "&sms_status=" . $smsStatus);
                exit();
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = "Database Error: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please enter a valid 10-digit mobile number.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Recharge</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
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
<?php include 'header.php'; ?>

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

        <form method="POST" action="recharge.php?id=<?php echo $plan['id']; ?>">
            <input type="hidden" name="plan_id" value="<?php echo htmlspecialchars($plan['id']); ?>">
            <input type="hidden" name="process_recharge" value="1">
<div class="filter-group">
    <label>Mobile Number</label>
    <div style="display: flex; gap: 0.5rem;">
        <input type="tel" id="mobile_number" name="mobile_number" placeholder="Enter 10 digit number" maxlength="10" pattern="[0-9]{10}" title="Please enter a 10-digit mobile number" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" style="flex: 1;">
        <button type="button" onclick="toggleScanner()" class="btn-apply" style="width: auto; padding: 0.5rem 1rem; background: #6366f1;">📷 Scan</button>
    </div>
</div>

<!-- QR Scanner Container -->
<div id="qr-reader" style="display: none; margin-bottom: 1.5rem; border: 2px dashed var(--primary); border-radius: 8px; overflow: hidden;"></div>

<div class="filter-group">
    <label>Payment Method</label>
    <select id="payment_method" name="payment_method" onchange="togglePaymentFields()" required>
        <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
        <option value="qr">Scan UPI QR Code</option>
        <option value="card">Credit / Debit Card</option>
        <option value="netbanking">Net Banking</option>
    </select>
</div>

<!-- UPI QR Details Section -->
<div id="qr_payment_fields" style="display: none; border: 1px solid var(--border-color); padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; background: #f9fafb;">
    <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">Scan this QR with any UPI app to pay ₹<?php echo $plan['price']; ?></p>
    <div id="payment-qr" style="display: flex; justify-content: center; margin-bottom: 1rem;">
        <!-- QR Code will be injected here -->
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=upi://pay?pa=smartrecharge@upi%26pn=SmartRecharge%26am=<?php echo $plan['price']; ?>%26cu=INR%26tn=Recharge" alt="UPI QR">
    </div>
    <p style="font-size: 0.75rem; color: #10b981;">✔️ Secure encrypted payment</p>
</div>

<!-- UPI ID Section -->
<div id="upi_fields" style="display: block; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
    <div class="filter-group">
        <label>Enter UPI ID</label>
        <input type="text" name="upi_id" placeholder="example@ybl, mobile@paytm">
    </div>
</div>

            <!-- Card Details Section -->
            <div id="card_fields" style="display: none; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div class="filter-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" oninput="this.value = this.value.replace(/[^0-9 ]/g, '');">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="filter-group">
                        <label>Expiry (MM/YY)</label>
                        <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5" oninput="this.value = this.value.replace(/[^0-9\/]/g, '');">
                    </div>
                    <div class="filter-group">
                        <label>CVV</label>
                        <input type="password" name="card_cvv" placeholder="***" maxlength="3" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                    </div>
                </div>
            </div>

            <!-- Net Banking Section -->
            <div id="bank_fields" style="display: none; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <div class="filter-group">
                    <label>Select Bank</label>
                    <select name="bank_name">
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
    const qrFields = document.getElementById('qr_payment_fields');

    if (cardFields) cardFields.style.display = 'none';
    if (bankFields) bankFields.style.display = 'none';
    if (upiFields) upiFields.style.display = 'none';
    if (qrFields) qrFields.style.display = 'none';

    if (method === 'card' && cardFields) {
        cardFields.style.display = 'block';
    } else if (method === 'netbanking' && bankFields) {
        bankFields.style.display = 'block';
    } else if (method === 'upi' && upiFields) {
        upiFields.style.display = 'block';
    } else if (method === 'qr' && qrFields) {
        qrFields.style.display = 'block';
    }
}

let html5QrCode;

function toggleScanner() {
    const scannerDiv = document.getElementById('qr-reader');
    if (scannerDiv.style.display === 'none') {
        scannerDiv.style.display = 'block';
        startScanner();
    } else {
        scannerDiv.style.display = 'none';
        if (html5QrCode) html5QrCode.stop();
    }
}

function startScanner() {
    html5QrCode = new Html5Qrcode("qr-reader");
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        // Look for a 10 digit number in the QR
        const match = decodedText.match(/[0-9]{10}/);
        if (match) {
            document.getElementById('mobile_number').value = match[0];
            toggleScanner(); // Close scanner
            alert("Mobile Number Scanned: " + match[0]);
        }
    };
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
}

// Ensure the correct fields are shown on page load
window.addEventListener('DOMContentLoaded', togglePaymentFields);
</script>

</body>
</html>
