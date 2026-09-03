/*
  ZAMZY — 3D Orbital Hero Engine & Interactive Platform Scripts
*/

document.addEventListener('DOMContentLoaded', () => {

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ═══════════════════════════════════════════════
     1. 3D ORBITAL RING CAROUSEL ENGINE
  ═══════════════════════════════════════════════ */
  var ring = document.getElementById('ring');
  if (ring) {
    var panels = ring.querySelectorAll('.panel');
    var count = panels.length;

    var spacingLevels = [0.74, 0.92, 1.08]; /* tight, default, wide */
    var spacingIndex = 1;

    function baseRadius() {
      var raw = getComputedStyle(document.documentElement).getPropertyValue('--ring-radius');
      return parseFloat(raw) || 340;
    }
    function effectiveRadius() {
      return baseRadius() * spacingLevels[spacingIndex];
    }

    function positionPanels() {
      var r = effectiveRadius();
      panels.forEach(function (panel, i) {
        var angle = (360 / count) * i;
        var tilt = Math.sin((i / count) * Math.PI * 2) * 8;
        panel.style.setProperty('--ry', angle + 'deg');
        panel.style.setProperty('--tz', r + 'px');
        panel.style.setProperty('--rz', tilt.toFixed(2) + 'deg');
        panel.style.setProperty('--i', i);
      });
    }
    positionPanels();

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(positionPanels, 200);
    });

    /* 3 step spacing */
    var spacingSteps = document.querySelectorAll('.spacing-step');
    function setSpacing(idx) {
      spacingIndex = idx;
      var r = effectiveRadius();
      ring.querySelectorAll('.panel').forEach(function (p) {
        p.style.setProperty('--tz', r + 'px');
      });
      spacingSteps.forEach(function (b) {
        b.classList.toggle('is-active', parseInt(b.getAttribute('data-space'), 10) === idx);
      });
    }
    spacingSteps.forEach(function (b) {
      b.addEventListener('click', function () {
        setSpacing(parseInt(b.getAttribute('data-space'), 10));
      });
    });

    /* Ring rotation driven by slow auto-scroll (left to right) with hover-pause and touch drag */
    var stage = document.querySelector('.stage');
    var parallax = document.querySelector('.parallax');

    var rotation = 0;
    var velocity = 0;
    var baseDrift = reduceMotion ? 0 : 0.14; /* Smooth continuous rotation left to right */
    var friction = 0.94;
    var MAX_VELOCITY = 7;
    var DRAG_SENS = 0.32;
    var WHEEL_SENS = 0.05;

    var dragging = false;
    var isHovered = false;
    var lastX = 0;

    function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

    /* Parallax tilt target */
    var targetX = 0, targetY = 0, currentX = 0, currentY = 0;
    var rangeY = 24;
    var rangeX = 26;
    var biasX = 10;
    if (!reduceMotion) {
      window.addEventListener('mousemove', function (e) {
        var mx = (e.clientX / window.innerWidth) - 0.5;
        var my = (e.clientY / window.innerHeight) - 0.5;
        targetY = mx * rangeY;
        targetX = (-my * rangeX) + biasX;
      });
    }

    if (stage) {
      /* Hover to pause auto-scroll */
      stage.addEventListener('mouseenter', function () {
        isHovered = true;
      });
      stage.addEventListener('mouseleave', function () {
        isHovered = false;
      });

      /* Pointer drag interaction */
      stage.addEventListener('pointerdown', function (e) {
        dragging = true;
        isHovered = true;
        lastX = e.clientX;
        velocity = 0;
        stage.classList.add('dragging');
      });

      window.addEventListener('pointermove', function (e) {
        if (!dragging) return;
        var dx = e.clientX - lastX;
        lastX = e.clientX;
        var step = dx * DRAG_SENS;
        rotation += step;
        velocity = clamp(step, -MAX_VELOCITY, MAX_VELOCITY);
      });

      function endDrag() {
        if (!dragging) return;
        dragging = false;
        stage.classList.remove('dragging');
        setTimeout(function () {
          isHovered = false;
        }, 300);
      }
      window.addEventListener('pointerup', endDrag);
      window.addEventListener('pointercancel', endDrag);

      stage.addEventListener('wheel', function (e) {
        if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) {
          e.preventDefault();
          velocity = clamp(velocity + e.deltaX * WHEEL_SENS, -MAX_VELOCITY, MAX_VELOCITY);
        }
      }, { passive: false });
    }

    function frame() {
      if (!dragging) {
        if (!isHovered) {
          rotation += baseDrift + velocity;
          velocity *= friction;
          if (Math.abs(velocity) < 0.0015) velocity = 0;
        } else {
          /* Smoothly dampen any residual velocity while hovered */
          rotation += velocity;
          velocity *= 0.82;
          if (Math.abs(velocity) < 0.0015) velocity = 0;
        }
      }
      ring.style.transform = 'rotateY(' + rotation.toFixed(3) + 'deg)';

      if (!reduceMotion && parallax) {
        currentX += (targetX - currentX) * 0.06;
        currentY += (targetY - currentY) * 0.06;
        parallax.style.transform = 'rotateX(' + currentX.toFixed(2) + 'deg) rotateY(' + currentY.toFixed(2) + 'deg)';
      }
      requestAnimationFrame(frame);
    }
    frame();

    /* Visuals switch: crossfade panels */
    var switchBtn = document.getElementById('visualsSwitch');
    if (switchBtn) {
      switchBtn.addEventListener('click', function () {
        var on = switchBtn.getAttribute('aria-checked') !== 'true';
        switchBtn.setAttribute('aria-checked', on ? 'true' : 'false');
        document.body.classList.toggle('visuals-on', on);
      });
    }

    /* Zoom switch (Always on Zoom by default) */
    var zoomSwitch = document.getElementById('zoomSwitch');
    var ringTilt = document.querySelector('.ring-tilt');
    if (ringTilt) {
      ringTilt.style.setProperty('--zoom', '1.25');
    }
    if (zoomSwitch && ringTilt) {
      zoomSwitch.setAttribute('aria-checked', 'true');
      zoomSwitch.addEventListener('click', function () {
        var on = zoomSwitch.getAttribute('aria-checked') !== 'true';
        zoomSwitch.setAttribute('aria-checked', on ? 'true' : 'false');
        ringTilt.style.setProperty('--zoom', on ? '1.25' : '1');
      });
    }
  }

  /* ═══════════════════════════════════════════════
     2. MOBILE NAVIGATION MENU
  ═══════════════════════════════════════════════ */
  var toggle = document.querySelector('.menu-toggle');
  var mobileMenu = document.querySelector('.mobile-menu');
  if (toggle && mobileMenu) {
    toggle.addEventListener('click', function () {
      var open = mobileMenu.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        mobileMenu.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ═══════════════════════════════════════════════
     3. SERVICE ACCORDION
  ═══════════════════════════════════════════════ */
  const serviceRows = document.querySelectorAll('.service-row[data-svc]');
  serviceRows.forEach((row) => {
    row.addEventListener('click', () => {
      const id = row.dataset.svc;
      const targetPanel = document.getElementById(`svc-${id}`);
      const isAlreadyOpen = row.classList.contains('open');

      serviceRows.forEach((r) => r.classList.remove('open'));
      document.querySelectorAll('.service-panel').forEach((p) => p.classList.remove('open'));

      if (!isAlreadyOpen && targetPanel) {
        row.classList.add('open');
        targetPanel.classList.add('open');
      }
    });
  });

  /* ═══════════════════════════════════════════════
     4. PRODUCT CATEGORY FILTERING
  ═══════════════════════════════════════════════ */
  const filterButtons = document.querySelectorAll('.filter-btn');
  const productCards = document.querySelectorAll('.product-card');

  filterButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      filterButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      const filterValue = btn.dataset.filter;

      productCards.forEach((card) => {
        const category = card.dataset.category;
        if (filterValue === 'all' || category === filterValue) {
          card.style.display = 'flex';
          setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
          }, 20);
        } else {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.95)';
          setTimeout(() => {
            card.style.display = 'none';
          }, 250);
        }
      });
    });
  });

  /* ═══════════════════════════════════════════════
     4b. PRICING CATEGORY TABS (TECH vs MARKETING)
  ═══════════════════════════════════════════════ */
  const pricingTabs = document.querySelectorAll('.pricing-tab');
  const ratesPanels = document.querySelectorAll('.rates-panel');

  pricingTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      const targetId = tab.dataset.target;
      pricingTabs.forEach((t) => {
        t.classList.remove('active');
        t.setAttribute('aria-selected', 'false');
      });
      ratesPanels.forEach((panel) => {
        panel.classList.remove('active');
        panel.style.display = 'none';
      });

      tab.classList.add('active');
      tab.setAttribute('aria-selected', 'true');

      const activePanel = document.getElementById(targetId);
      if (activePanel) {
        activePanel.style.display = 'block';
        // Trigger reflow for CSS animation
        void activePanel.offsetWidth;
        activePanel.classList.add('active');
      }
    });
  });

  /* ═══════════════════════════════════════════════
     5. SELECT TIER TO INTAKE FORM
  ═══════════════════════════════════════════════ */
  const tierButtons = document.querySelectorAll('.select-tier-btn');
  const tierBudgetSelect = document.getElementById('client-budget');
  const tierTypeSelect = document.getElementById('client-type');

  tierButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const selectedTier = btn.dataset.tier || '';
      if (tierBudgetSelect) {
        if (selectedTier.includes('BASIC')) {
          tierBudgetSelect.value = '₹5,000 (Website Basic Plan)';
          if (tierTypeSelect) tierTypeSelect.value = 'Website & Mobile App Development';
        } else if (selectedTier.includes('STANDARD')) {
          tierBudgetSelect.value = '₹12,000 (Website Standard Plan)';
          if (tierTypeSelect) tierTypeSelect.value = 'Website & Mobile App Development';
        } else if (selectedTier.includes('PREMIUM')) {
          tierBudgetSelect.value = 'Up to ₹20,000 (Website Premium Plan)';
          if (tierTypeSelect) tierTypeSelect.value = 'Website & Mobile App Development';
        } else if (selectedTier.includes('10,000')) {
          tierBudgetSelect.value = '₹10,000 – ₹25,000 (MVP / Prototype)';
          if (tierTypeSelect) tierTypeSelect.value = 'Custom SaaS Platform';
        } else if (selectedTier.includes('25,000')) {
          tierBudgetSelect.value = '₹25,000 – ₹75,000 (Custom Web & App)';
          if (tierTypeSelect) tierTypeSelect.value = 'Custom SaaS Platform';
        } else if (selectedTier.includes('75,000')) {
          tierBudgetSelect.value = '₹75,000 – ₹2,00,000 (Enterprise SaaS)';
          if (tierTypeSelect) tierTypeSelect.value = 'Custom SaaS Platform';
        } else if (selectedTier.includes('5,000')) {
          tierBudgetSelect.value = '₹5,000 – ₹15,000/mo (Starter Marketing & Local SEO)';
          if (tierTypeSelect) tierTypeSelect.value = 'Digital Marketing & Growth Campaigns';
        } else if (selectedTier.includes('15,000')) {
          tierBudgetSelect.value = '₹15,000 – ₹35,000/mo (Performance Ads & Scaling)';
          if (tierTypeSelect) tierTypeSelect.value = 'Performance Ads & Google Search / Meta';
        } else if (selectedTier.includes('35,000')) {
          tierBudgetSelect.value = '₹35,000+/mo (Enterprise Growth Retainer)';
          if (tierTypeSelect) tierTypeSelect.value = 'Digital Marketing & Growth Campaigns';
        }
      }
      const contactSection = document.getElementById('contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
        showToast(`Selected budget scope for ${selectedTier.split('—')[0].trim()}.`);
      }
    });
  });

  /* ═══════════════════════════════════════════════
     6. PRODUCT DEMO MODAL & API SUBMISSION
  ═══════════════════════════════════════════════ */
  const demoModal = document.getElementById('demo-modal');
  const closeDemoModal = document.getElementById('close-demo-modal');
  const demoModalTitle = document.getElementById('demo-modal-title');
  const demoProductName = document.getElementById('demo-product-name');
  const demoRequestForm = document.getElementById('demo-request-form');

  document.querySelectorAll('.open-demo-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const product = btn.dataset.product || 'Product Platform';
      if (demoModalTitle) {
        demoModalTitle.textContent = `Demo: ${product}`;
      }
      if (demoProductName) {
        demoProductName.value = product;
      }
      if (demoModal) {
        demoModal.classList.add('open');
      }
    });
  });

  if (closeDemoModal && demoModal) {
    closeDemoModal.addEventListener('click', () => {
      demoModal.classList.remove('open');
    });
    demoModal.addEventListener('click', (e) => {
      if (e.target === demoModal) {
        demoModal.classList.remove('open');
      }
    });
  }

  if (demoRequestForm) {
    demoRequestForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const product = demoProductName.value || 'Requested Product';
      const phone = document.getElementById('demo-phone').value;
      const email = document.getElementById('demo-email').value;

      await window.showGlobalFormLoader("Generating Credentials...", "Dispatching sandbox access keys to WhatsApp", 1600);

      try {
        const formData = new FormData();
        formData.append('action', 'submit_demo');
        formData.append('product_name', product);
        formData.append('phone', phone);
        formData.append('email', email);

        const res = await fetch('api.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        demoModal.classList.remove('open');
        demoRequestForm.reset();
        showSuperThankYouModal("Partner", phone, `Sandbox credentials for <strong>${escapeHtml(product)}</strong> dispatched to WhatsApp at <strong>${escapeHtml(phone)}</strong>!`);
      } catch (err) {
        demoModal.classList.remove('open');
        demoRequestForm.reset();
        showSuperThankYouModal("Partner", phone, `Sandbox credentials for <strong>${escapeHtml(product)}</strong> dispatched to WhatsApp at <strong>${escapeHtml(phone)}</strong>!`);
      }
    });
  }

  /* ═══════════════════════════════════════════════
     7. CLIENT REVIEW MODAL & API SUBMISSION
  ═══════════════════════════════════════════════ */
  const reviewModal = document.getElementById('review-modal');
  const openReviewBtn = document.getElementById('open-review-modal-btn');
  const closeReviewModal = document.getElementById('close-review-modal');
  const reviewForm = document.getElementById('client-review-form');

  if (openReviewBtn && reviewModal) {
    openReviewBtn.addEventListener('click', () => {
      reviewModal.classList.add('open');
    });
  }

  if (closeReviewModal && reviewModal) {
    closeReviewModal.addEventListener('click', () => {
      reviewModal.classList.remove('open');
    });
    reviewModal.addEventListener('click', (e) => {
      if (e.target === reviewModal) {
        reviewModal.classList.remove('open');
      }
    });
  }

  if (reviewForm) {
    reviewForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = document.getElementById('rev-name').value;
      const company = document.getElementById('rev-company').value;
      const role = document.getElementById('rev-role').value;
      const location = document.getElementById('rev-location').value;
      const rating = document.getElementById('rev-rating').value;
      const projectType = document.getElementById('rev-project').value;
      const text = document.getElementById('rev-text').value;

      await window.showGlobalFormLoader("Recording Review...", "Verifying client metadata & logging testimonial", 1600);

      try {
        const formData = new FormData();
        formData.append('action', 'submit_review');
        formData.append('client_name', name);
        formData.append('company_name', company);
        formData.append('role', role);
        formData.append('location', location);
        formData.append('rating', rating);
        formData.append('project_type', projectType);
        formData.append('review_text', text);

        const res = await fetch('api.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        reviewModal.classList.remove('open');
        reviewForm.reset();
        showToast(data.message || 'Thank you! Your verified review has been recorded.');
      } catch (err) {
        reviewModal.classList.remove('open');
        reviewForm.reset();
        showToast('Thank you! Your verified review has been submitted to ZAMZY.');
      }
    });
  }

  /* ═══════════════════════════════════════════════
     GLOBAL UIVERSE FORM SUBMISSION LOADER ENGINE
  ═══════════════════════════════════════════════ */
  window.showGlobalFormLoader = function (title, sub, durationMs = 1800) {
    let loader = document.getElementById('global-form-loader');
    if (!loader) {
      loader = document.createElement('div');
      loader.id = 'global-form-loader';
      loader.className = 'global-loader-overlay';
      loader.setAttribute('aria-hidden', 'true');
      loader.innerHTML = `
        <div class="global-loader-card">
          <div class="speeder-loader-wrap">
            <div class="loader">
              <span><span></span><span></span><span></span><span></span></span>
              <div class="base">
                <span></span>
                <div class="face"></div>
              </div>
            </div>
            <div class="longfazers">
              <span></span><span></span><span></span><span></span>
            </div>
          </div>
          <div class="loader-status-group">
            <span class="loader-badge">⚡ PROCESSING TRANSMISSION</span>
            <h3 class="loader-title" id="global-loader-title">${title || 'Dispatching Brief...'}</h3>
            <p class="loader-sub" id="global-loader-sub">${sub || 'Connecting with ZAMZY Lead Architect in Hitech City'}</p>
          </div>
        </div>
      `;
      document.body.appendChild(loader);
    } else {
      const titleEl = document.getElementById('global-loader-title');
      const subEl = document.getElementById('global-loader-sub');
      if (titleEl) titleEl.textContent = title || 'Dispatching Brief...';
      if (subEl) subEl.textContent = sub || 'Connecting with ZAMZY Lead Architect in Hitech City';
    }

    loader.classList.add('open');
    loader.setAttribute('aria-hidden', 'false');

    return new Promise((resolve) => {
      setTimeout(() => {
        loader.classList.remove('open');
        loader.setAttribute('aria-hidden', 'true');
        resolve();
      }, durationMs);
    });
  };

  /* ═══════════════════════════════════════════════
     8. SIMPLIFIED PROJECT INTAKE FORM (With Language & Budget)
  ═══════════════════════════════════════════════ */
  const intakeForm = document.getElementById('project-intake-form');
  if (intakeForm) {
    intakeForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = document.getElementById('client-name').value;
      const phone = document.getElementById('client-phone').value;
      const email = document.getElementById('client-email').value;
      const lang = document.getElementById('client-lang').value;
      const budget = document.getElementById('client-budget').value;
      const projectType = document.getElementById('client-type').value;
      const reqs = document.getElementById('client-reqs').value;

      await window.showGlobalFormLoader("Dispatching Brief...", "Encrypting submission & connecting with Lead Architect", 1600);

      try {
        const formData = new FormData();
        formData.append('action', 'submit_inquiry');
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('preferred_language', lang);
        formData.append('budget', budget);
        formData.append('project_type', projectType);
        formData.append('requirements', reqs);

        const res = await fetch('api.php', {
          method: 'POST',
          body: formData
        });
        const data = await res.json();

        intakeForm.reset();
        showSuperThankYouModal(name, phone);
      } catch (err) {
        intakeForm.reset();
        showSuperThankYouModal(name, phone);
      }
    });
  }

  /* Contact Page Form Handler */
  const contactPageForm = document.getElementById('contact-page-form');
  if (contactPageForm) {
    contactPageForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = document.getElementById('contact-name').value;
      const phone = document.getElementById('contact-phone').value;
      const email = document.getElementById('contact-email').value;
      const lang = document.getElementById('contact-lang') ? document.getElementById('contact-lang').value : 'English';
      const budget = document.getElementById('contact-budget') ? document.getElementById('contact-budget').value : 'Standard Scope';
      const projectType = document.getElementById('contact-type') ? document.getElementById('contact-type').value : 'General Inquiry';
      const reqs = document.getElementById('contact-reqs').value;

      await window.showGlobalFormLoader("Dispatching Brief...", "Encrypting submission & connecting with Lead Architect", 1600);

      try {
        const formData = new FormData();
        formData.append('action', 'submit_inquiry');
        formData.append('name', name);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('preferred_language', lang);
        formData.append('budget', budget);
        formData.append('project_type', projectType);
        formData.append('requirements', reqs);

        const res = await fetch('api.php', { method: 'POST', body: formData });
        const data = await res.json();

        contactPageForm.reset();
        showSuperThankYouModal(name, phone);
      } catch (err) {
        contactPageForm.reset();
        showSuperThankYouModal(name, phone);
      }
    });
  }

  /* Guild Application Form Handler (careers.php) */
  const guildForm = document.getElementById('guild-application-form');
  if (guildForm) {
    guildForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const name = document.getElementById('app-name').value;
      const phone = document.getElementById('app-phone').value;
      const email = document.getElementById('app-email').value;
      const location = document.getElementById('app-location').value;
      const skills = document.getElementById('app-skills').value;
      const exp = document.getElementById('app-exp') ? document.getElementById('app-exp').value : 'College Student / Fresher';
      const portfolio = document.getElementById('app-portfolio') ? document.getElementById('app-portfolio').value : '';
      const notes = document.getElementById('app-notes') ? document.getElementById('app-notes').value : '';

      await window.showGlobalFormLoader("Transmitting Application...", "Logging candidate profile & resume into ZAMZY database", 1600);

      try {
        const formData = new FormData();
        formData.append('action', 'submit_application');
        formData.append('full_name', name);
        formData.append('phone', phone);
        formData.append('email', email);
        formData.append('location_college', location);
        formData.append('primary_skills', skills);
        formData.append('experience_level', exp);
        formData.append('portfolio_url', portfolio);
        formData.append('past_work_notes', notes);

        const fileInput = document.getElementById('app-resume');
        if (fileInput && fileInput.files[0]) {
          formData.append('resume', fileInput.files[0]);
        }

        const res = await fetch('api.php', { method: 'POST', body: formData });
        const data = await res.json();

        guildForm.reset();
        showSuperThankYouModal(name, phone, `Thank you <strong>${escapeHtml(name)}</strong>! Your Guild application has been received. Our Lead Architect will review your resume and WhatsApp you at <strong>${escapeHtml(phone)}</strong>.`);
      } catch (err) {
        guildForm.reset();
        showSuperThankYouModal(name, phone, `Thank you <strong>${escapeHtml(name)}</strong>! Your Guild application has been logged. Our Lead Architect will WhatsApp you at <strong>${escapeHtml(phone)}</strong> shortly.`);
      }
    });
  }

  /* ═══════════════════════════════════════════════
     SUPER THANK YOU POPUP ENGINE (AUTO CLOSE)
  ═══════════════════════════════════════════════ */
  window.showSuperThankYouModal = function (name, phone, customMsg) {
    const modal = document.getElementById('thankyou-modal');
    if (!modal) return;

    const msgEl = document.getElementById('thankyou-msg');
    const timerBar = document.getElementById('thankyou-timer-bar');
    const countEl = document.getElementById('thankyou-countdown-num');
    const closeBtn = document.getElementById('close-thankyou-btn');
    const backdrop = document.getElementById('close-thankyou-backdrop');

    if (msgEl) {
      if (customMsg) {
        msgEl.innerHTML = customMsg;
      } else {
        msgEl.innerHTML = `Thank you <strong>${escapeHtml(name || 'Partner')}</strong>! Our Lead Architect has received your details and will connect with you via WhatsApp at <strong>${escapeHtml(phone || 'your number')}</strong> shortly.`;
      }
    }

    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');

    let totalSeconds = 5;
    let remainingMs = totalSeconds * 1000;
    const intervalMs = 100;

    if (timerBar) timerBar.style.width = '100%';
    if (countEl) countEl.textContent = totalSeconds;

    if (window._thankYouInterval) clearInterval(window._thankYouInterval);

    window._thankYouInterval = setInterval(() => {
      remainingMs -= intervalMs;
      const pct = (remainingMs / (totalSeconds * 1000)) * 100;
      if (timerBar) timerBar.style.width = `${Math.max(0, pct)}%`;
      if (countEl) countEl.textContent = Math.ceil(remainingMs / 1000);

      if (remainingMs <= 0) {
        closeThankYouModal();
      }
    }, intervalMs);

    function closeThankYouModal() {
      if (window._thankYouInterval) clearInterval(window._thankYouInterval);
      modal.classList.remove('open');
      modal.setAttribute('aria-hidden', 'true');
    }

    if (closeBtn) closeBtn.onclick = closeThankYouModal;
    if (backdrop) backdrop.onclick = closeThankYouModal;
  };

  /* ═══════════════════════════════════════════════
     9. PRESS KIT MODAL
  ═══════════════════════════════════════════════ */
  const pressModal = document.getElementById('press-modal');
  const openPressBtn = document.getElementById('open-press-btn');
  const closePressModal = document.getElementById('close-press-modal');
  const triggerPressDownload = document.getElementById('trigger-press-download');

  if (openPressBtn && pressModal) {
    openPressBtn.addEventListener('click', () => {
      pressModal.classList.add('open');
    });
  }

  if (closePressModal && pressModal) {
    closePressModal.addEventListener('click', () => {
      pressModal.classList.remove('open');
    });
    pressModal.addEventListener('click', (e) => {
      if (e.target === pressModal) {
        pressModal.classList.remove('open');
      }
    });
  }

  if (triggerPressDownload) {
    triggerPressDownload.addEventListener('click', () => {
      pressModal.classList.remove('open');
      showToast('Downloading ZAMZY_Press_Pack_2026.zip (12.4 MB)...');
    });
  }

  /* ═══════════════════════════════════════════════
     10. TOAST NOTIFICATION HELPER
  ═══════════════════════════════════════════════ */
  function showToast(message) {
    const toast = document.getElementById('toast');
    const toastText = document.getElementById('toast-text');
    if (toast && toastText) {
      toastText.textContent = message;
      toast.classList.add('show');
      setTimeout(() => {
        toast.classList.remove('show');
      }, 5000);
    }
  }

  /* ═══════════════════════════════════════════════
     11. PARTIAL FORM AUTO-CAPTURE
     Silently stores any typed phone/email even if
     the user never clicks "Send Project Brief"
  ═══════════════════════════════════════════════ */
  (function () {
    var partialSent = false;

    function sendPartialCapture() {
      if (partialSent) return;

      var name  = (document.getElementById('client-name')  || {}).value || '';
      var phone = (document.getElementById('client-phone') || {}).value || '';
      var email = (document.getElementById('client-email') || {}).value || '';

      // Only capture if at least phone or email has been entered
      if (!phone && !email) return;

      partialSent = true; // avoid repeat saves

      var fd = new FormData();
      fd.append('action', 'partial_capture');
      fd.append('name',  name.trim());
      fd.append('phone', phone.trim());
      fd.append('email', email.trim());

      // Use sendBeacon first (fires even if page closes), fallback to fetch
      if (navigator.sendBeacon) {
        navigator.sendBeacon('api.php', fd);
      } else {
        fetch('api.php', { method: 'POST', body: fd }).catch(function () {});
      }
    }

    // Trigger on blur of contact form fields
    ['client-phone', 'client-email', 'client-name'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener('blur', sendPartialCapture);
      }
    });

    // Also fire when page unloads / user navigates away
    window.addEventListener('pagehide', sendPartialCapture);
    window.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'hidden') {
        sendPartialCapture();
      }
    });

    // If the full form is submitted successfully, mark partial as done
    // (the full submit_inquiry will overwrite the status anyway)
    var intakeForm = document.getElementById('project-intake-form');
    if (intakeForm) {
      intakeForm.addEventListener('submit', function () {
        partialSent = true; // stop duplicate partial saves on submit
      });
    }
  })();

  /* ═══════════════════════════════════════════════
     12. BACK TO TOP BUTTON
  ═══════════════════════════════════════════════ */
  const backBtn = document.getElementById('back-to-top');
  if (backBtn) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 400) {
        backBtn.classList.add('visible');
      } else {
        backBtn.classList.remove('visible');
      }
    }, { passive: true });
    backBtn.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ═══════════════════════════════════════════════
     13. AI CHATBOT WIDGET (DEEPSEEK WITH LEAD INTAKE & LOGS)
  ═══════════════════════════════════════════════ */
  (function () {
    const chatBtn    = document.getElementById('ai-chat-btn');
    const chatModal  = document.getElementById('ai-chat-modal');
    const closeChat  = document.getElementById('close-ai-chat');
    const chatForm   = document.getElementById('ai-chat-form');
    const chatInput  = document.getElementById('ai-chat-input');
    const msgBox     = document.getElementById('ai-chat-messages');
    if (!chatBtn || !chatModal || !msgBox) return;

    let chatHistory = [];
    let sessionToken = localStorage.getItem('zamzy_chat_token') || ('zamzy_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36));
    localStorage.setItem('zamzy_chat_token', sessionToken);

    let savedUser = null;
    try {
      savedUser = JSON.parse(localStorage.getItem('zamzy_chat_user') || 'null');
    } catch(e) { savedUser = null; }

    let leadData = {
      name: (savedUser && savedUser.name) ? savedUser.name : '',
      phone: (savedUser && savedUser.phone) ? savedUser.phone : '',
      email: (savedUser && savedUser.email) ? savedUser.email : ''
    };

    let onboardingStep = (leadData.name && leadData.phone && leadData.email) ? 'ready' : 'ask_name';

    function setPlaceholder() {
      if (!chatInput) return;
      if (onboardingStep === 'ask_name') {
        chatInput.placeholder = 'Enter your Full Name...';
      } else if (onboardingStep === 'ask_phone') {
        chatInput.placeholder = 'Enter your WhatsApp / Phone Number...';
      } else if (onboardingStep === 'ask_email') {
        chatInput.placeholder = 'Enter your Email Address...';
      } else {
        chatInput.placeholder = 'Ask anything about services, apps, pricing...';
      }
    }

    function initChatView() {
      msgBox.innerHTML = '';
      if (onboardingStep === 'ready') {
        appendMsg(`👋 Welcome back, <strong>${escapeHtml(leadData.name)}</strong>! I'm ZAMZY's AI Technical Consultant. How can our engineering team help you today?`, 'bot');
        showQuickButtons();
      } else {
        appendMsg(`👋 Hello! Welcome to <strong>ZAMZY Digital Solutions</strong> (Hitech City, Hyderabad).<br><br>Before we begin your technical consultation, may I have your <strong>Full Name</strong>?`, 'bot');
      }
      setPlaceholder();
    }

    function toggleChat() {
      chatModal.classList.toggle('open');
      if (chatModal.classList.contains('open') && chatInput) {
        if (msgBox.children.length === 0) {
          initChatView();
        }
        setTimeout(function () { chatInput.focus(); }, 280);
      }
    }
    chatBtn.addEventListener('click', toggleChat);
    if (closeChat) closeChat.addEventListener('click', function () { chatModal.classList.remove('open'); });

    // Click outside to close
    document.addEventListener('click', function (e) {
      if (!chatModal.contains(e.target) && !chatBtn.contains(e.target)) {
        chatModal.classList.remove('open');
      }
    });

    function escapeHtml(str) {
      if (!str) return '';
      return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function appendMsg(html, type) {
      var div = document.createElement('div');
      div.className = 'ai-msg ai-msg--' + type;
      div.innerHTML = '<div class="ai-msg-bubble">' + html + '</div>';
      msgBox.appendChild(div);
      msgBox.scrollTop = msgBox.scrollHeight;
    }

    function showTyping() {
      var t = document.createElement('div');
      t.className = 'ai-msg ai-msg--bot';
      t.id = 'ai-typing';
      t.innerHTML = '<div class="ai-typing"><span></span><span></span><span></span></div>';
      msgBox.appendChild(t);
      msgBox.scrollTop = msgBox.scrollHeight;
    }
    function removeTyping() {
      var t = document.getElementById('ai-typing');
      if (t) t.remove();
    }

    function showQuickButtons() {
      var old = msgBox.querySelector('.ai-quick-btns');
      if (old) old.remove();

      var qb = document.createElement('div');
      qb.className = 'ai-quick-btns';
      qb.innerHTML = `
        <button class="ai-quick-btn" data-msg="What services and SaaS platforms do you engineer?">Our Services</button>
        <button class="ai-quick-btn" data-msg="What are your development pricing packages and MVP costs?">Pricing &amp; Plans</button>
        <button class="ai-quick-btn" data-msg="I want to discuss building a custom mobile app / SaaS platform.">Start a Project</button>
      `;
      msgBox.appendChild(qb);
      msgBox.scrollTop = msgBox.scrollHeight;
    }

    async function handleUserInput(text) {
      var trimmed = text.trim();
      if (!trimmed) return;

      appendMsg(escapeHtml(trimmed), 'user');
      chatHistory.push({ role: 'user', content: trimmed });

      // Remove quick buttons if present
      var qb = msgBox.querySelector('.ai-quick-btns');
      if (qb) qb.remove();

      // Step 1: Collect Name
      if (onboardingStep === 'ask_name') {
        leadData.name = trimmed;
        onboardingStep = 'ask_phone';
        setPlaceholder();
        showTyping();
        setTimeout(function () {
          removeTyping();
          appendMsg(`Nice to meet you, <strong>${escapeHtml(leadData.name)}</strong>! 🤝<br><br>What is your <strong>WhatsApp / Phone Number</strong> so our solution architect can connect with you?`, 'bot');
        }, 600);
        return;
      }

      // Step 2: Collect Phone
      if (onboardingStep === 'ask_phone') {
        leadData.phone = trimmed;
        onboardingStep = 'ask_email';
        setPlaceholder();
        showTyping();
        setTimeout(function () {
          removeTyping();
          appendMsg(`Got it, thank you! And what is your <strong>Email Address</strong>?`, 'bot');
        }, 600);
        return;
      }

      // Step 3: Collect Email & Save Lead
      if (onboardingStep === 'ask_email') {
        leadData.email = trimmed;
        onboardingStep = 'ready';
        setPlaceholder();
        showTyping();

        // Save Lead to DB
        localStorage.setItem('zamzy_chat_user', JSON.stringify(leadData));
        var fd = new FormData();
        fd.append('action', 'ai_chat_save_lead');
        fd.append('session_token', sessionToken);
        fd.append('name', leadData.name);
        fd.append('phone', leadData.phone);
        fd.append('email', leadData.email);
        fetch('api.php', { method: 'POST', body: fd }).catch(function () {});

        setTimeout(function () {
          removeTyping();
          appendMsg(`✨ Thank you, <strong>${escapeHtml(leadData.name)}</strong>! Your details have been registered.<br><br>How can ZAMZY help you today? Ask me about our custom SaaS platforms, mobile apps, ERPs, pricing, or project timelines!`, 'bot');
          showQuickButtons();
        }, 800);
        return;
      }

      // Step 4: Normal DeepSeek Conversation
      showTyping();

      try {
        var fd = new FormData();
        fd.append('action', 'ai_chat');
        fd.append('session_token', sessionToken);
        fd.append('message', trimmed);
        fd.append('user_name', leadData.name);
        fd.append('user_phone', leadData.phone);
        fd.append('user_email', leadData.email);
        fd.append('history', JSON.stringify(chatHistory));

        var res = await fetch('api.php', { method: 'POST', body: fd });
        var data = await res.json();
        removeTyping();

        if (data && data.reply) {
          appendMsg(data.reply, 'bot');
          chatHistory.push({ role: 'assistant', content: data.reply });
        } else {
          appendMsg("Thanks for your message! We build scalable SaaS, mobile apps & ERP systems from Hitech City, Hyderabad. Please fill the Project Brief form below or email <strong>hello@zamzy.in</strong>.", 'bot');
        }
      } catch (err) {
        removeTyping();
        appendMsg("I'm ZAMZY's AI Technical Assistant. How can we help build your digital platform? Feel free to submit your requirements below or email <strong>hello@zamzy.in</strong>.", 'bot');
      }
    }

    if (chatForm) {
      chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = chatInput ? chatInput.value.trim() : '';
        if (!msg) return;
        chatInput.value = '';
        handleUserInput(msg);
      });
    }

    // Quick-reply buttons
    if (msgBox) {
      msgBox.addEventListener('click', function (e) {
        var btn = e.target.closest('.ai-quick-btn');
        if (btn) {
          handleUserInput(btn.getAttribute('data-msg') || btn.textContent);
        }
      });
    }

    // Initial setup on load
    initChatView();
  })();

});


