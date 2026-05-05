<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge Successful!</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-card {
            max-width: 500px;
            margin: 6rem auto;
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 2rem;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .receipt-info {
            margin: 2rem 0;
            padding: 1.5rem;
            border-top: 1px dashed var(--border-color);
            border-bottom: 1px dashed var(--border-color);
            text-align: left;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="success-card">
        <div class="success-icon">✓</div>
        <h1 style="color: #10b981; margin-bottom: 0.5rem;">Recharge Successful!</h1>
        <p style="color: var(--text-muted);">Your recharge has been processed successfully.</p>

        <div class="receipt-info">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Mobile Number:</span>
                <strong>+91 <?php echo htmlspecialchars($_GET['mobile'] ?? 'XXXXXXXXXX'); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                <span>Amount Paid:</span>
                <strong>₹<?php echo htmlspecialchars($_GET['price'] ?? '0.00'); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Transaction ID:</span>
                <span style="font-family: monospace;"><?php echo strtoupper(bin2hex(random_bytes(6))); ?></span>
            </div>
        </div>

        <a href="index.php" class="btn-apply" style="text-decoration: none; display: inline-block;">
            Back to Home
        </a>
    </div>
</div>

</body>
</html>
