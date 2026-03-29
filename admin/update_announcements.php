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

$json_file = '../data/announcements.json';
if (!file_exists($json_file)) {
    file_put_contents($json_file, '[]');
}
$announcements = json_decode(file_get_contents($json_file), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $new = [
            'id'       => time(),
            'title'    => strip_tags(trim($_POST['title'] ?? 'Announcement')),
            'body'     => strip_tags(trim($_POST['body'] ?? '')),
            'cta_text' => strip_tags(trim($_POST['cta_text'] ?? 'Enquire Now')),
            'cta_link' => strip_tags(trim($_POST['cta_link'] ?? 'contact.html')),
            'active'   => isset($_POST['active']),
        ];
        array_unshift($announcements, $new);

    } elseif ($action === 'edit') {
        $index = (int)($_POST['index'] ?? -1);
        if ($index >= 0 && isset($announcements[$index])) {
            $announcements[$index] = [
                'id'       => $announcements[$index]['id'] ?? time(),
                'title'    => strip_tags(trim($_POST['title'] ?? 'Announcement')),
                'body'     => strip_tags(trim($_POST['body'] ?? '')),
                'cta_text' => strip_tags(trim($_POST['cta_text'] ?? 'Enquire Now')),
                'cta_link' => strip_tags(trim($_POST['cta_link'] ?? 'contact.html')),
                'active'   => isset($_POST['active']),
            ];
        }

    } elseif ($action === 'delete') {
        $index = (int)($_POST['index'] ?? -1);
        if ($index >= 0 && isset($announcements[$index])) {
            array_splice($announcements, $index, 1);
        }
    }

    file_put_contents($json_file, json_encode($announcements, JSON_PRETTY_PRINT));
    header('Location: dashboard.php?msg=Announcements+updated+successfully.#announcements');
    exit;
}

header('Location: dashboard.php');
exit;
