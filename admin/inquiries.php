<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';

// Handle Status & Notes Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id = intval($_POST['id'] ?? 0);
    $status = trim($_POST['status'] ?? 'new');

    if ($id > 0 && $pdo) {
        $stmt = $pdo->prepare("UPDATE `zamzy_inquiries` SET `status` = :status WHERE `id` = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
        $msg = "Inquiry #$id marked as " . strtoupper($status);
    }
}

// Filters, Search & Pagination
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['search'] ?? '');
$selectedId = intval($_GET['id'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "`status` = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(`name` LIKE :s OR `email` LIKE :s OR `phone` LIKE :s OR `preferred_language` LIKE :s OR `budget` LIKE :s OR `project_type` LIKE :s OR `requirements` LIKE :s)";
    $params[':s'] = "%$search%";
}

$whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// Count Total
$countSql = "SELECT COUNT(*) FROM `zamzy_inquiries`" . $whereClause;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalInquiries = $countStmt->fetchColumn();
$totalPages = ceil($totalInquiries / $limit);

// Fetch Paginated Inquiries
$sql = "SELECT * FROM `zamzy_inquiries`" . $whereClause . " ORDER BY `id` DESC LIMIT $limit OFFSET $offset";
$inquiries = [];
if ($pdo) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inquiries = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — Inquiries &amp; Lead Pipeline</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.2rem 0;
            margin-top: 1rem;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 1rem;
        }
        .pagination-links {
            display: flex;
            gap: 0.4rem;
            align-items: center;
        }
        .page-btn {
            font-family: var(--mono);
            font-size: 0.72rem;
            padding: 0.4rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            color: var(--dim);
            transition: var(--transition);
        }
        .page-btn:hover, .page-btn.active {
            background: var(--cyan);
            color: #050505;
            border-color: var(--cyan);
            font-weight: 700;
        }
    </style>
</head>
<body>

<!-- Mobile Admin Navigation Header -->
<div class="admin-mobile-header">
    <div class="admin-mobile-brand">
        <span class="admin-mobile-logo">ZAMZY<span>.</span></span>
        <span class="admin-mobile-tag">Executive Console</span>
    </div>
    <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Toggle Navigation">
        ☰
    </button>
</div>

