<?php
require_once 'db.php';
session_start();

// Mock login for the sample user (id=1)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = 'Test User';
}

$user_id = $_SESSION['user_id'];

// Fetch Recharge History with Plan details
$stmt = $pdo->prepare("
    SELECT rh.*, p.data_per_day, p.validity as plan_validity 
    FROM recharge_history rh 
    LEFT JOIN plans p ON rh.plan_id = p.id 
    WHERE rh.user_id = ? 
    ORDER BY rh.recharge_date DESC
");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

// Fetch Upcoming Expiries
$stmt = $pdo->prepare("
    SELECT rh.*, DATEDIFF(rh.expiry_date, CURDATE()) as days_left 
    FROM recharge_history rh 
    WHERE rh.user_id = ? AND rh.expiry_date >= CURDATE() 
    ORDER BY rh.expiry_date ASC
");
$stmt->execute([$user_id]);
$upcoming = $stmt->fetchAll();

// Fetch Notification History
$stmt = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY sent_at DESC LIMIT 5
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge Dashboard - Reminder System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .reminder-card {
            border-left: 5px solid var(--primary);
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-urgent { background: #fee2e2; color: #ef4444; }
        .status-warning { background: #fef3c7; color: #d97706; }
        .status-safe { background: #dcfce7; color: #16a34a; }
        
        .nav-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1rem;
        }
        .nav-tabs a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
        }
        .nav-tabs a.active {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>Manage your recharges and set automated reminders</p>
    </header>

    <div class="nav-tabs">
        <a href="index.php">Plan Browser</a>
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="#settings">Notification Settings</a>
    </div>

    <div class="dashboard-grid">
        <!-- Main Content: History & Upcoming -->
        <div class="main-content">
            <div class="stat-card">
                <h3>Upcoming Expiries</h3>
                <div style="margin-top: 1.5rem;">
                    <?php if (empty($upcoming)): ?>
                        <p style="color: var(--text-muted);">No active recharges found.</p>
                    <?php else: ?>
                        <?php foreach ($upcoming as $item): ?>
                            <div class="reminder-card">
                                <div>
                                    <strong style="display: block; font-size: 1.1rem;"><?php echo $item['operator']; ?> - <?php echo $item['mobile_number']; ?></strong>
                                    <span style="color: var(--text-muted); font-size: 0.875rem;">Expiry: <?php echo date('d M, Y', strtotime($item['expiry_date'])); ?></span>
                                </div>
                                <div style="text-align: right;">
                                    <?php 
                                        $class = 'status-safe';
                                        if ($item['days_left'] <= 1) $class = 'status-urgent';
                                        elseif ($item['days_left'] <= 3) $class = 'status-warning';
                                    ?>
                                    <span class="status-badge <?php echo $class; ?>">
                                        <?php echo $item['days_left']; ?> Days Left
                                    </span>
                                    <a href="recharge.php?id=<?php echo $item['plan_id']; ?>" style="display: block; margin-top: 0.5rem; font-size: 0.875rem; color: var(--primary);">Renew Now</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card">
                <h3>Recharge History</h3>
                <table class="comparison-table" style="margin-top: 1rem;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mobile</th>
                            <th>Plan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $row): ?>
                            <tr>
                                <td><?php echo date('d M', strtotime($row['recharge_date'])); ?></td>
                                <td><?php echo $row['mobile_number']; ?></td>
                                <td>₹<?php echo $row['amount']; ?></td>
                                <td><span class="status-badge status-safe"><?php echo $row['status']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($history)): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 2rem;">No history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar: Notifications & AI Suggestions -->
        <aside>
            <div class="stat-card">
                <h3>Recent Alerts</h3>
                <div style="margin-top: 1rem;">
                    <?php if (empty($notifications)): ?>
                        <p style="color: var(--text-muted); font-size: 0.875rem;">No recent notifications.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div style="padding: 0.75rem 0; border-bottom: 1px solid #eee;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted);">
                                    <span><?php echo $n['type']; ?></span>
                                    <span><?php echo date('H:i', strtotime($n['sent_at'])); ?></span>
                                </div>
                                <p style="font-size: 0.875rem; margin-top: 0.25rem;"><?php echo htmlspecialchars($n['message']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="stat-card" style="background: linear-gradient(135deg, #7c3aed, #db2777); color: white;">
                <h3 style="color: white;">AI Suggestion 🤖</h3>
                <p style="font-size: 0.875rem; margin-top: 0.5rem; opacity: 0.9;">Based on your usage, we recommend:</p>
                <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; margin-top: 1rem; border: 1px solid rgba(255,255,255,0.2);">
                    <strong>Airtel ₹299 Plan</strong>
                    <p style="font-size: 0.75rem;">1.5GB/Day + Unlimited Calls</p>
                    <a href="index.php?operator=Airtel" style="color: white; font-weight: bold; display: block; margin-top: 0.5rem;">View Details →</a>
                </div>
            </div>
        </aside>
    </div>
</div>

</body>
</html>
