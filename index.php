<?php
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: ' . BASE_URL . '/pages/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}

$settings     = getSettings($pdo);
$lang         = $settings['language'] ?? 'en';
$dir          = $lang === 'ar' ? 'rtl' : 'ltr';
$primaryColor = $settings['primary_color'] ?? '#C8860A';
$accentColor  = $settings['accent_color']  ?? '#1a1a1a';
$appName      = $settings['app_name']       ?? 'My Restaurant';
$logo         = $settings['logo']           ?? '';
$B            = BASE_URL;
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($appName) ?> — Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
:root {
  --primary: <?= $primaryColor ?>;
  --accent:  <?= $accentColor ?>;
  --font-main: <?= $lang === 'ar' ? "'Tajawal'" : "'DM Sans'" ?>, sans-serif;
  --font-display: <?= $lang === 'ar' ? "'Tajawal'" : "'Playfair Display'" ?>, serif;
}
body { font-family: var(--font-main); min-height: 100vh; display: flex; background: #f5f4f0; margin: 0; }
.login-left {
  width: 45%; background: var(--accent); display: flex; flex-direction: column;
  align-items: center; justify-content: center; padding: 60px 48px;
  position: relative; overflow: hidden;
}
.login-left::before { content:''; position:absolute; width:400px; height:400px; border-radius:50%; background:rgba(255,255,255,.03); top:-100px; right:-100px; }
.login-left::after  { content:''; position:absolute; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,.03); bottom:-80px; left:-80px; }
.brand-logo-login { width:180px; height:180px; border-radius:14px; object-fit:cover; margin-bottom:20px; display:block; border:2px solid rgba(255,255,255,.15); }
.login-left h1 { font-family:var(--font-display); color:#fff; font-size:2rem; font-weight:700; margin:0 0 8px; text-align:center; }
.login-left p  { color:var(--primary); font-size:.85rem; letter-spacing:.12em; text-transform:uppercase; margin:0; text-align:center; }
.login-right { flex:1; display:flex; align-items:center; justify-content:center; padding:60px 48px; }
.login-box { width:100%; max-width:380px; }
.login-box h2 { font-family:var(--font-display); font-size:1.6rem; font-weight:700; margin-bottom:6px; }
.login-box p.sub { color:#888; font-size:.875rem; margin-bottom:32px; }
.form-control { border-radius:9px; border-color:#e0ddd8; padding:11px 14px; font-size:.9rem; font-family:var(--font-main); }
.form-control:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(200,134,10,.12); }
.form-label { font-size:.82rem; font-weight:500; color:#555; }
.btn-login { background:var(--primary); border:none; color:#fff; border-radius:9px; padding:12px; font-size:.9rem; font-weight:500; width:100%; transition:all .2s; font-family:var(--font-main); cursor:pointer; }
.btn-login:hover { filter:brightness(1.1); }
.input-icon { position:relative; }
.input-icon i { position:absolute; top:50%; transform:translateY(-50%); <?= $dir === 'rtl' ? 'right' : 'left' ?>:13px; color:#aaa; font-size:1rem; pointer-events:none; }
.input-icon .form-control { padding-<?= $dir === 'rtl' ? 'right' : 'left' ?>:38px; }
.alert { border-radius:9px; font-size:.875rem; }
@media (max-width:768px) { .login-left { display:none; } .login-right { padding:40px 24px; } }
</style>
</head>
<body>

<div class="login-left">
  <?php if ($logo): ?>
    <img src="<?= $B ?>/pages/assets/<?= sanitize($logo) ?>" class="brand-logo-login" alt="Logo">
  <?php endif; ?>
  <h1><?= sanitize($appName) ?></h1>
  <p><?= $lang === 'ar' ? 'نظام نقطة البيع' : 'Point of Sale System' ?></p>
</div>

<div class="login-right">
  <div class="login-box">
    <h2><?= $lang === 'ar' ? 'مرحباً بك' : 'Welcome back' ?></h2>
    <p class="sub"><?= $lang === 'ar' ? 'سجّل الدخول للمتابعة' : 'Sign in to your account to continue' ?></p>

    <?php if ($error): ?>
      <div class="alert alert-danger py-2"><?= sanitize($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label"><?= $lang === 'ar' ? 'اسم المستخدم' : 'Username' ?></label>
        <div class="input-icon">
          <i class="bi bi-person"></i>
          <input type="text" name="username" class="form-control"
                 placeholder="<?= $lang === 'ar' ? 'اسم المستخدم' : 'Enter username' ?>" required>
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label"><?= $lang === 'ar' ? 'كلمة المرور' : 'Password' ?></label>
        <div class="input-icon">
          <i class="bi bi-lock"></i>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn-login">
        <?= $lang === 'ar' ? 'تسجيل الدخول' : 'Sign In' ?>
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
