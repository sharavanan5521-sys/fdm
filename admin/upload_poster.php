<?php
session_start();
require_once 'config.php';

// --- Auth + Session Timeout ---
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
    session_destroy();
    header('Location: login.php?msg=timeout');
    exit;
}
$_SESSION['last_activity'] = time();

// --- CSRF Check ---
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    header('Location: dashboard.php?err=Invalid+request.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['poster'])) {

    $file = $_FILES['poster'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        header('Location: dashboard.php?err=Upload+error.+Please+try+again.');
        exit;
    }

    // Size check (2MB max)
    if ($file['size'] > 2 * 1024 * 1024) {
        header('Location: dashboard.php?err=File+too+large.+Maximum+size+is+2MB.');
        exit;
    }

    // Verify it's actually an image using getimagesize
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        header('Location: dashboard.php?err=File+is+not+a+valid+image.');
        exit;
    }

    // Check MIME type against allowed list
    $allowed_mime = ['image/jpeg', 'image/png'];
    if (!in_array($image_info['mime'], $allowed_mime)) {
        header('Location: dashboard.php?err=Only+JPG+and+PNG+files+are+allowed.');
        exit;
    }

    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        header('Location: dashboard.php?err=Invalid+file+extension.');
        exit;
    }

    // Always save as promo-poster.jpg for frontend consistency
    $target = __DIR__ . '/../assets/promo-poster.jpg';

    if (move_uploaded_file($file['tmp_name'], $target)) {
        header('Location: dashboard.php?msg=Poster+updated+successfully.');
    } else {
        header('Location: dashboard.php?err=Failed+to+save+poster.+Check+folder+permissions.');
    }
    exit;
}

header('Location: dashboard.php');
exit;
