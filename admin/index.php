<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

// Fetch KPI Metrics
$totalInquiries = 0;
$newInquiries = 0;
$totalDemos = 0;
$totalReviews = 0;
$recentInquiries = [];
$recentDemos = [];
$recentReviews = [];

if ($pdo) {
    // KPI Counts
    $totalInquiries = $pdo->query("SELECT COUNT(*) FROM `zamzy_inquiries`")->fetchColumn();
    $newInquiries = $pdo->query("SELECT COUNT(*) FROM `zamzy_inquiries` WHERE `status` = 'new'")->fetchColumn();
    $totalDemos = $pdo->query("SELECT COUNT(*) FROM `zamzy_demo_requests`")->fetchColumn();
    $totalReviews = $pdo->query("SELECT COUNT(*) FROM `zamzy_testimonials`")->fetchColumn();

    // Recent Inquiries
    $stmt = $pdo->query("SELECT * FROM `zamzy_inquiries` ORDER BY `id` DESC LIMIT 5");
    $recentInquiries = $stmt->fetchAll();

    // Recent Demos
    $stmt = $pdo->query("SELECT * FROM `zamzy_demo_requests` ORDER BY `id` DESC LIMIT 5");
    $recentDemos = $stmt->fetchAll();

    // Recent Testimonials
    $stmt = $pdo->query("SELECT * FROM `zamzy_testimonials` ORDER BY `id` DESC LIMIT 4");
    $recentReviews = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — Executive Overview</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-shell">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div>
            <div class="admin-sidebar__brand">
                <span class="admin-sidebar__logo">ZAMZY.</span>
                <span class="admin-sidebar__sub">Executive Console</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__item active"><span>📊</span> Dashboard</a>
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads (<?= $newInquiries ?>)</a>
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests (<?= $totalDemos ?>)</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof (<?= $totalReviews ?>)</a>
                <a href="careers.php" class="admin-nav__item"><span>👥</span> Careers &amp; Guild</a>
                <a href="../index.html" target="_blank" class="admin-nav__item"><span>↗</span> View Live Site</a>
            </nav>
        </div>

        <div class="admin-sidebar__footer">
            <div class="admin-user-pill">
                <span class="dot">●</span> <?= htmlspecialchars($_SESSION['zamzy_admin_name'] ?? 'Administrator') ?>
            </div>
            <a href="logout.php" class="btn-admin btn-admin-outline btn-admin-sm" style="width:100%; text-align:center;">Terminate Session</a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1 class="admin-page-title">Executive Overview</h1>
                <p class="admin-page-sub">Headquarters Operations &amp; Inbound Project Intake</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="../index.php" target="_blank" class="btn-admin btn-admin-outline">
                    <span>↗</span> View Live Site
                </a>
            </div>
        </header>

        <!-- KPI Metric Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Total Inquiries</span>
                    <span class="kpi-card__icon">📬</span>
                </div>
                <div class="kpi-card__val"><?= $totalInquiries ?></div>
                <div class="kpi-card__sub"><?= $newInquiries ?> unread new briefs</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">SaaS Demo Requests</span>
                    <span class="kpi-card__icon">⚡</span>
                </div>
                <div class="kpi-card__val"><?= $totalDemos ?></div>
                <div class="kpi-card__sub">Across 22+ Product Engines</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Client Testimonials</span>
                    <span class="kpi-card__icon">★</span>
                </div>
                <div class="kpi-card__val"><?= $totalReviews ?></div>
                <div class="kpi-card__sub">5.0 Star Verified Rating</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Anna Nagar HQ</span>
                    <span class="kpi-card__icon">📍</span>
                </div>
                <div class="kpi-card__val" style="font-size:2.2rem;">ONLINE</div>
                <div class="kpi-card__sub">Chennai · 99.9% Uptime</div>
            </div>
        </div>

        <!-- Recent Inquiries Table -->
        <div class="admin-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                <h2 class="admin-card__title" style="margin-bottom:0;">Recent Project Inquiries</h2>
                <a href="inquiries.php" class="btn-admin btn-admin-outline btn-admin-sm">View All Inquiries →</a>
            </div>

            <?php if (empty($recentInquiries)): ?>
                <p style="opacity:0.6; padding:1.5rem 0;">No project inquiries submitted yet.</p>
            <?php else: ?>
                <div class="table-container" style="margin-bottom:0; border-top:none;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Client / Company</th>
                                <th>WhatsApp</th>
                                <th>Scope Tier</th>
                                <th>Requirements Preview</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentInquiries as $inq): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($inq['name']) ?></strong><br>
                                        <span style="opacity:0.7; font-size:0.7rem;"><?= htmlspecialchars($inq['company'] ?? 'Startup') ?> · <?= htmlspecialchars($inq['email']) ?></span>
                                    </td>
                                    <td>
                                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $inq['phone']) ?>" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm">
                                            💬 <?= htmlspecialchars($inq['phone']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge">
                                            <?= htmlspecialchars(explode('—', $inq['tier'])[0]) ?>
                                        </span>
                                    </td>
                                    <td style="max-width:280px; opacity:0.8; font-size:0.75rem;">
                                        <?= htmlspecialchars(substr($inq['requirements'], 0, 80)) ?>...
                                    </td>
                                    <td>
                                        <span class="badge-status <?= htmlspecialchars($inq['status']) ?>">
                                            <?= strtoupper($inq['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="inquiries.php?id=<?= $inq['id'] ?>" class="btn-admin btn-admin-sm">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Two Columns: Demo Requests & Testimonials -->
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap:2rem;">
            
            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <h3 class="admin-card__title" style="margin-bottom:0; font-size:1.5rem;">Demo Requests</h3>
                    <a href="demos.php" class="btn-admin btn-admin-outline btn-admin-sm">All Demos →</a>
                </div>
                <?php if (empty($recentDemos)): ?>
                    <p style="opacity:0.6;">No demo requests logged yet.</p>
                <?php else: ?>
                    <div class="table-container" style="margin-bottom:0;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Platform</th>
                                    <th>WhatsApp</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentDemos as $demo): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($demo['product_name']) ?></strong></td>
                                        <td>
                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $demo['phone']) ?>" target="_blank" style="text-decoration:underline;">
                                                <?= htmlspecialchars($demo['phone']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge-status <?= htmlspecialchars($demo['status']) ?>">
                                                <?= strtoupper($demo['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <div class="admin-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                    <h3 class="admin-card__title" style="margin-bottom:0; font-size:1.5rem;">Client Reviews</h3>
                    <a href="testimonials.php" class="btn-admin btn-admin-outline btn-admin-sm">+ Manage</a>
                </div>
                <?php if (empty($recentReviews)): ?>
                    <p style="opacity:0.6;">No testimonials found.</p>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:1rem;">
                        <?php foreach ($recentReviews as $rev): ?>
                            <div style="background:var(--bg-light); padding:1.2rem; border:1px solid var(--black);">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.4rem;">
                                    <strong><?= htmlspecialchars($rev['client_name']) ?></strong>
                                    <span>★★★★★</span>
                                </div>
                                <div style="font-size:0.68rem; opacity:0.7; margin-bottom:0.6rem;">
                                    <?= htmlspecialchars($rev['role']) ?> · <?= htmlspecialchars($rev['company_name']) ?> (<?= htmlspecialchars($rev['location']) ?>)
                                </div>
                                <p style="font-size:0.75rem; line-height:1.6; font-style:italic;">
                                    "<?= htmlspecialchars($rev['review_text']) ?>"
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </main>

</div>

</body>
</html>
