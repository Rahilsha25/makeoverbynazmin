<?php
session_start();
require_once __DIR__ . '/../db.php';

// If already logged in, redirect
if (isset($_SESSION['admin_user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
$stmt = mysqli_prepare($conn, "SELECT id, password_hash FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $id, $hash);
            mysqli_stmt_fetch($stmt);
            if (password_verify($password, $hash)) {
                        // Successful login: set session and last activity
                        session_regenerate_id(true);
                        $_SESSION['admin_user'] = $username;
                        $_SESSION['admin_id'] = $id;
                        $_SESSION['last_activity'] = time();
                        header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/johndoe.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>body{padding:30px;font-family:Arial,Helvetica,sans-serif} .login-box{max-width:420px;margin:0 auto;border:1px solid #ddd;padding:20px;border-radius:6px} .error{color:#b00;margin-bottom:10px}</style>
</head>
<body>
<div class="login-box">
    <div class="logo"><img src="../assets/logo.png" alt="Logo"></div>
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div>
            <label>Username</label><br>
            <input type="text" name="username" required>
        </div>
        <div style="margin-top:10px;">
            <label>Password</label><br>
            <input type="password" name="password" required>
        </div>
        <div style="margin-top:15px;">
            <button type="submit">Login</button>
        </div>
    </form>
</div>
</body>
</html>
