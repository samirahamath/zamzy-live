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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY Admin — Reviews &amp; Social Proof</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="admin-shell">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div>
            <div class="admin-sidebar__brand">
                <span class="admin-sidebar__logo">ZAMZY<span>.</span></span>
                <span class="admin-sidebar__sub">Executive Control</span>
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
                <h1 class="admin-page-title">Reviews &amp; Proof</h1>
                <p class="admin-page-sub">Manage and edit verified client testimonials published on the public showcase</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="#add-form" class="btn-admin">+ Add New Testimonial</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-box" style="border-color:#ef4444; background:rgba(239, 68, 68, 0.15); color:#fca5a5;">✕ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Add Testimonial Card -->
        <div class="admin-card" id="add-form">
            <h3 class="admin-card__title">+ Publish Verified Client Review</h3>
            <form action="testimonials.php" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.2rem; margin-bottom:1.2rem;">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Client Full Name *</label>
                        <input type="text" name="client_name" class="form-control-admin" placeholder="e.g. Karthik Subramanian" required>
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Company / Brand *</label>
                        <input type="text" name="company_name" class="form-control-admin" placeholder="e.g. Apex Retail" required>
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Role / Title</label>
                        <input type="text" name="role" class="form-control-admin" placeholder="e.g. Founder & CEO">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Location</label>
                        <input type="text" name="location" class="form-control-admin" placeholder="e.g. Anna Nagar, Chennai" value="Anna Nagar, Chennai">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Star Rating</label>
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
                    <input type="checkbox" id="featured" name="is_featured" value="1" checked>
                    <label for="featured" style="font-size:0.75rem; font-family:var(--mono); color:var(--dim);">Feature on Public Landing Page</label>
                </div>

                <button type="submit" class="btn-admin">Save &amp; Publish Testimonial →</button>
            </form>
        </div>

        <!-- Existing Testimonials Table with EDIT Capability -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
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
                            <td colspan="6" style="text-align:center; padding:3rem; opacity:0.6;">No testimonials recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($testimonials as $t): ?>
                            <?php 
                                $isPub = intval($t['is_published'] ?? $t['is_approved'] ?? 1); 
                                $isFeat = intval($t['is_featured'] ?? 1);
                            ?>
                            <tr>
                                <td><strong>#<?= $t['id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($t['client_name']) ?></strong><br>
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
                                    <div style="display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap;">
                                        <!-- Edit Button (Opens Modal) -->
                                        <button type="button" class="btn-admin btn-admin-sm edit-test-btn"
                                            data-id="<?= $t['id'] ?>"
                                            data-name="<?= htmlspecialchars($t['client_name'], ENT_QUOTES) ?>"
                                            data-company="<?= htmlspecialchars($t['company_name'], ENT_QUOTES) ?>"
                                            data-role="<?= htmlspecialchars($t['role'] ?? '', ENT_QUOTES) ?>"
                                            data-location="<?= htmlspecialchars($t['location'] ?? '', ENT_QUOTES) ?>"
                                            data-rating="<?= intval($t['rating'] ?? 5) ?>"
                                            data-project="<?= htmlspecialchars($t['project_type'] ?? '', ENT_QUOTES) ?>"
                                            data-review="<?= htmlspecialchars($t['review_text'], ENT_QUOTES) ?>"
                                            data-featured="<?= $isFeat ?>"
                                            data-published="<?= $isPub ?>"
                                            style="padding:0.35rem 0.75rem;">
                                            ✏️ Edit
                                        </button>

                                        <form action="testimonials.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_publish">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $isPub ?>">
                                            <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm" style="padding:0.35rem 0.65rem;">
                                                <?= $isPub ? 'Hide' : 'Publish' ?>
                                            </button>
                                        </form>

                                        <form action="testimonials.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this testimonial permanently?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                            <button type="submit" class="btn-admin btn-admin-sm" style="background:#ef4444; border-color:#ef4444; padding:0.35rem 0.65rem;">✕</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</div>

