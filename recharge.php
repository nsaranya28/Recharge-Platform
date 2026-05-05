<?php
require_once 'db.php';

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
        // Redirect to success page (simulating a successful payment)
        header("Location: success.php?mobile=" . $mobile . "&price=" . $plan['price']);
        exit();
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
                <input type="text" name="mobile_number" placeholder="Enter 10 digit number" maxlength="10" required>
            </div>

            <div class="filter-group">
                <label>Payment Method</label>
                <select required>
                    <option value="upi">UPI (GPay / PhonePe / Paytm)</option>
                    <option value="card">Credit / Debit Card</option>
                    <option value="netbanking">Net Banking</option>
                </select>
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

</body>
</html>
