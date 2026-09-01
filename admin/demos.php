<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_demo') {
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? 'pending');

    if ($id > 0 && $pdo) {
        $stmt = $pdo->prepare("UPDATE `zamzy_demo_requests` SET `status` = :st WHERE `id` = :id");
        $stmt->execute([':st' => $status, ':id' => $id]);
        $msg = "Demo request #$id marked as " . strtoupper($status);
    }
}

$demos = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM `zamzy_demo_requests` ORDER BY `id` DESC");
    $demos = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — SaaS Demo Requests</title>
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
                <a href="index.php" class="admin-nav__item"><span>📊</span> Dashboard</a>
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads</a>
                <a href="demos.php" class="admin-nav__item active"><span>⚡</span> Demo Requests</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof</a>
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

    <!-- Main Content -->
    <main class="admin-main">
        
        <header class="admin-topbar">
            <div>
                <h1 class="admin-page-title">SaaS Demo Requests</h1>
                <p class="admin-page-sub">Dispatch Sandbox Credentials Across 22+ Product Platforms</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="demos.php" class="btn-admin btn-admin-outline">Refresh Queue</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Demo Requests Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Product / Platform</th>
                        <th>WhatsApp Contact</th>
                        <th>Email Destination</th>
                        <th>Status</th>
                        <th>Instant Dispatch</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demos)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:3rem; opacity:0.6;">No demo requests submitted yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demos as $d): ?>
                            <tr>
                                <td><strong>#<?= $d['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($d['product_name']) ?></strong><br>
                                    <span style="font-size:0.68rem; opacity:0.6;"><?= date('M j, Y - H:i', strtotime($d['created_at'])) ?></span>
                                </td>
                                <td>
                                    <span style="font-weight:700;"><?= htmlspecialchars($d['phone']) ?></span>
                                </td>
                                <td>
                                    <span><?= htmlspecialchars($d['email'] ?? '—') ?></span>
                                </td>
                                <td>
                                    <span class="badge-status <?= htmlspecialchars($d['status']) ?>">
                                        <?= strtoupper($d['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $d['phone']);
                                        $prefill = urlencode("Hello! Here are your instant sandbox credentials for ZAMZY's {$d['product_name']} platform. URL: https://zamzy.in/sandbox Demo User: demo@zamzy.in Pass: demo2026");
                                    ?>
                                    <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $prefill ?>" target="_blank" class="btn-admin btn-admin-sm">
                                        💬 Dispatch Credentials
                                    </a>
                                </td>
                                <td>
                                    <form action="demos.php" method="POST" style="display:flex; gap:0.4rem;">
                                        <input type="hidden" name="action" value="update_demo">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <select name="status" class="form-control-admin" style="padding:0.4rem 0.6rem; font-size:0.7rem;">
                                            <option value="pending" <?= $d['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="dispatched" <?= $d['status'] === 'dispatched' ? 'selected' : '' ?>>Dispatched</option>
                                            <option value="contacted" <?= $d['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                                            <option value="closed" <?= $d['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                        </select>
                                        <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm">Save</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

</body>
</html>
