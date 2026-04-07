<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/license.php';
requireLogin();

$settings = getSettings($pdo);
$lang     = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$dir      = $lang === 'ar' ? 'rtl' : 'ltr';
$appName  = $settings['app_name'] ?? 'My Restaurant';
$B        = BASE_URL;

$license  = getLicenseStatus($pdo);

// Already active — nothing to do
if ($license['status'] === 'active') {
    header('Location: ' . $B . '/pages/dashboard.php');
    exit;
}

// ── Stripe Payment Link ────────────────────────────────────────────────────
// No secret key needed — payments handled entirely by Stripe
$stripePaymentLink = 'https://buy.stripe.com/eVqdR1fIA7dT73XbzyefC01';

$cancelled = isset($_GET['cancelled']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($appName) ?> — Activate License</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500&family=Playfair+Display:wght@700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<style>
:root { --primary:#c8860a; --accent:#1a1a1a; }
body { font-family:'DM Sans',sans-serif; background:#f5f4f0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
.card { border:none; border-radius:20px; box-shadow:0 4px 40px rgba(0,0,0,.1); max-width:480px; width:100%; overflow:hidden; }
.card-head { background:var(--accent); padding:36px 40px 28px; text-align:center; }
.card-head h1 { font-family:'Playfair Display',serif; color:#fff; font-size:1.6rem; margin:0 0 6px; }
.card-head p  { color:var(--primary); font-size:.8rem; letter-spacing:.1em; text-transform:uppercase; margin:0; }
.card-body { padding:36px 40px; }
.price-box { text-align:center; margin:24px 0; padding:24px; background:#faf9f7; border-radius:14px; border:2px solid #f0ede8; }
.price-box .price { font-size:3rem; font-weight:700; color:var(--primary); line-height:1; }
.price-box .price sup { font-size:1.4rem; vertical-align:top; margin-top:.5rem; }
.price-box .label { font-size:.8rem; color:#aaa; text-transform:uppercase; letter-spacing:.1em; margin-top:6px; }
.feature-list { list-style:none; padding:0; margin:0 0 28px; }
.feature-list li { display:flex; align-items:center; gap:10px; padding:7px 0; font-size:.875rem; color:#444; border-bottom:1px solid #f5f4f0; }
.feature-list li:last-child { border-bottom:none; }
.feature-list li i { color:#16a34a; font-size:1rem; flex-shrink:0; }
.btn-stripe { background:var(--primary); border:none; color:#fff; border-radius:10px; padding:14px 28px; font-size:1rem; font-weight:600; width:100%; cursor:pointer; transition:filter .2s; display:flex; align-items:center; justify-content:center; gap:10px; text-decoration:none; }
.btn-stripe:hover { filter:brightness(1.1); color:#fff; }
.btn-back { display:block; text-align:center; margin-top:14px; font-size:.82rem; color:#aaa; text-decoration:none; }
.btn-back:hover { color:#666; }
.badge-trial { display:inline-block; background:#fff8ee; color:#c8860a; border:1px solid #f5dfa0; border-radius:20px; font-size:.75rem; font-weight:600; padding:3px 12px; margin-bottom:16px; }
.alert { border-radius:10px; font-size:.875rem; }
.stripe-badge { display:flex; align-items:center; justify-content:center; gap:6px; font-size:.75rem; color:#bbb; margin-top:14px; }
</style>
</head>
<body>
<div class="card">
  <div class="card-head">
    <h1><?= $lang === 'ar' ? '🔓 تفعيل الترخيص' : '🔓 Activate License' ?></h1>
    <p><?= sanitize($appName) ?></p>
  </div>
  <div class="card-body">

    <?php if ($license['status'] === 'trial' && ($license['days_left'] ?? 0) > 0): ?>
      <div class="text-center">
        <span class="badge-trial">
          <i class="bi bi-clock"></i>
          <?= $license['days_left'] ?> <?= $lang === 'ar' ? 'يوم متبقٍ في التجربة' : 'trial day' . ($license['days_left'] == 1 ? '' : 's') . ' remaining' ?>
        </span>
      </div>
    <?php endif; ?>

    <?php if ($cancelled): ?>
      <div class="alert alert-warning mb-3">
        <i class="bi bi-x-circle me-1"></i>
        <?= $lang === 'ar' ? 'تم إلغاء الدفع. يمكنك المحاولة مرة أخرى.' : 'Payment cancelled. You can try again anytime.' ?>
      </div>
    <?php endif; ?>

    <div class="price-box">
      <div class="price"><sup>$</sup>10</div>
      <div class="label"><?= $lang === 'ar' ? 'دفعة واحدة — مدى الحياة' : 'One-time payment — Lifetime access' ?></div>
    </div>

    <ul class="feature-list">
      <li><i class="bi bi-check-circle-fill"></i> <?= $lang === 'ar' ? 'وصول مدى الحياة بدون رسوم شهرية' : 'Lifetime access, no monthly fees' ?></li>
      <li><i class="bi bi-check-circle-fill"></i> <?= $lang === 'ar' ? 'جميع الميزات: الطلبات، القائمة، السجل، الإعدادات' : 'All features: orders, menu, history, settings' ?></li>
      <li><i class="bi bi-check-circle-fill"></i> <?= $lang === 'ar' ? 'قاعدة بيانات SQLite محلية — بياناتك معك' : 'Local SQLite database — your data stays with you' ?></li>
      <li><i class="bi bi-check-circle-fill"></i> <?= $lang === 'ar' ? 'دعم اللغتين العربية والإنجليزية' : 'Arabic & English language support' ?></li>
      <li><i class="bi bi-check-circle-fill"></i> <?= $lang === 'ar' ? 'طباعة الإيصالات' : 'Receipt printing' ?></li>
    </ul>

    <a href="<?= $stripePaymentLink ?>" class="btn-stripe">
      <i class="bi bi-credit-card"></i>
      <?= $lang === 'ar' ? 'الدفع عبر Stripe — $10' : 'Pay with Stripe — $10' ?>
    </a>

    <div class="stripe-badge">
      <i class="bi bi-shield-lock-fill"></i>
      <?= $lang === 'ar' ? 'مدفوعات آمنة عبر Stripe' : 'Secure payment powered by Stripe' ?>
    </div>

    <a href="<?= $B ?>/pages/dashboard.php" class="btn-back">
      ← <?= $lang === 'ar' ? 'العودة إلى لوحة التحكم' : 'Back to Dashboard' ?>
    </a>

  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>