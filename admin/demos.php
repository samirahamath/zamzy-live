<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_demo') {
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'pending');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_demo_requests` SET `status` = :st WHERE `id` = :id");
            $stmt->execute([':st' => $status, ':id' => $id]);
            $msg = "Demo request #$id marked as " . strtoupper($status);
        }
    }

    if ($action === 'delete_single') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("DELETE FROM `zamzy_demo_requests` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Demo request #$id deleted successfully.";
        }
    }

    if ($action === 'delete_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("DELETE FROM `zamzy_demo_requests` WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " demo requests deleted successfully.";
        }
    }

    if ($action === 'edit_demo') {
        $id = intval($_POST['id'] ?? 0);
        $product = trim($_POST['product_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_demo_requests` SET `product_name` = :p, `phone` = :ph, `email` = :em, `status` = :st WHERE `id` = :id");
            $stmt->execute([':p' => $product, ':ph' => $phone, ':em' => $email, ':st' => $status, ':id' => $id]);
            $msg = "Demo request #$id updated successfully.";
        }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ZAMZY Admin — SaaS Demo Requests</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
</head>
<body>

<!-- Mobile Admin Navigation Header -->
<div class="admin-mobile-header">
    <div class="admin-mobile-brand">
        <span class="admin-mobile-logo">ZAMZY<span>.</span></span>
        <span class="admin-mobile-tag">Executive Console</span>
    </div>
    <button class="admin-mobile-toggle" id="adminMobileToggle" aria-label="Toggle Navigation">
        ☰ Menu
    </button>
</div>

<!-- Floating FAB Mobile Menu Button -->
<button class="admin-floating-fab" id="adminFabToggle" aria-label="Open Navigation Menu">
    ⚡ Menu
</button>

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
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads</a>
                <a href="demos.php" class="admin-nav__item active"><span>⚡</span> Demo Requests</a>
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

        <!-- Bulk Action Bar -->
        <form id="bulkForm" action="demos.php" method="POST">
            <input type="hidden" name="action" value="delete_bulk">
            <div class="bulk-action-bar">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" id="selectAll" class="admin-checkbox">
                    <strong>Select All</strong>
                </label>
                <button type="submit" class="btn-danger-admin" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Are you sure you want to PERMANENTLY DELETE selected demo requests?')">
                    🗑️ Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <!-- Demo Requests Table -->
            <div class="table-responsive-admin">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>ID</th>
                            <th>Product / Platform</th>
                            <th>WhatsApp Contact</th>
                            <th>Email Destination</th>
                            <th>Status</th>
                            <th>Instant Dispatch</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($demos)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:3rem; opacity:0.6;">No demo requests submitted yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($demos as $d): ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?= $d['id'] ?>" class="admin-checkbox rowCheckbox">
                                    </td>
                                    <td><strong>#<?= $d['id'] ?></strong></td>
                                    <td>
                                        <strong style="color:var(--white);"><?= htmlspecialchars($d['product_name']) ?></strong><br>
                                        <span style="font-size:0.68rem; opacity:0.6;"><?= date('M j, Y - H:i', strtotime($d['created_at'])) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight:700; color:var(--cyan);"><?= htmlspecialchars($d['phone']) ?></span>
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
                                        <div style="display:flex; gap:0.4rem; align-items:center;">
                                            <button type="button" class="btn-edit-admin" onclick="openEditModal(<?= htmlspecialchars(json_encode($d)) ?>)">
                                                ✏️ Edit
                                            </button>
                                            <button type="button" class="btn-danger-admin" style="padding:0.35rem 0.6rem; font-size:0.72rem;" onclick="deleteSingleDemo(<?= $d['id'] ?>)">
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

    </main>

</div>

<!-- Single Delete Form -->
<form id="singleDeleteForm" action="demos.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_single">
    <input type="hidden" name="id" id="singleDeleteId" value="">
</form>

<!-- Edit Demo Request Modal -->
<div class="admin-modal" id="editModal">
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">✏️ Edit Demo Request</h3>
            <button class="admin-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="demos.php" method="POST" style="display:flex; flex-direction:column; gap:1rem;">
            <input type="hidden" name="action" value="edit_demo">
            <input type="hidden" name="id" id="editDemoId" value="">

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Product Engine</label>
                <input type="text" name="product_name" id="editProductName" class="search-input" style="width:100%;" required>
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
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Status</label>
                <select name="status" id="editStatus" class="form-control-admin" style="width:100%;">
                    <option value="pending">Pending</option>
                    <option value="dispatched">Dispatched</option>
                    <option value="contacted">Contacted</option>
                    <option value="closed">Closed</option>
                </select>
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
    const fabBtn = document.getElementById('adminFabToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminSidebarOverlay');

    const toggleMenu = () => {
        if (sidebar && overlay) {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        }
    };

    if (toggleBtn) toggleBtn.addEventListener('click', toggleMenu);
    if (fabBtn) fabBtn.addEventListener('click', toggleMenu);

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }

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

function deleteSingleDemo(id) {
    if (confirm('Are you sure you want to PERMANENTLY DELETE Demo Request #' + id + '?')) {
        document.getElementById('singleDeleteId').value = id;
        document.getElementById('singleDeleteForm').submit();
    }
}

function openEditModal(d) {
    document.getElementById('editDemoId').value = d.id;
    document.getElementById('editProductName').value = d.product_name || '';
    document.getElementById('editPhone').value = d.phone || '';
    document.getElementById('editEmail').value = d.email || '';
    document.getElementById('editStatus').value = d.status || 'pending';
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}
</script>
</body>
</html>
