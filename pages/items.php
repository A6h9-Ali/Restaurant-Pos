<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$settings        = getSettings($pdo);
$lang            = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$currency_symbol = $settings['currency_symbol'] ?? '$';
$B               = BASE_URL;

$translations = [
  'en' => [
    'title'          => 'Menu Items',
    'add'            => 'Add Item',
    'edit'           => 'Edit Item',
    'name_en'        => 'Name (English)',
    'name_ar'        => 'Name (Arabic)',
    'price'          => 'Price',
    'photo'          => 'Photo',
    'status'         => 'Status',
    'actions'        => 'Actions',
    'active'         => 'Active',
    'inactive'       => 'Inactive',
    'save'           => 'Save Item',
    'cancel'         => 'Cancel',
    'delete_confirm' => 'Delete this item?',
    'no_items'       => 'No items yet. Add your first menu item.',
    'photo_hint'     => 'JPG, PNG up to 2MB',
    'saved'          => 'Item saved successfully.',
    'deleted'        => 'Item deleted.',
  ],
  'ar' => [
    'title'          => 'عناصر القائمة',
    'add'            => 'إضافة عنصر',
    'edit'           => 'تعديل العنصر',
    'name_en'        => 'الاسم (إنجليزي)',
    'name_ar'        => 'الاسم (عربي)',
    'price'          => 'السعر',
    'photo'          => 'الصورة',
    'status'         => 'الحالة',
    'actions'        => 'إجراءات',
    'active'         => 'نشط',
    'inactive'       => 'معطّل',
    'save'           => 'حفظ العنصر',
    'cancel'         => 'إلغاء',
    'delete_confirm' => 'حذف هذا العنصر؟',
    'no_items'       => 'لا عناصر بعد. أضف أول عنصر في القائمة.',
    'photo_hint'     => 'JPG أو PNG حتى 2MB',
    'saved'          => 'تم حفظ العنصر بنجاح.',
    'deleted'        => 'تم حذف العنصر.',
  ],
];
$t = $translations[$lang] ?? $translations['en'];

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $name_en = sanitize($_POST['name_en'] ?? '');
    $name_ar = sanitize($_POST['name_ar'] ?? '');
    $price   = floatval($_POST['price'] ?? 0);
    $active  = isset($_POST['active']) ? 1 : 0;
    $photo   = '';

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','webp'])) {
            $filename = uniqid('item_') . '.' . $ext;
            $dest = __DIR__ . '/assets/' . $filename;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photo = $filename;
            }
        }
    }

    if ($action === 'add') {
        $pdo->prepare("INSERT INTO items (name_en, name_ar, price, photo, active) VALUES (?, ?, ?, ?, ?)")
            ->execute([$name_en, $name_ar, $price, $photo, $active]);
        $msg = $t['saved'];
    } elseif ($action === 'edit') {
        $id = intval($_POST['id']);
        if ($photo) {
            $pdo->prepare("UPDATE items SET name_en=?, name_ar=?, price=?, photo=?, active=? WHERE id=?")
                ->execute([$name_en, $name_ar, $price, $photo, $active, $id]);
        } else {
            $pdo->prepare("UPDATE items SET name_en=?, name_ar=?, price=?, active=? WHERE id=?")
                ->execute([$name_en, $name_ar, $price, $active, $id]);
        }
        $msg = $t['saved'];
    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        $pdo->prepare("DELETE FROM items WHERE id=?")->execute([$id]);
        $msg = $t['deleted'];
    }
}

$items    = $pdo->query("SELECT * FROM items ORDER BY id DESC")->fetchAll();
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id=?");
    $stmt->execute([intval($_GET['edit'])]);
    $editItem = $stmt->fetch();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h1 class="page-title"><?= $t['title'] ?></h1>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#itemModal" id="addBtn">
    <i class="bi bi-plus-lg me-1"></i> <?= $t['add'] ?>
  </button>
</div>

<?php if ($msg): ?>
  <script>document.addEventListener('DOMContentLoaded',()=>showToast(<?= json_encode($msg) ?>));</script>
