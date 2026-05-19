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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=1.2">
    <style>
        :root {
            --primary: #6d28d9; /* Deep Violet */
            --primary-light: #8b5cf6;
            --secondary: #10b981; /* Success Green */
            --bg-body: #f8fafc;
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,0.05) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,0.05) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,0.05) 0, transparent 50%);
            color: var(--text-main);
        }

        /* HERO SECTION */
        .plan-comparison-header {
            margin-top: 6rem;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        .plan-comparison-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            letter-spacing: -1px;
        }
        .plan-comparison-header p {
            font-size: 1.25rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* TABS */
        .plan-tabs {
            display: flex;
            gap: 15px;
            margin-bottom: 2.5rem;
            overflow-x: auto;
            padding: 5px;
            justify-content: center;
        }
        .tab-item {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 600;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 999px;
            white-space: nowrap;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e2e8f0;
        }
        .tab-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
            color: var(--primary);
        }
        .tab-item.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            box-shadow: 0 10px 20px -5px rgba(109, 40, 217, 0.4);
        }

        /* PLAN CARDS */
        .plan-row-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.8);
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .plan-row-card:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 20px 40px -5px rgba(109, 40, 217, 0.15);
            border-color: rgba(109, 40, 217, 0.2);
        }
        .row-badge {
            position: absolute;
            top: -12px;
            left: 30px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 16px;
            border-radius: 999px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
            letter-spacing: 0.5px;
        }

        .plan-row-content {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            padding-bottom: 20px !important;
            border-bottom: 1px dashed #cbd5e1 !important;
        }
        
        .row-price { font-size: 32px; font-weight: 800; color: #0f172a; letter-spacing: -1px; }
        .row-main-val { font-size: 20px; font-weight: 700; color: #1e293b; }
        .row-subtext { font-size: 13px; color: #64748b; font-weight: 500; margin-top: 4px; }
        
        .btn-apply-orange {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            padding: 14px 40px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px -4px rgba(109, 40, 217, 0.4);
            letter-spacing: 0.5px;
        }
        .btn-apply-orange:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -4px rgba(109, 40, 217, 0.5);
        }

        .plan-row-footer {
            display: flex !important;
            justify-content: space-between !important;
            padding-top: 15px !important;
            align-items: center !important;
        }
        .cost-badge {
            background: #f1f5f9;
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
        }
        .cost-badge span { color: var(--primary); font-weight: 800; }
        .details-link { 
            color: var(--primary); 
            font-weight: 700; 
            text-decoration: none; 
            font-size: 14px; 
            background: #f5f3ff;
            padding: 6px 16px;
            border-radius: 99px;
            transition: all 0.2s;
        }
        .details-link:hover {
            background: var(--primary);
            color: white;
        }

        /* LAYOUT FIXES */
        .main-layout {
            display: block;
            width: 100%;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* DETAILS EXPAND */
        .plan-details-expand {
            max-height: 0;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
            border-radius: 16px;
            margin-top: 0;
            opacity: 0;
        }
        .plan-details-expand.active {
            max-height: 500px;
            padding: 25px;
            margin-top: 20px;
            opacity: 1;
            border: 1px solid #e2e8f0;
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 20px;
        }
        .details-item {
            display: flex;
            flex-direction: column;
        }
        .details-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-val {
            font-size: 16px;
            color: #0f172a;
            font-weight: 700;
            margin-top: 4px;
        }
        .ott-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef2f2;
            color: #dc2626;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 15px;
            border: 1px solid #fca5a5;
        }
        
        /* PLAN COLUMNS */
        .plan-col { flex: 1; }
        .price-col { flex: 1.2; }
        .validity-col { flex: 0.8; }
        .data-col { flex: 0.8; }
        .action-col { flex: 1; text-align: right; }

        /* FILTERS & SEARCH */
        .plan-selection-header {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .plan-selection-header h3 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
        }
        .search-filter-wrapper input {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 20px;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            width: 250px;
            transition: all 0.2s;
            outline: none;
        }
        .search-filter-wrapper input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.1);
        }
        .filter-btn-toggle {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            color: #0f172a;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .filter-btn-toggle:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        .filters-dropdown {
            position: relative;
        }
        .filters-menu {
            display: none;
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            width: 350px;
            padding: 2rem;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            z-index: 1000;
            border: 1px solid rgba(255,255,255,0.5);
        }
        .filters-menu.active {
            display: block;
            animation: slideInDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .filter-group label {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            display: block;
        }
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            font-family: 'Outfit', sans-serif;
            background: #f8fafc;
            outline: none;
        }
        .filter-group select:focus, .filter-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(109, 40, 217, 0.1);
        }
        .btn-apply {
            background: var(--primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            width: 100%;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .btn-apply:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        /* OPERATOR BADGES */
        .operator-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .operator-badge.jio { background: #e0f2fe; color: #0284c7; }
        .operator-badge.airtel { background: #fee2e2; color: #dc2626; }
        .operator-badge.vi { background: #fef9c3; color: #ca8a04; }

        @media (max-width: 768px) {
            .plan-row-content { flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-apply-orange { width: 100%; }
            .plan-selection-header { flex-direction: column; align-items: stretch; }
            .search-filter-wrapper input { width: 100%; }
            .plan-comparison-header h1 { font-size: 2.5rem; }
            .plan-tabs { justify-content: flex-start; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="container">


    <div class="plan-comparison-header">
        <h1>Smart Plan Comparison</h1>
        <p>Select your operator and find the perfect mobile recharge plan in seconds with our intelligent filtering system.</p>
    </div>

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
                <a href="index.php" class="tab-item <?php echo empty($_GET['max_price']) && empty($_GET['min_data']) && empty($_GET['max_validity']) ? 'active' : ''; ?>">RECOMMENDED</a>
                <a href="index.php?operator=<?php echo $operator; ?>&max_price=500" class="tab-item <?php echo isset($_GET['max_price']) && $_GET['max_price'] == '500' ? 'active' : ''; ?>">POPULAR</a>
                <a href="index.php?min_data=2" class="tab-item <?php echo isset($_GET['min_data']) && $_GET['min_data'] == '2' ? 'active' : ''; ?>">SMART PHONE</a>
                <a href="index.php?max_validity=1" class="tab-item <?php echo isset($_GET['max_validity']) && $_GET['max_validity'] == '1' ? 'active' : ''; ?>">DATA ADD ON</a>
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
