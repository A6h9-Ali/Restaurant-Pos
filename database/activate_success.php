<?php
require_once ('../includes/config.php');
require_once ('../includes/license.php');
requireLogin();

$settings = getSettings($pdo);
$lang     = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$dir      = $lang === 'ar' ? 'rtl' : 'ltr';
$appName  = $settings['app_name'] ?? 'My Restaurant';
$B        = BASE_URL;

// Already active — nothing to do
$license = getLicenseStatus($pdo);
if ($license['status'] !== 'active') {
    // Activate the license immediately on arrival
    $licenseKey = 'PL-' . strtoupper(bin2hex(random_bytes(8)));
    $pdo->prepare("INSERT OR REPLACE INTO settings (key,value) VALUES ('license_status','active')")->execute();
    $pdo->prepare("INSERT OR REPLACE INTO settings (key,value) VALUES ('license_key',?)")->execute([$licenseKey]);
    $pdo->prepare("INSERT OR REPLACE INTO settings (key,value) VALUES ('license_activated_at',?)")->execute([date('Y-m-d H:i:s')]);
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($appName) ?> — <?= $lang === 'ar' ? 'تم التفعيل!' : 'Activated!' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Playfair+Display:wght@700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root { --primary:#c8860a; --accent:#1a1a1a; }
body { font-family:'DM Sans',sans-serif; background:#f5f4f0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
.card { border:none; border-radius:20px; box-shadow:0 4px 40px rgba(0,0,0,.1); max-width:440px; width:100%; padding:48px 40px; text-align:center; }
.icon-circle { width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:2rem; margin:0 auto 20px; background:#e8f5e9; color:#16a34a; }
h2 { font-family:'Playfair Display',serif; font-size:1.6rem; font-weight:700; margin-bottom:10px; }
p.sub { color:#888; font-size:.875rem; margin-bottom:28px; }
.btn-go { display:block; background:var(--primary); color:#fff; border-radius:10px; padding:13px; font-size:.95rem; font-weight:600; text-decoration:none; transition:filter .2s; }
.btn-go:hover { filter:brightness(1.1); color:#fff; }
.redirect-note { font-size:.75rem; color:#ccc; margin-top:16px; }
</style>
<meta http-equiv="refresh" content="5;url=<?= $B ?>/pages/dashboard.php">
</head>
<body>
<div class="card">
  <div class="icon-circle"><i class="bi bi-check-lg"></i></div>
  <h2><?= $lang === 'ar' ? '🎉 تم التفعيل!' : '🎉 Activated!' ?></h2>
  <p class="sub">
    <?= $lang === 'ar'
      ? 'تم تفعيل ترخيصك مدى الحياة بنجاح. شكراً لدعمك!'
      : 'Your lifetime license has been activated. Thank you for your support!'
    ?>
  </p>
  <a href="<?= $B ?>/pages/dashboard.php" class="btn-go">
    <?= $lang === 'ar' ? 'الذهاب إلى لوحة التحكم' : 'Go to Dashboard' ?> →
  </a>
  <p class="redirect-note">
    <?= $lang === 'ar' ? 'سيتم التحويل تلقائياً خلال 5 ثوانٍ...' : 'Redirecting automatically in 5 seconds...' ?>
  </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>