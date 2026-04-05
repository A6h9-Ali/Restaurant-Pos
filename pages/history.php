<?php
require_once __DIR__ . '/../includes/config.php';

// ── AJAX: edit order — must be before any output ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_edit'])) {
    requireLogin();
    header('Content-Type: application/json; charset=utf-8');

    $orderId  = intval($_POST['order_id'] ?? 0);
    $items    = json_decode($_POST['items'] ?? '[]', true);
    $discount = floatval($_POST['discount'] ?? 0);
    $notes    = sanitize($_POST['notes'] ?? '');

    if ($orderId && is_array($items) && count($items) > 0) {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += floatval($item['price']) * intval($item['qty']);
        }
        $discount = min($discount, $subtotal);
        $total    = $subtotal - $discount;

        $pdo->prepare("UPDATE orders SET subtotal=?, discount=?, total=?, notes=? WHERE id=?")
            ->execute([$subtotal, $discount, $total, $notes, $orderId]);
        $pdo->prepare("DELETE FROM order_items WHERE order_id=?")->execute([$orderId]);

        foreach ($items as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id, item_id, item_name, item_price, quantity) VALUES (?,?,?,?,?)")
                ->execute([$orderId, intval($item['item_id'] ?? 0), sanitize($item['name'] ?? ''), floatval($item['price']), intval($item['qty'])]);
        }
        echo json_encode(['success' => true, 'subtotal' => $subtotal, 'discount' => $discount, 'total' => $total]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    exit;
}

requireLogin();

$settings = getSettings($pdo);
$lang     = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$sym      = $settings['currency_symbol'] ?? '$';
$logo     = !empty($settings['logo']) ? BASE_URL . '/pages/assets/' . $settings['logo'] : '';
$appName  = $settings['app_name'] ?? 'My Restaurant';
$B        = BASE_URL;

$getOrderUrl = $B . '/pages/get_order.php';
$historyUrl  = $B . '/pages/history.php';

$all_t = [
  'en' => [
    'title'        => 'Order History',
    'order_no'     => 'Order #',
    'date'         => 'Date',
    'items'        => 'Items',
    'subtotal'     => 'Subtotal',
    'discount'     => 'Discount',
    'total'        => 'Total',
    'notes'        => 'Notes',
    'view'         => 'View',
    'edit'         => 'Edit',
    'no_orders'    => 'No orders yet.',
    'receipt'      => 'Receipt',
    'close'        => 'Close',
    'print'        => 'Print Receipt',
    'thank_you'    => 'Thank you for your visit!',
    'edit_order'   => 'Edit Order',
    'item_name'    => 'Item',
    'qty'          => 'Qty',
    'price'        => 'Price',
    'amount'       => 'Amount',
    'discount_lbl' => 'Discount Amount',
    'save'         => 'Save Changes',
    'cancel'       => 'Cancel',
    'saved'        => 'Order updated successfully.',
    'remove_item'  => 'Remove',
    'empty_order'  => 'Order must have at least one item.',
    'net_error'    => 'Network error — check browser console (F12).',
  ],
  'ar' => [
    'title'        => 'سجل الطلبات',
    'order_no'     => 'رقم الطلب',
    'date'         => 'التاريخ',
    'items'        => 'العناصر',
    'subtotal'     => 'المجموع',
    'discount'     => 'الخصم',
    'total'        => 'الإجمالي',
    'notes'        => 'ملاحظات',
    'view'         => 'عرض',
    'edit'         => 'تعديل',
    'no_orders'    => 'لا طلبات بعد.',
    'receipt'      => 'إيصال',
    'close'        => 'إغلاق',
    'print'        => 'طباعة الإيصال',
    'thank_you'    => 'شكراً لزيارتكم!',
    'edit_order'   => 'تعديل الطلب',
    'item_name'    => 'الصنف',
    'qty'          => 'الكمية',
    'price'        => 'السعر',
    'amount'       => 'المبلغ',
    'discount_lbl' => 'مبلغ الخصم',
    'save'         => 'حفظ التغييرات',
    'cancel'       => 'إلغاء',
    'saved'        => 'تم تحديث الطلب بنجاح.',
    'remove_item'  => 'حذف',
    'empty_order'  => 'يجب أن يحتوي الطلب على عنصر واحد على الأقل.',
    'net_error'    => 'خطأ في الشبكة — افتح وحدة التحكم (F12).',
  ],
];
$t = $all_t[$lang] ?? $all_t['en'];

