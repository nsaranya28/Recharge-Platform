<?php
require_once 'db.php';
session_start();

// Handle Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: dashboard.php");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $language = $_POST['language'] ?? 'English';

    try {
        $updateStmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, whatsapp = ?, language = ? WHERE id = ?");
        $updateStmt->execute([$name, $email, $mobile, $whatsapp, $language, $user_id]);
        $_SESSION['user_name'] = $name;
        $success_msg = "Settings updated successfully!";
    } catch (PDOException $e) {
        $error_msg = "Update failed: " . $e->getMessage();
    }
}

// Fetch User Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

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

// Fetch AI Recommendations
require_once 'recommendation_helper.php';
$ai_recs = getAIRecommendations($pdo, $user_id);

// Handle Chatbot Query
$bot_response = '';
if (isset($_POST['chat_query'])) {
    $bot_response = getChatbotResponse($_POST['chat_query'], $pdo);
}
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
            cursor: pointer;
        }
        .nav-tabs a.active {
            background: var(--primary);
            color: white;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .settings-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .settings-form .filter-group { margin-bottom: 0; }
        .full-width { grid-column: 1 / -1; }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .alert-success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
    </style>
</head>
<?php include 'header.php'; ?>

<div class="container">

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="alert alert-error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="nav-tabs">
        <a href="index.php">Plan Browser</a>
        <a onclick="showTab('dashboard')" id="tab-btn-dashboard" class="active">Dashboard</a>
        <a onclick="showTab('settings')" id="tab-btn-settings">Notification Settings</a>
        <a href="?logout=1" style="margin-left: auto; color: #ef4444; font-weight: 500; text-decoration: none;">Logout</a>
    </div>

    <!-- Dashboard Tab -->
    <div id="dashboard-tab" class="tab-content active">
        <div class="dashboard-grid">
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
                                <th>Amount</th>
                                <th>Validity</th>
                                <th>Data/Day</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $row): ?>
                                <tr>
                                    <td><?php echo date('d M', strtotime($row['recharge_date'])); ?></td>
                                    <td><?php echo $row['mobile_number']; ?></td>
                                    <td><strong>₹<?php echo $row['amount']; ?></strong></td>
                                    <td><?php echo $row['plan_validity']; ?> Days</td>
                                    <td><?php echo $row['data_per_day']; ?> GB</td>
                                    <td><span style="color: var(--text-muted);"><?php echo date('d M, Y', strtotime($row['expiry_date'])); ?></span></td>
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

            <aside>
                <!-- AI Recommendations -->
                <div class="stat-card" style="background: linear-gradient(135deg, #7c3aed, #db2777); color: white;">
                    <h3 style="color: white;">AI Recommendations 🤖</h3>
                    <p style="font-size: 0.875rem; margin-top: 0.5rem; opacity: 0.9;">Personalized for your usage patterns:</p>
                    
                    <?php foreach ($ai_recs as $type => $plan): ?>
                        <?php if ($plan): ?>
                            <div style="background: rgba(255,255,255,0.1); padding: 1rem; border-radius: 8px; margin-top: 1rem; border: 1px solid rgba(255,255,255,0.2);">
                                <span style="font-size: 0.7rem; text-transform: uppercase; font-weight: bold; opacity: 0.8;"><?php echo str_replace('_', ' ', $type); ?></span>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.25rem;">
                                    <strong><?php echo $plan['operator']; ?> ₹<?php echo $plan['price']; ?></strong>
                                    <a href="recharge.php?id=<?php echo $plan['id']; ?>" style="color: white; font-size: 0.8rem; background: rgba(0,0,0,0.2); padding: 0.25rem 0.5rem; border-radius: 4px; text-decoration: none;">Choose</a>
                                </div>
                                <p style="font-size: 0.75rem; margin-top: 0.25rem;">
                                    <?php echo $plan['data_per_day']; ?>GB/Day | <?php echo $plan['validity']; ?> Days
                                    <?php if ($plan['ott_subscription']): ?>
                                        <br><span style="color: #fbbf24;">★ Includes <?php echo $plan['ott_subscription']; ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <!-- AI Chatbot -->
                <div class="stat-card">
                    <h3>Recharge Assistant 💬</h3>
                    <div id="chat-history" style="height: 150px; overflow-y: auto; font-size: 0.875rem; margin-bottom: 1rem; padding: 0.5rem; background: #f9fafb; border-radius: 8px; border: 1px solid #eee;">
                        <?php if ($bot_response): ?>
                            <div style="margin-bottom: 0.5rem; color: var(--primary);"><strong>You:</strong> <?php echo htmlspecialchars($_POST['chat_query']); ?></div>
                            <div style="color: var(--text-main);"><strong>Bot:</strong> <?php echo $bot_response; ?></div>
                        <?php else: ?>
                            <div style="color: var(--text-muted);">Hi! Ask me for the 'cheapest plan' or 'OTT offers'.</div>
                        <?php endif; ?>
                    </div>
                    <form method="POST" action="dashboard.php#chat-history">
                        <input type="text" name="chat_query" placeholder="Ask me something..." style="width: 100%; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: 6px; outline: none;">
                        <button type="submit" class="btn-apply" style="margin-top: 0.5rem; font-size: 0.875rem; padding: 0.5rem;">Send</button>
                    </form>
                </div>

                <div class="stat-card">
                    <h3>Recent Alerts</h3>
                    <div style="margin-top: 1rem;">
                        <?php if (empty($notifications)): ?>
                            <p style="color: var(--text-muted); font-size: 0.875rem;">No recent alerts sent.</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $n): ?>
                                <div style="padding: 0.75rem; border-bottom: 1px solid #eee; font-size: 0.85rem;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <strong style="color: var(--primary);"><?php echo $n['type']; ?></strong>
                                        <span style="color: var(--text-muted); font-size: 0.75rem;"><?php echo date('d M, H:i', strtotime($n['sent_at'])); ?></span>
                                    </div>
                                    <p style="color: var(--text-main); line-height: 1.4;"><?php echo htmlspecialchars(substr($n['message'], 0, 80)) . '...'; ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
    </div>

    <!-- Settings Tab -->
    <div id="settings-tab" class="tab-content">
        <div class="stat-card">
            <h3>Notification & Profile Settings</h3>
            <p style="color: var(--text-muted); margin-bottom: 2rem;">Customize how and where you receive recharge reminders.</p>
            
            <form method="POST" class="settings-form">
                <div class="filter-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                </div>
                <div class="filter-group">
                    <label>Email Address (For Alerts)</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>
                <div class="filter-group">
                    <label>Mobile Number (For SMS)</label>
                    <input type="text" name="mobile" value="<?php echo htmlspecialchars($user_data['mobile']); ?>" required>
                </div>
                <div class="filter-group">
                    <label>WhatsApp Number</label>
                    <input type="text" name="whatsapp" value="<?php echo htmlspecialchars($user_data['whatsapp']); ?>">
                </div>
                <div class="filter-group">
                    <label>Notification Language</label>
                    <select name="language">
                        <option value="English" <?php echo ($user_data['language'] == 'English') ? 'selected' : ''; ?>>English</option>
                        <option value="Tamil" <?php echo ($user_data['language'] == 'Tamil') ? 'selected' : ''; ?>>Tamil (தமிழ்)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Preferred Alert Timing</label>
                    <select disabled>
                        <option>Morning (9:00 AM)</option>
                        <option>Evening (6:00 PM)</option>
                    </select>
                </div>
                
                <div class="full-width" style="margin-top: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color);">
                    <button type="submit" name="update_settings" class="btn-apply" style="width: auto; padding: 0.75rem 2rem;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.nav-tabs a').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    document.getElementById('tab-btn-' + tabName).classList.add('active');
    
    // Update URL hash without jumping
    history.pushState(null, null, '#' + tabName);
}

// Handle initial load from hash
window.addEventListener('load', () => {
    const hash = window.location.hash.substring(1);
    if (hash === 'settings') {
        showTab('settings');
    }
});
</script>

</body>
</html>
