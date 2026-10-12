<?php
require __DIR__ . '/config.php';
$message = '';
$count = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($count > 0) {
    http_response_code(403);
    exit('Admin already exists. Delete setup_admin.php now and use login.php.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    if (!$email || strlen($password) < 12) {
        $message = 'Enter a valid email and a password of at least 12 characters.';
    } else {
        $stmt = db()->prepare('INSERT INTO admins (email, password_hash) VALUES (?, ?)');
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: login.php?created=1');
        exit;
    }
}
?><!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create admin</title>
<link rel="stylesheet" href="admin.css"><body class="auth-page"><form class="auth-card" method="post">
<h1>Create first admin</h1><p>This page works only when no admin exists.</p>
<?php if ($message): ?><p class="error"><?=e($message)?></p><?php endif; ?>
<input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>">
<label>Email<input name="email" type="email" required autocomplete="username"></label>
<label>Password (12+ characters)<input name="password" type="password" required minlength="12" autocomplete="new-password"></label>
<button>Create admin account</button>
<p class="warning">After creating the account, delete setup_admin.php from this folder.</p>
</form></body></html>
