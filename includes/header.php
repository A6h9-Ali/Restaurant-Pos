<?php
// includes/header.php
$settings     = getSettings($pdo);
$lang         = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$dir          = $lang === 'ar' ? 'rtl' : 'ltr';
$primaryColor = $settings['primary_color'] ?? '#C8860A';
$accentColor  = $settings['accent_color']  ?? '#1a1a1a';
$appName      = $settings['app_name']       ?? 'My Restaurant';
$logo         = $settings['logo']           ?? '';

$nav_translations = [
    'en' => [
        'dashboard' => 'Dashboard',
        'items'     => 'Menu Items',
        'orders'    => 'New Order',
        'history'   => 'Order History',
        'settings'  => 'Settings',
        'logout'    => 'Logout',
    ],
    'ar' => [
        'dashboard' => 'الرئيسية',
        'items'     => 'عناصر القائمة',
        'orders'    => 'طلب جديد',
        'history'   => 'سجل الطلبات',
        'settings'  => 'الإعدادات',
        'logout'    => 'تسجيل الخروج',
    ],
];
$nav = $nav_translations[$lang] ?? $nav_translations['en'];

$currentPage = basename($_SERVER['PHP_SELF']);
$B = BASE_URL; // shorthand
$license = $license ?? ['status' => 'trial', 'days_left' => 30];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= sanitize($appName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
<?php if ($logo): ?>
<link rel="icon" type="image/png" href="<?= $B ?>/pages/assets/<?= sanitize($logo) ?>">
<?php endif; ?>
<style>
:root {
  --primary: <?= $primaryColor ?>;
  --accent:  <?= $accentColor ?>;
  --sidebar-w: 240px;
  --font-main: <?= $lang === 'ar' ? "'Tajawal'" : "'DM Sans'" ?>, sans-serif;
  --font-display: <?= $lang === 'ar' ? "'Tajawal'" : "'Playfair Display'" ?>, serif;
}
*, *::before, *::after { box-sizing: border-box; }
body { font-family: var(--font-main); background: #f5f4f0; color: #1a1a1a; min-height: 100vh; }
.sidebar {
  position: fixed; top: 0; <?= $dir === 'rtl' ? 'right' : 'left' ?>: 0;
  width: var(--sidebar-w); height: 100vh;
  background: var(--accent); display: flex; flex-direction: column; z-index: 100; overflow: hidden;
}
.sidebar-brand { padding: 28px 20px 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
.sidebar-brand .brand-name { font-family: var(--font-display); font-size: 1.15rem; color: #fff; font-weight: 700; margin: 0; line-height: 1.2; }
.sidebar-brand .brand-sub  { font-size: .7rem; color: var(--primary); letter-spacing: .1em; text-transform: uppercase; margin: 0; }
.sidebar-brand img.brand-logo { width: 180px; height: 180px; border-radius: 8px; object-fit: cover; margin-bottom: 10px; display: block; }
.sidebar nav { flex: 1; padding: 16px 0; }
.nav-link-item {
  display: flex; align-items: center; gap: 12px; padding: 11px 22px;
  color: rgba(255,255,255,.6); text-decoration: none; font-size: .875rem; font-weight: 400;
  transition: all .18s; border-<?= $dir === 'rtl' ? 'right' : 'left' ?>: 3px solid transparent; margin: 1px 0;
}
.nav-link-item i { font-size: 1.1rem; min-width: 20px; }
.nav-link-item:hover { color: #fff; background: rgba(255,255,255,.05); }
.nav-link-item.active { color: #fff; background: rgba(255,255,255,.07); border-<?= $dir === 'rtl' ? 'right' : 'left' ?>-color: var(--primary); }
.sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.08); }
.sidebar-footer a { color: rgba(255,255,255,.5); text-decoration: none; font-size: .8rem; display: flex; align-items: center; gap: 8px; transition: color .18s; }
.sidebar-footer a:hover { color: #fff; }
.main-content { margin-<?= $dir === 'rtl' ? 'right' : 'left' ?>: var(--sidebar-w); min-height: 100vh; padding: 36px 36px 60px; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; }
.page-title { font-family: var(--font-display); font-size: 1.7rem; font-weight: 700; margin: 0; color: #1a1a1a; }
.card { border: none; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04); background: #fff; }
.card-header { background: transparent; border-bottom: 1px solid #f0ede8; font-weight: 500; padding: 18px 22px; font-size: .9rem; }
.card-body { padding: 22px; }
.btn-primary { background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; font-weight: 500; border-radius: 8px; padding: 8px 20px; }
.btn-primary:hover { filter: brightness(1.08); }
.btn-outline-primary { border-color: var(--primary) !important; color: var(--primary) !important; border-radius: 8px; }
.btn-outline-primary:hover { background: var(--primary) !important; color: #fff !important; }
.table th { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: #888; font-weight: 500; border-top: none; }
.form-control, .form-select { border-radius: 8px; border-color: #e8e5e0; font-size: .875rem; padding: 9px 13px; }
.form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(200,134,10,.12); }
.form-label { font-size: .82rem; font-weight: 500; color: #555; margin-bottom: 5px; }
.item-thumb { width: 48px; height: 48px; border-radius: 10px; object-fit: cover; background: #f0ede8; }
.toast-container { position: fixed; bottom: 24px; <?= $dir === 'rtl' ? 'left' : 'right' ?>: 24px; z-index: 9999; }
@media (max-width: 768px) {
  .sidebar { width: 200px; }
  :root { --sidebar-w: 200px; }
  .main-content { padding: 20px 16px 40px; }
}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-brand">
    <?php if ($logo): ?>
      <img src="<?= $B ?>/pages/assets/<?= sanitize($logo) ?>" class="brand-logo" alt="Logo">
    <?php endif; ?>
    <p class="brand-name"><?= sanitize($appName) ?></p>
    <p class="brand-sub"><?= $lang === 'ar' ? 'نقطة البيع' : 'Point of Sale' ?></p>
  </div>

  <nav>
    <a href="<?= $B ?>/pages/dashboard.php" class="nav-link-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
      <i class="bi bi-grid-1x2"></i> <?= $nav['dashboard'] ?>
    </a>
    <a href="<?= $B ?>/pages/items.php" class="nav-link-item <?= $currentPage === 'items.php' ? 'active' : '' ?>">
      <i class="bi bi-grid"></i> <?= $nav['items'] ?>
    </a>
    <a href="<?= $B ?>/pages/orders.php" class="nav-link-item <?= $currentPage === 'orders.php' ? 'active' : '' ?>">
      <i class="bi bi-receipt"></i> <?= $nav['orders'] ?>
    </a>
    <a href="<?= $B ?>/pages/history.php" class="nav-link-item <?= $currentPage === 'history.php' ? 'active' : '' ?>">
      <i class="bi bi-clock-history"></i> <?= $nav['history'] ?>
    </a>
    <a href="<?= $B ?>/pages/settings.php" class="nav-link-item <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
      <i class="bi bi-gear"></i> <?= $nav['settings'] ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= $B ?>/logout.php"><i class="bi bi-box-arrow-left"></i> <?= $nav['logout'] ?></a>
  </div>
</div>

<div class="main-content">

<?php if ($license['status'] === 'trial'): ?>
<div style="background:linear-gradient(90deg,#c8860a,#e6a020);color:#fff;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;border-radius:10px;margin-bottom:20px;font-size:.85rem;flex-wrap:wrap;gap:8px;">
  <span>
    <i class="bi bi-clock me-1"></i>
    <?= $lang === 'ar' ? 'وضع التجربة المجانية' : 'Free Trial' ?> —
    <strong><?= $license['days_left'] ?></strong>
    <?= $lang === 'ar' ? ' يوم متبقي' : ' day' . ($license['days_left'] == 1 ? '' : 's') . ' remaining' ?>
  </span>
  <a href="<?= $B ?>/activate.php"
     style="background:#fff;color:#c8860a;border-radius:6px;padding:5px 14px;font-weight:600;font-size:.82rem;text-decoration:none;white-space:nowrap;">
    <?= $lang === 'ar' ? '🔓 تفعيل الآن — $10' : '🔓 Activate Now — $10 lifetime' ?>
  </a>
</div>
<?php endif; ?>

