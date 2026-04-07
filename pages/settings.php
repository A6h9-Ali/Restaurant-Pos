<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
$license = requireLicense($pdo);

$settings = getSettings($pdo);
$lang = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$B    = BASE_URL;

$all_t = [
  'en' => [
    'title'         => 'App Settings',
    'general'       => 'General',
    'appearance'    => 'Appearance',
    'app_name'      => 'App / Restaurant Name',
    'language'      => 'Language',
    'currency'      => 'Currency',
    'primary_color' => 'Primary Color',
    'accent_color'  => 'Sidebar Color',
    'logo'          => 'Logo',
    'logo_hint'     => 'Recommended: 200x200px PNG',
    'save'          => 'Save Settings',
    'saved'         => 'Settings saved successfully.',
    'change_pass'   => 'Change Password',
    'old_pass'      => 'Current Password',
    'new_pass'      => 'New Password',
    'confirm_pass'  => 'Confirm Password',
    'update_pass'   => 'Update Password',
    'pass_saved'    => 'Password updated.',
    'pass_wrong'    => 'Current password is incorrect.',
    'pass_mismatch' => 'Passwords do not match.',
    'color_preview' => 'Color Preview',
    'sidebar_bg'    => 'Sidebar background',
    'primary_btn'   => 'Primary button',
  ],
  'ar' => [
    'title'         => 'إعدادات التطبيق',
    'general'       => 'عام',
    'appearance'    => 'المظهر',
    'app_name'      => 'اسم التطبيق / المطعم',
    'language'      => 'اللغة',
    'currency'      => 'العملة',
    'primary_color' => 'اللون الرئيسي',
    'accent_color'  => 'لون الشريط الجانبي',
    'logo'          => 'الشعار',
    'logo_hint'     => 'مقترح: PNG بابعاد 200x200',
    'save'          => 'حفظ الاعدادات',
    'saved'         => 'تم حفظ الاعدادات بنجاح.',
    'change_pass'   => 'تغيير كلمة المرور',
    'old_pass'      => 'كلمة المرور الحالية',
    'new_pass'      => 'كلمة المرور الجديدة',
    'confirm_pass'  => 'تاكيد كلمة المرور',
    'update_pass'   => 'تحديث كلمة المرور',
    'pass_saved'    => 'تم تحديث كلمة المرور.',
    'pass_wrong'    => 'كلمة المرور الحالية غير صحيحة.',
    'pass_mismatch' => 'كلمتا المرور غير متطابقتين.',
    'color_preview' => 'معاينة الالوان',
    'sidebar_bg'    => 'الشريط الجانبي',
    'primary_btn'   => 'زر رئيسي',
  ],
];
$t = $all_t[$lang] ?? $all_t['en'];

$msg     = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'settings') {
        $fields = ['app_name','language','currency','currency_symbol','primary_color','accent_color'];
        foreach ($fields as $f) {
            $val = sanitize($_POST[$f] ?? '');
            // SQLite uses INSERT OR REPLACE (equivalent to MySQL's ON DUPLICATE KEY UPDATE)
            $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")
                ->execute([$f, $val]);
        }
        if (!empty($_FILES['logo']['name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','webp','svg'])) {
                $filename = 'logo_' . uniqid() . '.' . $ext;
                $dest = __DIR__ . '/assets/' . $filename;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                    $pdo->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")
                        ->execute(['logo', $filename]);
                }
            }
        }
        $settings = getSettings($pdo);
        $lang = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
        $t    = $all_t[$lang] ?? $all_t['en'];
        $msg  = $t['saved'];

    } elseif ($action === 'password') {
        $oldPass = $_POST['old_pass'] ?? '';
        $newPass = $_POST['new_pass'] ?? '';
        $confirm = $_POST['confirm_pass'] ?? '';
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!password_verify($oldPass, $user['password'])) {
            $msg = $t['pass_wrong']; $msgType = 'error';
        } elseif ($newPass !== $confirm) {
            $msg = $t['pass_mismatch']; $msgType = 'error';
        } else {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $_SESSION['user_id']]);
            $msg = $t['pass_saved'];
        }
    }
}

$settings = getSettings($pdo);
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h1 class="page-title"><?= $t['title'] ?></h1>
</div>

