<?php
/**
 * AI Recommendation Engine for Recharge Plans
 */

function getAIRecommendations($pdo, $user_id) {
    // 1. Analyze User History
    $stmt = $pdo->prepare("
        SELECT 
            AVG(amount) as avg_spend, 
            AVG(validity) as avg_validity,
            MAX(rh.operator) as preferred_operator,
            COUNT(*) as recharge_count
        FROM recharge_history rh
        JOIN plans p ON rh.plan_id = p.id
        WHERE rh.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $history = $stmt->fetch();

    $avg_spend = $history['avg_spend'] ?: 300; // Default fallback
    $preferred_op = $history['preferred_operator'] ?: 'Jio';

    $recommendations = [];

    // --- Recommendation 1: Budget Plan (Lowest price for preferred operator) ---
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE operator = ? AND category = 'Budget' ORDER BY price ASC LIMIT 1");
    $stmt->execute([$preferred_op]);
    $recommendations['budget'] = $stmt->fetch();

    // --- Recommendation 2: Best Value Unlimited (Near avg spend) ---
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE operator = ? AND category = 'Unlimited' AND price BETWEEN ? AND ? ORDER BY data_per_day DESC LIMIT 1");
    $stmt->execute([$preferred_op, $avg_spend * 0.8, $avg_spend * 1.5]);
    $recommendations['unlimited'] = $stmt->fetch();

    // --- Recommendation 3: Long Validity (For heavy users) ---
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE operator = ? AND validity >= 84 ORDER BY cost_per_day ASC LIMIT 1");
    $stmt->execute([$preferred_op]);
    $recommendations['long_validity'] = $stmt->fetch();

    // --- Recommendation 4: OTT Bundled (Entertainment focused) ---
    $stmt = $pdo->prepare("SELECT * FROM plans WHERE ott_subscription IS NOT NULL AND operator = ? ORDER BY price ASC LIMIT 1");
    $stmt->execute([$preferred_op]);
    $recommendations['ott'] = $stmt->fetch();

    return $recommendations;
}

/**
 * AI Chatbot Logic (Simple Pattern Matching)
 */
function getChatbotResponse($query, $pdo) {
    $query = strtolower($query);
    
    // 1. Check for "Month" or "28/30 Days"
    if (strpos($query, 'month') !== false || strpos($query, '28 day') !== false || strpos($query, '30 day') !== false) {
        $stmt = $pdo->query("SELECT * FROM plans WHERE validity BETWEEN 24 AND 31 ORDER BY price ASC LIMIT 1");
        $plan = $stmt->fetch();
        if ($plan) {
            return "I found a great monthly plan for you! The <strong>{$plan['operator']} ₹{$plan['price']}</strong> pack offers {$plan['data_per_day']}GB/day for {$plan['validity']} days. <a href='recharge.php?id={$plan['id']}' style='color: #7c3aed; font-weight: bold; text-decoration: none;'>Recharge Now →</a>";
        }
    }

    // 2. Check for "Data" or "GB"
    if (strpos($query, 'data') !== false || strpos($query, 'gb') !== false || strpos($query, 'internet') !== false) {
        $stmt = $pdo->query("SELECT * FROM plans ORDER BY data_per_day DESC LIMIT 1");
        $plan = $stmt->fetch();
        return "If you need lots of data, the <strong>{$plan['operator']} ₹{$plan['price']}</strong> plan is best. It gives you <strong>{$plan['data_per_day']}GB per day</strong>! <a href='recharge.php?id={$plan['id']}' style='color: #7c3aed; font-weight: bold; text-decoration: none;'>View Plan →</a>";
    }

    // 3. Check for "Cheap" or "Budget"
    if (strpos($query, 'cheap') !== false || strpos($query, 'budget') !== false) {
        $stmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC LIMIT 1");
        $plan = $stmt->fetch();
        return "The most budget-friendly plan is <strong>₹{$plan['price']}</strong> from {$plan['operator']} ({$plan['validity']} days). <a href='recharge.php?id={$plan['id']}' style='color: #7c3aed; font-weight: bold; text-decoration: none;'>Get it here →</a>";
    }
    
    // 4. Check for "OTT" or specific services
    if (strpos($query, 'ott') !== false || strpos($query, 'netflix') !== false || strpos($query, 'hotstar') !== false) {
        $stmt = $pdo->query("SELECT * FROM plans WHERE ott_subscription IS NOT NULL LIMIT 1");
        $plan = $stmt->fetch();
        if ($plan) {
            return "For entertainment, I recommend the <strong>{$plan['operator']} ₹{$plan['price']}</strong> plan. It includes <strong>{$plan['ott_subscription']}</strong>! <a href='recharge.php?id={$plan['id']}' style='color: #7c3aed; font-weight: bold; text-decoration: none;'>Recharge →</a>";
        }
    }

    // 5. Operator specific
    $operators = ['jio', 'airtel', 'vi'];
    foreach ($operators as $op) {
        if (strpos($query, $op) !== false) {
            $stmt = $pdo->prepare("SELECT * FROM plans WHERE operator = ? ORDER BY price ASC LIMIT 1");
            $stmt->execute([ucfirst($op)]);
            $plan = $stmt->fetch();
            if ($plan) {
                return "Looking for " . ucfirst($op) . "? Their most popular plan is <strong>₹{$plan['price']}</strong> with {$plan['data_per_day']}GB/day. <a href='recharge.php?id={$plan['id']}' style='color: #7c3aed; font-weight: bold; text-decoration: none;'>Select Plan →</a>";
            }
        }
    }

    return "I'm your Smart Recharge Assistant! I can help you find <strong>monthly plans</strong>, <strong>data packs</strong>, or <strong>OTT offers</strong>. What are you looking for today?";
}
?>
