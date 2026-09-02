<?php
/**
 * ZAMZY — Executive Admin: Chat Reports & Full AI Conversation Logs
 */
require_once __DIR__ . '/auth_check.php';
checkAdminAuth();
require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$msg = '';
$error = '';

// Handle Delete & Edit Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'delete_single') {
        $delId = intval($_POST['id'] ?? 0);
        if ($delId > 0 && $pdo) {
            $pdo->prepare("DELETE FROM `zamzy_chat_sessions` WHERE `id` = :id")->execute([':id' => $delId]);
            $msg = "Chat session #{$delId} and transcript records deleted.";
        }
    }

    if ($action === 'delete_bulk') {
        $ids = $_POST['ids'] ?? [];
        if (!empty($ids) && is_array($ids) && $pdo) {
            $cleanIds = array_map('intval', $ids);
            $inClause = implode(',', $cleanIds);
            $pdo->exec("DELETE FROM `zamzy_chat_sessions` WHERE `id` IN ($inClause)");
            $msg = count($cleanIds) . " chat sessions deleted.";
        }
    }

    if ($action === 'edit_session') {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['user_name'] ?? '');
        $phone = trim($_POST['user_phone'] ?? '');
        $email = trim($_POST['user_email'] ?? '');

        if ($id > 0 && $pdo) {
            $stmt = $pdo->prepare("UPDATE `zamzy_chat_sessions` SET `user_name` = :n, `user_phone` = :p, `user_email` = :e WHERE `id` = :id");
            $stmt->execute([':n' => $name, ':p' => $phone, ':e' => $email, ':id' => $id]);
            $msg = "Chat session #$id updated successfully.";
        }
    }
}

// Search & Filter parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

// Base query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(`user_name` LIKE :search OR `user_phone` LIKE :search OR `user_email` LIKE :search OR `last_message` LIKE :search)";
    $params[':search'] = "%{$search}%";
}

if (!empty($statusFilter)) {
    if ($statusFilter === 'identified') {
        $where[] = "(`user_phone` IS NOT NULL AND `user_phone` != '') OR (`user_email` IS NOT NULL AND `user_email` != '')";
    } elseif ($statusFilter === 'in_progress') {
        $where[] = "`status` = 'in_progress'";
    } elseif ($statusFilter === 'lead_captured') {
        $where[] = "`status` = 'lead_captured'";
    }
}

$whereSql = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";

$totalRecords = 0;
$totalPages = 1;
$sessions = [];
$kpiTotalChats = 0;
$kpiIdentified = 0;
$kpiTotalMessages = 0;
$kpiToday = 0;