<!-- Mobile Drawer Overlay -->
<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<div class="admin-shell">

    <!-- Sidebar Drawer -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div>
            <div class="admin-sidebar__brand">
                <span class="admin-sidebar__logo">ZAMZY<span>.</span></span>
                <span class="admin-sidebar__sub">Executive Console</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__item"><span>📊</span> Dashboard</a>
                <a href="inquiries.php" class="admin-nav__item active"><span>📬</span> Inquiries &amp; Leads</a>
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof</a>
                <a href="careers.php" class="admin-nav__item"><span>👥</span> Careers &amp; Guild</a>
                <a href="../" target="_blank" class="admin-nav__item"><span>↗</span> View Live Site</a>
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
                <h1 class="admin-page-title">Project Inquiries</h1>
                <p class="admin-page-sub">Client Intake Leads, Language Preferences &amp; WhatsApp Connect (<?= $totalInquiries ?> Total)</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="inquiries.php" class="btn-admin btn-admin-outline">Refresh Pipeline</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <div class="filter-toolbar">
            <form action="inquiries.php" method="GET" style="display:flex; gap:0.8rem; flex-wrap:wrap; flex:1;">
                <input type="text" name="search" class="search-input" placeholder="Search by name, email, WhatsApp, language, or budget..." value="<?= htmlspecialchars($search) ?>">
                
                <select name="status" class="form-control-admin" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="partial" <?= $statusFilter === 'partial' ? 'selected' : '' ?>>⚡ Partial (Abandoned)</option>
                    <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
                    <option value="contacted" <?= $statusFilter === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                    <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="converted" <?= $statusFilter === 'converted' ? 'selected' : '' ?>>Converted</option>
                    <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>

                <button type="submit" class="btn-admin btn-admin-sm">Filter</button>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <a href="inquiries.php" class="btn-admin btn-admin-outline btn-admin-sm">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Inquiries Table -->
        <div class="table-responsive-admin">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client Details</th>
                        <th>Language &amp; Budget</th>
                        <th>Project Scope &amp; Brief</th>
                        <th>Status</th>
                        <th>WhatsApp Connect</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding:3rem; opacity:0.6;">No inquiries found matching your filters.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inq): ?>
                            <?php $isHighlighted = ($selectedId === intval($inq['id'])); ?>
                            <tr style="<?= $isHighlighted ? 'background:rgba(6,182,212,0.1);' : '' ?>">
                                <td><strong>#<?= $inq['id'] ?></strong></td>
                                <td>
                                    <strong style="font-size:0.9rem; color:var(--white);"><?= htmlspecialchars($inq['name']) ?></strong><br>
                                    <span style="font-size:0.75rem; color:var(--cyan);"><?= htmlspecialchars($inq['phone']) ?></span><br>
                                    <span style="font-size:0.7rem; color:var(--faint);"><?= htmlspecialchars($inq['email']) ?></span><br>
                                    <span style="font-size:0.65rem; color:var(--dim);"><?= date('M j, Y - H:i', strtotime($inq['created_at'])) ?></span>
                                </td>
                                <td>
                                    <span class="badge" style="font-size:0.68rem; margin-bottom:0.3rem;">
                                        🗣 <?= htmlspecialchars($inq['preferred_language'] ?? 'English') ?>
                                    </span><br>
                                    <span style="font-size:0.75rem; font-weight:700; color:var(--white);">
                                        <?= htmlspecialchars($inq['budget'] ?? $inq['tier'] ?? 'Custom') ?>
                                    </span>
                                </td>
                                <td style="max-width:320px; line-height:1.6; font-size:0.75rem;">
                                    <span style="color:var(--cyan); font-weight:600;"><?= htmlspecialchars($inq['project_type'] ?? 'Custom Build') ?></span><br>
                                    <?= nl2br(htmlspecialchars($inq['requirements'])) ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= htmlspecialchars($inq['status']) ?>">
                                        <?= strtoupper($inq['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $inq['phone']);
                                        $lang = htmlspecialchars($inq['preferred_language'] ?? 'English');
                                        $prefill = urlencode("Hello {$inq['name']}, thank you for submitting your project brief to ZAMZY (Zamzy.in). Our solution architect is ready to discuss your {$inq['project_type']} requirements.");
                                    ?>
                                    <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $prefill ?>" target="_blank" class="btn-admin btn-admin-sm">
                                        💬 WhatsApp
                                    </a>
                                </td>
                                <td>
                                    <form action="inquiries.php" method="POST" style="display:flex; flex-direction:column; gap:0.4rem; min-width:130px;">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="id" value="<?= $inq['id'] ?>">
                                        
                                        <select name="status" class="form-control-admin" style="padding:0.4rem 0.6rem; font-size:0.7rem;">
                                            <option value="partial" <?= $inq['status'] === 'partial' ? 'selected' : '' ?>>⚡ Partial</option>
                                            <option value="new" <?= $inq['status'] === 'new' ? 'selected' : '' ?>>New</option>
                                            <option value="contacted" <?= $inq['status'] === 'contacted' ? 'selected' : '' ?>>Contacted</option>
                                            <option value="in_progress" <?= $inq['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="converted" <?= $inq['status'] === 'converted' ? 'selected' : '' ?>>Converted</option>
                                            <option value="archived" <?= $inq['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
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

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination-bar">
                <div style="font-family:var(--mono); font-size:0.75rem; color:var(--faint);">
                    Showing <?= $offset + 1 ?> to <?= min($totalInquiries, $offset + $limit) ?> of <?= $totalInquiries ?> inquiries
                </div>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="page-btn">← Prev</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="page-btn <?= $page === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="page-btn">Next →</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('adminMobileToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');

    if (toggleBtn && sidebar && overlay) {
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }
});
</script>
</body>
</html>
