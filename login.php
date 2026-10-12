<?php
require __DIR__ . '/config.php';
if (is_logged_in()) { header('Location: admin.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT id, email, password_hash FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: admin.php');
        exit;
    }
    usleep(400000);
    $error = 'Invalid email or password.';
}
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Portfolio Admin Login</title>
<link rel="stylesheet" href="admin.css"><body class="auth-page"><form class="auth-card" method="post">
<h1>Portfolio Admin</h1><p>Sign in to edit portfolio content.</p>
<?php if (isset($_GET['created'])): ?><p class="success">Admin created. You can log in.</p><?php endif; ?>
<?php if ($error): ?><p class="error"><?=e($error)?></p><?php endif; ?>
<input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
<label>Email<input name="email" type="email" required autocomplete="username"></label>
<label>Password<input name="password" type="password" required autocomplete="current-password"></label>
<button>Login</button>
</form></body></html>