if ($pdo) {
    // Count total
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `zamzy_chat_sessions` {$whereSql}");
    $countStmt->execute($params);
    $totalRecords = $countStmt->fetchColumn() ?: 0;
    $totalPages = ceil($totalRecords / $limit);

    // Fetch sessions
    $query = "SELECT * FROM `zamzy_chat_sessions` {$whereSql} ORDER BY `updated_at` DESC LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $sessions = $stmt->fetchAll() ?: [];

    // KPI Stats
    $kpiTotalChats = $pdo->query("SELECT COUNT(*) FROM `zamzy_chat_sessions`")->fetchColumn() ?: 0;
    $kpiIdentified = $pdo->query("SELECT COUNT(*) FROM `zamzy_chat_sessions` WHERE (`user_phone` IS NOT NULL AND `user_phone` != '') OR (`user_email` IS NOT NULL AND `user_email` != '')")->fetchColumn() ?: 0;
    $kpiTotalMessages = $pdo->query("SELECT COUNT(*) FROM `zamzy_chat_messages`")->fetchColumn() ?: 0;
    $kpiToday = $pdo->query("SELECT COUNT(*) FROM `zamzy_chat_sessions` WHERE DATE(`created_at`) = CURDATE()")->fetchColumn() ?: 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ZAMZY Admin — Chat Reports &amp; AI Transcripts</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Barlow+Condensed:wght@400;600;700&family=IBM+Plex+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?= time() ?>">
    <style>
        .transcript-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.88);
            backdrop-filter: blur(12px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .transcript-modal.open { display: flex; }
        .transcript-box {
            background: #0d0d16;
            border: 1px solid rgba(139, 92, 246, 0.4);
            border-radius: 14px;
            width: 100%;
            max-width: 680px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.8), 0 0 30px rgba(139, 92, 246, 0.2);
        }
        .transcript-header {
            padding: 1.2rem 1.6rem;
            background: linear-gradient(90deg, rgba(139,92,246,0.18), rgba(6,182,212,0.1));
            border-bottom: 1px solid rgba(255,255,255,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .transcript-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            background: rgba(5,5,10,0.6);
        }
        .transcript-body::-webkit-scrollbar { width: 6px; }
        .transcript-body::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.3); border-radius: 3px; }
        .t-msg { display: flex; flex-direction: column; max-width: 82%; }
        .t-msg.user { align-self: flex-end; align-items: flex-end; }
        .t-msg.bot { align-self: flex-start; align-items: flex-start; }
        .t-bubble {
            padding: 0.8rem 1.1rem;
            border-radius: 12px;
            font-family: var(--mono);
            font-size: 0.78rem;
            line-height: 1.6;
        }
        .t-msg.user .t-bubble {
            background: linear-gradient(135deg, rgba(139,92,246,0.3), rgba(6,182,212,0.2));
            border: 1px solid rgba(6,182,212,0.35);
            color: #fff;
            border-bottom-right-radius: 2px;
        }
        .t-msg.bot .t-bubble {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--dim);
            border-bottom-left-radius: 2px;
        }
        .t-meta {
            font-family: var(--mono);
            font-size: 0.62rem;
            color: var(--faint);
            margin-top: 4px;
            letter-spacing: 0.05em;
        }
        .transcript-footer {
            padding: 1rem 1.6rem;
            background: rgba(255,255,255,0.02);
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
    </style>
</head>
<body class="admin-body">

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
                <a href="chats.php" class="admin-nav__item active"><span>💬</span> Chat Reports</a>
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
                <h1 class="admin-page-title">Chat Reports &amp; AI Logs</h1>
                <p class="admin-page-sub">Real-Time Transcripts, Visitor Lead Capture &amp; DeepSeek Conversation Intelligence</p>
            </div>
            <div class="admin-topbar__actions">
                <a href="chats.php" class="btn-admin btn-admin-outline">↻ Refresh Feed</a>
            </div>
        </header>

        <?php if (!empty($msg)): ?>
            <div class="alert-box">✓ <?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert-box" style="border-color:#ef4444; background:rgba(239, 68, 68, 0.15); color:#fca5a5;">✕ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Total Chat Sessions</span>
                    <span class="badge">All Time</span>
                </div>
                <div class="kpi-card__val"><?= number_format($kpiTotalChats) ?></div>
                <div class="kpi-card__sub">Recorded visitor threads</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Identified Leads</span>
                    <span class="badge" style="border-color:var(--cyan); color:var(--cyan);">High Intent</span>
                </div>
                <div class="kpi-card__val" style="color:var(--cyan);"><?= number_format($kpiIdentified) ?></div>
                <div class="kpi-card__sub">With Verified Phone / Email</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Messages Logged</span>
                    <span class="badge">DeepSeek AI</span>
                </div>
                <div class="kpi-card__val"><?= number_format($kpiTotalMessages) ?></div>
                <div class="kpi-card__sub">User + Bot interactions</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-card__top">
                    <span class="kpi-card__label">Today's Chats</span>
                    <span class="badge">Active</span>
                </div>
                <div class="kpi-card__val"><?= number_format($kpiToday) ?></div>
                <div class="kpi-card__sub">Engaged in last 24h</div>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="filter-toolbar">
            <form action="chats.php" method="GET" style="display:flex; gap:0.8rem; flex-wrap:wrap; flex:1;">
                <input type="text" name="search" class="search-input" placeholder="Search by name, phone, email, or message keyword..." value="<?= htmlspecialchars($search) ?>">
                
                <select name="status" class="form-control-admin" style="width:auto;" onchange="this.form.submit()">
                    <option value="">All Chat Sessions</option>
                    <option value="identified" <?= $statusFilter === 'identified' ? 'selected' : '' ?>>⚡ Identified Leads Only</option>
                    <option value="lead_captured" <?= $statusFilter === 'lead_captured' ? 'selected' : '' ?>>Profile Completed</option>
                    <option value="in_progress" <?= $statusFilter === 'in_progress' ? 'selected' : '' ?>>Anonymous / Ongoing</option>
                </select>

                <button type="submit" class="btn-admin">Filter</button>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <a href="chats.php" class="btn-admin btn-admin-outline">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Bulk Action Bar -->
        <form id="bulkForm" action="chats.php" method="POST">
            <input type="hidden" name="action" value="delete_bulk">
            <div class="bulk-action-bar">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" id="selectAll" class="admin-checkbox">
                    <strong>Select All</strong>
                </label>
                <button type="submit" class="btn-danger-admin" id="bulkDeleteBtn" style="display:none;" onclick="return confirm('Are you sure you want to PERMANENTLY DELETE selected chat sessions?')">
                    🗑️ Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>

            <!-- Chat Sessions Table -->
            <div class="table-responsive-admin">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>ID</th>
                            <th>Visitor Details</th>
                            <th>Contact Channels</th>
                            <th>Messages</th>
                            <th>Last Message Exchanged</th>
                            <th>Date / Activity</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr>
                                <td colspan="8" style="text-align:center; padding:3rem; color:var(--faint);">
                                    No chat reports found matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $s): ?>
                                <?php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $s['user_phone'] ?? '');
                                    if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="<?= $s['id'] ?>" class="admin-checkbox rowCheckbox">
                                    </td>
                                    <td><span class="badge">#<?= $s['id'] ?></span></td>
                                    <td>
                                        <strong style="color:var(--white);"><?= htmlspecialchars($s['user_name'] ?: 'Anonymous Visitor') ?></strong>
                                        <?php if (!empty($s['user_phone']) || !empty($s['user_email'])): ?>
                                            <div style="margin-top:4px;"><span class="badge" style="font-size:0.58rem; border-color:#10b981; color:#10b981;">✓ Lead Captured</span></div>
                                        <?php else: ?>
                                            <div style="margin-top:4px;"><span class="badge" style="font-size:0.58rem; color:var(--faint);">Browsing</span></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['user_phone'])): ?>
                                            <div><a href="https://wa.me/<?= $cleanPhone ?>" target="_blank" style="color:var(--cyan); text-decoration:underline;">📱 <?= htmlspecialchars($s['user_phone']) ?></a></div>
                                        <?php endif; ?>
                                        <?php if (!empty($s['user_email'])): ?>
                                            <div style="font-size:0.75rem; color:var(--dim);">✉ <?= htmlspecialchars($s['user_email']) ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($s['user_phone']) && empty($s['user_email'])): ?>
                                            <span style="color:var(--faint);">No contact info</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="background:rgba(139,92,246,0.15); border-color:var(--violet); color:#c4b5fd;">
                                            💬 <?= intval($s['total_messages']) ?> msgs
                                        </span>
                                    </td>
                                    <td style="max-width:240px; font-size:0.75rem; color:var(--dim); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                        <?= htmlspecialchars($s['last_message'] ?: 'Conversation initiated.') ?>
                                    </td>
                                    <td style="font-size:0.72rem; color:var(--faint);">
                                        <?= date('d M Y, h:i A', strtotime($s['updated_at'])) ?>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.4rem; align-items:center;">
                                            <button type="button" class="btn-admin btn-admin-sm view-transcript-btn" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['user_name'] ?: 'Visitor #' . $s['id']) ?>" data-phone="<?= htmlspecialchars($s['user_phone'] ?? '') ?>" data-email="<?= htmlspecialchars($s['user_email'] ?? '') ?>" style="padding:0.35rem 0.6rem; font-size:0.68rem;">
                                                👁 Transcript
                                            </button>
                                            <button type="button" class="btn-edit-admin" onclick="openEditModal(<?= htmlspecialchars(json_encode($s)) ?>)">
                                                ✏️ Edit
                                            </button>
                                            <button type="button" class="btn-danger-admin" style="padding:0.35rem 0.6rem; font-size:0.72rem;" onclick="deleteSingleChat(<?= $s['id'] ?>)">
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="page-link">← Prev</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="page-link <?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($statusFilter) ?>" class="page-link">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- Chat Transcript Modal -->
    <div id="transcript-modal" class="transcript-modal">
        <div class="transcript-box">
            <div class="transcript-header">
                <div>
                    <h3 id="t-modal-title" style="font-family:var(--display); font-size:1.1rem; color:#fff; margin-bottom:2px;">Chat Transcript</h3>
                    <div id="t-modal-sub" style="font-family:var(--mono); font-size:0.7rem; color:var(--dim);">Session Details</div>
                </div>
                <button id="close-transcript-modal" style="background:none; border:none; color:var(--dim); font-size:1.6rem; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <div class="transcript-body" id="transcript-body">
                <div style="text-align:center; color:var(--faint); font-family:var(--mono); font-size:0.8rem; padding:2rem;">Loading chat logs...</div>
            </div>
            <div class="transcript-footer">
                <div id="t-modal-footer-contact" style="font-family:var(--mono); font-size:0.72rem; color:var(--dim);"></div>
                <div style="display:flex; gap:0.8rem;">
                    <a id="t-modal-wa-btn" href="#" target="_blank" class="btn-admin" style="display:none; background:rgba(16,185,129,0.2); border-color:#10b981; color:#10b981;">WhatsApp Lead →</a>
                    <button id="close-transcript-btn" class="btn-admin btn-admin-outline">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('transcript-modal');
        const closeBtn = document.getElementById('close-transcript-modal');
        const closeBtn2 = document.getElementById('close-transcript-btn');
        const tBody = document.getElementById('transcript-body');
        const tTitle = document.getElementById('t-modal-title');
        const tSub = document.getElementById('t-modal-sub');
        const tWaBtn = document.getElementById('t-modal-wa-btn');
        const tContact = document.getElementById('t-modal-footer-contact');

        function closeModal() { modal.classList.remove('open'); }
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (closeBtn2) closeBtn2.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        document.querySelectorAll('.view-transcript-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const phone = btn.getAttribute('data-phone');
                const email = btn.getAttribute('data-email');

                tTitle.textContent = `Chat Transcript: ${name}`;
                tSub.textContent = `Session #${id} · ${phone ? 'Phone: ' + phone : ''} ${email ? ' | Email: ' + email : ''}`;
                
                tContact.textContent = phone ? `WhatsApp: ${phone}` : (email ? `Email: ${email}` : 'Anonymous visitor');
                if (phone) {
                    let cleanPhone = phone.replace(/[^0-9]/g, '');
                    if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;
                    tWaBtn.href = `https://wa.me/${cleanPhone}?text=${encodeURIComponent("Hello " + name + ", following up on your consultation with ZAMZY Digital Solutions.")}`;
                    tWaBtn.style.display = 'inline-flex';
                } else {
                    tWaBtn.style.display = 'none';
                }

                tBody.innerHTML = '<div style="text-align:center; color:var(--faint); font-family:var(--mono); font-size:0.8rem; padding:2rem;">Fetching conversation history...</div>';
                modal.classList.add('open');

                try {
                    const res = await fetch(`../api.php?action=get_chat_transcript&session_id=${id}`);
                    const data = await res.json();
                    
                    if (!data.success || !data.messages || data.messages.length === 0) {
                        tBody.innerHTML = '<div style="text-align:center; color:var(--faint); font-family:var(--mono); font-size:0.8rem; padding:2rem;">No messages found in this chat session.</div>';
                        return;
                    }

                    tBody.innerHTML = '';
                    data.messages.forEach(m => {
                        const isUser = m.sender === 'user';
                        const div = document.createElement('div');
                        div.className = `t-msg ${isUser ? 'user' : 'bot'}`;
                        
                        const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        
                        div.innerHTML = `
                            <div class="t-bubble">${escapeHtml(m.message)}</div>
                            <div class="t-meta">${isUser ? (name || 'Visitor') : 'ZAMZY AI Assistant'} · ${time}</div>
                        `;
                        tBody.appendChild(div);
                    });

                    tBody.scrollTop = tBody.scrollHeight;
                } catch (e) {
                    tBody.innerHTML = '<div style="text-align:center; color:#ef4444; font-family:var(--mono); font-size:0.8rem; padding:2rem;">Failed to load transcript.</div>';
                }
            });
        });

