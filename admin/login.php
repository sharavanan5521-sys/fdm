<?php
session_start();
require_once 'config.php';

// --- Session Timeout Check ---
if (isset($_SESSION['admin_logged_in']) && isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: login.php?msg=timeout');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

// --- Rate Limiting ---
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

$is_locked = $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS
    && (time() - $_SESSION['lockout_time']) < LOCKOUT_DURATION;

$error = '';
$timeout_msg = isset($_GET['msg']) && $_GET['msg'] === 'timeout';

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_locked) {

    // CSRF Check
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stored = ADMIN_PASS;
        $pass_ok = false;

        if (str_starts_with($stored, '$2y$')) {
            // Already hashed
            $pass_ok = password_verify($password, $stored);
        } else {
            // Plaintext — compare then auto-upgrade
            $pass_ok = ($password === $stored);
            if ($pass_ok) {
                // Auto-upgrade: rewrite config.php with bcrypt hash
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                $config_content = "<?php\n// =============================================\n//  ADMIN CONFIGURATION\n// =============================================\ndefine('ADMIN_USERNAME', 'admin');\ndefine('ADMIN_PASS', '$new_hash');\n\n// Session timeout in seconds (2 hours)\ndefine('SESSION_TIMEOUT', 7200);\n\n// Max login attempts before lockout\ndefine('MAX_LOGIN_ATTEMPTS', 5);\ndefine('LOCKOUT_DURATION', 900);\n";
                file_put_contents(__DIR__ . '/config.php', $config_content);
            }
        }

        if ($username === ADMIN_USERNAME && $pass_ok) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['last_activity'] = time();
            $_SESSION['login_attempts'] = 0;
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit;
        } else {
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
                $_SESSION['lockout_time'] = time();
            }
            $remaining = MAX_LOGIN_ATTEMPTS - $_SESSION['login_attempts'];
            $error = $remaining > 0
                ? "Invalid credentials. $remaining attempt(s) remaining."
                : 'Too many failed attempts. Account locked for 15 minutes.';
        }
    }

    // Regenerate CSRF token after each attempt
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$lockout_remaining = 0;
if ($is_locked) {
    $lockout_remaining = LOCKOUT_DURATION - (time() - $_SESSION['lockout_time']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Freedom Discovery</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7fa; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        .logo { font-size: 2rem; color: #0033cc; margin-bottom: 5px; }
        h2 { color: #0033cc; margin: 0 0 20px; font-size: 1.3rem; }
        input[type=text], input[type=password] { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 0.95rem; }
        input:focus { outline: none; border-color: #0033cc; }
        button { background: #ff8c00; color: white; border: none; padding: 11px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; font-size: 1rem; margin-top: 5px; }
        button:hover { background: #e07b00; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .error { color: #cc0000; font-size: 0.85em; margin-bottom: 12px; background: #fff0f0; padding: 8px; border-radius: 4px; }
        .info { color: #155724; font-size: 0.85em; margin-bottom: 12px; background: #d4edda; padding: 8px; border-radius: 4px; }
        .warning { color: #856404; font-size: 0.85em; margin-bottom: 12px; background: #fff3cd; padding: 8px; border-radius: 4px; }
        .back { color: #666; font-size: 0.8em; text-decoration: none; margin-top: 15px; display: block; }
        .back:hover { color: #0033cc; }
    </style>
</head>
<body>
<div class="login-box">
    <div class="logo"><i>🔐</i></div>
    <h2>Admin Portal</h2>

    <?php if ($timeout_msg): ?>
        <p class="warning">Session expired. Please log in again.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($is_locked): ?>
        <p class="error">Account locked. Try again in <?php echo ceil($lockout_remaining / 60); ?> minute(s).</p>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="username" placeholder="Username" disabled>
            <input type="password" name="password" placeholder="Password" disabled>
            <button type="submit" disabled>Login</button>
        </form>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="username" placeholder="Username" required autocomplete="username">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            <button type="submit">Login</button>
        </form>
    <?php endif; ?>

    <a href="../index.html" class="back">&larr; Back to Website</a>
</div>
</body>
</html>
