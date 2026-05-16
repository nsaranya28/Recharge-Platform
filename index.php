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
    <link rel="stylesheet" href="style.css?v=1.1">
    <style>
        /* FORCED GRID ARRANGEMENT */
        .services-container { 
            padding: 2rem 0; 
        }
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--primary);
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
            background: linear-gradient(135deg, var(--primary), #a78bfa) !important;
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(139, 92, 246, 0.3) !important;
        }
        .service-box-text {
            font-size: 14px !important;
            font-weight: 600 !important;
            color: #64748b !important;
            text-align: center !important;
        }
        .service-box:hover .service-box-text {
            color: var(--primary) !important;
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
            color: var(--primary) !important;
            border-bottom: 3px solid var(--primary) !important;
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
            background: var(--primary) !important;
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
        .details-link { color: var(--primary) !important; font-weight: 600 !important; text-decoration: none !important; font-size: 14px !important; }
        
        @media (max-width: 640px) {
            .plan-row-content { flex-direction: column !important; align-items: flex-start !important; gap: 15px !important; }
            .btn-apply-orange { width: 100% !important; text-align: center !important; }
        }

        /* NEW: Plan Details Expansion Styles */
        .plan-details-expand {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background: #f8fafc;
            border-radius: 0 0 16px 16px;
            margin: 0 -20px -20px -20px;
            border-top: 1px solid #f1f5f9;
        }
        .plan-details-expand.active {
            max-height: 500px;
            padding: 20px;
            margin-top: 10px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        .details-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .details-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
        }
        .details-val {
            font-size: 14px;
            color: #1e293b;
            font-weight: 700;
        }
        .ott-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef2f2;
            color: #dc2626;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* Layout */
        .main-layout {
            display: block;
            width: 100%;
        }

        /* Sidebar Filters - MOVED TO DROPDOWN */
        .filters-dropdown {
            position: relative;
        }
        .filters-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            width: 320px;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            z-index: 1000;
            border: 1px solid #f1f5f9;
            margin-top: 10px;
        }
        .filters-menu.active {
            display: block;
            animation: slideInDown 0.3s ease-out;
        }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .filter-btn-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border: 1.5px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .filter-btn-toggle:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: #f5f3ff;
        }
        .plan-selection-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        /* Operator Badges */
        .operator-badge {
            font-size: 10px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .operator-badge.jio { background: #e0f2fe; color: #0369a1; }
        .operator-badge.airtel { background: #fee2e2; color: #b91c1c; }
        .operator-badge.vi { background: #fef9c3; color: #a16207; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">


    <div class="plan-comparison-header" style="margin-top: 4rem; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--primary);">Smart Plan Comparison</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Select your operator and find the perfect plan in seconds</p>
    </div>


    <div class="main-layout">
        <main>
            <div class="plan-selection-header">
                <h3>Select Plan</h3>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <form method="GET" action="index.php" class="search-filter-wrapper">
                        <input type="text" name="search" placeholder="Search plans..." value="<?php echo htmlspecialchars($search); ?>" onchange="this.form.submit()">
                        <input type="hidden" name="operator" value="<?php echo htmlspecialchars($operator); ?>">
                        <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
                        <input type="hidden" name="min_validity" value="<?php echo htmlspecialchars($min_validity); ?>">
                        <input type="hidden" name="min_data" value="<?php echo htmlspecialchars($min_data); ?>">
                        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
                    </form>
                    
                    <div class="filters-dropdown">
                        <button type="button" class="filter-btn-toggle" onclick="toggleFilterMenu()">
                            <span style="font-size: 1.2rem;">⚡</span> Filters
                        </button>
                        
                        <div class="filters-menu" id="filters-menu">
                            <form method="GET" action="index.php">
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <h4 style="margin-bottom: 1.5rem; color: #1e293b;">Refine Results</h4>
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

                                <button type="submit" class="btn-apply">Apply Filters</button>
                                <a href="index.php" class="btn-reset">Reset All</a>
                            </form>
                        </div>
                    </div>
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
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                        <span class="operator-badge <?php echo strtolower($plan['operator']); ?>"><?php echo $plan['operator']; ?></span>
                                    </div>
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
                                <a href="javascript:void(0)" class="details-link" onclick="toggleDetails(this)">Details ⌄</a>
                            </div>

                            <div class="plan-details-expand">
                                <div class="details-grid">
                                    <div class="details-item">
                                        <span class="details-label">Total Data</span>
                                        <span class="details-val"><?php echo $plan['data_per_day'] * $plan['validity']; ?> GB</span>
                                    </div>
                                    <div class="details-item">
                                        <span class="details-label">Calls</span>
                                        <span class="details-val">Truly Unlimited</span>
                                    </div>
                                    <div class="details-item">
                                        <span class="details-label">SMS</span>
                                        <span class="details-val">100 SMS/day</span>
                                    </div>
                                    <div class="details-item">
                                        <span class="details-label">Category</span>
                                        <span class="details-val"><?php echo $plan['category']; ?></span>
                                    </div>
                                </div>
                                <?php if ($plan['ott_subscription']): ?>
                                    <div class="ott-badge">
                                        🎁 Includes <?php echo $plan['ott_subscription']; ?>
                                    </div>
                                <?php endif; ?>
                                <div style="margin-top: 15px; font-size: 13px; color: #64748b; line-height: 1.5;">
                                    * This plan is available for <?php echo $plan['operator']; ?> customers in all circles. 
                                    High speed data of <?php echo $plan['data_per_day']; ?>GB/day available. 
                                    Post quota speed reduces to 64Kbps.
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Comparison Table -->
            <?php if (!empty($compare_plans)): ?>
                <section id="comparison" class="comparison-section">
                    <h2 style="margin-bottom: 1.5rem; color: var(--primary);">Plan Comparison</h2>
                    <div style="overflow-x: auto; background: white; padding: 1.5rem; border-radius: 16px; border: 1px solid #f1f5f9;">
                        <table class="comparison-table" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="border-bottom: 2px solid #f1f5f9;">
                                    <th style="padding: 1rem; text-align: left;">Feature</th>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <th style="padding: 1rem; text-align: left;"><?php echo $p['operator']; ?> - ₹<?php echo $p['price']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem;">Price</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td style="padding: 1rem;">₹<?php echo $p['price']; ?></td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem;">Validity</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td style="padding: 1rem;"><?php echo $p['validity']; ?> Days</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 1rem;">Data per Day</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td style="padding: 1rem;"><?php echo $p['data_per_day']; ?> GB</td>
                                    <?php endforeach; ?>
                                </tr>
                                <tr>
                                    <td style="padding: 1rem;">Cost per Day</td>
                                    <?php foreach ($compare_plans as $p): ?>
                                        <td style="padding: 1rem;">₹<?php echo number_format($p['price'] / $p['validity'], 2); ?></td>
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

<script>
    function toggleFilterMenu() {
        document.getElementById('filters-menu').classList.toggle('active');
    }

    // Close menu when clicking outside
    window.onclick = function(event) {
        if (!event.target.closest('.filter-btn-toggle') && !event.target.closest('.filters-menu')) {
            var dropdowns = document.getElementsByClassName("filters-menu");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('active')) {
                    openDropdown.classList.remove('active');
                }
            }
        }
    }

    function toggleDetails(btn) {
        const card = btn.closest('.plan-row-card');
        const expand = card.querySelector('.plan-details-expand');
        const isActive = expand.classList.contains('active');
        
        // Close all others
        document.querySelectorAll('.plan-details-expand').forEach(el => {
            el.classList.remove('active');
        });
        document.querySelectorAll('.details-link').forEach(el => {
            el.innerHTML = 'Details ⌄';
        });

        if (!isActive) {
            expand.classList.add('active');
            btn.innerHTML = 'Close ⌃';
        } else {
            expand.classList.remove('active');
            btn.innerHTML = 'Details ⌄';
        }
    }
</script>
</body>
</html>
