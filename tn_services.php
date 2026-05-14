<?php
session_start();
require_once 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tamil Nadu Recharge & Bill Payments - SmartRecharge</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="tn_style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .tn-hero {
            padding: 4rem 0;
            background: radial-gradient(circle at 10% 20%, rgba(139, 92, 246, 0.05) 0%, rgba(236, 72, 153, 0.05) 90%);
            border-radius: 40px;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }
        .tn-hero-img {
            max-width: 100%;
            filter: drop-shadow(0 20px 40px rgba(139, 92, 246, 0.3));
        }
        .service-panel {
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
            padding: 1.5rem;
        }
        .panel-inner {
            background: var(--tn-bg-end);
            max-width: 550px;
            width: 100%;
            border-radius: 32px;
            padding: 3rem;
            position: relative;
            box-shadow: 0 50px 100px -20px rgba(0,0,0,0.3);
        }
        .dark-mode .panel-inner { background: #1e293b; }
        
        .close-btn {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            cursor: pointer;
            color: var(--tn-text-muted);
        }

        .usage-chart {
            height: 100px;
            display: flex;
            align-items: flex-end;
            gap: 4px;
            margin: 1.5rem 0;
        }
        .bar {
            flex: 1;
            background: var(--tn-primary);
            border-radius: 4px 4px 0 0;
            opacity: 0.6;
            transition: height 1s ease;
        }
        .bar:hover { opacity: 1; }

        /* Notification */
        .tn-notify {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: #22c55e;
            color: white;
            padding: 1rem 2rem;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.3);
            display: none;
            z-index: 3000;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>
<body class="tn-body">

    <?php include 'header.php'; ?>

    <div class="container py-5">
        <!-- Dashboard Header -->
        <div class="d-flex justify-content-between align-items-center mb-5 mt-4">
            <div>
                <h1 class="tn-header-gradient" style="font-size: 2.5rem; margin-bottom: 0.5rem;" id="txt-title">தமிழ்நாடு ரீசார்ஜ் & பில் சேவைகள்</h1>
                <p class="text-muted fw-500" id="txt-subtitle">Tamil Nadu Digital Service Dashboard</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="lang-switch">
                    <div class="lang-btn active" onclick="switchLang('EN')" id="btn-en">EN</div>
                    <div class="lang-btn" onclick="switchLang('TA')" id="btn-ta">தமிழ்</div>
                </div>
                <div class="icon-btn" onclick="toggleDarkMode()">
                    <i data-lucide="moon" id="theme-icon"></i>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <div class="tn-hero p-5 d-flex align-items-center">
            <div class="row w-100 align-items-center">
                <div class="col-lg-6">
                    <span class="tn-badge mb-3">Special Offer</span>
                    <h2 style="font-weight: 800; font-size: 2.5rem; margin-bottom: 1.5rem;" id="hero-title">Happy Pongal! Get 20% Cashback on TANGEDCO Bills</h2>
                    <p class="text-muted mb-4" id="hero-desc">Experience the future of digital payments in Tamil Nadu. Secure, fast, and local.</p>
                    <button class="tn-btn" style="width: auto; padding: 1rem 2.5rem;" onclick="openService('Electricity')">Pay EB Bill Now</button>
                </div>
                <div class="col-lg-6 text-center d-none d-lg-block">
                    <img src="tamil_nadu_digital_portal_hero_1778784439571.png" alt="TN Portal" class="tn-hero-img floating">
                </div>
            </div>
        </div>

        <!-- Service Grid -->
        <div class="tn-grid">
            <!-- Electricity -->
            <div class="tn-glass-card tn-service-card" onclick="openService('Electricity')">
                <div class="tn-icon-wrapper" style="color: #fbbf24;"><i data-lucide="zap"></i></div>
                <h4 class="fw-bold">Electricity</h4>
                <p class="text-muted small">TANGEDCO / TNEB</p>
                <span class="tn-badge">Most Used</span>
            </div>

            <!-- Water -->
            <div class="tn-glass-card tn-service-card" onclick="openService('Water')">
                <div class="tn-icon-wrapper" style="color: #0ea5e9;"><i data-lucide="droplets"></i></div>
                <h4 class="fw-bold">Water</h4>
                <p class="text-muted small">Metro Water / TWAD</p>
            </div>

            <!-- Mobile -->
            <div class="tn-glass-card tn-service-card" onclick="openService('Mobile')">
                <div class="tn-icon-wrapper" style="color: #8b5cf6;"><i data-lucide="smartphone"></i></div>
                <h4 class="fw-bold">Mobile</h4>
                <p class="text-muted small">Airtel, Jio, VI, BSNL</p>
            </div>

            <!-- DTH -->
            <div class="tn-glass-card tn-service-card" onclick="openService('DTH')">
                <div class="tn-icon-wrapper" style="color: #ec4899;"><i data-lucide="tv"></i></div>
                <h4 class="fw-bold">DTH</h4>
                <p class="text-muted small">Sun Direct, Tata Play</p>
            </div>

            <!-- Gas -->
            <div class="tn-glass-card tn-service-card" onclick="openService('Gas')">
                <div class="tn-icon-wrapper" style="color: #f97316;"><i data-lucide="flame"></i></div>
                <h4 class="fw-bold">Gas Booking</h4>
                <p class="text-muted small">Indane, HP, Bharat</p>
            </div>

            <!-- FASTag -->
            <div class="tn-glass-card tn-service-card" onclick="openService('FASTag')">
                <div class="tn-icon-wrapper" style="color: #10b981;"><i data-lucide="car"></i></div>
                <h4 class="fw-bold">FASTag</h4>
                <p class="text-muted small">Toll Recharge</p>
            </div>

            <!-- Broadband -->
            <div class="tn-glass-card tn-service-card" onclick="openService('Broadband')">
                <div class="tn-icon-wrapper" style="color: #6366f1;"><i data-lucide="wifi"></i></div>
                <h4 class="fw-bold">Broadband</h4>
                <p class="text-muted small">ACT, Airtel, JioFiber</p>
            </div>

            <!-- EV Recharge -->
            <div class="tn-glass-card tn-service-card" onclick="openService('EV')">
                <div class="tn-icon-wrapper" style="color: #22c55e;"><i data-lucide="battery-charging"></i></div>
                <h4 class="fw-bold">EV Recharge</h4>
                <p class="text-muted small">Tata, Ather, Statiq</p>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="mt-5 pt-5">
            <h3 class="fw-bold mb-4">Recent Transactions</h3>
            <div class="tn-glass-card p-0 overflow-hidden">
                <table class="table table-borderless mb-0">
                    <tbody id="history-body">
                        <tr style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <td class="p-4"><i data-lucide="zap" class="text-warning"></i></td>
                            <td class="p-4"><strong>TANGEDCO Payment</strong><br><small class="text-muted">14 May 2026</small></td>
                            <td class="p-4">Cons ID: 10928374</td>
                            <td class="p-4 text-end fw-bold">₹ 1,450.00</td>
                        </tr>
                        <tr>
                            <td class="p-4"><i data-lucide="smartphone" class="text-primary"></i></td>
                            <td class="p-4"><strong>Airtel Recharge</strong><br><small class="text-muted">12 May 2026</small></td>
                            <td class="p-4">9840XXXXXX</td>
                            <td class="p-4 text-end fw-bold">₹ 299.00</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Service Detail Panel -->
    <div class="service-panel" id="service-panel">
        <div class="panel-inner">
            <div class="close-btn" onclick="closeService()"><i data-lucide="x"></i></div>
            <div class="text-center mb-4">
                <div class="tn-icon-wrapper mb-3" id="panel-icon-bg">
                    <i data-lucide="zap" id="panel-icon"></i>
                </div>
                <h3 class="fw-bold" id="panel-title">Electricity Bill</h3>
                <p class="text-muted" id="panel-subtitle">TANGEDCO / TNEB</p>
            </div>

            <form id="service-form" onsubmit="event.preventDefault(); processService();">
                <div class="mb-4">
                    <label class="form-label fw-600" id="lbl-main-input">Consumer Number</label>
                    <input type="text" class="form-control fin-input p-3 rounded-4 border-2" placeholder="e.g. 10928374" required id="main-input">
                </div>

                <!-- Bill Preview Area (Hidden) -->
                <div id="bill-preview" style="display: none;">
                    <div class="usage-chart" id="usage-chart">
                        <div class="bar" style="height: 40%;"></div>
                        <div class="bar" style="height: 60%;"></div>
                        <div class="bar" style="height: 45%;"></div>
                        <div class="bar" style="height: 80%;"></div>
                        <div class="bar" style="height: 50%;"></div>
                        <div class="bar" style="height: 90%;"></div>
                    </div>
                    <div class="tn-glass-card p-3 mb-4" style="background: rgba(139, 92, 246, 0.05); border: 2px dashed var(--tn-primary);">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current Bill</span>
                            <span class="fw-800 fs-5" id="val-amount">₹ 850.00</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Due Date</span>
                            <span class="text-danger fw-bold">25 May, 2026</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="tn-btn py-3" id="btn-action">Fetch Bill</button>
            </form>

            <div class="text-center mt-4">
                <img src="https://upload.wikimedia.org/wikipedia/en/thumb/5/53/Tamil_Nadu_Government_Logo.png/150px-Tamil_Nadu_Government_Logo.png" alt="TN Govt" style="width: 40px; opacity: 0.5;">
                <p class="small text-muted mt-2">Authorized TN Service Portal</p>
            </div>
        </div>
    </div>

    <!-- AI Voice Assistant -->
    <div class="floating-support" onclick="startVoiceAssistant()" title="Tamil Voice Assistant">
        <i data-lucide="mic"></i>
    </div>

    <!-- Success Notify -->
    <div class="tn-notify" id="success-notify">
        <div class="d-flex align-items-center gap-3">
            <i data-lucide="check-circle" size="30"></i>
            <div>
                <strong id="notif-title">Success!</strong>
                <p class="mb-0 small" id="notif-msg">Payment processed successfully.</p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        let currentLang = 'EN';
        let isDarkMode = false;
        let activeCat = '';

        const translations = {
            'EN': {
                'title': 'Tamil Nadu Recharge & Bill Payments',
                'subtitle': 'Tamil Nadu Digital Service Dashboard',
                'hero-title': 'Happy Pongal! Get 20% Cashback on TANGEDCO Bills',
                'hero-desc': 'Experience the future of digital payments in Tamil Nadu. Secure, fast, and local.',
                'btn-eb': 'Pay EB Bill Now',
                'lbl-eb': 'Consumer Number',
                'btn-fetch': 'Fetch Bill',
                'notif-success': 'Payment Successful!'
            },
            'TA': {
                'title': 'தமிழ்நாடு ரீசார்ஜ் & பில் சேவைகள்',
                'subtitle': 'தமிழ்நாடு டிஜிட்டல் சேவை போர்டல்',
                'hero-title': 'இனிய பொங்கல் வாழ்த்துகள்! மின்சார பில்லில் 20% கேஷ்பேக்',
                'hero-desc': 'தமிழ்நாட்டின் டிஜிட்டல் கட்டணங்களின் எதிர்காலத்தை அனுபவியுங்கள். பாதுகாப்பானது மற்றும் விரைவானது.',
                'btn-eb': 'மின்சார பில் செலுத்தவும்',
                'lbl-eb': 'நுகர்வோர் எண்',
                'btn-fetch': 'பில்லை சரிபார்க்கவும்',
                'notif-success': 'கட்டணம் வெற்றிகரமாக செலுத்தப்பட்டது!'
            }
        };

        function switchLang(l) {
            currentLang = l;
            document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
            document.getElementById('btn-' + l.toLowerCase()).classList.add('active');
            
            const t = translations[l];
            document.getElementById('txt-title').innerText = t['title'];
            document.getElementById('txt-subtitle').innerText = t['subtitle'];
            document.getElementById('hero-title').innerText = t['hero-title'];
            document.getElementById('hero-desc').innerText = t['hero-desc'];
        }

        function toggleDarkMode() {
            isDarkMode = !isDarkMode;
            document.body.classList.toggle('dark-mode');
            const icon = document.getElementById('theme-icon');
            icon.setAttribute('data-lucide', isDarkMode ? 'sun' : 'moon');
            lucide.createIcons();
        }

        function openService(cat) {
            activeCat = cat;
            const panel = document.getElementById('service-panel');
            panel.style.display = 'flex';
            
            document.getElementById('panel-title').innerText = cat;
            document.getElementById('bill-preview').style.display = 'none';
            document.getElementById('btn-action').innerText = translations[currentLang]['btn-fetch'];
            
            // Customize icon
            const icons = {
                'Electricity': { icon: 'zap', color: '#fbbf24', sub: 'TANGEDCO / TNEB', label: 'Consumer Number' },
                'Water': { icon: 'droplets', color: '#0ea5e9', sub: 'Metro Water / TWAD', label: 'Connection Number' },
                'Mobile': { icon: 'smartphone', color: '#8b5cf6', sub: 'Prepaid / Postpaid', label: 'Mobile Number' },
                'Gas': { icon: 'flame', color: '#f97316', sub: 'LPG Booking', label: 'LPG ID' }
            };
            
            const config = icons[cat] || icons['Electricity'];
            document.getElementById('panel-icon').setAttribute('data-lucide', config.icon);
            document.getElementById('panel-subtitle').innerText = config.sub;
            document.getElementById('lbl-main-input').innerText = config.label;
            
            lucide.createIcons();
        }

        function closeService() {
            document.getElementById('service-panel').style.display = 'none';
        }

        function processService() {
            const btn = document.getElementById('btn-action');
            if (btn.innerText.includes('Fetch') || btn.innerText.includes('சரிபார்க்கவும்')) {
                btn.innerText = 'Searching...';
                setTimeout(() => {
                    document.getElementById('bill-preview').style.display = 'block';
                    btn.innerText = 'Pay Now';
                }, 1500);
            } else {
                showNotify();
                closeService();
            }
        }

        function showNotify() {
            const n = document.getElementById('success-notify');
            n.style.display = 'block';
            setTimeout(() => n.style.display = 'none', 3000);
        }

        function startVoiceAssistant() {
            const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = 'ta-IN';
            
            recognition.onstart = () => {
                alert("சொல்லுங்கள்... (Say something like 'EB Bill')");
            };

            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript.toLowerCase();
                if (transcript.includes('ஈபி') || transcript.includes('eb') || transcript.includes('மின்சாரம்')) {
                    openService('Electricity');
                }
            };
            recognition.start();
        }
    </script>
</body>
</html>
