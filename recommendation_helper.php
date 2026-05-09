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
            MAX(operator) as preferred_operator,
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
    
    if (strpos($query, 'cheap') !== false || strpos($query, 'budget') !== false) {
        $stmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC LIMIT 1");
        $plan = $stmt->fetch();
        return "The cheapest plan available is ₹{$plan['price']} from {$plan['operator']} with {$plan['validity']} day validity.";
    }
    
    if (strpos($query, 'ott') !== false || strpos($query, 'netflix') !== false || strpos($query, 'hotstar') !== false) {
        $stmt = $pdo->query("SELECT * FROM plans WHERE ott_subscription IS NOT NULL LIMIT 1");
        $plan = $stmt->fetch();
        return "If you want entertainment, I recommend the {$plan['operator']} ₹{$plan['price']} plan which includes {$plan['ott_subscription']}!";
    }

    if (strpos($query, 'jio') !== false) {
        return "Jio has some great unlimited plans. Would you like to see plans with 1.5GB or 2GB data per day?";
    }

    return "I'm your Smart Recharge Assistant! I can help you find budget plans, long-validity packs, or plans with OTT subscriptions. What are you looking for today?";
}
?>