<!-- Edit Testimonial Modal -->
<div class="modal-backdrop" id="edit-test-modal" style="display:none; position:fixed; inset:0; background:rgba(5,5,5,0.85); backdrop-filter:blur(14px); z-index:9999; align-items:center; justify-content:center; padding:1.5rem;">
    <div class="admin-card" style="max-width:680px; width:100%; position:relative; box-shadow:0 0 50px rgba(6,182,212,0.3); border-color:var(--cyan);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
            <h3 class="admin-card__title" style="margin-bottom:0;">✏️ Edit Testimonial #<span id="edit-id-display"></span></h3>
            <span id="close-edit-modal" style="font-size:1.6rem; cursor:pointer; color:var(--dim); line-height:1;">&times;</span>
        </div>

        <form action="testimonials.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit-id">

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:1rem;">
                <div class="form-group-admin">
                    <label class="form-label-admin">Client Name *</label>
                    <input type="text" name="client_name" id="edit-name" class="form-control-admin" required>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Company *</label>
                    <input type="text" name="company_name" id="edit-company" class="form-control-admin" required>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Role</label>
                    <input type="text" name="role" id="edit-role" class="form-control-admin">
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Location</label>
                    <input type="text" name="location" id="edit-location" class="form-control-admin">
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Star Rating</label>
                    <select name="rating" id="edit-rating" class="form-control-admin">
                        <option value="5">★★★★★ (5 Stars)</option>
                        <option value="4">★★★★☆ (4 Stars)</option>
                        <option value="3">★★★☆☆ (3 Stars)</option>
                    </select>
                </div>
                <div class="form-group-admin">
                    <label class="form-label-admin">Project Scope</label>
                    <input type="text" name="project_type" id="edit-project" class="form-control-admin">
                </div>
            </div>

            <div class="form-group-admin" style="margin-bottom:1rem;">
                <label class="form-label-admin">Testimonial Content *</label>
                <textarea name="review_text" id="edit-review" class="form-control-admin" style="min-height:90px;" required></textarea>
            </div>

            <div style="display:flex; gap:1.6rem; margin-bottom:1.4rem; flex-wrap:wrap;">
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.75rem; font-family:var(--mono);">
                    <input type="checkbox" name="is_featured" id="edit-featured" value="1">
                    Featured on Landing Page
                </label>
                <label style="display:flex; align-items:center; gap:0.5rem; font-size:0.75rem; font-family:var(--mono);">
                    <input type="checkbox" name="is_published" id="edit-published" value="1">
                    Live / Published
                </label>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.8rem;">
                <button type="button" id="cancel-edit-modal" class="btn-admin btn-admin-outline">Cancel</button>
                <button type="submit" class="btn-admin">Update Testimonial →</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('edit-test-modal');
    const closeBtn = document.getElementById('close-edit-modal');
    const cancelBtn = document.getElementById('cancel-edit-modal');

    document.querySelectorAll('.edit-test-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-id').value = btn.dataset.id;
            document.getElementById('edit-id-display').textContent = btn.dataset.id;
            document.getElementById('edit-name').value = btn.dataset.name;
            document.getElementById('edit-company').value = btn.dataset.company;
            document.getElementById('edit-role').value = btn.dataset.role;
            document.getElementById('edit-location').value = btn.dataset.location;
            document.getElementById('edit-rating').value = btn.dataset.rating;
            document.getElementById('edit-project').value = btn.dataset.project;
            document.getElementById('edit-review').value = btn.dataset.review;
            document.getElementById('edit-featured').checked = btn.dataset.featured == '1';
            document.getElementById('edit-published').checked = btn.dataset.published == '1';

            editModal.style.display = 'flex';
        });
    });

    function hideModal() {
        editModal.style.display = 'none';
    }

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);
    if (editModal) {
        editModal.addEventListener('click', (e) => {
            if (e.target === editModal) hideModal();
        });
    }
});
</script>

</body>
</html>