$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 200")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<style>
.edit-item-row td { vertical-align:middle; padding:6px 8px; }
.edit-qty-input   { width:68px; text-align:center; display:inline-block; }
.btn-remove-row   { background:none; border:none; color:#ccc; cursor:pointer; padding:3px 7px; border-radius:5px; transition:color .15s,background .15s; font-size:1rem; }
.btn-remove-row:hover { color:#fff; background:#e53935; }
</style>

<div class="page-header">
  <h1 class="page-title"><?= $t['title'] ?></h1>
</div>

<div class="card">
  <div class="card-body p-0">
    <?php if (empty($orders)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-clock-history" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:10px;"></i>
        <p><?= $t['no_orders'] ?></p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#faf9f7;">
          <tr>
            <th style="padding-<?= $lang==='ar'?'right':'left' ?>:22px;"><?= $t['order_no'] ?></th>
            <th><?= $t['date'] ?></th>
            <th><?= $t['subtotal'] ?></th>
            <th><?= $t['discount'] ?></th>
            <th><?= $t['total'] ?></th>
            <th><?= $t['notes'] ?></th>
            <th style="width:110px;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr id="order-row-<?= (int)$o['id'] ?>">
            <td style="padding-<?= $lang==='ar'?'right':'left' ?>:22px;">
              <span class="badge" style="background:#f0ede8;color:#666;font-weight:500;font-size:.78rem;">
                <?= sanitize($o['order_number']) ?>
              </span>
            </td>
            <td style="font-size:.83rem;color:#666;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td class="row-subtotal"><?= $sym . number_format($o['subtotal'], 2) ?></td>
            <td class="row-discount" style="color:#e53935;">–<?= $sym . number_format($o['discount'], 2) ?></td>
            <td class="row-total"><strong><?= $sym . number_format($o['total'], 2) ?></strong></td>
            <td class="row-notes" style="font-size:.8rem;color:#999;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
              <?= sanitize($o['notes']) ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-secondary"
                        onclick="viewOrder(<?= (int)$o['id'] ?>)"
                        title="<?= $t['view'] ?>">
                  <i class="bi bi-receipt"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary"
                        onclick="editOrder(<?= (int)$o['id'] ?>)"
                        title="<?= $t['edit'] ?>">
                  <i class="bi bi-pencil"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:430px;">
    <div class="modal-content" style="border-radius:14px;border:none;">
      <div class="modal-header border-0 pb-1">
        <h6 class="modal-title" style="font-weight:600;"><?= $t['receipt'] ?></h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-0" id="receiptBody"></div>
      <div class="modal-footer border-0 pt-0 gap-2">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= $t['close'] ?></button>
        <button class="btn btn-primary" onclick="printModal()">
          <i class="bi bi-printer me-1"></i><?= $t['print'] ?>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius:14px;border:none;">
      <div class="modal-header border-0 pb-1">
        <h6 class="modal-title" style="font-weight:600;">
          <?= $t['edit_order'] ?> — <span id="editOrderNum" style="color:var(--primary);"></span>
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive mb-3">
          <table class="table align-middle mb-0" style="font-size:.875rem;">
            <thead style="background:#faf9f7;">
              <tr>
                <th><?= $t['item_name'] ?></th>
                <th style="width:100px;text-align:center;"><?= $t['qty'] ?></th>
                <th style="width:110px;"><?= $t['price'] ?></th>
                <th style="width:110px;"><?= $t['amount'] ?></th>
                <th style="width:44px;"></th>
              </tr>
            </thead>
            <tbody id="editItemsBody"></tbody>
          </table>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label"><?= $t['notes'] ?></label>
            <textarea id="editNotes" class="form-control" rows="3" style="resize:none;font-size:.875rem;"></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label"><?= $t['discount_lbl'] ?> (<?= $sym ?>)</label>
            <input type="number" id="editDiscount" class="form-control" min="0" step="0.01" value="0" oninput="recalcEdit()">
            <div class="mt-3 p-3" style="background:#faf9f7;border-radius:10px;font-size:.85rem;">
              <div class="d-flex justify-content-between mb-1 text-muted">
                <span><?= $t['subtotal'] ?></span><span id="editSubtotalDisplay">—</span>
              </div>
              <div class="d-flex justify-content-between mb-1" style="color:#e53935;">
                <span>– <?= $t['discount'] ?></span><span id="editDiscountDisplay">—</span>
              </div>
              <div class="d-flex justify-content-between fw-bold pt-2" style="border-top:1px solid #eee;">
                <span><?= $t['total'] ?></span>
                <span id="editTotalDisplay" style="color:var(--primary);">—</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 gap-2">
        <button class="btn btn-outline-secondary" data-bs-dismiss="modal"><?= $t['cancel'] ?></button>
        <button class="btn btn-primary" onclick="saveEdit()">
          <i class="bi bi-check-lg me-1"></i><?= $t['save'] ?>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
var SYM         = <?= json_encode($sym) ?>;
var APP_NAME    = <?= json_encode($appName) ?>;
var APP_LOGO    = <?= json_encode($logo) ?>;
var IS_RTL      = <?= $lang === 'ar' ? 'true' : 'false' ?>;
var LANG        = <?= json_encode($lang) ?>;
var TH          = <?= json_encode($t) ?>;
var URL_GET     = <?= json_encode($getOrderUrl) ?>;
var URL_HISTORY = <?= json_encode($historyUrl) ?>;

var currentEditId = null;

function xhr(method, url, data, onSuccess, onError) {
  var req = new XMLHttpRequest();
  req.open(method, url, true);
  if (method === 'POST') req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  req.onreadystatechange = function() {
    if (req.readyState !== 4) return;
    if (req.status === 200) {
      try {
        onSuccess(JSON.parse(req.responseText));
      } catch(e) {
        if (onError) onError('Response was not JSON: ' + req.responseText.substring(0, 200));
      }
    } else {
      if (onError) onError('HTTP error ' + req.status);
    }
  };
  req.onerror = function() { if (onError) onError(TH.net_error); };
  req.send(data || null);
}

function viewOrder(id) {
  xhr('GET', URL_GET + '?id=' + id, null,
    function(data) {
      if (data.error) { alert('Error: ' + data.error); return; }
      document.getElementById('receiptBody').innerHTML = buildReceiptHtml(data);
      new bootstrap.Modal(document.getElementById('receiptModal')).show();
    },
    function(err) { alert('Could not load order.\n\n' + err); }
  );
}

function buildReceiptHtml(data) {
  var dir    = IS_RTL ? 'rtl' : 'ltr';
  var aStart = IS_RTL ? 'right' : 'left';
  var aEnd   = IS_RTL ? 'left'  : 'right';
  var flex   = IS_RTL ? 'row-reverse' : 'row';
  var font   = IS_RTL ? 'Tajawal,Arial,sans-serif' : 'inherit';

  var logoHtml = APP_LOGO
    ? '<img src="'+ "assets/" + APP_LOGO + '" style="width:180px;height:180px;border-radius:10px;object-fit:cover;display:block;margin:0 auto 10px;">'
    : '';

  var dateStr = '';
  try { dateStr = new Date(data.order.created_at.replace(' ','T')).toLocaleString(); } catch(e) { dateStr = data.order.created_at; }

  var rows = '';
  for (var i = 0; i < data.items.length; i++) {
    var it = data.items[i];
    rows += '<tr>'
      + '<td style="padding:5px 2px;text-align:' + aStart + ';">' + it.item_name + '</td>'
      + '<td style="text-align:center;padding:5px 4px;">' + it.quantity + '</td>'
      + '<td style="text-align:' + aEnd + ';padding:5px 2px;">' + SYM + parseFloat(it.item_price).toFixed(2) + '</td>'
      + '<td style="text-align:' + aEnd + ';padding:5px 2px;">' + SYM + (parseFloat(it.item_price) * parseInt(it.quantity)).toFixed(2) + '</td>'
      + '</tr>';
  }

  var notesHtml = data.order.notes
    ? '<div style="margin-top:10px;font-size:.78rem;color:#999;border-top:1px solid #eee;padding-top:8px;">' + data.order.notes + '</div>'
    : '';

  return '<div dir="' + dir + '" style="font-family:' + font + ';">'
    + '<div style="text-align:center;margin-bottom:16px;">' + logoHtml
    + '<div style="font-size:1rem;font-weight:700;">' + APP_NAME + '</div>'
    + '<div style="font-size:.8rem;color:#999;">' + TH.order_no + ': <strong>' + data.order.order_number + '</strong></div>'
    + '<div style="font-size:.75rem;color:#bbb;">' + dateStr + '</div>'
    + '</div>'
    + '<table style="width:100%;border-collapse:collapse;font-size:.82rem;border-top:1px dashed #ccc;border-bottom:1px dashed #ccc;margin-bottom:12px;" dir="' + dir + '">'
    + '<thead><tr style="color:#aaa;">'
    + '<th style="text-align:' + aStart + ';padding:6px 2px;font-weight:500;">' + TH.items + '</th>'
    + '<th style="text-align:center;font-weight:500;">x</th>'
    + '<th style="text-align:' + aEnd + ';font-weight:500;">' + TH.price + '</th>'
    + '<th style="text-align:' + aEnd + ';font-weight:500;">' + TH.amount + '</th>'
    + '</tr></thead><tbody>' + rows + '</tbody></table>'
    + '<div style="font-size:.85rem;">'
    + '<div style="display:flex;flex-direction:' + flex + ';justify-content:space-between;padding:3px 0;color:#666;"><span>' + TH.subtotal + '</span><span>' + SYM + parseFloat(data.order.subtotal).toFixed(2) + '</span></div>'
    + '<div style="display:flex;flex-direction:' + flex + ';justify-content:space-between;padding:3px 0;color:#e53935;"><span>– ' + TH.discount + '</span><span>–' + SYM + parseFloat(data.order.discount).toFixed(2) + '</span></div>'
    + '<div style="display:flex;flex-direction:' + flex + ';justify-content:space-between;padding:8px 0 0;font-size:1rem;font-weight:700;border-top:1px solid #eee;margin-top:6px;"><span>' + TH.total + '</span><span>' + SYM + parseFloat(data.order.total).toFixed(2) + '</span></div>'
    + notesHtml
    + '</div>'
    + '<div style="text-align:center;margin-top:18px;font-size:.78rem;color:#bbb;">' + TH.thank_you + '</div>'
    + '</div>';
}

function printModal() {
  var content = document.getElementById('receiptBody').innerHTML;
  if (!content) return;
  var dir = IS_RTL ? 'rtl' : 'ltr';
  var gFont = IS_RTL ? '<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">' : '';
  var fam = IS_RTL ? "'Tajawal',Arial,sans-serif" : "'Courier New',monospace";
  var w = window.open('', '_blank', 'width=430,height=680');
  if (!w) { alert('Allow popups in your browser to print.'); return; }
  w.document.open();
  w.document.write('<!DOCTYPE html><html lang="' + LANG + '" dir="' + dir + '">');
  w.document.write('<head><meta charset="UTF-8"><title>Receipt</title>');
  w.document.write(gFont);
  w.document.write('<style>body{font-family:' + fam + ';padding:20px;max-width:380px;margin:0 auto;}img{max-width:100%;}@media print{body{padding:0;}}</style>');
  w.document.write('</head><body>');
  w.document.write(content);
  w.document.write('<scr' + 'ipt>window.onload=function(){window.print();window.close();}</' + 'script>');
  w.document.write('</body></html>');
  w.document.close();
}

function editOrder(id) {
  currentEditId = id;
  xhr('GET', URL_GET + '?id=' + id, null,
    function(data) {
      if (data.error) { alert('Error: ' + data.error); return; }
      document.getElementById('editOrderNum').textContent = data.order.order_number;
      document.getElementById('editDiscount').value       = parseFloat(data.order.discount).toFixed(2);
      document.getElementById('editNotes').value          = data.order.notes || '';
      renderEditRows(data.items);
      new bootstrap.Modal(document.getElementById('editModal')).show();
    },
    function(err) { alert('Could not load order.\n\n' + err); }
  );
}

function renderEditRows(items) {
  var html = '';
  for (var i = 0; i < items.length; i++) {
    var it     = items[i];
    var iid    = it.item_id || 0;
    var iname  = (it.item_name || '').replace(/"/g, '&quot;');
    var iprice = parseFloat(it.item_price) || 0;
    var iqty   = parseInt(it.quantity) || 1;
    html += '<tr class="edit-item-row" data-item-id="' + iid + '" data-name="' + iname + '" data-price="' + iprice.toFixed(2) + '">'
      + '<td style="font-weight:500;">' + it.item_name + '</td>'
      + '<td><input type="number" class="form-control edit-qty-input" min="1" value="' + iqty + '" oninput="recalcEdit()"></td>'
      + '<td>' + SYM + iprice.toFixed(2) + '</td>'
      + '<td class="row-amt" style="font-weight:500;">' + SYM + (iprice * iqty).toFixed(2) + '</td>'
      + '<td><button type="button" class="btn-remove-row" onclick="removeEditRow(this)"><i class="bi bi-x-lg"></i></button></td>'
      + '</tr>';
  }
  document.getElementById('editItemsBody').innerHTML = html;
  recalcEdit();
}

function removeEditRow(btn) { btn.closest('tr').remove(); recalcEdit(); }

function recalcEdit() {
  var rows     = document.querySelectorAll('#editItemsBody .edit-item-row');
  var subtotal = 0;
  for (var i = 0; i < rows.length; i++) {
    var price = parseFloat(rows[i].getAttribute('data-price')) || 0;
    var qty   = parseInt(rows[i].querySelector('.edit-qty-input').value) || 0;
    var amt   = price * qty;
    subtotal += amt;
    rows[i].querySelector('.row-amt').textContent = SYM + amt.toFixed(2);
  }
  var disc  = Math.min(parseFloat(document.getElementById('editDiscount').value) || 0, subtotal);
  var total = subtotal - disc;
  document.getElementById('editSubtotalDisplay').textContent = SYM + subtotal.toFixed(2);
  document.getElementById('editDiscountDisplay').textContent = '–' + SYM + disc.toFixed(2);
  document.getElementById('editTotalDisplay').textContent    = SYM + total.toFixed(2);
}

function saveEdit() {
  if (!currentEditId) return;
  var rows = document.querySelectorAll('#editItemsBody .edit-item-row');
  if (rows.length === 0) { alert(TH.empty_order); return; }

  var items = [];
  for (var i = 0; i < rows.length; i++) {
    items.push({
      item_id: rows[i].getAttribute('data-item-id'),
      name:    rows[i].getAttribute('data-name'),
      price:   rows[i].getAttribute('data-price'),
      qty:     parseInt(rows[i].querySelector('.edit-qty-input').value) || 1,
    });
  }

  var body = 'ajax_edit=1'
    + '&order_id='  + encodeURIComponent(currentEditId)
    + '&items='     + encodeURIComponent(JSON.stringify(items))
    + '&discount='  + encodeURIComponent(document.getElementById('editDiscount').value || 0)
    + '&notes='     + encodeURIComponent(document.getElementById('editNotes').value);

  xhr('POST', URL_HISTORY, body,
    function(res) {
      if (res.success) {
        var tr = document.getElementById('order-row-' + currentEditId);
        if (tr) {
          tr.querySelector('.row-subtotal').textContent = SYM + parseFloat(res.subtotal).toFixed(2);
          tr.querySelector('.row-discount').textContent = '–' + SYM + parseFloat(res.discount).toFixed(2);
          tr.querySelector('.row-total').innerHTML      = '<strong>' + SYM + parseFloat(res.total).toFixed(2) + '</strong>';
          tr.querySelector('.row-notes').textContent    = document.getElementById('editNotes').value;
        }
        bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
        showToast(TH.saved);
      } else {
        alert('Save failed: ' + (res.error || 'unknown'));
      }
    },
    function(err) { alert(TH.net_error + '\n\n' + err); }
  );
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
