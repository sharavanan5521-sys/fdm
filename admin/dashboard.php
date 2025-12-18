<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Ensure data file exists
$json_file = '../data/events.json';
if (!file_exists($json_file)) {
    file_put_contents($json_file, '[]');
}

$events = json_decode(file_get_contents($json_file), true) ?? [];

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Freedom Discovery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            color: #0033cc;
            margin: 0;
        }

        .logout {
            color: #cc0000;
            text-decoration: none;
            font-weight: bold;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .card h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 2px solid #ff8c00;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        /* Form Styles */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        input[type="text"],
        textarea,
        input[type="date"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            background: #0033cc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background: #002288;
        }

        .delete-btn {
            background: #cc0000;
            padding: 5px 10px;
            font-size: 0.8em;
            margin-left: 10px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .event-item {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #0033cc;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <?php if ($msg): ?>
            <div class="success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <!-- === POSTER UPLOAD === -->
        <div class="card">
            <h2><i class="fas fa-image"></i> Update Popup Poster</h2>
            <p>Recommended Size: 800x600px (JPG/PNG). Max: 2MB.</p>
            <form action="upload_poster.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="poster" accept="image/*" required>
                <button type="submit">Upload Poster</button>
            </form>
        </div>

        <!-- === ADD NEW EVENT === -->
        <div class="card">
            <h2><i class="fas fa-calendar-plus"></i> Add New Event</h2>
            <form action="update_events.php" method="POST">
                <input type="hidden" name="action" value="add">
                <label>Event Name</label>
                <input type="text" name="name" required placeholder="e.g. AI Masterclass">

                <label>Date & Time</label>
                <input type="text" name="date" required placeholder="e.g. 25th Oct 2025, 10:00 AM">

                <label>Location</label>
                <input type="text" name="location" required placeholder="e.g. Zoom / Kuala Lumpur">

                <label>Description</label>
                <textarea name="description" rows="3" required placeholder="Brief agenda..."></textarea>

                <button type="submit">Add Event</button>
            </form>
        </div>

        <!-- === MANAGE EVENTS === -->
        <div class="card">
            <h2><i class="fas fa-list"></i> Manage Events</h2>
            <?php if (empty($events)): ?>
                <p>No upcoming events.</p>
            <?php else: ?>
                <?php foreach ($events as $index => $event): ?>
                    <div class="event-item">
                        <div>
                            <strong><?php echo htmlspecialchars($event['name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($event['date']); ?> |
                                <?php echo htmlspecialchars($event['location']); ?></small>
                        </div>
                        <form action="update_events.php" method="POST" onsubmit="return confirm('Delete this event?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div style="text-align: center;">
            <a href="../index.html" target="_blank">View Website</a>
        </div>
    </div>

</body>

</html>