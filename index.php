<?php
require_once 'db.php';

// Initialize variables for filtering
$operator = $_GET['operator'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$min_validity = $_GET['min_validity'] ?? '';
$min_data = $_GET['min_data'] ?? '';
$sort = $_GET['sort'] ?? 'price_asc';
$search = $_GET['search'] ?? '';

// Build SQL Query
$sql = "SELECT * FROM plans WHERE 1=1";
$params = [];

if ($operator) {
    $sql .= " AND operator = ?";
    $params[] = $operator;
}

if ($max_price) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
}

if ($min_validity) {
    $sql .= " AND validity >= ?";
    $params[] = $min_validity;
}

$max_validity = $_GET['max_validity'] ?? '';
if ($max_validity) {
    $sql .= " AND validity <= ?";
    $params[] = $max_validity;
}

if ($min_data) {
    $sql .= " AND data_per_day >= ?";
    $params[] = $min_data;
}

if ($search) {
    $sql .= " AND (operator LIKE ? OR price LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Sorting logic
switch ($sort) {
    case 'price_asc': $sql .= " ORDER BY price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY price DESC"; break;
    case 'validity_desc': $sql .= " ORDER BY validity DESC"; break;
    default: $sql .= " ORDER BY price ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$plans = $stmt->fetchAll();

// Handle Comparison
$compare_plans = [];
if (isset($_POST['compare']) && !empty($_POST['plan_ids'])) {
    $ids = $_POST['plan_ids'];
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $comp_stmt = $pdo->prepare("SELECT * FROM plans WHERE id IN ($placeholders)");
    $comp_stmt->execute($ids);
    $compare_plans = $comp_stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Recharge Plans Comparison</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">Recharge</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="recharge.php">Recharge</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
            <button class="nav-toggle" aria-label="Toggle navigation">&#9776;</button>
        </div>
    </nav>
    <script>
        const navToggle = document.querySelector('.nav-toggle');
        const navMenu = document.querySelector('.nav-menu');
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    </script>

<div class="container">
    <header>
        <h1>Smart Recharge Comparison</h1>
        <p>Find the best mobile plans from Jio, Airtel, and VI</p>
    </header>

    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" action="index.php" class="search-input-wrapper">
            <input type="text" name="search" placeholder="Search by operator or price..." value="<?php echo htmlspecialchars($search); ?>">
            <!-- Keep filters when searching -->
            <input type="hidden" name="operator" value="<?php echo htmlspecialchars($operator); ?>">
            <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
            <input type="hidden" name="min_validity" value="<?php echo htmlspecialchars($min_validity); ?>">
            <input type="hidden" name="min_data" value="<?php echo htmlspecialchars($min_data); ?>">
        </form>
    </div>

    <div class="main-layout">
        <!-- Sidebar Filters -->
        <aside class="filters-card">
            <h2>Filters</h2>
            <form method="GET" action="index.php">
                <div class="filter-group">
                    <label>Operator</label>
                    <select name="operator">
                        <option value="">All Operators</option>
                        <option value="Jio" <?php if($operator == 'Jio') echo 'selected'; ?>>Jio</option>
                        <option value="Airtel" <?php if($operator == 'Airtel') echo 'selected'; ?>>Airtel</option>
                        <option value="VI" <?php if($operator == 'VI') echo 'selected'; ?>>VI</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Max Price (₹)</label>
                    <input type="number" name="max_price" placeholder="e.g. 500" value="<?php echo htmlspecialchars($max_price); ?>">
                </div>

                <div class="filter-group">
                    <label>Min Validity (Days)</label>
                    <input type="number" name="min_validity" placeholder="e.g. 28" value="<?php echo htmlspecialchars($min_validity); ?>">
                </div>

                <div class="filter-group">
                    <label>Min Data per Day (GB)</label>
                    <select name="min_data">
                        <option value="">Any</option>
                        <option value="1" <?php if($min_data == '1') echo 'selected'; ?>>1 GB+</option>
                        <option value="1.5" <?php if($min_data == '1.5') echo 'selected'; ?>>1.5 GB+</option>
                        <option value="2" <?php if($min_data == '2') echo 'selected'; ?>>2 GB+</option>
                        <option value="3" <?php if($min_data == '3') echo 'selected'; ?>>3 GB+</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Sort By</label>
                    <select name="sort">
                        <option value="price_asc" <?php if($sort == 'price_asc') echo 'selected'; ?>>Price: Low to High</option>
                        <option value="price_desc" <?php if($sort == 'price_desc') echo 'selected'; ?>>Price: High to Low</option>
                        <option value="validity_desc" <?php if($sort == 'validity_desc') echo 'selected'; ?>>Longest Validity</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Quick Filters</label>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="index.php?max_validity=1" class="btn-reset" style="text-align: left; margin-top: 0; padding: 0.5rem; background: #f0fdf4; color: #166534; border-radius: 6px; border: 1px solid #bbf7d0;">⚡ 1-Day Data Boosters</a>
                    </div>
                </div>

                <button type="submit" class="btn-apply">Apply Filters</button>
                <a href="index.php" class="btn-reset">Reset All</a>
            </form>
        </aside>

        <!-- Plans Grid -->
        <main>
            <form method="POST" action="index.php#comparison">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3>Available Plans (<?php echo count($plans); ?>)</h3>
                    <button type="submit" name="compare" class="btn-apply" style="width: auto; padding: 0.5rem 1.5rem;">Compare Selected</button>
                </div>

                <div class="plans-grid">
                    <?php if (empty($plans)): ?>
                        <p style="grid-column: 1/-1; text-align: center; padding: 3rem; color: var(--text-muted);">No plans found matching your criteria.</p>
                    <?php else: ?>
                        <?php foreach ($plans as $plan): ?>
                            <div class="plan-card <?php echo $plan['is_best_plan'] ? 'best-plan' : ''; ?>">
                                <?php if ($plan['is_best_plan']): ?>
                                    <div class="badge-best">BEST VALUE</div>
                                <?php endif; ?>
                                
                                <span class="operator-tag tag-<?php echo strtolower($plan['operator']); ?>">
                                    <?php echo $plan['operator']; ?>
                                </span>
                                
                                <div class="plan-price">
                                    ₹<?php echo number_format($plan['price'], 0); ?>
                                </div>
                                
                                <div class="plan-details">
                                    <div class="detail-item">
                                        <span>Validity</span>
                                        <span><?php echo $plan['validity']; ?> Days</span>
                                    </div>
                                    <div class="detail-item">
                                        <span>Data</span>
                                        <span><?php echo $plan['data_per_day']; ?> GB/Day</span>
                                    </div>
                                    <div class="detail-item">
                                        <span>Total Data</span>
                                        <span><?php echo $plan['validity'] * $plan['data_per_day']; ?> GB</span>
                                    </div>
                                </div>

                                <label class="compare-checkbox">
                                    <input type="checkbox" name="plan_ids[]" value="<?php echo $plan['id']; ?>">
                                    Add to Compare
                                </label>

                                <a href="recharge.php?id=<?php echo $plan['id']; ?>" class="btn-apply" style="display: block; text-align: center; text-decoration: none; margin-top: 1rem; background: var(--primary);">
                                    Recharge Now
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Comparison Table -->
            <?php if (!empty($compare_plans)): ?>
                <section id="comparison" class="comparison-section">
                    <h2>Plan Comparison</h2>
                    <div style="overflow-x: auto;">
                        <table class="comparison-table">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <th><?php echo $p['operator']; ?> - ₹<?php echo $p['price']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Price</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td>₹<?php echo $p['price']; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Validity</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td><?php echo $p['validity']; ?> Days</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Data per Day</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td><?php echo $p['data_per_day']; ?> GB</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td>Cost per Day</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td>₹<?php echo number_format($p['price'] / $p['validity'], 2); ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>

</body>
</html>
