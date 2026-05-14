<?php
session_start();
require_once 'db.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Guest User';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EV Recharge - SmartRecharge Premium</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --ev-primary: #7c3aed;
            --ev-secondary: #db2777;
            --ev-gradient: linear-gradient(135deg, #7c3aed, #db2777);
            --ev-glass: rgba(255, 255, 255, 0.7);
            --ev-glass-border: rgba(255, 255, 255, 0.3);
            --ev-shadow: 0 20px 40px -10px rgba(124, 58, 237, 0.2);
            --ev-bg: #f5f3ff;
            --ev-text: #1e1b4b;
        }

        body.dark-mode {
            --ev-bg: #0f172a;
            --ev-text: #f1f5f9;
            --ev-glass: rgba(30, 41, 59, 0.7);
            --ev-glass-border: rgba(255, 255, 255, 0.1);
            background-color: var(--ev-bg);
            color: var(--ev-text);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--ev-bg);
            transition: background-color 0.4s ease, color 0.4s ease;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .ev-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        /* Glass Card */
        .glass-card {
            background: var(--ev-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--ev-glass-border);
            border-radius: 24px;
            padding: 2rem;
            box-shadow: var(--ev-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px -12px rgba(124, 58, 237, 0.3);
        }

        /* Header Section */
        .ev-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--ev-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .controls {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .icon-btn {
            background: var(--ev-glass);
            border: 1px solid var(--ev-glass-border);
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--ev-text);
        }

        .icon-btn:hover {
            background: var(--ev-primary);
            color: white;
            transform: scale(1.1);
        }

        /* Main Layout */
        .ev-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 968px) {
            .ev-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Form Styling */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--ev-glass-border);
            padding: 1rem;
            border-radius: 14px;
            font-family: inherit;
            font-size: 1rem;
            color: var(--ev-text);
            outline: none;
            transition: all 0.3s ease;
        }

        .dark-mode .form-input {
            background: rgba(0, 0, 0, 0.2);
        }

        .form-input:focus {
            border-color: var(--ev-primary);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        .provider-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .provider-item {
            border: 1px solid var(--ev-glass-border);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.3);
        }

        .provider-item.active {
            border-color: var(--ev-primary);
            background: rgba(124, 58, 237, 0.1);
            transform: scale(1.05);
        }

        .provider-logo {
            width: 40px;
            height: 40px;
            margin-bottom: 0.5rem;
            filter: grayscale(1);
            transition: filter 0.3s;
        }

        .provider-item.active .provider-logo {
            filter: grayscale(0);
        }

        /* Neon Button */
        .neon-btn {
            width: 100%;
            padding: 1.25rem;
            background: var(--ev-gradient);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px -5px rgba(124, 58, 237, 0.5);
        }

        .neon-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -10px rgba(124, 58, 237, 0.6);
        }

        .neon-btn::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.6s ease-out;
        }

        .neon-btn:active::after {
            transform: scale(1);
        }

        /* Bill Details Display */
        .bill-details {
            display: none; /* Shown after "Check Bill" */
            margin-top: 2rem;
            animation: slideUp 0.5s ease-out forwards;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid var(--ev-glass-border);
        }

        .detail-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 600;
        }

        /* Battery Animation */
        .battery-container {
            width: 100px;
            height: 50px;
            border: 4px solid var(--ev-text);
            border-radius: 8px;
            position: relative;
            padding: 4px;
            margin: 2rem auto;
        }

        .battery-container::after {
            content: '';
            width: 6px;
            height: 20px;
            background: var(--ev-text);
            position: absolute;
            right: -10px;
            top: 11px;
            border-radius: 0 4px 4px 0;
        }

        .battery-level {
            height: 100%;
            background: #22c55e;
            width: 0%;
            border-radius: 2px;
            transition: width 1s ease-in-out;
            box-shadow: 0 0 15px rgba(34, 197, 94, 0.5);
        }

        .charging-anim .battery-level {
            animation: charging 2s infinite linear;
        }

        @keyframes charging {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        /* Success Popup */
        #success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 3rem;
            border-radius: 32px;
            text-align: center;
            max-width: 400px;
            position: relative;
        }

        .dark-mode .modal-content {
            background: #1e293b;
        }

        .check-icon {
            width: 80px;
            height: 80px;
            background: #22c55e;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            animation: pop 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes pop {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        /* Banner */
        .offer-banner {
            background: linear-gradient(90deg, #7c3aed, #db2777);
            color: white;
            padding: 1rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .offer-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 100%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        /* AI Section */
        .ai-section {
            background: rgba(124, 58, 237, 0.05);
            border: 1px dashed var(--ev-primary);
            padding: 1.5rem;
            border-radius: 16px;
            margin-top: 1.5rem;
        }

        .qr-section {
            text-align: center;
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 16px;
            color: black;
        }

        /* Loading Spinner */
        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid var(--ev-primary);
            border-bottom-color: transparent;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
            display: none;
        }

        @keyframes rotation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .lang-active {
            color: var(--ev-primary);
            font-weight: 700;
        }

        /* Illustration Container */
        .illustration-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .illustration-container img {
            max-width: 100%;
            filter: drop-shadow(0 20px 40px rgba(124, 58, 237, 0.3));
        }

        .floating-icon {
            position: absolute;
            animation: float 3s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body>

    <div class="ev-container">
        <!-- Header -->
        <div class="ev-header">
            <a href="index.php" class="brand-logo" style="text-decoration: none;">
                <i data-lucide="zap"></i>
                <span>SmartRecharge</span>
            </a>
            <div class="controls">
                <div class="icon-btn" onclick="toggleLanguage()" id="lang-toggle" title="Switch Language">EN</div>
                <div class="icon-btn" onclick="toggleDarkMode()" title="Toggle Theme">
                    <i data-lucide="moon" id="theme-icon"></i>
                </div>
                <div class="icon-btn" onclick="startVoiceAssistant()" title="Voice Assistant">
                    <i data-lucide="mic"></i>
                </div>
            </div>
        </div>

        <!-- Offer Banner -->
        <div class="offer-banner">
            <i data-lucide="sparkles"></i>
            <div>
                <strong id="txt-offer-title">Special Launch Offer!</strong>
                <p id="txt-offer-desc" style="font-size: 0.8rem; opacity: 0.9;">Get 10% cashback up to ₹50 on your first EV recharge.</p>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="ev-grid">
            <!-- Left Column: Bill Check Form -->
            <div class="glass-card">
                <h2 id="txt-main-title" style="margin-bottom: 2rem; font-size: 1.8rem;">EV Recharge & Bill</h2>
                
                <form id="bill-form" onsubmit="event.preventDefault(); checkBill();">
                    <div class="form-group">
                        <label class="form-label" id="lbl-provider">Select Provider</label>
                        <div class="provider-grid">
                            <div class="provider-item active" onclick="selectProvider(this, 'tata')">
                                <i data-lucide="zap" style="color: #00a1e1;"></i>
                                <div style="font-size: 0.8rem; margin-top: 5px;">Tata Power</div>
                            </div>
                            <div class="provider-item" onclick="selectProvider(this, 'mg')">
                                <i data-lucide="battery-charging" style="color: #ff3e3e;"></i>
                                <div style="font-size: 0.8rem; margin-top: 5px;">MG Charge</div>
                            </div>
                            <div class="provider-item" onclick="selectProvider(this, 'ather')">
                                <i data-lucide="activity" style="color: #22c55e;"></i>
                                <div style="font-size: 0.8rem; margin-top: 5px;">Ather Grid</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" id="lbl-consumer-id">Consumer ID / Mobile Number</label>
                        <input type="text" class="form-input" placeholder="e.g. 123456789" required id="consumer-id">
                    </div>

                    <div class="form-group">
                        <label class="form-label" id="lbl-vehicle-no">Vehicle Number</label>
                        <input type="text" class="form-input" placeholder="e.g. TN 01 AB 1234" required id="vehicle-no">
                    </div>

                    <button type="submit" class="neon-btn" id="btn-check">
                        <span id="btn-text">Check Bill</span>
                        <div class="loader" id="btn-loader"></div>
                    </button>
                </form>

                <!-- Bill Details (Initially Hidden) -->
                <div class="bill-details" id="bill-details">
                    <h3 style="margin: 2rem 0 1rem; border-bottom: 2px solid var(--ev-primary); display: inline-block;">Bill Summary</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Customer Name</span>
                        <span class="detail-value" id="val-name">Saranya N.</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Station Name</span>
                        <span class="detail-value" id="val-station">Main City Hub - Slot 4</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Units Consumed</span>
                        <span class="detail-value" id="val-units">45.2 kWh</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Current Bill</span>
                        <span class="detail-value" style="color: var(--ev-secondary); font-size: 1.2rem;" id="val-bill">₹ 452.00</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Due Date</span>
                        <span class="detail-value" id="val-due">20 May, 2026</span>
                    </div>

                    <!-- AI Recommended Section -->
                    <div class="ai-section">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.5rem; color: var(--ev-primary);">
                            <i data-lucide="brain"></i>
                            <strong id="txt-ai-reco">AI Recommendation</strong>
                        </div>
                        <p style="font-size: 0.85rem;" id="txt-ai-desc">Based on your usage, we recommend recharging with <b>₹500</b> to cover your next 3 sessions with extra savings.</p>
                        <button class="icon-btn" style="width: auto; padding: 0 1rem; height: 35px; font-size: 0.8rem; margin-top: 10px;" onclick="applyAiReco()">Apply ₹500</button>
                    </div>

                    <div class="battery-container" id="battery-box">
                        <div class="battery-level" id="battery-fill"></div>
                    </div>

                    <button class="neon-btn" id="pay-now-btn" style="margin-top: 1rem;" onclick="showPaymentOptions()">
                        Pay Now
                    </button>

                    <!-- QR Payment (Moved here) -->
                    <div id="payment-options" style="display: none; margin-top: 2rem;">
                        <h3 style="margin-bottom: 1rem; text-align: center;">Scan to Pay</h3>
                        <div class="qr-section" style="background: white; padding: 1.5rem; border-radius: 16px; color: black; box-shadow: inset 0 2px 10px rgba(0,0,0,0.1);">
                            <div style="width: 150px; height: 150px; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; background: #f8fafc; border-radius: 12px; border: 2px dashed #e2e8f0;">
                                <i data-lucide="qr-code" size="100" style="color: #1e293b;"></i>
                            </div>
                            <p style="font-size: 0.85rem; color: #64748b; text-align: center;">Scan this QR with any UPI App (PhonePe, GPay, Paytm)</p>
                            <button class="neon-btn" style="margin-top: 1.5rem;" onclick="processPayment()">
                                Confirm Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Illustration & History -->
            <div class="glass-card" style="display: flex; flex-direction: column; gap: 2rem;">
                <div class="illustration-container">
                    <i data-lucide="leaf" class="floating-icon" style="top: 0; left: 10%; color: #22c55e;"></i>
                    <i data-lucide="zap" class="floating-icon" style="top: 20%; right: 5%; color: #fbbf24; animation-delay: 0.5s;"></i>
                    <img src="ev_charging_illustration_1778782506091.png" alt="EV Charging">
                </div>

                <!-- Recent History -->
                <div>
                    <h3 id="txt-history-title" style="margin-bottom: 1rem;">Recent History</h3>
                    <div class="glass-card" style="padding: 1rem; background: rgba(255,255,255,0.1); margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600;">Tata Power - TN 01...</div>
                                <div style="font-size: 0.8rem; opacity: 0.7;">12 May, 2026</div>
                            </div>
                            <div style="font-weight: 700; color: #22c55e;">₹ 250</div>
                        </div>
                    </div>
                    <div class="glass-card" style="padding: 1rem; background: rgba(255,255,255,0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-weight: 600;">Ather Grid - TN 01...</div>
                                <div style="font-size: 0.8rem; opacity: 0.7;">05 May, 2026</div>
                            </div>
                            <div style="font-weight: 700; color: #22c55e;">₹ 180</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal">
        <div class="modal-content">
            <div class="check-icon">
                <i data-lucide="check"></i>
            </div>
            <h2 style="margin-bottom: 1rem;">Payment Successful!</h2>
            <p style="color: #64748b; margin-bottom: 2rem;">Your EV wallet has been credited with ₹452. Transaction ID: #EV982347</p>
            <button class="neon-btn" onclick="closeModal()">Great!</button>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();

        let currentLang = 'EN';
        let isDarkMode = false;

        const translations = {
            'EN': {
                'main-title': 'EV Recharge & Bill',
                'provider': 'Select Provider',
                'consumer-id': 'Consumer ID / Mobile Number',
                'vehicle-no': 'Vehicle Number',
                'btn-check': 'Check Bill',
                'offer-title': 'Special Launch Offer!',
                'offer-desc': 'Get 10% cashback up to ₹50 on your first EV recharge.',
                'history-title': 'Recent History',
                'ai-reco': 'AI Recommendation',
                'ai-desc': 'Based on your usage, we recommend recharging with <b>₹500</b> to cover your next 3 sessions with extra savings.'
            },
            'TA': {
                'main-title': 'மின்சார வாகன ரீசார்ஜ்',
                'provider': 'சேவை வழங்குநரைத் தேர்ந்தெடுக்கவும்',
                'consumer-id': 'நுகர்வோர் ஐடி / மொபைல் எண்',
                'vehicle-no': 'வாகன எண்',
                'btn-check': 'பில்லைச் சரிபார்க்கவும்',
                'offer-title': 'சிறப்பு அறிமுக சலுகை!',
                'offer-desc': 'உங்கள் முதல் ரீசார்ஜில் 10% வரை கேஷ்பேக் பெறுங்கள்.',
                'history-title': 'சமீபத்திய வரலாறு',
                'ai-reco': 'AI பரிந்துரை',
                'ai-desc': 'உங்கள் பயன்பாட்டின் அடிப்படையில், அடுத்த 3 அமர்வுகளுக்கு ₹500 ரீசார்ஜ் செய்ய பரிந்துரைக்கிறோம்.'
            }
        };

        function toggleLanguage() {
            currentLang = currentLang === 'EN' ? 'TA' : 'EN';
            document.getElementById('lang-toggle').innerText = currentLang;
            
            const t = translations[currentLang];
            document.getElementById('txt-main-title').innerText = t['main-title'];
            document.getElementById('lbl-provider').innerText = t['provider'];
            document.getElementById('lbl-consumer-id').innerText = t['consumer-id'];
            document.getElementById('lbl-vehicle-no').innerText = t['vehicle-no'];
            document.getElementById('btn-text').innerText = t['btn-check'];
            document.getElementById('txt-offer-title').innerText = t['offer-title'];
            document.getElementById('txt-offer-desc').innerText = t['offer-desc'];
            document.getElementById('txt-history-title').innerText = t['history-title'];
            document.getElementById('txt-ai-reco').innerText = t['ai-reco'];
            document.getElementById('txt-ai-desc').innerHTML = t['ai-desc'];
        }

        function toggleDarkMode() {
            isDarkMode = !isDarkMode;
            document.body.classList.toggle('dark-mode');
            const icon = document.getElementById('theme-icon');
            icon.setAttribute('data-lucide', isDarkMode ? 'sun' : 'moon');
            lucide.createIcons();
        }

        function selectProvider(element, provider) {
            document.querySelectorAll('.provider-item').forEach(item => item.classList.remove('active'));
            element.classList.add('active');
        }

        function checkBill() {
            const btnText = document.getElementById('btn-text');
            const loader = document.getElementById('btn-loader');
            
            btnText.style.display = 'none';
            loader.style.display = 'inline-block';

            // Simulate fetching data
            setTimeout(() => {
                loader.style.display = 'none';
                btnText.style.display = 'block';
                document.getElementById('bill-details').style.display = 'block';
                
                // Animate Battery
                const batteryFill = document.getElementById('battery-fill');
                const batteryBox = document.getElementById('battery-box');
                batteryBox.classList.add('charging-anim');
                setTimeout(() => {
                    batteryBox.classList.remove('charging-anim');
                    batteryFill.style.width = '85%';
                }, 2000);
            }, 1500);
        }

        function applyAiReco() {
            document.getElementById('val-bill').innerText = '₹ 500.00';
            document.getElementById('val-bill').style.color = '#22c55e';
            alert("AI Recommended amount applied!");
        }

        function showPaymentOptions() {
            document.getElementById('pay-now-btn').style.display = 'none';
            document.getElementById('payment-options').style.display = 'block';
            
            // Smooth scroll to the payment section
            document.getElementById('payment-options').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            
            // Re-render icons if needed
            lucide.createIcons();
        }

        function processPayment() {
            // Fake processing
            document.getElementById('success-modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('success-modal').style.display = 'none';
            window.location.href = 'dashboard.php';
        }

        function startVoiceAssistant() {
            const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = currentLang === 'EN' ? 'en-US' : 'ta-IN';
            
            recognition.onstart = () => {
                alert("Listening... Say 'Check Bill'");
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript.toLowerCase();
                console.log(transcript);
                if (transcript.includes('check bill') || transcript.includes('பில்')) {
                    checkBill();
                }
            };

            recognition.start();
        }
    </script>
</body>
</html>
