<?php
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';
$error = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Add Job Opening
    if ($action === 'add_job') {
        $title = trim($_POST['title'] ?? '');
        $dept = trim($_POST['department'] ?? 'Engineering');
        $type = trim($_POST['employment_type'] ?? 'Freelance / Project-Based');
        $loc = trim($_POST['location'] ?? 'Remote / Chennai');
        $exp = trim($_POST['experience_level'] ?? 'Students & Freelancers');
        $stipend = trim($_POST['stipend_salary'] ?? 'Project Commission');
        $desc = trim($_POST['description'] ?? '');
        $reqs = trim($_POST['requirements'] ?? '');

        if (!empty($title) && !empty($desc)) {
            $stmt = $pdo->prepare("INSERT INTO `zamzy_careers_jobs` (`title`, `department`, `employment_type`, `location`, `experience_level`, `stipend_salary`, `description`, `requirements`, `is_active`) VALUES (:title, :dept, :type, :loc, :exp, :stipend, :desc, :reqs, 1)");
            $stmt->execute([
                ':title' => $title,
                ':dept' => $dept,
                ':type' => $type,
                ':loc' => $loc,
                ':exp' => $exp,
                ':stipend' => $stipend,
                ':desc' => $desc,
                ':reqs' => $reqs
            ]);
            $msg = "New role '$title' published successfully.";
        } else {
            $error = "Please fill in title and description.";
        }
    }

    // 2. Toggle Job Active Status
    elseif ($action === 'toggle_job') {
        $id = intval($_POST['id'] ?? 0);
        $status = intval($_POST['current_status'] ?? 0) === 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE `zamzy_careers_jobs` SET `is_active` = :st WHERE `id` = :id");
        $stmt->execute([':st' => $status, ':id' => $id]);
        $msg = "Role status updated.";
    }

    // 3. Delete Job
    elseif ($action === 'delete_job') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM `zamzy_careers_jobs` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);
        $msg = "Role removed.";
    }

    // 4. Update Application Status & Notes
    elseif ($action === 'update_application') {
        $id = intval($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? 'new');
        $notes = trim($_POST['internal_notes'] ?? '');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_careers_applications` SET `status` = :status, `internal_notes` = :notes WHERE `id` = :id");
            $stmt->execute([':status' => $status, ':notes' => $notes, ':id' => $id]);
            $msg = "Application #$id updated to " . strtoupper($status) . ".";
        }
    }

    // 5. Delete Single Application
    elseif ($action === 'delete_app') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("DELETE FROM `zamzy_careers_applications` WHERE `id` = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Application #$id deleted.";
        }
    }

    // 6. Delete Bulk Applications
    elseif ($action === 'delete_bulk_apps') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("DELETE FROM `zamzy_careers_applications` WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " applications deleted.";
        }
    }

    // 7. Edit Application
    elseif ($action === 'edit_application') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $college = trim($_POST['location_college'] ?? '');
        $skills = trim($_POST['primary_skills'] ?? '');
        $status = trim($_POST['status'] ?? 'new');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_careers_applications` SET `full_name` = :n, `phone` = :p, `email` = :e, `location_college` = :c, `primary_skills` = :s, `status` = :st WHERE `id` = :id");
            $stmt->execute([':n' => $name, ':p' => $phone, ':e' => $email, ':c' => $college, ':s' => $skills, ':st' => $status, ':id' => $id]);
            $msg = "Application #$id updated successfully.";
        }
    }
}

