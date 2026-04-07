<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/license.php';

// If already active, send to dashboard
$license = getLicenseStatus($pdo);
if ($license['status'] === 'active') {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}
// If still in trial, also send to dashboard
if ($license['status'] === 'trial' && $license['days_left'] > 0) {
    header('Location: ' . BASE_URL . '/pages/dashboard.php');
    exit;
}

$settings = getSettings($pdo);
$lang     = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$dir      = $lang === 'ar' ? 'rtl' : 'ltr';
$appName  = $settings['app_name'] ?? 'My Restaurant';
$logo     = $settings['logo'] ?? '';
$B        = BASE_URL;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $B . '/index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($appName) ?> — Trial Expired</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Playfair+Display:wght@700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root { --primary:#c8860a; --accent:#1a1a1a; }
* { box-sizing:border-box; }
body {
  font-family: <?= $lang === 'ar' ? "'Tajawal'" : "'DM Sans'" ?>, sans-serif;
  background: #f5f4f0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}
.wrap { max-width: 520px; width: 100%; }

/* Expired card */
.exp-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 40px rgba(0,0,0,.10);
  overflow: hidden;
  margin-bottom: 16px;
}
.exp-head {
  background: var(--accent);
  padding: 36px 40px 28px;
  text-align: center;
}
.exp-head .lock-icon {
  font-size: 2.8rem;
  margin-bottom: 10px;
  display: block;
}
.exp-head h1 {
  font-family: 'Playfair Display', serif;
  color: #fff;
  font-size: 1.6rem;
  margin: 0 0 6px;
}
.exp-head p {
  color: rgba(255,255,255,.55);
  font-size: .875rem;
  margin: 0;
}
.exp-body { padding: 36px 40px; }

/* Price box */
.price-box {
  text-align: center;
  padding: 22px;
  background: #faf9f7;
  border-radius: 14px;
  border: 2px solid #f0ede8;
  margin-bottom: 24px;
}
.price-box .price { font-size: 3rem; font-weight: 700; color: var(--primary); line-height: 1; }
.price-box .price sup { font-size: 1.4rem; vertical-align: top; margin-top: .5rem; }
.price-box .label { font-size: .78rem; color: #aaa; text-transform: uppercase; letter-spacing: .1em; margin-top: 5px; }

/* Features */
.feature-list { list-style: none; padding: 0; margin: 0 0 28px; }
.feature-list li { display: flex; align-items: center; gap: 10px; padding: 7px 0; font-size: .875rem; color: #444; border-bottom: 1px solid #f5f4f0; }
.feature-list li:last-child { border-bottom: none; }
.feature-list li i { color: #16a34a; flex-shrink: 0; }

/* CTA */
.btn-activate {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  background: var(--primary);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 14px 28px;
  font-size: 1rem;
  font-weight: 600;
  width: 100%;
  text-decoration: none;
  transition: filter .2s;
  cursor: pointer;
}
.btn-activate:hover { filter: brightness(1.1); color: #fff; }

.stripe-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: .75rem;
  color: #bbb;
  margin-top: 12px;
}
.logout-link {
  display: block;
  text-align: center;
  margin-top: 16px;
  font-size: .8rem;
  color: #ccc;
  text-decoration: none;
}
.logout-link:hover { color: #888; }

/* Blurred preview strips */
.preview-strip {
  background: #fff;
  border-radius: 14px;
  box-shadow: 0 2px 12px rgba(0,0,0,.06);
  padding: 16px 20px;
  margin-bottom: 10px;
  filter: blur(3px);
  user-select: none;
  pointer-events: none;
  opacity: .6;
}
.preview-row { height: 12px; background: #eee; border-radius: 6px; margin-bottom: 8px; }
.preview-row.w80 { width: 80%; }
.preview-row.w60 { width: 60%; }
.preview-row.w40 { width: 40%; }
</style>
</head>
<body>
<div class="wrap">

  <!-- Blurred "ghost" of the app behind the wall -->
  <div class="preview-strip">
    <div class="preview-row w80"></div>
    <div class="preview-row w60"></div>
    <div class="preview-row w40"></div>
  </div>

  <div class="exp-card">
    <div class="exp-head">
      <?php if ($logo): ?>
        <img src="<?= $B ?>/pages/assets/<?= sanitize($logo) ?>"
             style="width:64px;height:64px;border-radius:10px;object-fit:cover;display:block;margin:0 auto 14px;border:2px solid rgba(255,255,255,.15);">
      <?php else: ?>
        <span class="lock-icon">🔒</span>
      <?php endif; ?>
      <h1><?= $lang === 'ar' ? 'انتهت فترة التجربة' : 'Trial Period Ended' ?></h1>
      <p>
        <?= $lang === 'ar'
          ? 'لقد انتهت فترة التجربة المجانية لمدة 30 يومًا. فعّل الترخيص للمتابعة.'
          : 'Your 30-day free trial has ended. Activate your license to continue.'
        ?>
      </p>
    </div>

    <div class="exp-body">
      <div class="price-box">
        <div class="price"><sup>$</sup>10</div>
        <div class="label"><?= $lang === 'ar' ? 'دفعة واحدة — وصول مدى الحياة' : 'One-time · Lifetime access' ?></div>
      </div>

      <ul class="feature-list">
        <li><i class="bi bi-check-circle-fill"></i><?= $lang === 'ar' ? 'وصول كامل بدون قيود' : 'Full access, no restrictions' ?></li>
        <li><i class="bi bi-check-circle-fill"></i><?= $lang === 'ar' ? 'لا رسوم شهرية أو سنوية' : 'No monthly or annual fees ever' ?></li>
        <li><i class="bi bi-check-circle-fill"></i><?= $lang === 'ar' ? 'الطلبات، القائمة، السجل، الإعدادات' : 'Orders, menu, history, settings' ?></li>
        <li><i class="bi bi-check-circle-fill"></i><?= $lang === 'ar' ? 'طباعة الإيصالات' : 'Receipt printing' ?></li>
        <li><i class="bi bi-check-circle-fill"></i><?= $lang === 'ar' ? 'دعم اللغة العربية والإنجليزية' : 'Arabic & English support' ?></li>
      </ul>

      <a href="<?= $B ?>/activate.php" class="btn-activate">
        <i class="bi bi-credit-card"></i>
        <?= $lang === 'ar' ? 'تفعيل الآن — $10 مدى الحياة' : 'Activate Now — $10 Lifetime' ?>
      </a>

      <div class="stripe-badge">
        <i class="bi bi-shield-lock-fill"></i>
        <?= $lang === 'ar' ? 'مدفوعات آمنة عبر Stripe' : 'Secure payment powered by Stripe' ?>
      </div>

      <a href="<?= $B ?>/license_expired.php?logout=1" class="logout-link">
        <i class="bi bi-box-arrow-left me-1"></i>
        <?= $lang === 'ar' ? 'تسجيل الخروج' : 'Logout' ?>
      </a>
    </div>
  </div>

  <div class="preview-strip">
    <div class="preview-row w60"></div>
    <div class="preview-row w80"></div>
    <div class="preview-row w40"></div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
