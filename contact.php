<?php
require_once __DIR__ . '/db.php';
$pdo = getDbConnection();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Contact ZAMZY — Technology, Digital Marketing &amp; Media Production Agency</title>
  <meta name="description"
    content="Get in touch with ZAMZY. We specialize in custom software development, high-converting digital marketing campaigns, and professional video editing &amp; media production." />
  <meta name="keywords"
    content="Contact ZAMZY, Digital Agency Hyderabad, Software Development, Custom Web Apps, Digital Marketing Agency, Video Editing Services, Meta Ads, SEO Agency, ZAMZY Contact, Zamzy.in" />
  <meta name="robots" content="index, follow" />
  <link rel="canonical" href="https://zamzy.in/contact.php" />

  <!-- Meta Pixel Code -->
  <script>
    !function (f, b, e, v, n, t, s) {
      if (f.fbq) return; n = f.fbq = function () {
        n.callMethod ?
          n.callMethod.apply(n, arguments) : n.queue.push(arguments)
      };
      if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
      n.queue = []; t = b.createElement(e); t.async = !0;
      t.src = v; s = b.getElementsByTagName(e)[0];
      s.parentNode.insertBefore(t, s)
    }(window, document, 'script',
      'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '1063958663209939');
    fbq('track', 'PageView');
  </script>
  <noscript><img height="1" width="1" style="display:none"
      src="https://www.facebook.com/tr?id=1063958663209939&ev=PageView&noscript=1" /></noscript>
  <!-- End Meta Pixel Code -->

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://zamzy.in/contact.php" />
  <meta property="og:title" content="Contact ZAMZY — Tech, Digital Marketing &amp; Video Editing Agency" />
  <meta property="og:description"
    content="Submit your project brief to ZAMZY. We deliver custom software, performance marketing, SEO, and high-impact video editing." />
  <meta property="og:image" content="https://zamzy.in/images/logo.png" />

  <!-- Fonts & Core Styles -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&family=Barlow+Condensed:wght@400;600;700&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="tooplate-vora-bold-style.css" />

  <style>
    .contact-page-hero {
      padding: 130px 5vw 40px 5vw;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    .contact-page-hero__eyebrow {
      font-family: var(--mono);
      font-size: 0.75rem;
      letter-spacing: 0.25em;
      color: var(--cyan);
      text-transform: uppercase;
      margin-bottom: 0.8rem;
    }

    .contact-page-hero__title {
      font-family: var(--display);
      font-size: clamp(2.2rem, 5vw, 3.8rem);
      font-weight: 700;
      line-height: 1.12;
      color: var(--white);
      max-width: 900px;
      margin: 0 auto 1.2rem auto;
    }

    .contact-page-hero__desc {
      font-family: var(--mono);
      font-size: clamp(0.85rem, 1.3vw, 1rem);
      color: var(--dim);
      max-width: 660px;
      margin: 0 auto;
      line-height: 1.7;
    }

    .contact-main-section {
      padding: 40px 5vw 80px 5vw;
      position: relative;
      z-index: 2;
    }

    .contact-grid-container {
      display: grid;
      grid-template-columns: 1fr 1.3fr;
      gap: 2.5rem;
      max-width: 1200px;
      margin: 0 auto;
    }

    @media (max-width: 992px) {
      .contact-grid-container {
        display: flex;
        flex-direction: column;
      }

      .contact-form-wrapper {
        order: 1;
      }

      .contact-info-cards {
        order: 2;
      }
    }

    .contact-info-cards {
      display: flex;
      flex-direction: column;
      gap: 1.4rem;
    }

    .contact-card-box {
      background: rgba(255, 255, 255, 0.025);
      border: 1px solid rgba(255, 255, 255, 0.09);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-radius: 16px;
      padding: 1.6rem 1.4rem;
      transition: var(--transition);
    }

    .contact-card-box:hover {
      border-color: rgba(6, 182, 212, 0.45);
      box-shadow: 0 0 25px rgba(6, 182, 212, 0.18);
      transform: translateY(-2px);
    }

    .contact-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 0.8rem;
    }

    .contact-card-icon {
      font-size: 1.4rem;
      width: 42px;
      height: 42px;
      border-radius: 10px;
      background: rgba(6, 182, 212, 0.12);
      border: 1px solid rgba(6, 182, 212, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .contact-card-title {
      font-family: var(--display);
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--white);
    }

    .contact-card-text {
      font-family: var(--mono);
      font-size: 0.84rem;
      color: var(--dim);
      line-height: 1.65;
    }

    .contact-card-link {
      color: var(--cyan);
      text-decoration: underline;
      font-weight: 600;
    }

    .contact-form-wrap {
      background: rgba(10, 10, 18, 0.85);
      border: 1px solid rgba(6, 182, 212, 0.35);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-radius: 20px;
      padding: 2.2rem 2rem;
      box-shadow: 0 0 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(6, 182, 212, 0.12);
    }

    .contact-form-title {
      font-family: var(--display);
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 0.4rem;
    }

    .contact-form-sub {
      font-family: var(--mono);
      font-size: 0.82rem;
      color: var(--dim);
      margin-bottom: 1.8rem;
    }

    .faq-section {
      padding: 60px 5vw 90px 5vw;
      position: relative;
      z-index: 2;
      max-width: 1200px;
      margin: 0 auto;
    }

    .faq-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 1.4rem;
      margin-top: 2rem;
    }

    .faq-card {
      background: rgba(255, 255, 255, 0.02);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 14px;
      padding: 1.5rem;
    }

    .faq-q {
      font-family: var(--display);
      font-size: 1.05rem;
      font-weight: 700;
      color: var(--white);
      margin-bottom: 0.6rem;
    }

    .faq-a {
      font-family: var(--mono);
      font-size: 0.82rem;
      color: var(--dim);
      line-height: 1.6;
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
      <li><a href="contact.php" class="active" style="color:var(--cyan);">Contact</a></li>
    </ul>
    <button class="menu-toggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </nav>

  <!-- Mobile Drawer Menu -->
  <div class="mobile-menu">
    <a href="index.html#hero">Hero</a>
    <a href="index.html#about">Studio</a>
    <a href="index.html#launchpad">Launchpad</a>
    <a href="index.html#products">Products</a>
    <a href="index.html#services">Services</a>
    <a href="index.html#testimonials">Reviews</a>
    <a href="index.html#rates">Rates</a>
    <a href="careers">Careers &amp; Guild</a>
    <a href="contact.php" style="color:var(--cyan);">Contact</a>
  </div>

  <!-- Hero Section -->
  <header class="contact-page-hero">
    <div class="contact-page-hero__eyebrow">GET IN TOUCH // TECH • MARKETING • VIDEO EDITING</div>
    <h1 class="contact-page-hero__title">Contact ZAMZY for 360° Growth</h1>
    <p class="contact-page-hero__desc">
      Looking for custom software development, ROI-driven digital marketing campaigns, or professional video &amp; media editing? Submit your project requirements below for a 24-hour response &amp; custom proposal.
    </p>
  </header>

  <!-- Main Contact Section -->
  <section class="contact-main-section">
    <div class="contact-grid-container">

      <!-- Left Column: Direct Info Cards -->
      <div class="contact-info-cards">

        <!-- Card 1: Creative & Tech Studio -->
        <div class="contact-card-box">
          <div class="contact-card-header">
            <div class="contact-card-icon">📍</div>
            <div class="contact-card-title">Tech &amp; Media Studio</div>
          </div>
          <div class="contact-card-text">
            <strong>ZAMZY DIGITAL SOLUTIONS</strong><br />
            Hitech City, Hyderabad, Telangana, India<br />
            <span style="color:var(--faint); font-size:0.78rem;">Full-stack hub for software development, performance marketing, video editing &amp; branding.</span>
          </div>
        </div>

        <!-- Card 2: WhatsApp & Telephony -->
        <div class="contact-card-box">
          <div class="contact-card-header">
            <div class="contact-card-icon">💬</div>
            <div class="contact-card-title">WhatsApp &amp; Direct Line</div>
          </div>
          <div class="contact-card-text">
            Direct Lead Consultant Line:<br />
            <a href="https://wa.me/919876543210?text=Hi%20ZAMZY%2C%20I%20have%20a%20project%20query%20for%20Tech%2FMarketing%2FEditing"
              target="_blank" class="contact-card-link">+91 98765 43210 (WhatsApp Chat)</a><br />
            <span style="color:var(--faint); font-size:0.78rem;">Immediate strategy consultation &amp; sample portfolio showcase.</span>
          </div>
        </div>

        <!-- Card 3: Email Support -->
        <div class="contact-card-box">
          <div class="contact-card-header">
            <div class="contact-card-icon">✉️</div>
            <div class="contact-card-title">Email Inquiries</div>
          </div>
          <div class="contact-card-text">
            Official Inquiry Inbox:<br />
            <a href="mailto:contact@zamzy.in" class="contact-card-link">contact@zamzy.in</a> / <a
              href="mailto:hello@zamzy.in" class="contact-card-link">hello@zamzy.in</a><br />
            <span style="color:var(--faint); font-size:0.78rem;">24-hour response SLA on all RFPs, marketing briefs &amp; editing scopes.</span>
          </div>
        </div>

        <!-- Card 4: Operating Hours & Guarantees -->
        <div class="contact-card-box">
          <div class="contact-card-header">
            <div class="contact-card-icon">🛡️</div>
            <div class="contact-card-title">100% Client Quality Guarantee</div>
          </div>
          <div class="contact-card-text">
            • <strong>100% IP &amp; Asset Ownership:</strong> Full source code, raw video files &amp; campaign ownership.<br />
            • <strong>NDAs Available:</strong> Signed prior to reviewing confidential business concepts.<br />
            • <strong>Operating Hours:</strong> Mon–Sat 09:00–19:00 IST.
          </div>
        </div>

      </div>

      <!-- Right Column: Interactive Intake Form -->
      <div class="contact-form-wrap">
        <h2 class="contact-form-title">Submit Project Brief</h2>
        <p class="contact-form-sub">Fill in your tech, marketing, or video editing requirements for a free strategy consultation &amp; proposal.
        </p>

        <form id="contact-page-form">
          <div class="form-grid">

            <!-- 1. Full Name -->
            <div class="form-group">
              <label class="form-label" for="contact-name">Your Name <span class="req">*</span></label>
              <input type="text" id="contact-name" class="form-input" placeholder="e.g. Rahul Sharma" required />
            </div>

            <!-- 2. WhatsApp / Phone -->
            <div class="form-group">
              <label class="form-label" for="contact-phone">WhatsApp / Mobile Number <span class="req">*</span></label>
              <input type="tel" id="contact-phone" class="form-input" placeholder="e.g. +91 98765 43210" required />
            </div>

            <!-- 3. Work Email -->
            <div class="form-group">
              <label class="form-label" for="contact-email">Work Email Address <span class="req">*</span></label>
              <input type="email" id="contact-email" class="form-input" placeholder="e.g. rahul@company.com" required />
            </div>

            <!-- 4. Preferred Language -->
            <div class="form-group">
              <label class="form-label" for="contact-lang">Preferred Language <span class="req">*</span></label>
              <input list="lang-list-contact" type="text" id="contact-lang" class="form-input"
                placeholder="e.g. English, Tamil, Hindi" value="English" required />
              <datalist id="lang-list-contact">
                <option value="English">
                <option value="Tamil (தமிழ்)">
                <option value="Telugu (తెలుగు)">
                <option value="Hindi (हिंदी)">
                <option value="Kannada (ಕನ್ನಡ)">
                <option value="Malayalam (മലയാളം)">
              </datalist>
            </div>

            <!-- 5. Estimated Budget -->
            <div class="form-group">
              <label class="form-label" for="contact-budget">Estimated Budget <span class="req">*</span></label>
              <input list="budget-list-contact" type="text" id="contact-budget" class="form-input"
                placeholder="e.g. ₹25,000 – ₹75,000" value="₹25,000 – ₹75,000 (Custom Web & App)" required />
              <datalist id="budget-list-contact">
                <option value="₹5,000 – ₹15,000 (Video Editing / Ads / Branding)">
                <option value="₹15,000 – ₹35,000/mo (Digital Marketing / SEO)">
                <option value="₹25,000 – ₹75,000 (Custom Web & App Development)">
                <option value="₹75,000+ (Enterprise SaaS & Full Growth)">
              </datalist>
            </div>

            <!-- 6. Project Type -->
            <div class="form-group">
              <label class="form-label" for="contact-type">Project Type</label>
              <input list="type-list-contact" type="text" id="contact-type" class="form-input"
                placeholder="e.g. Tech, Marketing, or Editing" value="Technology & Software Development" />
              <datalist id="type-list-contact">
                <option value="Technology & Software Development">
                <option value="Website & Mobile App Development">
                <option value="Digital Marketing & Meta/Google Ads">
                <option value="SEO & Social Media Management">
                <option value="Video Editing & Reels Production">
                <option value="Graphic Design & Brand Identity">
                <option value="School / Restaurant ERP Software">
                <option value="WhatsApp Automation & AI Chatbots">
              </datalist>
            </div>

            <!-- 7. Requirements -->
            <div class="form-group full-width">
              <label class="form-label" for="contact-reqs">Project Description &amp; Scope <span
                  class="req">*</span></label>
              <textarea id="contact-reqs" class="form-textarea"
                placeholder="Describe what you want to build, key features needed, and your target timeline..."
                required></textarea>
            </div>

          </div>

          <p class="form-disclaimer">
            🔒 By submitting this form, your brief is logged in the ZAMZY database. 100% confidential. Zero spam.
          </p>

          <button type="submit" class="fancy" id="contact-submit-btn" style="width: 100%; margin-top: 1rem;">
            <span class="top-key"></span>
            <span class="text">Submit Brief &amp; Get Free Scope →</span>
            <span class="bottom-key-1"></span>
            <span class="bottom-key-2"></span>
          </button>
        </form>

      </div>

    </div>
  </section>

  <!-- FAQ Section (Infinite Auto-Scrolling Marquee) -->
  <section class="faq-section">
    <div style="text-align:center;">
      <span class="badge">Frequently Asked Questions</span>
      <h2 class="section-title" style="margin-top:0.8rem;">Common Client Inquiries</h2>
      <p style="font-family:var(--mono); font-size:0.8rem; color:var(--dim); margin-top:0.4rem;">
        Hover or tap any card to pause auto-scrolling
      </p>
    </div>

    <div class="faq-marquee-wrapper">
      <div class="faq-track">
        <!-- Set 1: 6 FAQ Cards -->
        <div class="faq-scroll-card">
          <span class="faq-scroll-badge">⚡ SLA &amp; Response</span>
          <div class="faq-scroll-q">How fast will I hear back after submitting?</div>
          <div class="faq-scroll-a">Our Technical Lead reviews all submitted briefs and responds via WhatsApp and Email
            within 24 to 48 business hours with a clear initial scope breakdown.</div>
        </div>

        <div class="faq-scroll-card">
          <span class="faq-scroll-badge">🔒 NDA &amp; Security</span>
          <div class="faq-scroll-q">Do you execute mutual NDAs prior to discussion?</div>
          <div class="faq-scroll-a">Yes. If your business project involves proprietary algorithms or unannounced IP, we
            sign standard mutual NDAs before receiving sensitive data.</div>
        </div>

        <div class="faq-scroll-card">
          <span class="faq-scroll-badge">💻 100% IP Ownership</span>
          <div class="faq-scroll-q">Who owns the intellectual property and code?</div>
          <div class="faq-scroll-a">You do. 100% of the code, database schemas, and infrastructure deployment scripts
            are transferred to your company’s Git repository upon invoice clearance.</div>
        </div>

        <div class="faq-scroll-card">
          <span class="faq-scroll-badge">🚀 Sandbox Demos</span>
          <div class="faq-scroll-q">Can I request a live demo of your ready-made platforms?</div>
          <div class="faq-scroll-a">Absolutely. Submit your contact details above or click "Request Demo" on any product
            card across our site for immediate sandbox credentials.</div>
        </div>

        <div class="faq-scroll-card">
          <span class="faq-scroll-badge">🛠 Technology Stack</span>
          <div class="faq-scroll-q">What tech stack do you use for SaaS &amp; Mobile?</div>
          <div class="faq-scroll-a">We build with Next.js, TypeScript, Flutter, Node.js, NestJS, Go, Python, and
            PostgreSQL with Docker &amp; Cloudflare edge infrastructure.</div>
        </div>

        <div class="faq-scroll-card">
          <span class="faq-scroll-badge">📈 Post-Launch Support</span>
          <div class="faq-scroll-q">Do you offer ongoing post-launch maintenance?</div>
          <div class="faq-scroll-a">Yes. All projects include a 30-day post-launch guarantee plus optional monthly
            retainer squads for continuous feature updates &amp; SLA monitoring.</div>
        </div>

        <!-- Set 2: Duplicated for Seamless Loop -->
        <div class="faq-scroll-card" aria-hidden="true">
          <span class="faq-scroll-badge">⚡ SLA &amp; Response</span>
          <div class="faq-scroll-q">How fast will I hear back after submitting?</div>
          <div class="faq-scroll-a">Our Technical Lead reviews all submitted briefs and responds via WhatsApp and Email
            within 24 to 48 business hours with a clear initial scope breakdown.</div>
        </div>

        <div class="faq-scroll-card" aria-hidden="true">
          <span class="faq-scroll-badge">🔒 NDA &amp; Security</span>
          <div class="faq-scroll-q">Do you execute mutual NDAs prior to discussion?</div>
          <div class="faq-scroll-a">Yes. If your business project involves proprietary algorithms or unannounced IP, we
            sign standard mutual NDAs before receiving sensitive data.</div>
        </div>

        <div class="faq-scroll-card" aria-hidden="true">
          <span class="faq-scroll-badge">💻 100% IP Ownership</span>
          <div class="faq-scroll-q">Who owns the intellectual property and code?</div>
          <div class="faq-scroll-a">You do. 100% of the code, database schemas, and infrastructure deployment scripts
            are transferred to your company’s Git repository upon invoice clearance.</div>
        </div>

        <div class="faq-scroll-card" aria-hidden="true">
          <span class="faq-scroll-badge">🚀 Sandbox Demos</span>
          <div class="faq-scroll-q">Can I request a live demo of your ready-made platforms?</div>
          <div class="faq-scroll-a">Absolutely. Submit your contact details above or click "Request Demo" on any product
            card across our site for immediate sandbox credentials.</div>
        </div>

        <div class="faq-scroll-card" aria-hidden="true">
          <span class="faq-scroll-badge">🛠 Technology Stack</span>
          <div class="faq-scroll-q">What tech stack do you use for SaaS &amp; Mobile?</div>
          <div class="faq-scroll-a">We build with Next.js, TypeScript, Flutter, Node.js, NestJS, Go, Python, and
            PostgreSQL with Docker &amp; Cloudflare edge infrastructure.</div>
        </div>

        <div class="faq-scroll-card" aria-hidden="true">
          <span class="faq-scroll-badge">📈 Post-Launch Support</span>
          <div class="faq-scroll-q">Do you offer ongoing post-launch maintenance?</div>
          <div class="faq-scroll-a">Yes. All projects include a 30-day post-launch guarantee plus optional monthly
            retainer squads for continuous feature updates &amp; SLA monitoring.</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="footer-inner">
      <!-- Column 1: Brand & Bio -->
      <div class="footer-brand">
        <div class="footer-logo">
          <img src="images/logo.png" alt="ZAMZY" class="brand-logo-img" />
        </div>
        <p class="footer-tagline">
          ZAMZY.IN — Engineering High-Performance SaaS Platforms, Mobile Ecosystems &amp; Automated Cloud Systems.
        </p>
        <span class="footer-copy">© 2026 ZAMZY Digital Engineering Agency. All rights reserved.</span>
      </div>

      <!-- Column 2: Navigation Links -->
      <div>
        <div class="footer-col-title">Navigation</div>
        <div class="footer-links-col">
          <a href="index.html#hero">Home</a>
          <a href="index.html#about">Studio</a>
          <a href="index.html#launchpad">Launchpad</a>
          <a href="index.html#products">Products</a>
          <a href="index.html#services">Services</a>
          <a href="index.html#testimonials">Reviews</a>
          <a href="index.html#rates">Rates</a>
        </div>
      </div>

      <!-- Column 3: Merchant Compliance Policies -->
      <div>
        <div class="footer-col-title">Merchant Policies</div>
        <div class="footer-links-col">
          <a href="privacy-policy.php">Privacy Policy</a>
          <a href="terms-and-conditions.php">Terms &amp; Conditions</a>
          <a href="refund-policy.php">Refund &amp; Cancellation</a>
          <a href="shipping-policy.php">Shipping &amp; Delivery</a>
          <a href="contact.php" style="color:var(--cyan);">Contact Support</a>
        </div>
      </div>

      <!-- Column 4: Contact & Office -->
      <div>
        <div class="footer-col-title">Engineering Office</div>
        <div class="footer-links-col" style="font-size:0.82rem; color:var(--dim); line-height:1.7;">
          <p><strong>ZAMZY DIGITAL SOLUTIONS</strong></p>
          <p>📍 Hitech City, Hyderabad, Telangana, India</p>
          <p>💬 <a href="https://wa.me/919876543210" target="_blank"
              style="color:var(--cyan); text-decoration:underline;">+91 98765 43210 (WhatsApp)</a></p>
          <p>✉️ <a href="mailto:contact@zamzy.in"
              style="color:var(--cyan); text-decoration:underline;">contact@zamzy.in</a></p>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
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

  <!-- Fixed Mobile Quick-Action Dock -->
  <div class="mobile-action-dock" id="mobile-action-dock" role="navigation" aria-label="Quick Actions">
    <a href="index.html#services" class="dock-btn dock-btn--services" aria-label="Explore Services">
      <span class="dock-btn__icon">🛠</span>
      <span class="dock-btn__label">Services</span>
    </a>
    <a href="contact.php" class="dock-btn dock-btn--talk active" aria-label="Start a Project / Let's Talk">
      <span class="dock-btn__icon">💬</span>
      <span class="dock-btn__label">Let's Talk</span>
    </a>
    <a href="careers" class="dock-btn dock-btn--careers" aria-label="Explore Careers & Guild">
      <span class="dock-btn__icon">💼</span>
      <span class="dock-btn__label">Careers</span>
    </a>
  </div>

  <!-- ═══════════════════════════════════════════════
       SUPER THANK YOU POPUP MODAL (AUTO CLOSE)
  ═══════════════════════════════════════════════ -->
  <div class="thankyou-modal" id="thankyou-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="thankyou-backdrop" id="close-thankyou-backdrop"></div>
    <div class="thankyou-card">
      <div class="thankyou-icon-wrap">
        <div class="thankyou-icon">✓</div>
      </div>
      <span class="thankyou-badge">⚡ TRANSMISSION RECEIVED</span>
      <h2 class="thankyou-title">SUPER THANK YOU!</h2>
      <p class="thankyou-subtitle">Your Engineering Brief is Successfully Logged</p>
      <p class="thankyou-msg" id="thankyou-msg">
        Thank you! Our Lead Architect has received your details and will connect with you via WhatsApp shortly.
      </p>
      <div class="thankyou-timer-wrap">
        <div class="thankyou-timer-bar" id="thankyou-timer-bar"></div>
      </div>
      <div class="thankyou-footer">
        <span class="thankyou-countdown">Closing automatically in <strong id="thankyou-countdown-num">5</strong>s</span>
        <button type="button" class="btn btn-outline" id="close-thankyou-btn"
          style="padding: 0.5rem 1.2rem; font-size: 0.76rem;">Close Now →</button>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="tooplate-vora-bold-script.js"></script>

</body>

</html>