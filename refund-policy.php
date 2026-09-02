<?php
require_once __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Cancellation &amp; Refund Policy — ZAMZY Digital Engineering | Hitech City, Hyderabad</title>
  <meta name="description" content="Cancellation and Refund Policy for ZAMZY Digital Solutions. Clear terms for milestone refunds, project cancellations, and Razorpay/PayPal SLA timelines." />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://zamzy.in/refund-policy.php" />

  <!-- Fonts & Core Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="tooplate-vora-bold-style.css" />

  <style>
    .legal-page-hero {
      padding: 130px 5vw 40px 5vw;
      text-align: center;
      position: relative;
      z-index: 2;
    }
    .legal-hero__eyebrow {
      font-family: var(--mono);
      font-size: 0.75rem;
      letter-spacing: 0.25em;
      color: var(--cyan);
      text-transform: uppercase;
      margin-bottom: 0.8rem;
    }
    .legal-hero__title {
      font-family: var(--display);
      font-size: clamp(2rem, 4.5vw, 3.4rem);
      font-weight: 700;
      color: var(--white);
      margin-bottom: 1rem;
    }
    .legal-hero__meta {
      font-family: var(--mono);
      font-size: 0.8rem;
      color: var(--dim);
    }

    .legal-content-container {
      max-width: 900px;
      margin: 0 auto;
      padding: 20px 5vw 100px 5vw;
      position: relative;
      z-index: 2;
    }
    .legal-box {
      background: rgba(10, 10, 18, 0.85);
      border: 1px solid rgba(6, 182, 212, 0.25);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 3rem 2.5rem;
      color: var(--dim);
      font-family: var(--mono);
      font-size: 0.88rem;
      line-height: 1.8;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.6);
    }
    .legal-box h2 {
      font-family: var(--display);
      font-size: 1.35rem;
      color: var(--white);
      margin: 2rem 0 0.8rem 0;
      padding-bottom: 0.4rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .legal-box h2:first-child {
      margin-top: 0;
    }
    .legal-box p {
      margin-bottom: 1.2rem;
    }
    .legal-box ul {
      margin: 0.5rem 0 1.2rem 1.5rem;
      list-style-type: square;
    }
    .legal-box li {
      margin-bottom: 0.5rem;
    }
    .legal-highlight {
      color: var(--cyan);
      font-weight: 600;
    }
  </style>
</head>
<body>

  <!-- Ambient Cosmic Aura -->
  <div class="aura"></div>

  <!-- Navigation Bar -->
  <nav class="nav">
    <a href="index.html" class="brand-logo-wrap">
      <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
    </a>
    <ul class="nav-links">
      <li><a href="index.html#about">Studio</a></li>
      <li><a href="index.html#launchpad">Launchpad</a></li>
      <li><a href="index.html#products">Products</a></li>
      <li><a href="index.html#services">Services</a></li>
      <li><a href="index.html#testimonials">Reviews</a></li>
      <li><a href="index.html#rates">Rates</a></li>
      <li><a href="careers">Careers</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-menu">
    <a href="index.html#hero">Hero</a>
    <a href="index.html#about">Studio</a>
    <a href="index.html#products">Products</a>
    <a href="index.html#services">Services</a>
    <a href="careers">Careers &amp; Guild</a>
    <a href="contact.php">Contact</a>
  </div>

  <!-- Legal Hero Header -->
  <header class="legal-page-hero">
    <div class="legal-hero__eyebrow">CUSTOMER GUARANTEE &amp; REFUND DISCLOSURE</div>
    <h1 class="legal-hero__title">Cancellation &amp; Refund Policy</h1>
    <div class="legal-hero__meta">
      Entity: ZAMZY DIGITAL SOLUTIONS · Effective Date: January 1, 2026 · Payment Partners: Razorpay &amp; PayPal
    </div>
  </header>

  <!-- Main Legal Document Content -->
  <main class="legal-content-container">
    <div class="legal-box">
      
      <h2>1. Overview &amp; Commitment</h2>
      <p>
        At <span class="legal-highlight">ZAMZY DIGITAL SOLUTIONS</span> ("ZAMZY"), customer satisfaction, transparent milestone deliverables, and fair commercial practices are core to our engineering operations. This Cancellation and Refund Policy outlines your rights regarding project cancellations, advance milestone refunds, and digital service subscription adjustments.
      </p>

      <h2>2. Project Cancellation Terms</h2>
      <p>Clients may cancel custom software development projects subject to the following milestone stages:</p>
      <ul>
        <li><strong>Pre-Initiation Cancellation (Before Work Commences):</strong> If a cancellation request is submitted in writing within 48 hours of making an initial milestone payment and prior to architectural discovery or repository setup, a <span class="legal-highlight">100% full refund</span> will be issued.</li>
        <li><strong>Mid-Milestone In-Progress Cancellation:</strong> If work has commenced under an active Statement of Work (SOW), the client is entitled to a pro-rata refund for uncompleted future milestones. Work completed and approved up to the cancellation date is non-refundable.</li>
        <li><strong>Final Project Completion &amp; Git Transfer:</strong> Once a project milestone is reviewed, approved, and final source code/Git repository ownership is transferred to the client, payments for that milestone are non-refundable.</li>
      </ul>

      <h2>3. Ready-Made SaaS Products &amp; Sandbox Demos</h2>
      <p>
        For pre-built software products (such as CRM Suites, IVR Telephony Gateways, School ERPs, and POS Systems):
      </p>
      <ul>
        <li>Clients are provided free live sandbox demo credentials prior to purchase.</li>
        <li>If a ready-made platform suffers from unresolvable core technical failures that prevent deployment and cannot be rectified by our engineering team within 7 business days, a <span class="legal-highlight">100% full refund</span> is granted.</li>
      </ul>

      <h2>4. Refund Processing SLA &amp; Timelines (Razorpay &amp; PayPal)</h2>
      <p>When a refund is approved by our billing team:</p>
      <ul>
        <li><strong>Processing SLA:</strong> Refunds are initiated within <strong>24 to 48 business hours</strong> of written approval.</li>
        <li><strong>Payment Destination:</strong> Refunds are credited strictly back to the original payment source used during checkout (Original Credit/Debit Card, Netbanking account, UPI VPA, or PayPal balance).</li>
        <li><strong>Crediting Timeline:</strong>
          <ul>
            <li><strong>Razorpay (India - UPI / Netbanking / Cards):</strong> Appears in the bank account within <span class="legal-highlight">5 to 7 business days</span> depending on the issuer bank.</li>
            <li><strong>PayPal (International):</strong> Appears in the PayPal account / card within <span class="legal-highlight">3 to 5 business days</span>.</li>
          </ul>
        </li>
      </ul>

      <h2>5. How to Request a Refund or Cancellation</h2>
      <p>To request a cancellation or submit a refund inquiry, please follow these steps:</p>
      <ol>
        <li>Send an email to <a href="mailto:contact@zamzy.in" class="legal-highlight">contact@zamzy.in</a> with the subject line <strong>"Refund Request - [Invoice / Order ID]"</strong>.</li>
        <li>Include your registered Name, Mobile Number, Project Title, and reason for cancellation.</li>
        <li>Our billing desk will review the milestone state and respond within 24 business hours.</li>
      </ol>

      <h2>6. Contact Details for Refund Queries</h2>
      <p>
        <strong>Billing &amp; Merchant Support Desk:</strong> ZAMZY Digital Solutions<br />
        <strong>Address:</strong> Hitech City, Hyderabad, Telangana 500081, India.<br />
        <strong>Email:</strong> <a href="mailto:contact@zamzy.in" class="legal-highlight">contact@zamzy.in</a> / <a href="mailto:support@zamzy.in" class="legal-highlight">support@zamzy.in</a><br />
        <strong>WhatsApp Support:</strong> <a href="https://wa.me/919876543210" class="legal-highlight">+91 98765 43210</a>
      </p>

    </div>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
        </div>
        <p class="footer-tagline">
          ZAMZY.IN — Engineering High-Performance SaaS Platforms, Mobile Ecosystems &amp; Automated Cloud Systems.
        </p>
        <span class="footer-copy">© 2026 ZAMZY Digital Engineering Agency. All rights reserved.</span>
      </div>

      <div>
        <div class="footer-col-title">Navigation</div>
        <div class="footer-links-col">
          <a href="index.html#hero">Home</a>
          <a href="index.html#about">Studio</a>
          <a href="index.html#launchpad">Launchpad</a>
          <a href="index.html#products">Products</a>
          <a href="index.html#services">Services</a>
          <a href="careers">Careers &amp; Guild</a>
          <a href="contact.php">Contact Us</a>
        </div>
      </div>

      <div>
        <div class="footer-col-title">Merchant Policies</div>
        <div class="footer-links-col">
          <a href="privacy-policy.php">Privacy Policy</a>
          <a href="terms-and-conditions.php">Terms &amp; Conditions</a>
          <a href="refund-policy.php" style="color:var(--cyan);">Refund &amp; Cancellation</a>
          <a href="shipping-policy.php">Shipping &amp; Delivery</a>
          <a href="contact.php">Contact Support</a>
        </div>
      </div>

      <div>
        <div class="footer-col-title">Engineering Office</div>
        <div class="footer-links-col" style="font-size:0.82rem; color:var(--dim); line-height:1.7;">
          <p><strong>ZAMZY DIGITAL SOLUTIONS</strong></p>
          <p>📍 Hitech City, Hyderabad, Telangana, India</p>
          <p>💬 <a href="https://wa.me/919876543210" target="_blank" style="color:var(--cyan); text-decoration:underline;">+91 98765 43210 (WhatsApp)</a></p>
          <p>✉️ <a href="mailto:contact@zamzy.in" style="color:var(--cyan); text-decoration:underline;">contact@zamzy.in</a></p>
        </div>
      </div>
    </div>

    <div class="footer-bottom-bar">
      <div class="footer-bottom-text">
        ⚡ High-Concurrency Systems · 99.9% Uptime SLA · Hitech City, Hyderabad
      </div>
      <div class="footer-social-links">
        <a href="privacy-policy.php" class="footer-social-link">Privacy Policy</a>
        <a href="terms-and-conditions.php" class="footer-social-link">Terms &amp; Conditions</a>
        <a href="refund-policy.php" class="footer-social-link">Refund Policy</a>
        <a href="shipping-policy.php" class="footer-social-link">Shipping Policy</a>
      </div>
    </div>
  </footer>

  <script src="tooplate-vora-bold-script.js"></script>
</body>
</html>
