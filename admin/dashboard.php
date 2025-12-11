<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

// Fetch images
$images = [];
$stmt = mysqli_prepare($conn, "SELECT id, filename, category, title, alt_text FROM images ORDER BY id DESC");
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $id, $filename, $category, $title, $description);
while (mysqli_stmt_fetch($stmt)) {
    $images[] = [
        'id' => $id,
        'filename' => $filename,
        'category' => $category,
        'title' => $title,
        'description' => $description
    ];
}
mysqli_stmt_close($stmt);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
</head>
    <link rel="stylesheet" href="../assets/css/johndoe.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px}</style>
</head>
<body>
<div class="wrap admin-page">
    <div class="admin-header">
        <img src="../assets/logo.png" class="admin-logo" alt="Logo">
        <h1>Admin Dashboard</h1>
        <div class="admin-actions">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_user']); ?></span>
<a href="logout.php">Logout</a>
        </div>
    </div>

<?php if (!empty($_GET['msg'])): ?>
    <?php if ($_GET['msg'] === 'uploaded' || $_GET['msg'] === 'updated' || $_GET['msg'] === 'deleted'): ?>
        <div style="
            padding:12px 18px;
            border-radius:6px;
            font-size:14px;
            margin-bottom:15px;
            background:#e6f9ed;
            border-left:4px solid #28a745;
            color:#155724;
            animation:fadeIn 0.3s ease;
        ">
            Action completed successfully.
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($_GET['error'])): ?>
    <div style="
        padding:12px 18px;
        border-radius:6px;
        font-size:14px;
        margin-bottom:15px;
        background:#fdecea;
        border-left:4px solid #dc3545;
        color:#721c24;
        animation:fadeIn 0.3s ease;
    ">
        An error occurred: <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>


<div style="
    background:#fff;
    border-radius:10px;
    padding:20px 25px;
    box-shadow:0 3px 12px rgba(0,0,0,0.08);
    margin-bottom:25px;
    animation:fadeIn 0.4s ease;
">
    <div style="margin-bottom:15px;">
        <h3 style="margin:0;font-size:20px;color:#333;">Upload Image</h3>
        <div style="color:#777;font-size:13px;margin-top:3px;">
            Allowed: jpg, jpeg, png, webp — max 5MB
        </div>
    </div>

    <div>
        <form action="upload.php" method="post" enctype="multipart/form-data">

            <div style="display:flex;flex-wrap:wrap;gap:20px;">

                <div style="flex:1;min-width:220px;display:flex;flex-direction:column;">
                    <label style="font-size:14px;margin-bottom:6px;font-weight:500;color:#555;">Image file</label>
                    <input type="file" name="image" accept="image/*" required
                        style="padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
                </div>

                <div style="flex:1;min-width:220px;display:flex;flex-direction:column;">
                    <label style="font-size:14px;margin-bottom:6px;font-weight:500;color:#555;">Category</label>
                    <select name="category" required
                        style="padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
                        <option value="bridal">Bridal</option>
                        <option value="rentals">Rentals</option>
                        <option value="hairstyles">Hairstyles</option>
                    </select>
                </div>

                <div style="flex-basis:100%;display:flex;flex-direction:column;">
                    <label style="font-size:14px;margin-bottom:6px;font-weight:500;color:#555;">Title</label>
                    <input type="text" name="title" required
                        style="padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;">
                </div>

                <div style="flex-basis:100%;display:flex;flex-direction:column;">
                    <label style="font-size:14px;margin-bottom:6px;font-weight:500;color:#555;">Description</label>
                    <textarea name="description" rows="3"
                        style="padding:10px 12px;border:1px solid #ddd;border-radius:6px;font-size:14px;"></textarea>
                </div>
            </div>

            <div style="margin-top:15px;">
                <button type="submit" 
                    style="
                        padding:10px 18px;
                        background:#007bff;
                        border:none;
                        border-radius:6px;
                        color:white;
                        font-size:14px;
                        cursor:pointer;
                        transition:0.2s;
                    "
                    onmouseover="this.style.background='#0069d9'"
                    onmouseout="this.style.background='#007bff'"
                >
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

    <h2 style="margin-top:24px;margin-bottom:8px">Uploaded Images</h2>
    <?php if (count($images) === 0): ?>
        <p>No images yet.</p>
    <?php else: ?>
        <div class="gallery">
            <?php foreach ($images as $img): ?>
                <div class="gcard">
                    <img src="../assets/imgs/<?php echo htmlspecialchars($img['filename']); ?>" class="gthumb" alt="<?php echo htmlspecialchars($img['title']); ?>">
                    <div class="gmeta">
                        <h4><?php echo htmlspecialchars($img['title']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($img['description'])); ?></p>
                    </div>
                    <div class="gfooter">
                        <div class="category"><?php echo htmlspecialchars($img['category']); ?></div>
                        <div class="actions">
                            <a class="btn btn-ghost" href="delete.php?id=<?php echo $img['id']; ?>" onclick="return confirm('Delete this image?');">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
