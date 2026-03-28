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

$json_file = '../data/events.json';
if (!file_exists($json_file)) {
    file_put_contents($json_file, '[]');
}
$events = json_decode(file_get_contents($json_file), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $raw_date = $_POST['date'] ?? '';
        $display_date = $raw_date ? date('j M Y, g:i A', strtotime($raw_date)) : 'TBD';

        $new_event = [
            'name'        => strip_tags(trim($_POST['name'] ?? 'Untitled Event')),
            'date'        => $display_date,
            'date_raw'    => $raw_date,
            'location'    => strip_tags(trim($_POST['location'] ?? 'Online')),
            'description' => strip_tags(trim($_POST['description'] ?? ''))
        ];
        array_unshift($events, $new_event);

    } elseif ($action === 'edit') {
        $index = (int)($_POST['index'] ?? -1);
        if ($index >= 0 && isset($events[$index])) {
            $raw_date = $_POST['date'] ?? '';
            $display_date = $raw_date ? date('j M Y, g:i A', strtotime($raw_date)) : 'TBD';

            $events[$index] = [
                'name'        => strip_tags(trim($_POST['name'] ?? 'Untitled Event')),
                'date'        => $display_date,
                'date_raw'    => $raw_date,
                'location'    => strip_tags(trim($_POST['location'] ?? 'Online')),
                'description' => strip_tags(trim($_POST['description'] ?? ''))
            ];
        }

    } elseif ($action === 'delete') {
        $index = (int)($_POST['index'] ?? -1);
        if ($index >= 0 && isset($events[$index])) {
            array_splice($events, $index, 1);
        }
    }

    file_put_contents($json_file, json_encode($events, JSON_PRETTY_PRINT));
    header('Location: dashboard.php?msg=Events+updated+successfully.');
    exit;
}

header('Location: dashboard.php');
exit;
