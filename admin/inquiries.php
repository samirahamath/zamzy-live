<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';

// Handle Status & Notes Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_status') {
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'new');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_inquiries` SET `status` = :status WHERE `id` = :id");
            $stmt->execute([':status' => $status, ':id' => $id]);
            $msg = "Inquiry #$id marked as " . strtoupper($status);
        }
    }

    if ($action === 'delete_single') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("DELETE FROM `zamzy_inquiries` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Inquiry #$id deleted successfully.";
        }
    }

    if ($action === 'delete_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("DELETE FROM `zamzy_inquiries` WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " inquiries deleted successfully.";
        }
    }

    if ($action === 'edit_inquiry') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'new');
        $reqs = trim($_POST['requirements'] ?? '');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_inquiries` SET `name` = :name, `phone` = :phone, `email` = :email, `status` = :status, `requirements` = :reqs WHERE `id` = :id");
            $stmt->execute([':name' => $name, ':phone' => $phone, ':email' => $email, ':status' => $status, ':reqs' => $reqs, ':id' => $id]);
            $msg = "Inquiry #$id updated successfully.";
        }
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

        <!-- Bulk Action Bar -->
        <form id="bulkForm" action="inquiries.php" method="POST">
            <input type="hidden" name="action" value="delete_bulk">
            <div class="bulk-action-bar">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" id="selectAll" class="admin-checkbox">
                    <strong>Select All</strong>
                </label>
                <button type="submit" class="btn-danger-admin" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Are you sure you want to PERMANENTLY DELETE all selected inquiries?')">
                    🗑️ Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <!-- Inquiries Table -->
            <div class="table-responsive-admin">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>ID</th>
                            <th>Client Details</th>
                            <th>Language &amp; Budget</th>
                            <th>Project Scope &amp; Brief</th>
                            <th>Status</th>
                            <th>WhatsApp Connect</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($inquiries)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:3rem; opacity:0.6;">No inquiries found matching your filters.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inquiries as $inq): ?>
                                <?php $isHighlighted = ($selectedId === intval($inq['id'])); ?>
                                <tr style="<?= $isHighlighted ? 'background:rgba(6,182,212,0.1);' : '' ?>">
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?= $inq['id'] ?>" class="admin-checkbox rowCheckbox">
                                    </td>
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
                                    <td style="max-width:300px; line-height:1.6; font-size:0.75rem;">
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
                                            $prefill = urlencode("Hello {$inq['name']}, thank you for submitting your project brief to ZAMZY (Zamzy.in). Our solution architect is ready to discuss your {$inq['project_type']} requirements.");
                                        ?>
                                        <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $prefill ?>" target="_blank" class="btn-admin btn-admin-sm">
                                            💬 WhatsApp
                                        </a>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.4rem; align-items:center;">
                                            <button type="button" class="btn-edit-admin" onclick="openEditModal(<?= htmlspecialchars(json_encode($inq)) ?>)">
                                                ✏️ Edit
                                            </button>
                                            <button type="button" class="btn-danger-admin" style="padding:0.35rem 0.6rem; font-size:0.72rem;" onclick="deleteSingleInquiry(<?= $inq['id'] ?>)">
                                                🗑️
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>

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

<!-- Single Delete Form -->
<form id="singleDeleteForm" action="inquiries.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_single">
    <input type="hidden" name="id" id="singleDeleteId" value="">
</form>

<!-- Edit Inquiry Modal Dialog -->
<div class="admin-modal" id="editModal">
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">✏️ Edit Inquiry Brief</h3>
            <button class="admin-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="inquiries.php" method="POST" style="display:flex; flex-direction:column; gap:1rem;">
            <input type="hidden" name="action" value="edit_inquiry">
            <input type="hidden" name="id" id="editInquiryId" value="">

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Client Full Name</label>
                <input type="text" name="name" id="editName" class="search-input" style="width:100%;" required>
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">WhatsApp / Phone Number</label>
                <input type="text" name="phone" id="editPhone" class="search-input" style="width:100%;" required>
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Email Address</label>
                <input type="email" name="email" id="editEmail" class="search-input" style="width:100%;">
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Lead Status</label>
                <select name="status" id="editStatus" class="form-control-admin" style="width:100%;">
                    <option value="partial">⚡ Partial (Abandoned)</option>
                    <option value="new">New</option>
                    <option value="contacted">Contacted</option>
                    <option value="in_progress">In Progress</option>
                    <option value="converted">Converted</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Project Requirements / Notes</label>
                <textarea name="requirements" id="editRequirements" class="search-input" style="width:100%; min-height:110px; font-family:sans-serif; line-height:1.5;"></textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top:0.8rem;">
                <button type="button" class="btn-admin btn-admin-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-admin">Save Changes</button>
            </div>
        </form>
    </div>
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

    // Checkbox Multiple Select & Bulk Delete Controls
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.rowCheckbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');

    function updateBulkState() {
        const checked = document.querySelectorAll('.rowCheckbox:checked');
        const count = checked.length;
        selectedCount.textContent = count;
        if (count > 0) {
            bulkDeleteBtn.style.display = 'inline-flex';
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', (e) => {
            rowCheckboxes.forEach(cb => cb.checked = e.target.checked);
            updateBulkState();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            if (!cb.checked && selectAll) selectAll.checked = false;
            updateBulkState();
        });
    });
});

function deleteSingleInquiry(id) {
    if (confirm('Are you sure you want to PERMANENTLY DELETE Inquiry #' + id + '?')) {
        document.getElementById('singleDeleteId').value = id;
        document.getElementById('singleDeleteForm').submit();
    }
}

function openEditModal(inq) {
    document.getElementById('editInquiryId').value = inq.id;
    document.getElementById('editName').value = inq.name || '';
    document.getElementById('editPhone').value = inq.phone || '';
    document.getElementById('editEmail').value = inq.email || '';
    document.getElementById('editStatus').value = inq.status || 'new';
    document.getElementById('editRequirements').value = inq.requirements || '';
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}
</script>
</body>
</html>
