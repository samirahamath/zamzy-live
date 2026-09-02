<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';
$pdo = getDbConnection();

$error = '';
$success = '';

if (isset($_SESSION['zamzy_admin_logged']) && $_SESSION['zamzy_admin_logged'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if ($pdo) {
            $stmt = $pdo->prepare("SELECT * FROM `zamzy_admin_users` WHERE `username` = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['zamzy_admin_logged'] = true;
                $_SESSION['zamzy_admin_id'] = $user['id'];
                $_SESSION['zamzy_admin_name'] = $user['name'];
                $_SESSION['zamzy_admin_username'] = $user['username'];
                $_SESSION['zamzy_admin_role'] = $user['role'];

                $dashUrl = (strpos($_SERVER['REQUEST_URI'] ?? '', '/zz/') !== false) ? '/zz/admin/index.php' : '/admin/index.php';
                header('Location: ' . $dashUrl);
                exit;
            } else {
                $error = 'Invalid username or password credentials.';
            }
        } else {
            $error = 'Database connection error. Ensure MySQL is running.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZAMZY — Executive Admin Authentication</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;600;700&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --void: #050505;
            --violet: #8b5cf6;
            --cyan: #06b6d4;
            --white: #ffffff;
            --dim: rgba(255, 255, 255, 0.8);
            --faint: rgba(255, 255, 255, 0.45);
            --mono: 'JetBrains Mono', monospace;
            --display: 'Space Grotesk', sans-serif;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: var(--void);
            color: var(--white);
            font-family: var(--display);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .aura {
            position: fixed;
            inset: -25%;
            background: radial-gradient(circle at 50% 44%, rgba(58, 28, 92, 0.6), rgba(24, 12, 46, 0.22) 34%, transparent 64%);
            z-index: 0;
            pointer-events: none;
        }

        .login-card {
            background: #0d0d16;
            border: 1px solid rgba(6, 182, 212, 0.4);
            width: 100%;
            max-width: 440px;
            padding: 3.5rem 2.8rem;
            border-radius: 16px;
            box-shadow: 0 0 60px rgba(6, 182, 212, 0.25), inset 0 0 30px rgba(139, 92, 246, 0.15);
            position: relative;
            z-index: 10;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2.2rem;
        }

        .brand-title {
            font-family: var(--mono);
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            color: var(--white);
            line-height: 1;
        }

        .brand-title span.dot {
            color: var(--cyan);
            text-shadow: 0 0 12px var(--cyan);
        }

        .brand-subtitle {
            font-family: var(--mono);
            font-size: 0.68rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--cyan);
            margin-top: 0.5rem;
            opacity: 0.85;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            font-family: var(--mono);
            font-size: 0.75rem;
            padding: 0.9rem 1.1rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
            border-radius: 6px;
        }

        .form-group {
            margin-bottom: 1.4rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-family: var(--mono);
            font-size: 0.68rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--cyan);
            font-weight: 700;
        }

        .form-input {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--white);
            font-family: var(--mono);
            font-size: 0.85rem;
            padding: 0.9rem 1.1rem;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.35);
            background: rgba(255, 255, 255, 0.08);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--violet), var(--cyan));
            border: 1px solid rgba(6, 182, 212, 0.5);
            color: var(--white);
            font-family: var(--mono);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 1.1rem;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.35);
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-login:hover {
            box-shadow: 0 0 35px rgba(6, 182, 212, 0.6);
            transform: translateY(-2px);
        }

        .login-footer {
            margin-top: 2rem;
            text-align: center;
            font-family: var(--mono);
            font-size: 0.62rem;
            color: var(--faint);
            letter-spacing: 0.1em;
            line-height: 1.6;
        }

        .login-hint {
            margin-top: 1.4rem;
            padding: 0.9rem;
            background: rgba(6, 182, 212, 0.05);
            border: 1px dashed rgba(6, 182, 212, 0.3);
            border-radius: 6px;
            font-family: var(--mono);
            font-size: 0.68rem;
            color: var(--dim);
            text-align: center;
        }

        .login-hint strong {
            color: var(--cyan);
        }
    </style>
</head>
<body>

    <div class="aura"></div>

    <div class="login-card">
        <div class="brand-header">
            <h1 class="brand-title">ZAMZY<span class="dot">.</span></h1>
            <p class="brand-subtitle">Executive Console</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label class="form-label" for="username">Admin Username</label>
                <input type="text" id="username" name="username" class="form-input" placeholder="admin" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Security Password</label>
                <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">Enter Control Panel →</button>
        </form>

        <div class="login-hint">
            Default credentials:<br>
            Username: <strong>admin</strong> &nbsp;|&nbsp; Password: <strong>zamzy@2026</strong>
        </div>

        <div class="login-footer">
            ZAMZY.IN · Anna Nagar, Chennai<br>
            Protected by End-to-End Enterprise Auth
        </div>
    </div>

</body>
</html>
