<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    header('Location: dashboard.php?error=upload');
    exit;
}

$file = $_FILES['image'];
$allowed_ext = ['jpg','jpeg','png','webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if ($file['size'] > $max_size) {
    header('Location: dashboard.php?error=size');
    exit;
}

$finfo = @getimagesize($file['tmp_name']);
if ($finfo === false) {
    header('Location: dashboard.php?error=type');
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed_ext)) {
    header('Location: dashboard.php?error=ext');
    exit;
}

$unique = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$dest_dir = __DIR__ . '/../assets/imgs/';
if (!is_dir($dest_dir)) {
    mkdir($dest_dir, 0755, true);
}
$dest_path = $dest_dir . $unique;

if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
    header('Location: dashboard.php?error=move');
    exit;
}

$category = $_POST['category'] ?? 'bridal';
$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';

$query = "INSERT INTO images (filename, category, title, alt_text) VALUES (?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'ssss', $unique, $category, $title, $description);
if (!mysqli_stmt_execute($stmt)) {
    // On DB error, remove uploaded file
    @unlink($dest_path);
    header('Location: dashboard.php?error=db');
    exit;
}
mysqli_stmt_close($stmt);

header('Location: dashboard.php?msg=uploaded');
exit;
