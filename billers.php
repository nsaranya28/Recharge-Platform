<?php
session_start();
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popular Billers & Services - SmartRecharge</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="billers_style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .search-wrapper {
            position: relative;
            max-width: 800px;
            margin: 2rem auto;
        }
        .search-icon {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--fin-text-muted);
        }
        .search-input {
            padding-left: 3.5rem !important;
            height: 60px;
            font-size: 1.1rem;
            box-shadow: var(--fin-shadow) !important;
        }
        .section-header {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .section-header i {
            color: var(--fin-primary);
        }
        .section-header h2 {
            font-weight: 800;
            font-size: 1.75rem;
            margin: 0;
        }
        .biller-selected-panel {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(8px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .panel-content {
            background: var(--fin-bg);
            max-width: 500px;
            width: 100%;
            border-radius: 32px;
            padding: 2.5rem;
            position: relative;
            box-shadow: 0 50px 100px -20px rgba(0,0,0,0.25);
        }
        .dark-mode .panel-content { background: #1e293b; }
        
        .close-panel {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            cursor: pointer;
            color: var(--fin-text-muted);
        }
        
        /* Chatbot */
        #ai-chatbot {
            position: fixed;
            bottom: 6rem;
            right: 2rem;
            width: 350px;
            height: 450px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            display: none;
            flex-direction: column;
            z-index: 1001;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .dark-mode #ai-chatbot { background: #1e293b; border-color: #334155; }
        .chat-header {
            background: var(--fin-gradient);
            padding: 1rem;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chat-messages {
            flex: 1;
            padding: 1rem;
            overflow-y: auto;
            font-size: 0.9rem;
        }
        .chat-input {
            padding: 1rem;
            border-top: 1px solid #eee;
            display: flex;
            gap: 8px;
        }
        .dark-mode .chat-input { border-color: #334155; }
        
        .notification-popup {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: #22c55e;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);
            display: none;
            z-index: 3000;
            animation: fadeInRight 0.3s ease;
        }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(50px); } to { opacity: 1; transform: translateX(0); } }

        /* Offer Card */
        .offer-card {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            padding: 2rem;
            border-radius: 24px;
            margin-bottom: 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            position: relative;
        }
        .offer-card::after {
            content: '⚡';
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.1;
        }
    </style>
</head>
<body class="billers-body">

    <?php include 'header.php'; ?>

    <div class="container py-5 mt-5">
        <!-- Top Controls -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 style="font-weight: 800; font-size: 2.5rem; letter-spacing: -1px;">Popular Billers</h1>
                <p class="text-muted" id="txt-subtitle">Recharge & pay bills in seconds</p>
            </div>
            <div class="d-flex gap-3">
                <button class="icon-btn" onclick="toggleLanguage()" id="lang-btn">EN</button>
                <button class="icon-btn" onclick="toggleDarkMode()">
                    <i data-lucide="moon" id="theme-icon"></i>
                </button>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="search-wrapper">
            <i data-lucide="search" class="search-icon"></i>
            <input type="text" class="fin-input search-input" placeholder="Search for electricity, gas, water billers..." id="service-search" oninput="handleSearch()">
        </div>

        <!-- Offer Banner -->
        <div class="offer-card">
            <div>
                <h3 style="font-weight: 700;">Super Cash!</h3>
                <p style="opacity: 0.9; max-width: 300px;">Get 15% cashback up to ₹100 on your first Utility Bill payment.</p>
                <button class="btn btn-light btn-sm rounded-pill px-4 fw-bold" style="color: #7c3aed;">Claim Now</button>
            </div>
            <div class="d-none d-md-block">
                <i data-lucide="gift" size="80" style="opacity: 0.5;"></i>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="category-tabs" id="category-tabs">
            <div class="category-tab active" onclick="filterCategory('all')">
                <i data-lucide="grid"></i> All
            </div>
            <div class="category-tab" onclick="filterCategory('Electricity')">
                <i data-lucide="zap"></i> Electricity
            </div>
            <div class="category-tab" onclick="filterCategory('DTH')">
                <i data-lucide="tv"></i> DTH
            </div>
            <div class="category-tab" onclick="filterCategory('Gas')">
                <i data-lucide="flame"></i> Piped Gas
            </div>
            <div class="category-tab" onclick="filterCategory('FASTag')">
                <i data-lucide="car"></i> FASTag
            </div>
            <div class="category-tab" onclick="filterCategory('Water')">
                <i data-lucide="droplet"></i> Water
            </div>
            <div class="category-tab" onclick="filterCategory('Broadband')">
                <i data-lucide="wifi"></i> Broadband
            </div>
        </div>

        <!-- Main Grid -->
        <div class="services-grid" id="services-grid">
            <!-- Dynamically populated -->
        </div>

        <!-- Detailed Panel for Selected Category -->
        <div id="service-detail-panel" class="service-panel mt-5">
            <div class="section-header">
                <i data-lucide="arrow-left" style="cursor:pointer;" onclick="hideDetailPanel()"></i>
                <h2 id="detail-title">Electricity Billers</h2>
            </div>
            <div class="biller-grid" id="biller-grid">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>

    <!-- Biller Selection Modal -->
    <div class="biller-selected-panel" id="biller-modal">
        <div class="panel-content">
            <div class="close-panel" onclick="closeBillerModal()">
                <i data-lucide="x"></i>
            </div>
            <div class="text-center mb-4">
                <div class="service-card-icon" style="background: rgba(124, 58, 237, 0.1);">
                    <i id="modal-icon" data-lucide="zap"></i>
                </div>
                <h3 id="modal-biller-name" style="font-weight: 800;">WBSEDCL</h3>
                <p class="text-muted" id="modal-category">Electricity Bill Payment</p>
            </div>

            <form id="biller-form" onsubmit="event.preventDefault(); handleBillerAction();">
                <div class="mb-4">
                    <label class="form-label fw-bold" id="lbl-input-field">Consumer Number</label>
                    <input type="text" class="fin-input" placeholder="Enter ID" required id="biller-input">
                </div>
                <div id="additional-fields"></div>
                
                <div id="bill-fetch-area" style="display: none;">
                    <div class="glass-card p-3 mb-4" style="background: rgba(124, 58, 237, 0.05); border: 1px dashed var(--fin-primary);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current Bill</span>
                            <span class="fw-bold" id="fetched-amount">₹ 1,245.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Due Date</span>
                            <span class="text-danger fw-bold" id="fetched-due">28 May, 2026</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Customer</span>
                            <span class="fw-bold">Saranya N.</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="fin-btn w-100" id="btn-fetch-pay">Fetch Bill</button>
            </form>

            <div class="mt-4 text-center">
                <p style="font-size: 0.8rem; color: var(--fin-text-muted);">
                    <i data-lucide="shield-check" size="14"></i> 100% Secure Payments
                </p>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <div class="floating-recharge" onclick="toggleChatbot()" title="AI Assistant">
        <i data-lucide="message-square"></i>
    </div>

    <!-- AI Chatbot -->
    <div id="ai-chatbot">
        <div class="chat-header">
            <div class="d-flex align-items-center gap-2">
                <i data-lucide="bot"></i>
                <span class="fw-bold">Smart Assistant</span>
            </div>
            <i data-lucide="minimize-2" style="cursor: pointer;" onclick="toggleChatbot()"></i>
        </div>
        <div class="chat-messages" id="chat-messages">
            <div class="mb-2"><strong>Bot:</strong> Hi! I can help you find billers or check recharge offers. How can I help?</div>
        </div>
        <form class="chat-input" onsubmit="event.preventDefault(); sendChatMessage();">
            <input type="text" class="form-control rounded-pill border-0 bg-light" placeholder="Type a message..." id="chat-query">
            <button class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i data-lucide="send" size="18"></i>
            </button>
        </form>
    </div>

    <!-- Notification -->
    <div class="notification-popup" id="success-notify">
        <div class="d-flex align-items-center gap-2">
            <i data-lucide="check-circle"></i>
            <span id="notify-msg">Payment Successful!</span>
        </div>
    </div>

    <script>
        // Data Structure for Categories and Billers
        const serviceCategories = [
            { id: 'electricity', title: 'Electricity', icon: 'zap', count: '12 Billers', billers: ['WBSEDCL', 'MSEDCL', 'PSPCL', 'TNEB', 'UHBVN', 'APDCL', 'APEPDCL', 'KESCO', 'BESCOM', 'UGVCL', 'Adani Electricity', 'MGVCL'] },
            { id: 'dth', title: 'DTH', icon: 'tv', count: '5 Operators', billers: ['Tata Play', 'Sun Direct', 'Airtel DTH', 'Dish TV', 'd2h DTH'] },
            { id: 'gas', title: 'Piped Gas', icon: 'flame', count: '6 Billers', billers: ['Mahanagar Gas', 'IGL', 'Adani Gas', 'Torrent Gas', 'Avantika Gas', 'Gujarat Gas'] },
            { id: 'landline', title: 'Landline', icon: 'phone', count: '5 Billers', billers: ['Airtel', 'BSNL Corporate', 'BSNL Consumer', 'MTNL Delhi', 'MTNL Mumbai'] },
            { id: 'mobile', title: 'Mobile Recharge', icon: 'smartphone', count: '4 Operators', billers: ['Airtel Recharge', 'BSNL Recharge', 'Jio Recharge', 'VI Recharge'] },
            { id: 'postpaid', title: 'Mobile Postpaid', icon: 'file-text', count: '4 Operators', billers: ['BSNL Postpaid', 'Jio Postpaid', 'Airtel Postpaid', 'VI Postpaid'] },
            { id: 'lpg', title: 'Cylinder Booking', icon: 'container', count: '3 Billers', billers: ['Bharat Gas', 'HP Gas', 'Indane Gas'] },
            { id: 'fastag', title: 'FASTag', icon: 'car', count: '8 Issuers', billers: ['SBI FASTag', 'ICICI FASTag', 'IHMCL FASTag', 'Paytm FASTag', 'Equitas FASTag', 'Federal FASTag', 'HDFC FASTag', 'AXIS FASTag'] },
            { id: 'water', title: 'Water Bill', icon: 'droplet', count: '3 Billers', billers: ['BWSSB', 'DJB', 'HMWSSB'] },
            { id: 'broadband', title: 'Broadband', icon: 'wifi', count: '3 Billers', billers: ['Airtel Broadband', 'Kerala Vision Broadband', 'ACT Fibernet'] }
        ];

        let currentFilter = 'all';
        let selectedBiller = null;
        let isDarkMode = false;
        let lang = 'EN';

        // Initialize
        function init() {
            // Check for category in URL
            const urlParams = new URLSearchParams(window.location.search);
            const catParam = urlParams.get('cat');
            
            if (catParam) {
                currentFilter = catParam;
                // Find and activate the correct tab
                const tabs = document.querySelectorAll('.category-tab');
                tabs.forEach(t => {
                    if (t.innerText.trim().includes(catParam)) {
                        tabs.forEach(tmp => tmp.classList.remove('active'));
                        t.classList.add('active');
                    }
                });
            }

            renderCategories();
            lucide.createIcons();
        }

        function renderCategories() {
            const grid = document.getElementById('services-grid');
            grid.innerHTML = '';
            
            const filtered = currentFilter === 'all' 
                ? serviceCategories 
                : serviceCategories.filter(c => c.title === currentFilter);

            filtered.forEach(cat => {
                const card = document.createElement('div');
                card.className = 'service-card';
                card.onclick = () => showDetailPanel(cat);
                card.innerHTML = `
                    <div class="service-card-icon"><i data-lucide="${cat.icon}"></i></div>
                    <div class="service-card-title">${cat.title}</div>
                    <div class="service-card-count">${cat.count}</div>
                `;
                grid.appendChild(card);
            });
            lucide.createIcons();
        }

        function filterCategory(catName) {
            currentFilter = catName;
            document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
            event.currentTarget.classList.add('active');
            renderCategories();
            hideDetailPanel();
        }

        function showDetailPanel(cat) {
            document.getElementById('services-grid').style.display = 'none';
            document.getElementById('category-tabs').style.display = 'none';
            document.getElementById('service-detail-panel').style.display = 'block';
            document.getElementById('detail-title').innerText = cat.title + ' Billers';
            
            const bGrid = document.getElementById('biller-grid');
            bGrid.innerHTML = '';
            cat.billers.forEach(b => {
                const item = document.createElement('div');
                item.className = 'biller-item';
                item.onclick = () => openBillerModal(b, cat);
                item.innerHTML = `
                    <div class="fw-bold" style="font-size:0.9rem;">${b}</div>
                `;
                bGrid.appendChild(item);
            });
        }

        function hideDetailPanel() {
            document.getElementById('services-grid').style.display = 'grid';
            document.getElementById('category-tabs').style.display = 'flex';
            document.getElementById('service-detail-panel').style.display = 'none';
        }

        function openBillerModal(biller, cat) {
            selectedBiller = { name: biller, cat: cat };
            document.getElementById('modal-biller-name').innerText = biller;
            document.getElementById('modal-category').innerText = cat.title;
            document.getElementById('modal-icon').setAttribute('data-lucide', cat.icon);
            
            // Customize input label
            let label = "Consumer Number";
            if(cat.id === 'mobile') label = "Mobile Number";
            if(cat.id === 'dth') label = "Subscriber ID";
            if(cat.id === 'lpg') label = "LPG ID";
            if(cat.id === 'fastag') label = "Vehicle Number";
            document.getElementById('lbl-input-field').innerText = label;
            
            document.getElementById('biller-modal').style.display = 'flex';
            document.getElementById('bill-fetch-area').style.display = 'none';
            document.getElementById('btn-fetch-pay').innerText = 'Fetch Bill';
            lucide.createIcons();
        }

        function closeBillerModal() {
            document.getElementById('biller-modal').style.display = 'none';
        }

        function handleBillerAction() {
            const btn = document.getElementById('btn-fetch-pay');
            if (btn.innerText === 'Fetch Bill') {
                btn.innerText = 'Processing...';
                btn.disabled = true;
                
                setTimeout(() => {
                    document.getElementById('bill-fetch-area').style.display = 'block';
                    btn.innerText = 'Pay Now';
                    btn.disabled = false;
                }, 1500);
            } else {
                // Payment success
                showNotification("Payment Successful for " + selectedBiller.name);
                closeBillerModal();
            }
        }

        function showNotification(msg) {
            const n = document.getElementById('success-notify');
            document.getElementById('notify-msg').innerText = msg;
            n.style.display = 'block';
            setTimeout(() => n.style.display = 'none', 3000);
        }

        function handleSearch() {
            const q = document.getElementById('service-search').value.toLowerCase();
            const grid = document.getElementById('services-grid');
            const cards = grid.querySelectorAll('.service-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.service-card-title').innerText.toLowerCase();
                if (title.includes(q)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function toggleChatbot() {
            const chat = document.getElementById('ai-chatbot');
            chat.style.display = chat.style.display === 'flex' ? 'none' : 'flex';
        }

        function sendChatMessage() {
            const query = document.getElementById('chat-query').value;
            if(!query) return;
            
            const msgArea = document.getElementById('chat-messages');
            msgArea.innerHTML += `<div class="mb-2 text-end"><strong>You:</strong> ${query}</div>`;
            document.getElementById('chat-query').value = '';
            
            setTimeout(() => {
                let resp = "I'm looking into that for you. You can find electricity billers in the 'Electricity' section.";
                if(query.toLowerCase().includes('offer')) resp = "Currently we have 15% cashback on all utility bills!";
                msgArea.innerHTML += `<div class="mb-2"><strong>Bot:</strong> ${resp}</div>`;
                msgArea.scrollTop = msgArea.scrollHeight;
            }, 1000);
        }

        function toggleDarkMode() {
            isDarkMode = !isDarkMode;
            document.body.classList.toggle('dark-mode');
            const icon = document.getElementById('theme-icon');
            icon.setAttribute('data-lucide', isDarkMode ? 'sun' : 'moon');
            lucide.createIcons();
        }

        function toggleLanguage() {
            lang = lang === 'EN' ? 'TA' : 'EN';
            document.getElementById('lang-btn').innerText = lang;
            // Add translation logic here if needed
        }

        window.onload = init;
    </script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