<!-- Single Delete Form -->
<form id="singleDeleteForm" action="chats.php" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_single">
    <input type="hidden" name="id" id="singleDeleteId" value="">
</form>

<!-- Edit Chat Session Modal -->
<div class="admin-modal" id="editModal">
    <div class="admin-modal-content">
        <div class="admin-modal-header">
            <h3 class="admin-modal-title">✏️ Edit Visitor Lead Info</h3>
            <button class="admin-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form action="chats.php" method="POST" style="display:flex; flex-direction:column; gap:1rem;">
            <input type="hidden" name="action" value="edit_session">
            <input type="hidden" name="id" id="editSessionId" value="">

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Visitor Name</label>
                <input type="text" name="user_name" id="editName" class="search-input" style="width:100%;">
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">WhatsApp / Phone Number</label>
                <input type="text" name="user_phone" id="editPhone" class="search-input" style="width:100%;">
            </div>

            <div>
                <label style="font-family:var(--mono); font-size:0.75rem; color:var(--cyan); margin-bottom:0.4rem; display:block;">Email Address</label>
                <input type="email" name="user_email" id="editEmail" class="search-input" style="width:100%;">
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

        // Transcript Modal Controls
        const modal = document.getElementById('transcript-modal');
        const closeBtn = document.getElementById('t-modal-close');
        const closeBtn2 = document.getElementById('close-transcript-btn');
        const tBody = document.getElementById('transcript-body');
        const tTitle = document.getElementById('t-modal-title');
        const tSub = document.getElementById('t-modal-sub');
        const tWaBtn = document.getElementById('t-modal-wa-btn');
        const tContact = document.getElementById('t-modal-footer-contact');

        function closeModal() { modal.classList.remove('open'); }
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (closeBtn2) closeBtn2.addEventListener('click', closeModal);
        if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

        document.querySelectorAll('.view-transcript-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const phone = btn.getAttribute('data-phone');
                const email = btn.getAttribute('data-email');

                tTitle.textContent = `Chat Transcript: ${name}`;
                tSub.textContent = `Session #${id} · ${phone ? 'Phone: ' + phone : ''} ${email ? ' | Email: ' + email : ''}`;
                
                tContact.textContent = phone ? `WhatsApp: ${phone}` : (email ? `Email: ${email}` : 'Anonymous visitor');
                if (phone) {
                    let cleanPhone = phone.replace(/[^0-9]/g, '');
                    if (cleanPhone.length === 10) cleanPhone = '91' + cleanPhone;
                    tWaBtn.href = `https://wa.me/${cleanPhone}?text=${encodeURIComponent("Hello " + name + ", following up on your consultation with ZAMZY Digital Solutions.")}`;
                    tWaBtn.style.display = 'inline-flex';
                } else {
                    tWaBtn.style.display = 'none';
                }

                tBody.innerHTML = '<div style="text-align:center; color:var(--faint); font-family:var(--mono); font-size:0.8rem; padding:2rem;">Fetching conversation history...</div>';
                modal.classList.add('open');

                try {
                    const res = await fetch(`../api.php?action=get_chat_transcript&session_id=${id}`);
                    const data = await res.json();
                    
                    if (!data.success || !data.messages || data.messages.length === 0) {
                        tBody.innerHTML = '<div style="text-align:center; color:var(--faint); font-family:var(--mono); font-size:0.8rem; padding:2rem;">No messages found in this chat session.</div>';
                        return;
                    }

                    tBody.innerHTML = '';
                    data.messages.forEach(m => {
                        const isUser = m.sender === 'user';
                        const div = document.createElement('div');
                        div.className = `t-msg ${isUser ? 'user' : 'bot'}`;
                        
                        const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        
                        div.innerHTML = `
                            <div class="t-bubble">${escapeHtml(m.message)}</div>
                            <div class="t-meta">${isUser ? (name || 'Visitor') : 'ZAMZY AI Assistant'} · ${time}</div>
                        `;
                        tBody.appendChild(div);
                    });

                    tBody.scrollTop = tBody.scrollHeight;
                } catch (e) {
                    tBody.innerHTML = '<div style="text-align:center; color:#ef4444; font-family:var(--mono); font-size:0.8rem; padding:2rem;">Failed to load transcript.</div>';
                }
            });
        });

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });

    function deleteSingleChat(id) {
        if (confirm('Are you sure you want to PERMANENTLY DELETE Chat Session #' + id + '?')) {
            document.getElementById('singleDeleteId').value = id;
            document.getElementById('singleDeleteForm').submit();
        }
    }

    function openEditModal(s) {
        document.getElementById('editSessionId').value = s.id;
        document.getElementById('editName').value = s.user_name || '';
        document.getElementById('editPhone').value = s.user_phone || '';
        document.getElementById('editEmail').value = s.user_email || '';
        document.getElementById('editModal').classList.add('open');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('open');
    }
    </script>
</body>
</html>
