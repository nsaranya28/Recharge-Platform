<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Smart Recharge</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .contact-wrapper {
            max-width: 1000px;
            margin: 4rem auto;
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 3rem;
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        }

        .contact-info h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--primary), #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .contact-info p {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }

        .info-cards {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .info-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--bg-color);
            border-radius: 16px;
            transition: transform 0.3s ease;
        }

        .info-card:hover {
            transform: translateX(10px);
        }

        .info-card .icon {
            font-size: 1.5rem;
            width: 50px;
            height: 50px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .info-card .details h4 {
            margin: 0;
            font-size: 1rem;
            color: var(--text-main);
        }

        .info-card .details p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        .form-group input, .form-group textarea {
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            outline: none;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
        }

        @media (max-width: 768px) {
            .contact-wrapper {
                grid-template-columns: 1fr;
                padding: 2rem;
                margin: 2rem 1rem;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <div class="contact-wrapper">
        <div class="contact-info">
            <h2>Get in Touch</h2>
            <p>Have questions about our recharge plans? We're here to help you 24/7.</p>
            
            <div class="info-cards">
                <div class="info-card">
                    <div class="icon">📍</div>
                    <div class="details">
                        <h4>Our Location</h4>
                        <p>123 Digital Plaza, Tech City</p>
                    </div>
                </div>
                <div class="info-card">
                    <div class="icon">📧</div>
                    <div class="details">
                        <h4>Email Us</h4>
                        <p>support@smartrecharge.com</p>
                    </div>
                </div>
                <div class="info-card">
                    <div class="icon">📞</div>
                    <div class="details">
                        <h4>Call Us</h4>
                        <p>+1 (555) 000-1234</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" placeholder="Enter your name">
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" placeholder="Enter your email">
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" placeholder="What is this about?">
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" rows="5" placeholder="How can we help?"></textarea>
            </div>
            <button type="submit" class="btn-apply">Send Message</button>
        </div>
    </div>
</div>

</body>
</html>
