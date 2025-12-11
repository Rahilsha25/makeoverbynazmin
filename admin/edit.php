<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? 'bridal';

    // Handle optional file replacement
    $new_filename = null;
    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];
        $allowed_ext = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $finfo = @getimagesize($file['tmp_name']);
        if ($finfo !== false && in_array($ext, $allowed_ext)) {
            $unique = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest_dir = __DIR__ . '/../assets/imgs/';
            if (!is_dir($dest_dir)) mkdir($dest_dir, 0755, true);
            $dest_path = $dest_dir . $unique;
            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                $new_filename = $unique;
            }
        }
    }

    if ($new_filename !== null) {
        // Get old filename to remove
        $s = mysqli_prepare($conn, "SELECT filename FROM images WHERE id = ? LIMIT 1");
        mysqli_stmt_bind_param($s, 'i', $id);
        mysqli_stmt_execute($s);
        mysqli_stmt_bind_result($s, $oldfn);
        mysqli_stmt_fetch($s);
        mysqli_stmt_close($s);
        if (!empty($oldfn) && file_exists(__DIR__ . '/../assets/imgs/' . $oldfn)) {
            @unlink(__DIR__ . '/../assets/imgs/' . $oldfn);
        }

        $q = mysqli_prepare($conn, "UPDATE images SET filename = ?, category = ?, title = ?, description = ? WHERE id = ?");
        mysqli_stmt_bind_param($q, 'ssssi', $new_filename, $category, $title, $description, $id);
        mysqli_stmt_execute($q);
        mysqli_stmt_close($q);
    } else {
        $q = mysqli_prepare($conn, "UPDATE images SET category = ?, title = ?, description = ? WHERE id = ?");
        mysqli_stmt_bind_param($q, 'sssi', $category, $title, $description, $id);
        mysqli_stmt_execute($q);
        mysqli_stmt_close($q);
    }

    header('Location: dashboard.php?msg=updated');
    exit;
}

// fetch current
$s = mysqli_prepare($conn, "SELECT filename, category, title, description FROM images WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
mysqli_stmt_bind_result($s, $filename, $category, $title, $description);
if (!mysqli_stmt_fetch($s)) {
    mysqli_stmt_close($s);
    header('Location: dashboard.php');
    exit;
}
mysqli_stmt_close($s);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Image</title>
</head>
<body>
<h1>Edit Image</h1>
<p><a href="dashboard.php">Back to Dashboard</a></p>
<form method="post" enctype="multipart/form-data">
    <div>
        <img src="../assets/imgs/<?php echo htmlspecialchars($filename); ?>" style="max-width:200px;display:block;margin-bottom:8px">
        <label>Replace image (optional)</label><br>
        <input type="file" name="image" accept="image/*">
    </div>
    <div style="margin-top:8px;">
        <label>Category</label><br>
        <select name="category">
            <option value="bridal" <?php echo $category==='bridal'?'selected':''; ?>>Bridal</option>
            <option value="rentals" <?php echo $category==='rentals'?'selected':''; ?>>Rentals</option>
            <option value="hairstyles" <?php echo $category==='hairstyles'?'selected':''; ?>>Hairstyles</option>
        </select>
    </div>
    <div style="margin-top:8px;">
        <label>Title</label><br>
        <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>">
    </div>
    <div style="margin-top:8px;">
        <label>Description</label><br>
        <textarea name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
    </div>
    <div style="margin-top:10px;">
        <button type="submit">Save</button>
    </div>
</form>
</body>
</html>
