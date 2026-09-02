<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['client_name'] ?? '');
        $company = trim($_POST['company_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $loc = trim($_POST['location'] ?? 'Anna Nagar, Chennai');
        $rating = intval($_POST['rating'] ?? 5);
        $project = trim($_POST['project_type'] ?? 'Custom SaaS Platform');
        $review = trim($_POST['review_text'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;

        if (!empty($name) && !empty($company) && !empty($review)) {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_testimonials` (`client_name`, `company_name`, `role`, `location`, `rating`, `project_type`, `review_text`, `is_featured`, `is_published`, `is_approved`) VALUES (:name, :company, :role, :loc, :rating, :project, :review, :featured, 1, 1)");
            $stmt->execute([
                ':name' => $name,
                ':company' => $company,
                ':role' => $role,
                ':loc' => $loc,
                ':rating' => $rating,
                ':project' => $project,
                ':review' => $review,
                ':featured' => $is_featured
            ]);
            $msg = 'Testimonial added and published successfully.';
        } else {
            $error = 'Please fill out required fields (Name, Company, Review).';
        }
    } elseif ($action === 'edit') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['client_name'] ?? '');
        $company = trim($_POST['company_name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $loc = trim($_POST['location'] ?? 'Anna Nagar, Chennai');
        $rating = intval($_POST['rating'] ?? 5);
        $project = trim($_POST['project_type'] ?? 'Custom SaaS Platform');
        $review = trim($_POST['review_text'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        if ($id > 0 && !empty($name) && !empty($company) && !empty($review)) {
            $stmt = $pdo->prepare("UPDATE `zamzy_testimonials` SET `client_name` = :name, `company_name` = :company, `role` = :role, `location` = :loc, `rating` = :rating, `project_type` = :project, `review_text` = :review, `is_featured` = :featured, `is_published` = :published, `is_approved` = :published WHERE `id` = :id");
            $stmt->execute([
                ':name' => $name,
                ':company' => $company,
                ':role' => $role,
                ':loc' => $loc,
                ':rating' => $rating,
                ':project' => $project,
                ':review' => $review,
                ':featured' => $is_featured,
                ':published' => $is_published,
                ':id' => $id
            ]);
            $msg = "Testimonial #$id updated successfully.";
        } else {
            $error = 'Please provide valid values for all required fields.';
        }
    } elseif ($action === 'toggle_publish') {
        $id = intval($_POST['id'] ?? 0);
        $status = intval($_POST['current_status'] ?? 0) === 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE `zamzy_testimonials` SET `is_published` = :st, `is_approved` = :st WHERE `id` = :id");
        $stmt->execute([':st' => $status, ':id' => $id]);
        $msg = 'Publish status updated.';
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM `zamzy_testimonials` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);
        $msg = 'Testimonial deleted.';
    } elseif ($action === 'delete_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("DELETE FROM `zamzy_testimonials` WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " testimonials deleted successfully.";
        }
    }
}

// Fetch all testimonials
$testimonials = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM `zamzy_testimonials` ORDER BY `id` DESC");
    $testimonials = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ZAMZY Admin — Reviews &amp; Social Proof</title>
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
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item active"><span>★</span> Reviews / Proof</a>
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
                <h1 class="admin-page-title">Reviews &amp; Social Proof</h1>
                <p class="admin-page-sub">Manage Client Testimonials, Ratings &amp; Featured Case Studies</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="testimonials.php" class="btn-admin btn-admin-outline">Refresh Data</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert-box" style="border-color:#ef4444; background:rgba(239,68,68,0.1); color:#ff6b6b;">⚠ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add Testimonial Card -->
        <div class="admin-card" style="margin-bottom:2rem;">
            <h3 style="font-family:var(--display); font-size:1.1rem; color:var(--white); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                ➕ Add New Client Testimonial
            </h3>
            <form action="testimonials.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1rem;">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Client Name *</label>
                        <input type="text" name="client_name" class="form-control-admin" placeholder="e.g. Samir A." required>
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Company / Organization *</label>
                        <input type="text" name="company_name" class="form-control-admin" placeholder="e.g. ShaCart Technologies" required>
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Role / Title</label>
                        <input type="text" name="role" class="form-control-admin" placeholder="e.g. Founder &amp; CEO">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1rem; margin-bottom:1rem;">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Location</label>
                        <input type="text" name="location" class="form-control-admin" placeholder="e.g. Anna Nagar, Chennai" value="Anna Nagar, Chennai">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Rating (Stars)</label>
                        <select name="rating" class="form-control-admin">
                            <option value="5" selected>★★★★★ (5 Stars)</option>
                            <option value="4">★★★★☆ (4 Stars)</option>
                            <option value="3">★★★☆☆ (3 Stars)</option>
                        </select>
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Project Scope</label>
                        <input type="text" name="project_type" class="form-control-admin" placeholder="e.g. Custom SaaS Platform" value="Custom SaaS Platform">
                    </div>
                </div>

                <div class="form-group-admin">
                    <label class="form-label-admin">Verified Testimonial Content *</label>
                    <textarea name="review_text" class="form-control-admin" style="min-height:90px;" placeholder="Paste the client testimonial here..." required></textarea>
                </div>

                <div style="margin-bottom:1.5rem; display:flex; align-items:center; gap:0.6rem;">
                    <input type="checkbox" id="featured" name="is_featured" value="1" checked class="admin-checkbox">
                    <label for="featured" style="font-size:0.75rem; font-family:var(--mono); color:var(--dim);">Feature on Public Landing Page</label>
                </div>

                <button type="submit" class="btn-admin">Save &amp; Publish Testimonial →</button>
            </form>
        </div>

        <!-- Bulk Action Bar -->
        <form id="bulkForm" action="testimonials.php" method="POST">
            <input type="hidden" name="action" value="delete_bulk">
            <div class="bulk-action-bar">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" id="selectAll" class="admin-checkbox">
                    <strong>Select All</strong>
                </label>
                <button type="submit" class="btn-danger-admin" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Are you sure you want to PERMANENTLY DELETE selected testimonials?')">
                    🗑️ Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <!-- Testimonials Table -->
            <div class="table-responsive-admin">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>ID</th>
                            <th>Client &amp; Company</th>
                            <th>Rating &amp; Scope</th>
                            <th>Review Text</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($testimonials)): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; padding:3rem; opacity:0.6;">No testimonials recorded yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($testimonials as $t): ?>
                                <?php 
                                    $isPub = intval($t['is_published'] ?? $t['is_approved'] ?? 1); 
                                    $isFeat = intval($t['is_featured'] ?? 1);
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?= $t['id'] ?>" class="admin-checkbox rowCheckbox">
                                    </td>
                                    <td><strong>#<?= $t['id'] ?></strong></td>
                                    <td>
                                        <strong style="color:var(--white);"><?= htmlspecialchars($t['client_name']) ?></strong><br>
                                        <span style="font-size:0.7rem; color:var(--cyan);"><?= htmlspecialchars($t['role'] ?? '') ?> @ <?= htmlspecialchars($t['company_name']) ?></span><br>
                                        <span style="font-size:0.65rem; color:var(--faint);">📍 <?= htmlspecialchars($t['location'] ?? 'Chennai') ?></span>
                                    </td>
                                    <td>
                                        <span style="color:var(--cyan);"><?= str_repeat('★', intval($t['rating'] ?? 5)) ?></span><br>
                                        <span style="font-size:0.68rem; color:var(--faint);">
                                            <?= htmlspecialchars($t['project_type'] ?? 'Custom Software') ?>
                                        </span>
                                    </td>
                                    <td style="max-width:320px; line-height:1.6; font-size:0.75rem;">
                                        "<?= htmlspecialchars($t['review_text']) ?>"
                                    </td>
                                    <td>
                                        <?php if ($isPub): ?>
                                            <span class="badge-status converted">Live</span>
                                        <?php else: ?>
                                            <span class="badge-status">Hidden</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.4rem; align-items:center;">
                                            <button type="button" class="btn-edit-admin edit-test-btn"
                                                data-id="<?= $t['id'] ?>"
                                                data-name="<?= htmlspecialchars($t['client_name'], ENT_QUOTES) ?>"
                                                data-company="<?= htmlspecialchars($t['company_name'], ENT_QUOTES) ?>"
                                                data-role="<?= htmlspecialchars($t['role'] ?? '', ENT_QUOTES) ?>"
                                                data-location="<?= htmlspecialchars($t['location'] ?? '', ENT_QUOTES) ?>"
                                                data-rating="<?= intval($t['rating'] ?? 5) ?>"
                                                data-project="<?= htmlspecialchars($t['project_type'] ?? '', ENT_QUOTES) ?>"
                                                data-review="<?= htmlspecialchars($t['review_text'], ENT_QUOTES) ?>"
                                                data-featured="<?= $isFeat ?>"
                                                data-published="<?= $isPub ?>">
                                                ✏️ Edit
                                            </button>

                                            <button type="button" class="btn-danger-admin" style="padding:0.35rem 0.6rem; font-size:0.72rem;" onclick="deleteSingleTestimonial(<?= $t['id'] ?>)">
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
<form id="singleDeleteForm" action="testimonials.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="singleDeleteId" value="">
</form>

<!-- Edit Testimonial Modal -->
<div class="admin-modal" id="edit-test-modal">
    <div class="admin-modal-content" style="max-width:620px;">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">✏️ Edit Testimonial</h3>
            <button class="admin-modal-close" id="close-edit-modal">&times;</button>
        </div>
        <form action="testimonials.php" method="POST" style="display:flex; flex-direction:column; gap:1rem;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                <div>
                    <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Client Name *</label>
                    <input type="text" name="client_name" id="edit_name" class="search-input" style="width:100%;" required>
                </div>
                <div>
                    <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Company Name *</label>
                    <input type="text" name="company_name" id="edit_company" class="search-input" style="width:100%;" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                <div>
                    <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Role / Title</label>
                    <input type="text" name="role" id="edit_role" class="search-input" style="width:100%;">
                </div>
                <div>
                    <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Location</label>
                    <input type="text" name="location" id="edit_location" class="search-input" style="width:100%;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem;">
                <div>
                    <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Rating (Stars)</label>
                    <select name="rating" id="edit_rating" class="form-control-admin" style="width:100%;">
                        <option value="5">★★★★★ (5 Stars)</option>
                        <option value="4">★★★★☆ (4 Stars)</option>
                        <option value="3">★★★☆☆ (3 Stars)</option>
                    </select>
                </div>
                <div>
                    <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Project Scope</label>
                    <input type="text" name="project_type" id="edit_project" class="search-input" style="width:100%;">
                </div>
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Review Content *</label>
                <textarea name="review_text" id="edit_review" class="search-input" style="width:100%; min-height:100px; font-family:sans-serif; line-height:1.5;" required></textarea>
            </div>

            <div style="display:flex; gap:1.5rem; margin-top:0.4rem;">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="is_featured" id="edit_featured" value="1" class="admin-checkbox">
                    <span style="font-family:var(--mono); font-size:0.75rem;">Feature on Home</span>
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" name="is_published" id="edit_published" value="1" class="admin-checkbox">
                    <span style="font-family:var(--mono); font-size:0.75rem;">Publish Live</span>
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top:0.8rem;">
                <button type="button" class="btn-admin btn-admin-outline" id="close-modal-btn">Cancel</button>
                <button type="submit" class="btn-admin">Update Testimonial →</button>
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

    // Edit Modal logic
    const modal = document.getElementById('edit-test-modal');
    const closeBtn = document.getElementById('close-edit-modal');
    const cancelBtn = document.getElementById('close-modal-btn');

    function closeModal() { modal.classList.remove('open'); }
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

    document.querySelectorAll('.edit-test-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_name').value = btn.getAttribute('data-name');
            document.getElementById('edit_company').value = btn.getAttribute('data-company');
            document.getElementById('edit_role').value = btn.getAttribute('data-role');
            document.getElementById('edit_location').value = btn.getAttribute('data-location');
            document.getElementById('edit_rating').value = btn.getAttribute('data-rating');
            document.getElementById('edit_project').value = btn.getAttribute('data-project');
            document.getElementById('edit_review').value = btn.getAttribute('data-review');
            document.getElementById('edit_featured').checked = (btn.getAttribute('data-featured') === '1');
            document.getElementById('edit_published').checked = (btn.getAttribute('data-published') === '1');
            modal.classList.add('open');
        });
    });
});

function deleteSingleTestimonial(id) {
    if (confirm('Are you sure you want to PERMANENTLY DELETE Testimonial #' + id + '?')) {
        document.getElementById('singleDeleteId').value = id;
        document.getElementById('singleDeleteForm').submit();
    }
}
</script>
</body>
</html>
