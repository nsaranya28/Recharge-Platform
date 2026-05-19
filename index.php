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
        /* HERO SECTION */
        .plan-comparison-header {
            margin-top: 4rem;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        .plan-comparison-header h1 {
            font-size: 3.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.75rem;
            letter-spacing: -1.5px;
        }
        .plan-comparison-header p {
            font-size: 1.15rem;
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

        /* DETAILS EXPAND */
        .plan-details-expand {
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(124, 58, 237, 0.03);
            border-radius: var(--radius-sm);
            margin-top: 0;
            opacity: 0;
        }
        .plan-details-expand.active {
            max-height: 500px;
            padding: 20px;
            margin-top: 15px;
            opacity: 1;
            border: 1px solid var(--border-color);
        }
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }
        .details-item {
            display: flex;
            flex-direction: column;
        }
        .details-label {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .details-val {
            font-size: 15px;
            color: var(--text-main);
            font-weight: 700;
            margin-top: 2px;
        }
        .ott-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(236, 72, 153, 0.1);
            color: var(--secondary);
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 15px;
            border: 1px solid rgba(236, 72, 153, 0.2);
        }
        
        /* PLAN COLUMNS */
        .plan-col { flex: 1; }
        .price-col { flex: 1.2; }
        .validity-col { flex: 0.8; }
        .data-col { flex: 0.8; }
        .action-col { flex: 1; text-align: right; }

        /* FILTERS & SEARCH */
        .plan-selection-header {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 1.25rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--glass-border);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .plan-selection-header h3 {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -0.5px;
        }
        .search-filter-wrapper input {
            background: rgba(255,255,255,0.4);
            border: 1px solid var(--border-color);
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-family: inherit;
            font-size: 0.9rem;
            width: 250px;
            transition: var(--transition);
            outline: none;
            color: var(--text-main);
        }
        body.dark-mode .search-filter-wrapper input {
            background: rgba(15, 23, 42, 0.4);
        }
        .search-filter-wrapper input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        body.dark-mode .search-filter-wrapper input:focus {
            background: rgba(15, 23, 42, 0.6);
        }
        .filter-btn-toggle {
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-family: inherit;
            color: var(--text-main);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
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
            background: var(--card-bg);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            width: 320px;
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: 0 20px 45px rgba(0,0,0,0.15);
            z-index: 1000;
            border: 1px solid var(--glass-border);
        }
        .filters-menu.active {
            display: block;
            animation: slideInDown 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .filter-group label {
            font-family: inherit;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
            display: block;
        }
        
        .filters-menu .filter-group select, 
        .filters-menu .filter-group input {
            width: 100%;
            padding: 10px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
            font-family: inherit;
            background: rgba(255,255,255,0.4);
            color: var(--text-main);
            outline: none;
        }
        body.dark-mode .filters-menu .filter-group select, 
        body.dark-mode .filters-menu .filter-group input {
            background: rgba(15, 23, 42, 0.4);
        }
        .filters-menu .filter-group select:focus, 
        .filters-menu .filter-group input:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        body.dark-mode .filters-menu .filter-group select:focus, 
        body.dark-mode .filters-menu .filter-group input:focus {
            background: rgba(15, 23, 42, 0.7);
        }
        
        .btn-reset {
            display: inline-block;
            width: 100%;
            text-align: center;
            margin-top: 10px;
            font-weight: 700;
            color: var(--text-muted);
            text-decoration: none;
            padding: 8px;
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }
        .btn-reset:hover {
            background: rgba(239, 68, 68, 0.08);
            color: var(--error);
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
