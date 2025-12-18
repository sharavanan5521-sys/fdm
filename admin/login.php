<?php
session_start();

// --- CONFIGURATION ---
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'Freedom2025'; // CHANGE THIS PASSWORD!

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
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
        .login-box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); width: 300px; text-align: center; }
        h2 { color: #0033cc; margin-bottom: 20px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #ff8c00; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold; }
        button:hover { background: #e07b00; }
        .error { color: red; font-size: 0.9em; margin-bottom: 15px; }
        a { color: #666; font-size: 0.8em; text-decoration: none; margin-top: 15px; display: block; }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Admin Portal</h2>
    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <a href="../index.html">&larr; Back to Website</a>
</div>

</body>
</html>