<?php if ($msg): ?>
<script>document.addEventListener('DOMContentLoaded', function() {
  showToast(<?= json_encode($msg) ?>, <?= json_encode($msgType) ?>);
});</script>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><?= $t['general'] ?> &amp; <?= $t['appearance'] ?></div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="settings">

          <div class="mb-3">
            <label class="form-label"><?= $t['app_name'] ?></label>
            <input type="text" name="app_name" class="form-control"
                   value="<?= sanitize($settings['app_name'] ?? '') ?>" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label"><?= $t['language'] ?></label>
              <select name="language" class="form-select">
                <option value="en" <?= ($settings['language'] ?? 'en') === 'en' ? 'selected' : '' ?>>English</option>
                <option value="ar" <?= ($settings['language'] ?? '') === 'ar' ? 'selected' : '' ?>>العربية</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label"><?= $t['currency'] ?></label>
              <select name="currency" id="currencySelect" class="form-select" onchange="updateSymbol(this.value)">
                <option value="USD" <?= ($settings['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - US Dollar</option>
                <option value="IQD" <?= ($settings['currency'] ?? '') === 'IQD' ? 'selected' : '' ?>>IQD - دينار عراقي</option>
                <option value="SAR" <?= ($settings['currency'] ?? '') === 'SAR' ? 'selected' : '' ?>>SAR - ريال سعودي</option>
                <option value="AED" <?= ($settings['currency'] ?? '') === 'AED' ? 'selected' : '' ?>>AED - درهم اماراتي</option>
                <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR - Euro</option>
                <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP - British Pound</option>
              </select>
            </div>
          </div>

          <input type="hidden" name="currency_symbol" id="currencySymbol"
                 value="<?= sanitize($settings['currency_symbol'] ?? '$') ?>">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label"><?= $t['primary_color'] ?></label>
              <div class="d-flex gap-2 align-items-center">
                <input type="color" name="primary_color" id="pickerPrimary"
                       class="form-control form-control-color"
                       value="<?= sanitize($settings['primary_color'] ?? '#C8860A') ?>"
                       style="width:48px;height:40px;padding:2px;">
                <input type="text" id="primaryHex" class="form-control"
                       value="<?= sanitize($settings['primary_color'] ?? '#C8860A') ?>"
                       readonly style="font-family:monospace;">
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label"><?= $t['accent_color'] ?></label>
              <div class="d-flex gap-2 align-items-center">
                <input type="color" name="accent_color" id="pickerAccent"
                       class="form-control form-control-color"
                       value="<?= sanitize($settings['accent_color'] ?? '#1a1a1a') ?>"
                       style="width:48px;height:40px;padding:2px;">
                <input type="text" id="accentHex" class="form-control"
                       value="<?= sanitize($settings['accent_color'] ?? '#1a1a1a') ?>"
                       readonly style="font-family:monospace;">
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label"><?= $t['logo'] ?></label>
            <div class="d-flex align-items-center gap-3">
              <?php if (!empty($settings['logo'])): ?>
                <img src="<?= $B ?>/pages/assets/<?= sanitize($settings['logo']) ?>"
                     style="width:56px;height:56px;border-radius:10px;object-fit:cover;border:1px solid #eee;">
              <?php else: ?>
                <div style="width:56px;height:56px;border-radius:10px;background:#f5f4f0;display:flex;align-items:center;justify-content:center;">
                  <i class="bi bi-image text-muted"></i>
                </div>
              <?php endif; ?>
              <div>
                <input type="file" name="logo" class="form-control" accept="image/*" style="max-width:260px;">
                <small class="text-muted"><?= $t['logo_hint'] ?></small>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary"><?= $t['save'] ?></button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card mb-4">
      <div class="card-header"><?= $t['change_pass'] ?></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="password">
          <div class="mb-3">
            <label class="form-label"><?= $t['old_pass'] ?></label>
            <input type="password" name="old_pass" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><?= $t['new_pass'] ?></label>
            <input type="password" name="new_pass" class="form-control" required>
          </div>
          <div class="mb-4">
            <label class="form-label"><?= $t['confirm_pass'] ?></label>
            <input type="password" name="confirm_pass" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary"><?= $t['update_pass'] ?></button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><?= $t['color_preview'] ?></div>
      <div class="card-body">
        <div style="border-radius:10px;padding:16px;background:var(--accent);">
          <p style="color:#fff;margin:0;font-size:.875rem;"><?= $t['sidebar_bg'] ?></p>
          <div style="margin-top:10px;display:inline-block;padding:6px 16px;border-radius:6px;background:var(--primary);color:#fff;font-size:.8rem;">
            <?= $t['primary_btn'] ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var symbols = { USD:'$', IQD:' د.ع ', SAR:'r.s', AED:'d.e', EUR:'€', GBP:'£' };
function updateSymbol(val) {
  document.getElementById('currencySymbol').value = symbols[val] || val;
}
document.getElementById('pickerPrimary').addEventListener('input', function() {
  document.getElementById('primaryHex').value = this.value;
});
document.getElementById('pickerAccent').addEventListener('input', function() {
  document.getElementById('accentHex').value = this.value;
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
