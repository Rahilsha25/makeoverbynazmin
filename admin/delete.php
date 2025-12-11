<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Get filename
$stmt = mysqli_prepare($conn, "SELECT filename FROM images WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $filename);
if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    header('Location: dashboard.php');
    exit;
}
mysqli_stmt_close($stmt);

$file_path = __DIR__ . '/../assets/imgs/' . $filename;
if (file_exists($file_path)) {
    @unlink($file_path);
}

$del = mysqli_prepare($conn, "DELETE FROM images WHERE id = ?");
mysqli_stmt_bind_param($del, 'i', $id);
mysqli_stmt_execute($del);
mysqli_stmt_close($del);

header('Location: dashboard.php?msg=deleted');
exit;
