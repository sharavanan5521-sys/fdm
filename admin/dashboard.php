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

// --- CSRF Token ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Load Events ---
$json_file = '../data/events.json';
if (!file_exists($json_file)) {
    file_put_contents($json_file, '[]');
}
$events = json_decode(file_get_contents($json_file), true) ?? [];

// --- Load Announcements ---
$ann_file = '../data/announcements.json';
if (!file_exists($ann_file)) {
    file_put_contents($ann_file, '[]');
}
$announcements = json_decode(file_get_contents($ann_file), true) ?? [];

// --- Check for current poster ---
$poster_exists = file_exists('../assets/promo-poster.jpg');
$poster_url = $poster_exists ? '../assets/promo-poster.jpg?v=' . filemtime('../assets/promo-poster.jpg') : '';

$msg = $_GET['msg'] ?? '';
$err = $_GET['err'] ?? '';

// --- Edit mode (events) ---
$edit_index = isset($_GET['edit']) ? (int)$_GET['edit'] : -1;
$edit_event = ($edit_index >= 0 && isset($events[$edit_index])) ? $events[$edit_index] : null;

// --- Edit mode (announcements) ---
$edit_ann_index = isset($_GET['edit_ann']) ? (int)$_GET['edit_ann'] : -1;
$edit_ann = ($edit_ann_index >= 0 && isset($announcements[$edit_ann_index])) ? $announcements[$edit_ann_index] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Freedom Discovery Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 900px; margin: 0 auto; }

        /* Header */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .header h1 { color: #0033cc; margin: 0; font-size: 1.5rem; }
        .header-links { display: flex; gap: 15px; align-items: center; }
        .header-links a { text-decoration: none; font-size: 0.85rem; font-weight: 600; }
        .link-site { color: #0033cc; }
        .link-site:hover { text-decoration: underline; }
        .logout { color: #cc0000; }
        .logout:hover { text-decoration: underline; }

        /* Quick Nav */
        .quick-nav { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 25px; }
        .quick-nav a { background: white; border: 1px solid #ddd; padding: 6px 14px; border-radius: 20px; text-decoration: none; font-size: 0.8rem; color: #555; transition: all 0.2s; }
        .quick-nav a:hover { background: #0033cc; color: white; border-color: #0033cc; }

        /* Cards */
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); margin-bottom: 25px; }
        .card h2 { margin-top: 0; color: #333; border-bottom: 2px solid #ff8c00; display: inline-block; padding-bottom: 5px; margin-bottom: 20px; font-size: 1.1rem; }

        /* Alerts */
        .success { background: #d4edda; color: #155724; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px 15px; border-radius: 5px; margin-bottom: 20px; }

        /* Forms */
        label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; }
        input[type="text"], input[type="datetime-local"], textarea, input[type="file"] {
            width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 4px;
            margin-bottom: 14px; font-size: 0.95rem; font-family: inherit;
        }
        input:focus, textarea:focus { outline: none; border-color: #0033cc; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        .btn { border: none; padding: 9px 18px; border-radius: 4px; cursor: pointer; font-weight: 600; font-size: 0.9rem; transition: background 0.2s; }
        .btn-primary { background: #0033cc; color: white; }
        .btn-primary:hover { background: #002299; }
        .btn-danger { background: #cc0000; color: white; padding: 5px 10px; font-size: 0.8rem; }
        .btn-danger:hover { background: #aa0000; }
        .btn-warning { background: #ff8c00; color: white; padding: 5px 10px; font-size: 0.8rem; }
        .btn-warning:hover { background: #e07b00; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }

        /* Poster Preview */
        .poster-preview { margin-bottom: 15px; }
        .poster-preview img { max-width: 200px; border-radius: 6px; border: 1px solid #ddd; display: block; }
        .poster-preview p { margin: 5px 0 0; font-size: 0.8rem; color: #888; }
        .no-poster { background: #f8f9fa; border: 2px dashed #ddd; border-radius: 6px; padding: 20px; text-align: center; color: #999; font-size: 0.9rem; margin-bottom: 15px; }

        /* Events List */
        .event-item { background: #f9f9f9; padding: 14px 15px; margin-bottom: 10px; border-left: 4px solid #0033cc; border-radius: 0 4px 4px 0; }
        .event-item.editing { border-left-color: #ff8c00; background: #fff8f0; }
        .event-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
        .event-info strong { display: block; margin-bottom: 3px; }
        .event-info small { color: #666; }
        .event-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .empty-state { text-align: center; color: #999; padding: 20px; font-size: 0.9rem; }

        /* Edit form inside card */
        .edit-form { background: #fff8f0; border: 1px solid #ffd699; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
        .edit-form h3 { margin-top: 0; color: #ff8c00; font-size: 1rem; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
        <div class="header-links">
            <a href="../index.html" target="_blank" class="link-site"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <!-- Quick Nav -->
    <div class="quick-nav">
        <a href="../index.html" target="_blank"><i class="fas fa-home"></i> Home</a>
        <a href="../about.html" target="_blank"><i class="fas fa-info-circle"></i> About</a>
        <a href="../services.html" target="_blank"><i class="fas fa-cogs"></i> Services</a>
        <a href="../events.html" target="_blank"><i class="fas fa-calendar"></i> Events</a>
        <a href="../contact.html" target="_blank"><i class="fas fa-envelope"></i> Contact</a>
        <a href="#announcements"><i class="fas fa-bullhorn"></i> Announcements</a>
    </div>

    <!-- Alerts -->
    <?php if ($msg): ?>
        <div class="success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($err): ?>
        <div class="error-msg"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($err); ?></div>
    <?php endif; ?>

    <!-- Poster Upload -->
    <div class="card">
        <h2><i class="fas fa-image"></i> Promo Popup Poster</h2>

        <?php if ($poster_exists): ?>
            <div class="poster-preview">
                <img src="<?php echo htmlspecialchars($poster_url); ?>" alt="Current Poster">
                <p><i class="fas fa-check-circle" style="color:green"></i> Current poster is live</p>
            </div>
        <?php else: ?>
            <div class="no-poster"><i class="fas fa-image" style="font-size:2rem; display:block; margin-bottom:8px;"></i>No poster uploaded yet. Upload one to show a popup on the homepage.</div>
        <?php endif; ?>

        <form action="upload_poster.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <label>Upload New Poster <small style="color:#999;">(JPG/PNG, max 2MB, recommended 800&times;600px)</small></label>
            <input type="file" name="poster" accept="image/jpeg,image/png" required>
            <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload Poster</button>
        </form>
    </div>

    <!-- ======= ANNOUNCEMENTS SECTION ======= -->
    <div class="card" id="announcements">
        <h2><i class="fas fa-bullhorn"></i> Site Announcements <span style="font-size:0.85rem;font-weight:normal;color:#999;">(<?php echo count($announcements); ?> total)</span></h2>

        <!-- Edit Announcement Form -->
        <?php if ($edit_ann): ?>
        <div class="edit-form" id="edit-ann-form">
            <h3><i class="fas fa-edit"></i> Editing: <?php echo htmlspecialchars($edit_ann['title']); ?></h3>
            <form action="update_announcements.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="index" value="<?php echo $edit_ann_index; ?>">
                <label>Title</label>
                <input type="text" name="title" required value="<?php echo htmlspecialchars($edit_ann['title']); ?>">
                <label>Body Text</label>
                <textarea name="body" rows="3" required><?php echo htmlspecialchars($edit_ann['body']); ?></textarea>
                <div class="form-row">
                    <div>
                        <label>Button Text</label>
                        <input type="text" name="cta_text" value="<?php echo htmlspecialchars($edit_ann['cta_text']); ?>" placeholder="Enquire Now">
                    </div>
                    <div>
                        <label>Button Link</label>
                        <input type="text" name="cta_link" value="<?php echo htmlspecialchars($edit_ann['cta_link']); ?>" placeholder="contact.html">
                    </div>
                </div>
                <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                    <input type="checkbox" name="active" <?php echo $edit_ann['active'] ? 'checked' : ''; ?> style="width:auto;margin:0;">
                    Active (show on website)
                </label>
                <div style="display:flex;gap:10px;margin-top:14px;">
                    <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="dashboard.php#announcements" class="btn btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;gap:5px;"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Add New Announcement -->
        <form action="update_announcements.php" method="POST" style="margin-bottom:20px;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="add">
            <label>Title</label>
            <input type="text" name="title" required placeholder="e.g. Training Hall Available for Rent">
            <label>Body Text</label>
            <textarea name="body" rows="3" required placeholder="Brief announcement details..."></textarea>
            <div class="form-row">
                <div>
                    <label>Button Text</label>
                    <input type="text" name="cta_text" placeholder="Enquire Now">
                </div>
                <div>
                    <label>Button Link</label>
                    <input type="text" name="cta_link" placeholder="contact.html or https://wa.me/60124883300">
                </div>
            </div>
            <label style="display:flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
                <input type="checkbox" name="active" checked style="width:auto;margin:0;">
                Active (show on website)
            </label>
            <button type="submit" class="btn btn-primary" style="margin-top:14px;"><i class="fas fa-plus"></i> Add Announcement</button>
        </form>

        <!-- Existing Announcements List -->
        <?php if (empty($announcements)): ?>
            <div class="empty-state"><i class="fas fa-bullhorn" style="font-size:2rem;display:block;margin-bottom:8px;"></i>No announcements yet. Add one above.</div>
        <?php else: ?>
            <?php foreach ($announcements as $ai => $ann): ?>
                <div class="event-item <?php echo ($edit_ann_index === $ai) ? 'editing' : ''; ?>" style="border-left-color:<?php echo $ann['active'] ? '#1ebe57' : '#aaa'; ?>">
                    <div class="event-header">
                        <div class="event-info">
                            <strong><?php echo htmlspecialchars($ann['title']); ?></strong>
                            <small><?php echo htmlspecialchars(substr($ann['body'], 0, 80)); ?>...</small>
                            <small style="color:<?php echo $ann['active'] ? '#1ebe57' : '#aaa'; ?>;font-weight:600;">
                                <?php echo $ann['active'] ? '● Active' : '○ Inactive'; ?>
                            </small>
                        </div>
                        <div class="event-actions">
                            <a href="dashboard.php?edit_ann=<?php echo $ai; ?>#edit-ann-form" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form action="update_announcements.php" method="POST" onsubmit="return confirm('Delete this announcement?');" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="index" value="<?php echo $ai; ?>">
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Edit Event Form (shown when editing) -->
    <?php if ($edit_event): ?>
    <div class="edit-form" id="edit-form">
        <h3><i class="fas fa-edit"></i> Editing: <?php echo htmlspecialchars($edit_event['name']); ?></h3>
        <form action="update_events.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="index" value="<?php echo $edit_index; ?>">
            <label>Event Name</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($edit_event['name']); ?>">
            <div class="form-row">
                <div>
                    <label>Date &amp; Time</label>
                    <input type="datetime-local" name="date" required value="<?php echo htmlspecialchars($edit_event['date_raw'] ?? ''); ?>">
                </div>
                <div>
                    <label>Location</label>
                    <input type="text" name="location" required value="<?php echo htmlspecialchars($edit_event['location']); ?>">
                </div>
            </div>
            <label>Description</label>
            <textarea name="description" rows="3" required><?php echo htmlspecialchars($edit_event['description']); ?></textarea>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center; gap:5px;"><i class="fas fa-times"></i> Cancel</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Add New Event -->
    <div class="card">
        <h2><i class="fas fa-calendar-plus"></i> Add New Event</h2>
        <form action="update_events.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="action" value="add">
            <label>Event Name</label>
            <input type="text" name="name" required placeholder="e.g. AI Masterclass">
            <div class="form-row">
                <div>
                    <label>Date &amp; Time</label>
                    <input type="datetime-local" name="date" required>
                </div>
                <div>
                    <label>Location</label>
                    <input type="text" name="location" required placeholder="e.g. Zoom / Kuala Lumpur">
                </div>
            </div>
            <label>Description</label>
            <textarea name="description" rows="3" required placeholder="Brief agenda..."></textarea>
            <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Add Event</button>
        </form>
    </div>

    <!-- Manage Events -->
    <div class="card">
        <h2><i class="fas fa-list"></i> Manage Events <span style="font-size:0.85rem;font-weight:normal;color:#999;">(<?php echo count($events); ?> total)</span></h2>
        <?php if (empty($events)): ?>
            <div class="empty-state"><i class="fas fa-calendar-times" style="font-size:2rem; display:block; margin-bottom:8px;"></i>No events yet. Add one above.</div>
        <?php else: ?>
            <?php foreach ($events as $index => $event): ?>
                <div class="event-item <?php echo ($edit_index === $index) ? 'editing' : ''; ?>">
                    <div class="event-header">
                        <div class="event-info">
                            <strong><?php echo htmlspecialchars($event['name']); ?></strong>
                            <small><?php echo htmlspecialchars($event['date']); ?> &bull; <?php echo htmlspecialchars($event['location']); ?></small>
                        </div>
                        <div class="event-actions">
                            <a href="dashboard.php?edit=<?php echo $index; ?>#edit-form" class="btn btn-warning"><i class="fas fa-edit"></i> Edit</a>
                            <form action="update_events.php" method="POST" onsubmit="return confirm('Delete \'<?php echo addslashes(htmlspecialchars($event['name'])); ?>\'?');" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="index" value="<?php echo $index; ?>">
                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