<?php endif; ?>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($items)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-grid" style="font-size:2.5rem;opacity:.3;"></i>
        <p class="mt-3"><?= $t['no_items'] ?></p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#faf9f7;">
          <tr>
            <th style="width:60px;padding-left:22px;"><?= $lang === 'ar' ? 'صورة' : 'Photo' ?></th>
            <th><?= $lang === 'ar' ? 'الاسم' : 'Name' ?></th>
            <th><?= $t['price'] ?></th>
            <th><?= $t['status'] ?></th>
            <th style="width:120px;"><?= $t['actions'] ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td style="padding-<?= $lang === 'ar' ? 'right' : 'left' ?>:22px;">
              <?php if ($item['photo']): ?>
                <img src="<?= $B ?>/pages/assets/<?= sanitize($item['photo']) ?>" class="item-thumb" alt="">
              <?php else: ?>
                <div class="item-thumb d-flex align-items-center justify-content-center" style="background:#f0ede8;">
                  <i class="bi bi-image text-muted"></i>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <div style="font-weight:500;"><?= sanitize($lang === 'ar' ? $item['name_ar'] : $item['name_en']) ?></div>
              <div style="font-size:.78rem;color:#999;"><?= sanitize($lang === 'ar' ? $item['name_en'] : $item['name_ar']) ?></div>
            </td>
            <td><strong><?= $currency_symbol . number_format($item['price'], 2) ?></strong></td>
            <td>
              <span class="badge rounded-pill"
                    style="background:<?= $item['active'] ? '#e8f5e9' : '#fce4ec' ?>;color:<?= $item['active'] ? '#2e7d32' : '#c62828' ?>;font-weight:500;font-size:.75rem;">
                <?= $item['active'] ? $t['active'] : $t['inactive'] ?>
              </span>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-secondary me-1"
                      onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)"
                      title="<?= $t['edit'] ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <form method="POST" style="display:inline;" onsubmit="return confirm('<?= $t['delete_confirm'] ?>')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Item Modal -->
<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;border:none;">
      <form method="POST" enctype="multipart/form-data" id="itemForm">
        <input type="hidden" name="action" id="formAction" value="add">
        <input type="hidden" name="id" id="formId" value="">

        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="modalTitle" style="font-weight:600;"><?= $t['add'] ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4">
          <div class="text-center mb-3">
            <div id="photoPreview"
                 style="width:90px;height:90px;border-radius:14px;background:#f5f4f0;margin:0 auto;display:flex;align-items:center;justify-content:center;overflow:hidden;cursor:pointer;border:2px dashed #ddd;"
                 onclick="document.getElementById('photoInput').click()">
              <i class="bi bi-camera" style="font-size:1.6rem;color:#ccc;" id="cameraIcon"></i>
              <img id="previewImg" src="" style="display:none;width:100%;height:100%;object-fit:cover;">
            </div>
            <small style="color:#aaa;font-size:.75rem;display:block;margin-top:6px;"><?= $t['photo_hint'] ?></small>
            <input type="file" name="photo" id="photoInput" accept="image/*" style="display:none;">
          </div>

          <div class="mb-3">
            <label class="form-label"><?= $t['name_en'] ?></label>
            <input type="text" name="name_en" id="f_name_en" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><?= $t['name_ar'] ?></label>
            <input type="text" name="name_ar" id="f_name_ar" class="form-control" dir="rtl" required>
          </div>
          <div class="mb-3">
            <label class="form-label"><?= $t['price'] ?> (<?= $currency_symbol ?>)</label>
            <input type="number" name="price" id="f_price" class="form-control" step="0.01" min="0" required>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="active" id="f_active" checked>
            <label class="form-check-label" for="f_active" style="font-size:.875rem;"><?= $t['active'] ?></label>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 px-4">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= $t['cancel'] ?></button>
          <button type="submit" class="btn btn-primary"><?= $t['save'] ?></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const addTitle  = <?= json_encode($t['add']) ?>;
const editTitle = <?= json_encode($t['edit']) ?>;
const baseUrl   = <?= json_encode($B) ?>;

document.getElementById('addBtn').addEventListener('click', () => {
  document.getElementById('modalTitle').textContent = addTitle;
  document.getElementById('formAction').value = 'add';
  document.getElementById('formId').value = '';
  document.getElementById('itemForm').reset();
  resetPreview();
});

function editItem(item) {
  document.getElementById('modalTitle').textContent = editTitle;
  document.getElementById('formAction').value = 'edit';
  document.getElementById('formId').value = item.id;
  document.getElementById('f_name_en').value = item.name_en;
  document.getElementById('f_name_ar').value = item.name_ar;
  document.getElementById('f_price').value = item.price;
  document.getElementById('f_active').checked = item.active == 1;
  if (item.photo) {
    document.getElementById('previewImg').src = baseUrl + '/pages/assets/' + item.photo;
    document.getElementById('previewImg').style.display = 'block';
    document.getElementById('cameraIcon').style.display = 'none';
  } else { resetPreview(); }
  new bootstrap.Modal(document.getElementById('itemModal')).show();
}

function resetPreview() {
  document.getElementById('previewImg').style.display = 'none';
  document.getElementById('previewImg').src = '';
  document.getElementById('cameraIcon').style.display = '';
}

document.getElementById('photoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    document.getElementById('previewImg').src = e.target.result;
    document.getElementById('previewImg').style.display = 'block';
    document.getElementById('cameraIcon').style.display = 'none';
  };
  reader.readAsDataURL(file);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