// ----------------------------------------------------
// Applications Query with Search, Filter & Pagination
// ----------------------------------------------------
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$skillFilter = trim($_GET['skill'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];

if (!empty($statusFilter)) {
    $where[] = "`status` = :status";
    $params[':status'] = $statusFilter;
}
if (!empty($skillFilter)) {
    $where[] = "`primary_skills` LIKE :skill";
    $params[':skill'] = "%$skillFilter%";
}
if (!empty($search)) {
    $where[] = "(`full_name` LIKE :s OR `email` LIKE :s OR `phone` LIKE :s OR `location_college` LIKE :s OR `primary_skills` LIKE :s OR `past_work_notes` LIKE :s)";
    $params[':s'] = "%$search%";
}

$whereClause = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";

// Get Total Count
$countSql = "SELECT COUNT(*) FROM `zamzy_careers_applications`" . $whereClause;
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalApplicants = $countStmt->fetchColumn();
$totalPages = ceil($totalApplicants / $limit);

// Fetch Paginated Applications
$appSql = "SELECT * FROM `zamzy_careers_applications`" . $whereClause . " ORDER BY `id` DESC LIMIT $limit OFFSET $offset";
$appStmt = $pdo->prepare($appSql);
$appStmt->execute($params);
$applications = $appStmt->fetchAll();

// Fetch All Job Openings
$jobs = [];
if ($pdo) {
    $stmt = $pdo->query("SELECT * FROM `zamzy_careers_jobs` ORDER BY `id` DESC");
    $jobs = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ZAMZY Admin — Careers &amp; Freelance Guild Pool</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
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
                <span class="admin-sidebar__sub">Executive Control</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__item"><span>📊</span> Dashboard</a>
                <a href="inquiries.php" class="admin-nav__item"><span>📬</span> Inquiries &amp; Leads</a>
                <a href="demos.php" class="admin-nav__item"><span>⚡</span> Demo Requests</a>
                <a href="chats.php" class="admin-nav__item"><span>💬</span> Chat Reports</a>
                <a href="testimonials.php" class="admin-nav__item"><span>★</span> Reviews / Proof</a>
                <a href="careers.php" class="admin-nav__item active"><span>👥</span> Careers &amp; Guild</a>
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
                <h1 class="admin-page-title">Careers &amp; Freelance Developer Guild</h1>
                <p class="admin-page-sub">Match College Developers, Freelancers &amp; Talent Pool to Paid Projects</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="#add-role" class="btn-admin">+ Post New Role</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-box" style="border-color:#ef4444; background:rgba(239, 68, 68, 0.15); color:#fca5a5;">✕ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Stats Overview KPI Cards -->
        <div class="kpi-grid" style="margin-bottom:2.5rem;">
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Registered Freelancers</span>
                    <span class="badge">Talent Pool</span>
                </div>
                <div class="kpi-card__val"><?= number_format($totalApplicants) ?></div>
                <div class="kpi-card__sub">College Developers &amp; Specialists</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Active Job Roles</span>
                    <span class="badge">Live</span>
                </div>
                <div class="kpi-card__val"><?= count($jobs) ?></div>
                <div class="kpi-card__sub">Open Listings on Careers Page</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">New Applications</span>
                    <span class="badge" style="border-color:var(--cyan); color:var(--cyan);">Unscreened</span>
                </div>
                <div class="kpi-card__val" style="color:var(--cyan);"><?= number_format(count(array_filter($applications, fn($a) => $a['status'] === 'new'))) ?></div>
                <div class="kpi-card__sub">Pending Direct WhatsApp Outreach</div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             SECTION 1: TALENT APPLICATIONS PIPELINE
        ═══════════════════════════════════════════════ -->
        <div style="margin-bottom:2.5rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem; flex-wrap:wrap; gap:1rem;">
                <h2 style="font-family:var(--display); font-size:1.4rem; font-weight:700; color:var(--white);">
                    Talent Applications &amp; Freelancer Network
                </h2>
                <span class="badge">Showing Page <?= $page ?> of <?= max(1, $totalPages) ?> (<?= $totalApplicants ?> total records)</span>
            </div>

            <!-- Filter Toolbar -->
            <div class="filter-toolbar">
                <form action="careers.php" method="GET" style="display:flex; gap:0.8rem; flex-wrap:wrap; flex:1;">
                    <input type="text" name="search" class="search-input" placeholder="Search by name, college, location, skills, or portfolio..." value="<?= htmlspecialchars($search) ?>">
                    
                    <select name="status" class="form-control-admin" style="width:auto;" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>New</option>
                        <option value="shortlisted" <?= $statusFilter === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                        <option value="assigned_project" <?= $statusFilter === 'assigned_project' ? 'selected' : '' ?>>Assigned Project</option>
                        <option value="active_guild" <?= $statusFilter === 'active_guild' ? 'selected' : '' ?>>Active Guild Member</option>
                        <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>

                    <button type="submit" class="btn-admin btn-admin-sm">Filter</button>
                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                        <a href="careers.php" class="btn-admin btn-admin-outline btn-admin-sm">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Bulk Action Bar -->
            <form id="bulkForm" action="careers.php" method="POST">
                <input type="hidden" name="action" value="delete_bulk_apps">
                <div class="bulk-action-bar">
                    <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <input type="checkbox" id="selectAll" class="admin-checkbox">
                        <strong>Select All</strong>
                    </label>
                    <button type="submit" class="btn-danger-admin" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Are you sure you want to PERMANENTLY DELETE selected candidate applications?')">
                        🗑️ Delete Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>

                <!-- Applications Table -->
                <div class="table-responsive-admin">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>ID</th>
                                <th>Candidate Details</th>
                                <th>College &amp; Location</th>
                                <th>Primary Skills &amp; Experience</th>
                                <th>Availability &amp; Payout</th>
                                <th>Portfolio / Past Work</th>
                                <th>Status</th>
                                <th>Direct Contact</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applications)): ?>
                                <tr>
                                    <td colspan="10" style="text-align:center; padding:3rem; opacity:0.6;">No applications found matching your criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="ids[]" value="<?= $app['id'] ?>" class="admin-checkbox rowCheckbox">
                                        </td>
                                        <td><strong>#<?= $app['id'] ?></strong></td>
                                        <td>
                                            <strong style="color:var(--white); font-size:0.9rem;"><?= htmlspecialchars($app['full_name']) ?></strong><br>
                                            <span style="font-size:0.75rem; color:var(--cyan);"><?= htmlspecialchars($app['phone']) ?></span><br>
                                            <span style="font-size:0.7rem; color:var(--faint);"><?= htmlspecialchars($app['email']) ?></span><br>
                                            <span style="font-size:0.65rem; color:var(--dim);"><?= date('M j, Y', strtotime($app['created_at'])) ?></span>
                                        </td>
                                        <td>
                                            <span style="font-weight:600; color:var(--white); font-size:0.8rem;">
                                                🎓 <?= htmlspecialchars($app['location_college']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="color:var(--cyan); font-weight:700; font-size:0.8rem;">
                                                <?= htmlspecialchars($app['primary_skills']) ?>
                                            </span><br>
                                            <span class="badge" style="font-size:0.65rem; margin-top:0.3rem;">
                                                <?= htmlspecialchars($app['experience_level']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size:0.75rem; color:var(--white);">⏱ <?= htmlspecialchars($app['availability_hours']) ?></span><br>
                                            <span style="font-size:0.7rem; color:var(--faint);">💰 <?= htmlspecialchars($app['expected_payout']) ?></span>
                                        </td>
                                        <td style="max-width:240px; font-size:0.75rem; line-height:1.5;">
                                            <?php if (!empty($app['portfolio_url'])): ?>
                                                <a href="<?= htmlspecialchars($app['portfolio_url']) ?>" target="_blank" style="color:var(--cyan); text-decoration:underline;">
                                                    🔗 [Portfolio / GitHub]
                                                </a><br>
                                            <?php endif; ?>
                                            <?php if (!empty($app['resume_file'])): ?>
                                                <a href="../<?= htmlspecialchars($app['resume_file']) ?>" target="_blank" class="badge" style="display:inline-block; margin:0.25rem 0; background:rgba(6,182,212,0.15); color:var(--cyan); border:1px solid rgba(6,182,212,0.4); text-decoration:none; padding:3px 7px;">
                                                    📄 View Resume
                                                </a><br>
                                            <?php endif; ?>
                                            <span style="color:var(--dim); font-size:0.7rem;"><?= nl2br(htmlspecialchars($app['past_work_notes'] ?? '—')) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge-status <?= htmlspecialchars($app['status']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', $app['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $cleanPhone = preg_replace('/[^0-9]/', '', $app['phone']);
                                                $prefill = urlencode("Hello {$app['full_name']}! This is ZAMZY Digital Engineering (Zamzy.in). We reviewed your {$app['primary_skills']} profile for our Developer Guild. We have a client project sprint that matches your skillset.");
                                            ?>
                                            <a href="https://wa.me/<?= $cleanPhone ?>?text=<?= $prefill ?>" target="_blank" class="btn-admin btn-admin-sm">
                                                💬 WhatsApp Lead
                                            </a>
                                        </td>
                                        <td>
                                            <div style="display:flex; gap:0.4rem; align-items:center;">
                                                <button type="button" class="btn-edit-admin" onclick="openEditModal(<?= htmlspecialchars(json_encode($app)) ?>)">
                                                    ✏️ Edit
                                                </button>
                                                <button type="button" class="btn-danger-admin" style="padding:0.35rem 0.6rem; font-size:0.72rem;" onclick="deleteSingleApp(<?= $app['id'] ?>)">
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
                        Showing <?= $offset + 1 ?> to <?= min($totalApplicants, $offset + $limit) ?> of <?= $totalApplicants ?> candidates
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
        </div>

        <!-- ═══════════════════════════════════════════════
             SECTION 2: MANAGE JOB ROLES & OPENINGS
        ═══════════════════════════════════════════════ -->
        <div style="margin-top:3rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem;">
                <h2 style="font-family:var(--display); font-size:1.4rem; font-weight:700; color:var(--white);">
                    Manage Public Job Openings (careers.php)
                </h2>
                <a href="../careers" target="_blank" class="btn-admin btn-admin-outline btn-admin-sm">↗ View Careers Page</a>
            </div>

            <!-- Add Job Card -->
            <div class="admin-card" id="add-role" style="margin-bottom:2rem;">
                <h3 class="admin-card__title">+ Publish New Role / Opening</h3>
                <form action="careers.php" method="POST">
                    <input type="hidden" name="action" value="add_job">

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.2rem; margin-bottom:1.2rem;">
                        <div class="form-group-admin">
                            <label class="form-label-admin">Role Title *</label>
                            <input type="text" name="title" class="form-control-admin" placeholder="e.g. Flutter Mobile Developer" required>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Department *</label>
                            <input type="text" name="department" class="form-control-admin" placeholder="e.g. Mobile Engineering" required>
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Employment Model</label>
                            <input type="text" name="employment_type" class="form-control-admin" placeholder="e.g. Project Commission / Freelance" value="Freelance / Project-Based">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Location</label>
                            <input type="text" name="location" class="form-control-admin" placeholder="e.g. Remote / Chennai" value="Remote / Chennai">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Experience Level</label>
                            <input type="text" name="experience_level" class="form-control-admin" placeholder="e.g. Students & Freelancers" value="Students & Freelancers">
                        </div>
                        <div class="form-group-admin">
                            <label class="form-label-admin">Stipend / Commission Payout</label>
                            <input type="text" name="stipend_salary" class="form-control-admin" placeholder="e.g. ₹20,000 – ₹45,000 per project" value="₹15,000 – ₹45,000 per project milestone">
                        </div>
                    </div>

                    <div class="form-group-admin" style="margin-bottom:1.2rem;">
                        <label class="form-label-admin">Role Description *</label>
                        <textarea name="description" class="form-control-admin" style="min-height:80px;" placeholder="Describe what the developer will build..." required></textarea>
                    </div>

                    <div class="form-group-admin" style="margin-bottom:1.4rem;">
                        <label class="form-label-admin">Key Requirements *</label>
                        <input type="text" name="requirements" class="form-control-admin" placeholder="e.g. Flutter & Dart, Riverpod, API integration, Git" required>
                    </div>

                    <button type="submit" class="btn-admin">Publish Role to Careers Page →</button>
                </form>
            </div>

            <!-- Existing Jobs Table -->
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Role &amp; Department</th>
                            <th>Type &amp; Location</th>
                            <th>Stipend / Commission</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jobs)): ?>
                            <tr>
                                <td colspan="6" style="text-align:center; padding:2.5rem; opacity:0.6;">No job roles created yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($jobs as $j): ?>
                                <tr>
                                    <td><strong>#<?= $j['id'] ?></strong></td>
                                    <td>
                                        <strong style="color:var(--white);"><?= htmlspecialchars($j['title']) ?></strong><br>
                                        <span style="font-size:0.7rem; color:var(--cyan);"><?= htmlspecialchars($j['department']) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-size:0.75rem;"><?= htmlspecialchars($j['employment_type']) ?></span><br>
                                        <span style="font-size:0.68rem; color:var(--faint);">📍 <?= htmlspecialchars($j['location']) ?></span>
                                    </td>
                                    <td>
                                        <span style="font-weight:700; color:var(--cyan); font-size:0.78rem;"><?= htmlspecialchars($j['stipend_salary']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($j['is_active']): ?>
                                            <span class="badge-status converted">Active</span>
                                        <?php else: ?>
                                            <span class="badge-status">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.4rem; align-items:center;">
                                            <form action="careers.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="toggle_job">
                                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= $j['is_active'] ?>">
                                                <button type="submit" class="btn-admin btn-admin-outline btn-admin-sm">
                                                    <?= $j['is_active'] ? 'Disable' : 'Enable' ?>
                                                </button>
                                            </form>

                                            <form action="careers.php" method="POST" style="display:inline;" onsubmit="return confirm('Delete this job opening permanently?');">
                                                <input type="hidden" name="action" value="delete_job">
                                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
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
        </div>

    </main>

</div>

<!-- Single Delete Form -->
<form id="singleDeleteForm" action="careers.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_app">
    <input type="hidden" name="id" id="singleDeleteId" value="">
</form>

<!-- Edit Candidate Modal Dialog -->
<div class="admin-modal" id="editModal">
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">✏️ Edit Candidate Profile</h3>
            <button class="admin-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="careers.php" method="POST" style="display:flex; flex-direction:column; gap:1rem;">
            <input type="hidden" name="action" value="edit_application">
            <input type="hidden" name="id" id="editAppId" value="">

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Candidate Full Name *</label>
                <input type="text" name="full_name" id="editName" class="search-input" style="width:100%;" required>
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">WhatsApp / Phone *</label>
                <input type="text" name="phone" id="editPhone" class="search-input" style="width:100%;" required>
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Email Address</label>
                <input type="email" name="email" id="editEmail" class="search-input" style="width:100%;">
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">College / Location</label>
                <input type="text" name="location_college" id="editCollege" class="search-input" style="width:100%;">
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Primary Tech Stack / Skills</label>
                <input type="text" name="primary_skills" id="editSkills" class="search-input" style="width:100%;">
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Application Status</label>
                <select name="status" id="editStatus" class="form-control-admin" style="width:100%;">
                    <option value="new">New</option>
                    <option value="shortlisted">Shortlisted</option>
                    <option value="assigned_project">Assigned Project</option>
                    <option value="active_guild">Active Guild Member</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.8rem; margin-top:0.8rem;">
                <button type="button" class="btn-admin btn-admin-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-admin">Save Candidate Profile</button>
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

function deleteSingleApp(id) {
    if (confirm('Are you sure you want to PERMANENTLY DELETE Candidate Application #' + id + '?')) {
        document.getElementById('singleDeleteId').value = id;
        document.getElementById('singleDeleteForm').submit();
    }
}

function openEditModal(app) {
    document.getElementById('editAppId').value = app.id;
    document.getElementById('editName').value = app.full_name || '';
    document.getElementById('editPhone').value = app.phone || '';
    document.getElementById('editEmail').value = app.email || '';
    document.getElementById('editCollege').value = app.location_college || '';
    document.getElementById('editSkills').value = app.primary_skills || '';
    document.getElementById('editStatus').value = app.status || 'new';
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}
</script>
</body>
</html>
