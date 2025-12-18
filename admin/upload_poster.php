<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['poster'])) {
    $target_dir = "../assets/";
    $file_extension = strtolower(pathinfo($_FILES["poster"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . "promo-poster." . $file_extension;

    // Validate image
    $check = getimagesize($_FILES["poster"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Allow only JPG/PNG
    if ($file_extension != "jpg" && $file_extension != "png" && $file_extension != "jpeg") {
        die("Sorry, only JPG, JPEG, & PNG files are allowed.");
    }

    // Force rename to 'promo-poster.jpg' (or whatever ext)
    // NOTE: To keep it consistent for frontend, we might want to force conversion or strict naming.
    // For simplicity, we just save it. But frontend needs to know extension.
    // BETTER FIX: Frontend should look for 'promo-poster.jpg' or we force save as jpg.
    // Let's force save as 'promo-poster.jpg' if it's a jpeg.

    $final_target = $target_dir . "promo-poster.jpg";

    if (move_uploaded_file($_FILES["poster"]["tmp_name"], $final_target)) {
        header("Location: dashboard.php?msg=Poster Updated Successfully!");
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>