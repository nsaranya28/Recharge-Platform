<?php
session_start();
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
    <style>
        /* FORCED GRID ARRANGEMENT */
        .services-container { 
            padding: 2rem 0; 
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }
        .services-box-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)) !important;
            gap: 20px !important;
            background: #ffffff;
            padding: 2.5rem !important;
            border-radius: 24px !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        }
        .service-box {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            text-decoration: none !important;
            padding: 10px !important;
            border-radius: 16px !important;
            transition: all 0.3s ease !important;
        }
        .service-box:hover {
            background: #f8fafc !important;
            transform: translateY(-5px) !important;
        }
        .service-box-icon {
            width: 65px !important;
            height: 65px !important;
            background: #f1f5f9 !important;
            border-radius: 20px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 28px !important;
            margin-bottom: 12px !important;
            transition: all 0.3s ease !important;
        }
        .service-box.active .service-box-icon {
            background: linear-gradient(135deg, #7c3aed, #4f46e5) !important;
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.3) !important;
        }
        .service-box-text {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            text-align: center !important;
        }
        .service-box:hover .service-box-text {
            color: #7c3aed !important;
        }
        @media (max-width: 640px) {
            .services-box-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                padding: 1.5rem !important;
                gap: 10px !important;
            }
            .service-box-icon {
                width: 55px !important;
                height: 55px !important;
                font-size: 22px !important;
            }
            .service-box-text { font-size: 12px !important; }
        }

        /* FORCED PLAN ARRANGEMENT */
        .plan-tabs {
            display: flex !important;
            gap: 25px !important;
            border-bottom: 2px solid #e2e8f0 !important;
            margin-bottom: 2rem !important;
            padding-bottom: 10px !important;
            overflow-x: auto;
        }
        .tab-item {
            text-decoration: none !important;
            color: #64748b !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            white-space: nowrap;
        }
        .tab-item.active {
            color: #f97316 !important;
            border-bottom: 3px solid #f97316 !important;
            padding-bottom: 7px !important;
        }
        .plan-row-card {
            background: #ffffff !important;
            border-radius: 16px !important;
            padding: 20px !important;
            margin-bottom: 15px !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05) !important;
            border: 1px solid #f1f5f9 !important;
            position: relative;
        }
        .plan-row-content {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding-bottom: 15px !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .plan-col { flex: 1; }
        .row-price { font-size: 24px !important; font-weight: 800 !important; color: #1e293b !important; }
        .row-main-val { font-size: 18px !important; font-weight: 700 !important; color: #1e293b !important; }
        .row-subtext { font-size: 12px !important; color: #94a3b8 !important; }
        .btn-apply-orange {
            background: #f97316 !important;
            color: white !important;
            padding: 12px 35px !important;
            border-radius: 10px !important;
            font-weight: 700 !important;
            text-decoration: none !important;
            display: inline-block !important;
        }
        .plan-row-footer {
            display: flex !important;
            justify-content: space-between !important;
            padding-top: 10px !important;
            align-items: center !important;
        }
        .cost-badge {
            background: #f8fafc !important;
            padding: 5px 15px !important;
            border-radius: 99px !important;
            font-size: 12px !important;
            color: #64748b !important;
        }
        .details-link { color: #f97316 !important; font-weight: 600 !important; text-decoration: none !important; font-size: 14px !important; }
        
        @media (max-width: 640px) {
            .plan-row-content { flex-direction: column !important; align-items: flex-start !important; gap: 15px !important; }
            .btn-apply-orange { width: 100% !important; text-align: center !important; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">
    <!-- Main Service Grid (Freecharge Style) -->
    <section class="services-container">
        <h2 class="section-title">Recharges & Bill Payments</h2>
        <div class="services-box-grid">
            <a href="index.php" class="service-box active">
                <div class="service-box-icon">📱</div>
                <div class="service-box-text">Mobile</div>
            </a>
            <a href="electricity.php" class="service-box">
                <div class="service-box-icon">💡</div>
                <div class="service-box-text">Electricity</div>
            </a>
            <a href="#" class="service-box">
                <div class="service-box-icon">📡</div>
                <div class="service-box-text">DTH</div>
            </a>
            <a href="#" class="service-box">
                <div class="service-box-icon">🚗</div>
                <div class="service-box-text">FASTag</div>
            </a>
            <a href="#" class="service-box">
                <div class="service-box-icon">🔋</div>
                <div class="service-box-text">EV Recharge</div>
            </a>
            <a href="#" class="service-box">
                <div class="service-box-icon">☎️</div>
                <div class="service-box-text">Landline</div>
            </a>
            <a href="#" class="service-box">
                <div class="service-box-icon">🏛️</div>
                <div class="service-box-text">Property Tax</div>
            </a>
            <a href="#" class="service-box">
                <div class="service-box-icon">➕</div>
                <div class="service-box-text">More</div>
            </a>
        </div>
    </section>

    <div class="plan-comparison-header" style="margin-top: 4rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--primary);">Smart Plan Comparison</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Select your operator and find the perfect plan in seconds</p>
    </div>

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

        <!-- Freecharge Style Plan Selection -->
        <main>
            <div class="plan-selection-header">
                <h3>Select Plan</h3>
                <div class="search-filter-wrapper">
                    <input type="text" name="search" placeholder="Search for a plan or enter amount" value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="plan-tabs">
                <a href="index.php" class="tab-item active">RECOMMENDED</a>
                <a href="index.php?operator=<?php echo $operator; ?>&max_price=500" class="tab-item">POPULAR</a>
                <a href="index.php?min_data=2" class="tab-item">SMART PHONE</a>
                <a href="index.php?max_validity=1" class="tab-item">DATA ADD ON</a>
            </div>

            <div class="plans-list-container">
                <?php if (empty($plans)): ?>
                    <p style="text-align: center; padding: 3rem; color: var(--text-muted);">No plans found matching your criteria.</p>
                <?php else: ?>
                    <?php foreach ($plans as $plan): ?>
                        <div class="plan-row-card">
                            <?php if ($plan['is_best_plan']): ?>
                                <div class="row-badge">Popular</div>
                            <?php endif; ?>
                            
                            <div class="plan-row-content">
                                <div class="plan-col price-col">
                                    <div class="row-price">₹<?php echo number_format($plan['price'], 0); ?></div>
                                    <div class="row-subtext">Unlimited Calls</div>
                                </div>
                                
                                <div class="plan-col validity-col">
                                    <div class="row-main-val"><?php echo $plan['validity']; ?> Days</div>
                                    <div class="row-subtext">Validity</div>
                                </div>

                                <div class="plan-col data-col">
                                    <div class="row-main-val"><?php echo $plan['data_per_day']; ?> GB/day</div>
                                    <div class="row-subtext">Data</div>
                                </div>

                                <div class="plan-col action-col">
                                    <a href="recharge.php?id=<?php echo $plan['id']; ?>" class="btn-apply-orange">
                                        APPLY
                                    </a>
                                </div>
                            </div>
                            
                            <div class="plan-row-footer">
                                <div class="cost-badge">Cost per day <span>₹<?php echo number_format($plan['price'] / $plan['validity'], 1); ?></span></div>
                                <a href="#" class="details-link">Details ⌄</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

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
