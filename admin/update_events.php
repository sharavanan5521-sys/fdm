<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$json_file = '../data/events.json';
$events = json_decode(file_get_contents($json_file), true) ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $new_event = [
            'name' => $_POST['name'] ?? 'Untitled Event',
            'date' => $_POST['date'] ?? 'TBD',
            'location' => $_POST['location'] ?? 'Online',
            'description' => $_POST['description'] ?? ''
        ];
        // Add to beginning of array
        array_unshift($events, $new_event);

    } elseif ($action === 'delete') {
        $index = $_POST['index'] ?? -1;
        if ($index >= 0 && isset($events[$index])) {
            array_splice($events, $index, 1);
        }
    }

    // Save back to file
    file_put_contents($json_file, json_encode($events, JSON_PRETTY_PRINT));

    header("Location: dashboard.php?msg=Events Updated!");
    exit;
}
?>