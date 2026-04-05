<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$settings       = getSettings($pdo);
$lang           = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$currencySymbol = $settings['currency_symbol'] ?? '$';
$appName        = $settings['app_name'] ?? 'My Restaurant';
$B              = BASE_URL;

$translations_o = [
  'en' => [
    'title'        => 'New Order',
    'order'        => 'Current Order',
    'search'       => 'Search items...',
    'subtotal'     => 'Subtotal',
    'discount'     => 'Discount',
    'discount_pct' => '% off',
    'total'        => 'Total',
    'notes'        => 'Order Notes',
    'place'        => 'Place Order',
    'clear'        => 'Clear',
    'print'        => 'Print Receipt',
    'new_order'    => 'New Order',
    'add_items'    => 'Click items from the menu to add them',
    'order_placed' => 'Order placed!',
    'receipt_title'=> 'Receipt',
    'date'         => 'Date',
    'order_no'     => 'Order #',
    'item'         => 'Item',
    'qty'          => 'Qty',
    'price'        => 'Price',
    'amount'       => 'Amount',
    'thank_you'    => 'Thank you for your visit!',
  ],
  'ar' => [
    'title'        => 'طلب جديد',
    'order'        => 'الطلب الحالي',
    'search'       => 'بحث في العناصر...',
    'subtotal'     => 'المجموع',
    'discount'     => 'الخصم',
    'discount_pct' => '% خصم',
    'total'        => 'الإجمالي',
    'notes'        => 'ملاحظات',
    'place'        => 'تأكيد الطلب',
    'clear'        => 'مسح',
    'print'        => 'طباعة الإيصال',
    'new_order'    => 'طلب جديد',
    'add_items'    => 'انقر على عناصر القائمة لإضافتها',
    'order_placed' => 'تم تأكيد الطلب!',
    'receipt_title'=> 'إيصال',
    'date'         => 'التاريخ',
    'order_no'     => 'طلب رقم',
    'item'         => 'الصنف',
    'qty'          => 'الكمية',
    'price'        => 'السعر',
    'amount'       => 'المبلغ',
    'thank_you'    => 'شكراً لزيارتكم!',
  ],
];
$t = $translations_o[$lang] ?? $translations_o['en'];

// Save order via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_order'])) {
    $orderData = json_decode($_POST['order_data'], true);
    if ($orderData && !empty($orderData['items'])) {
        $orderNumber = generateOrderNumber();
        $subtotal    = floatval($orderData['subtotal']);
        $discount    = floatval($orderData['discount']);
        $total       = floatval($orderData['total']);
        $notes       = sanitize($orderData['notes'] ?? '');

        $pdo->prepare("INSERT INTO orders (order_number, subtotal, discount, total, notes) VALUES (?,?,?,?,?)")
            ->execute([$orderNumber, $subtotal, $discount, $total, $notes]);
        $orderId = $pdo->lastInsertId();

        foreach ($orderData['items'] as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id, item_id, item_name, item_price, quantity) VALUES (?,?,?,?,?)")
                ->execute([$orderId, $item['id'], $item['name'], $item['price'], $item['qty']]);
        }

        echo json_encode(['success' => true, 'order_number' => $orderNumber, 'order_id' => $orderId]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

$items = $pdo->query("SELECT * FROM items WHERE active=1 ORDER BY name_" . $lang)->fetchAll();
include __DIR__ . '/../includes/header.php';
?>

<style>
.pos-layout { display:grid; grid-template-columns:1fr 360px; gap:20px; height:calc(100vh - 100px); }
.menu-panel { overflow-y:auto; }
.order-panel { display:flex; flex-direction:column; height:100%; }
.item-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:14px; }
.menu-item-card { background:#fff; border-radius:14px; overflow:hidden; cursor:pointer; transition:transform .15s,box-shadow .15s; box-shadow:0 1px 3px rgba(0,0,0,.06); user-select:none; }
.menu-item-card:hover { transform:translateY(-2px); box-shadow:0 4px 16px rgba(0,0,0,.1); }
.menu-item-card:active { transform:scale(.97); }
.item-img { width:100%; height:110px; object-fit:cover; }
.item-img-placeholder { width:100%; height:110px; background:#f5f4f0; display:flex; align-items:center; justify-content:center; }
.item-card-body { padding:10px 12px; }
.item-card-name { font-size:.82rem; font-weight:500; line-height:1.3; margin-bottom:4px; }
.item-card-price { font-size:.85rem; color:var(--primary); font-weight:600; }
.order-list { flex:1; overflow-y:auto; padding:12px 0; }
.order-row { display:flex; align-items:center; gap:8px; padding:8px 16px; border-bottom:1px solid #f5f4f0; }
.order-row:last-child { border-bottom:none; }
.order-row-name { flex:1; font-size:.85rem; font-weight:500; }
.order-row-sub { font-size:.75rem; color:#999; }
.qty-btn { width:26px; height:26px; border-radius:6px; border:1px solid #e0ddd8; background:#f8f7f5; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:.9rem; font-weight:600; transition:all .1s; }
.qty-btn:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.qty-display { min-width:22px; text-align:center; font-size:.85rem; font-weight:600; }
.order-summary { padding:14px 16px; border-top:1px solid #f0ede8; background:#fff; }
.summary-row { display:flex; justify-content:space-between; align-items:center; font-size:.85rem; padding:3px 0; }
.summary-row.total { font-size:1rem; font-weight:700; padding-top:8px; margin-top:6px; border-top:1px solid #f0ede8; }
.discount-input-row { display:flex; gap:6px; align-items:center; margin:8px 0; }
.discount-input-row input { width:72px; padding:5px 9px; border-radius:7px; border:1px solid #e0ddd8; font-size:.85rem; text-align:center; }
.search-bar { position:sticky; top:0; background:#f5f4f0; padding-bottom:14px; z-index:5; }
</style>

<div class="page-header">
  <h1 class="page-title"><?= $t['title'] ?></h1>
</div>

<div class="pos-layout">
  <!-- LEFT: Menu -->
  <div class="menu-panel">
    <div class="search-bar">
      <input type="text" id="searchInput" class="form-control" placeholder="<?= $t['search'] ?>" oninput="filterItems()">
    </div>
    <div class="item-grid" id="itemGrid">
      <?php foreach ($items as $item): ?>
      <div class="menu-item-card"
           onclick='addToOrder(<?= json_encode([
             'id'    => $item['id'],
             'name'  => $lang === 'ar' ? $item['name_ar'] : $item['name_en'],
             'price' => floatval($item['price']),
             'photo' => $item['photo'],
           ]) ?>)'
           data-name="<?= strtolower(sanitize($item['name_en'] . ' ' . $item['name_ar'])) ?>">
        <?php if ($item['photo']): ?>
          <img src="<?= $B ?>/pages/assets/<?= sanitize($item['photo']) ?>" class="item-img" alt="">
        <?php else: ?>
          <div class="item-img-placeholder"><i class="bi bi-egg-fried" style="font-size:1.8rem;color:#ccc;"></i></div>
        <?php endif; ?>
        <div class="item-card-body">
          <div class="item-card-name"><?= sanitize($lang === 'ar' ? $item['name_ar'] : $item['name_en']) ?></div>
          <div class="item-card-price"><?= $currencySymbol ?><?= number_format($item['price'], 2) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- RIGHT: Order -->
  <div class="card order-panel">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span><?= $t['order'] ?></span>
      <button class="btn btn-sm btn-outline-secondary" onclick="clearOrder()"><?= $t['clear'] ?></button>
    </div>

    <div class="order-list" id="orderList">
      <div id="emptyMsg" class="text-center py-4 text-muted" style="font-size:.85rem;">
        <i class="bi bi-basket" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
        <?= $t['add_items'] ?>
      </div>
    </div>

    <div class="order-summary">
      <textarea id="orderNotes" class="form-control mb-3" rows="2"
                placeholder="<?= $t['notes'] ?>" style="font-size:.82rem;resize:none;"></textarea>

      <div class="summary-row">
        <span style="color:#666;"><?= $t['subtotal'] ?></span>
        <span id="subtotalDisplay"><?= $currencySymbol ?>0.00</span>
      </div>

      <div class="discount-input-row">
        <span style="font-size:.82rem;color:#666;flex:1;"><?= $t['discount'] ?></span>
        <input type="number" id="discountPct" min="0" max="100" value="0" placeholder="%" onchange="recalc()">
        <span style="font-size:.8rem;color:#999;"><?= $t['discount_pct'] ?></span>
        <input type="number" id="discountAmt" min="0" value="0"
               placeholder="<?= $currencySymbol ?>" onchange="recalcFromAmt()">
      </div>

      <div class="summary-row">
        <span style="color:#e53935;">- <?= $t['discount'] ?></span>
        <span id="discountDisplay" style="color:#e53935;">-<?= $currencySymbol ?>0.00</span>
      </div>

      <div class="summary-row total">
        <span><?= $t['total'] ?></span>
        <span id="totalDisplay" style="color:var(--primary);"><?= $currencySymbol ?>0.00</span>
      </div>

      <button class="btn btn-primary w-100 mt-3" onclick="placeOrder()" id="placeBtn" disabled>
        <i class="bi bi-check-circle me-1"></i> <?= $t['place'] ?>
      </button>
    </div>
  </div>
</div>

<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
    <div class="modal-content" style="border-radius:14px;">
      <div class="modal-body p-0">
        <div id="receiptContent" style="padding:24px;"></div>
        <div class="d-flex gap-2 p-3 border-top">
          <button class="btn btn-primary flex-fill" onclick="printReceipt()">
            <i class="bi bi-printer me-1"></i> <?= $t['print'] ?>
          </button>
          <button class="btn btn-outline-secondary flex-fill" onclick="startNewOrder()" data-bs-dismiss="modal">
            <i class="bi bi-plus me-1"></i> <?= $t['new_order'] ?>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const sym     = <?= json_encode($currencySymbol) ?>;
const lang    = <?= json_encode($lang) ?>;
const appName = <?= json_encode($appName) ?>;
const appLogo = <?= json_encode(!empty($settings['logo']) ? $B . '/pages/assets/' . $settings['logo'] : '') ?>;
const isRtl   = <?= json_encode($lang === 'ar') ?>;
const ordersUrl = <?= json_encode($B . '/pages/orders.php') ?>;
const tStr = <?= json_encode([
  'receipt_title' => $t['receipt_title'],
  'date'          => $t['date'],
  'order_no'      => $t['order_no'],
  'item'          => $t['item'],
  'qty'           => $t['qty'],
  'price'         => $t['price'],
  'amount'        => $t['amount'],
  'subtotal'      => $t['subtotal'],
  'discount'      => $t['discount'],
  'total'         => $t['total'],
  'thank_you'     => $t['thank_you'],
  'order_placed'  => $t['order_placed'],
]) ?>;

let order = [];

function fmt(n) { return sym + parseFloat(n).toFixed(2); }

function addToOrder(item) {
  const existing = order.find(i => i.id === item.id);
  if (existing) { existing.qty++; } else { order.push({ ...item, qty: 1 }); }
  renderOrder();
}

function changeQty(id, delta) {
  const idx = order.findIndex(i => i.id === id);
  if (idx === -1) return;
  order[idx].qty += delta;
  if (order[idx].qty <= 0) order.splice(idx, 1);
  renderOrder();
}

function clearOrder() {
  order = [];
  document.getElementById('discountPct').value = 0;
  document.getElementById('discountAmt').value = 0;
  document.getElementById('orderNotes').value  = '';
  renderOrder();
}

function renderOrder() {
  const list     = document.getElementById('orderList');
  const placeBtn = document.getElementById('placeBtn');

  if (order.length === 0) {
    list.innerHTML = `<div class="text-center py-4 text-muted" style="font-size:.85rem;">
      <i class="bi bi-basket" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>
      <?= $t['add_items'] ?>
    </div>`;
    placeBtn.disabled = true;
    recalc();
    return;
  }

  placeBtn.disabled = false;
  let html = '';
  order.forEach(item => {
    html += `<div class="order-row">
      <div style="flex:1;">
        <div class="order-row-name">${item.name}</div>
        <div class="order-row-sub">${fmt(item.price)} × ${item.qty} = ${fmt(item.price * item.qty)}</div>
      </div>
      <div class="qty-btn" onclick="changeQty(${item.id}, -1)">−</div>
      <div class="qty-display">${item.qty}</div>
      <div class="qty-btn" onclick="changeQty(${item.id}, 1)">+</div>
    </div>`;
  });
  list.innerHTML = html;
  recalc();
}

function recalc() {
  const subtotal   = order.reduce((sum, i) => sum + i.price * i.qty, 0);
  const pct        = Math.min(100, Math.max(0, parseFloat(document.getElementById('discountPct').value) || 0));
  const discountAmt = subtotal * pct / 100;
  document.getElementById('discountAmt').value = discountAmt.toFixed(2);
  const total = subtotal - discountAmt;
  document.getElementById('subtotalDisplay').textContent  = fmt(subtotal);
  document.getElementById('discountDisplay').textContent  = '-' + fmt(discountAmt);
  document.getElementById('totalDisplay').textContent     = fmt(total);
}

function recalcFromAmt() {
  const subtotal = order.reduce((sum, i) => sum + i.price * i.qty, 0);
  const discAmt  = Math.min(subtotal, Math.max(0, parseFloat(document.getElementById('discountAmt').value) || 0));
  const pct      = subtotal > 0 ? (discAmt / subtotal * 100) : 0;
  document.getElementById('discountPct').value = pct.toFixed(1);
  const total = subtotal - discAmt;
  document.getElementById('subtotalDisplay').textContent  = fmt(subtotal);
  document.getElementById('discountDisplay').textContent  = '-' + fmt(discAmt);
  document.getElementById('totalDisplay').textContent     = fmt(total);
}

function placeOrder() {
  if (!order.length) return;
  const subtotal    = order.reduce((sum, i) => sum + i.price * i.qty, 0);
  const discountAmt = parseFloat(document.getElementById('discountAmt').value) || 0;
  const total       = subtotal - discountAmt;
  const notes       = document.getElementById('orderNotes').value;

  const payload = {
    items: order.map(i => ({ id: i.id, name: i.name, price: i.price, qty: i.qty })),
    subtotal, discount: discountAmt, total, notes,
  };

  fetch(ordersUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'ajax_order=1&order_data=' + encodeURIComponent(JSON.stringify(payload)),
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast(tStr.order_placed);
      buildReceipt(res.order_number, payload);
      new bootstrap.Modal(document.getElementById('receiptModal')).show();
    }
  });
}

function buildReceipt(orderNumber, payload) {
  const now       = new Date();
  const dateStr   = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
  const dir       = isRtl ? 'rtl' : 'ltr';
  const aStart    = isRtl ? 'right' : 'left';
  const aEnd      = isRtl ? 'left'  : 'right';
  const logoHtml  = appLogo ? `<img src="${appLogo}" style="width:180px;height:180px;border-radius:10px;object-fit:cover;margin-bottom:10px;display:block;margin-left:auto;margin-right:auto;">` : '';

  let rows = payload.items.map(i => `<tr>
    <td style="padding:4px 0;text-align:${aStart};">${i.name}</td>
    <td style="text-align:center;padding:4px 6px;">${i.qty}</td>
    <td style="text-align:${aEnd};padding:4px 0;">${fmt(i.price)}</td>
    <td style="text-align:${aEnd};padding:4px 0;">${fmt(i.price * i.qty)}</td>
  </tr>`).join('');

  document.getElementById('receiptContent').innerHTML = `
    <div dir="${dir}" style="font-family:${isRtl ? 'Tajawal,Arial' : 'inherit'};">
      <div style="text-align:center;margin-bottom:16px;">${logoHtml}
        <div style="font-size:1.1rem;font-weight:700;">${appName}</div>
        <div style="font-size:.8rem;color:#999;">${tStr.receipt_title}</div>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#666;margin-bottom:14px;flex-direction:${isRtl?'row-reverse':'row'};">
        <span>${tStr.order_no}: <strong>${orderNumber}</strong></span>
        <span>${dateStr}</span>
      </div>
      <table style="width:100%;border-collapse:collapse;font-size:.82rem;border-top:1px dashed #ccc;border-bottom:1px dashed #ccc;margin-bottom:12px;" dir="${dir}">
        <thead><tr style="color:#999;">
          <th style="text-align:${aStart};padding:6px 0;font-weight:500;">${tStr.item}</th>
          <th style="text-align:center;padding:6px 6px;font-weight:500;">${tStr.qty}</th>
          <th style="text-align:${aEnd};padding:6px 0;font-weight:500;">${tStr.price}</th>
          <th style="text-align:${aEnd};padding:6px 0;font-weight:500;">${tStr.amount}</th>
        </tr></thead>
        <tbody>${rows}</tbody>
      </table>
      <div style="font-size:.85rem;" dir="${dir}">
        <div style="display:flex;justify-content:space-between;padding:3px 0;color:#666;flex-direction:${isRtl?'row-reverse':'row'};"><span>${tStr.subtotal}</span><span>${fmt(payload.subtotal)}</span></div>
        <div style="display:flex;justify-content:space-between;padding:3px 0;color:#e53935;flex-direction:${isRtl?'row-reverse':'row'};"><span>- ${tStr.discount}</span><span>-${fmt(payload.discount)}</span></div>
        <div style="display:flex;justify-content:space-between;padding:8px 0 0;font-size:1rem;font-weight:700;border-top:1px solid #eee;margin-top:6px;flex-direction:${isRtl?'row-reverse':'row'};"><span>${tStr.total}</span><span>${fmt(payload.total)}</span></div>
      </div>
      <div style="text-align:center;margin-top:20px;font-size:.78rem;color:#aaa;">${tStr.thank_you}</div>
    </div>`;
}

function printReceipt() {
  const content = document.getElementById('receiptContent').innerHTML;
  const dir = isRtl ? 'rtl' : 'ltr';
  const gFont = isRtl ? '<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">' : '';
  const fontFamily = isRtl ? "'Tajawal',Arial,sans-serif" : "'Courier New',monospace";
  const w = window.open('', '_blank', 'width=420,height=650');
  w.document.write(`<!DOCTYPE html><html lang="${lang}" dir="${dir}"><head><meta charset="UTF-8"><title>Receipt</title>${gFont}
    <style>body{font-family:${fontFamily};padding:20px;max-width:380px;margin:0 auto;direction:${dir};}@media print{body{padding:0;}}img{max-width:100%;}</style>
  </head><body>${content}<script>window.onload=()=>{window.print();window.close();}<\/script></body></html>`);
  w.document.close();
}

function startNewOrder() { clearOrder(); }

function filterItems() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  document.querySelectorAll('.menu-item-card').forEach(card => {
    card.style.display = card.dataset.name.includes(q) ? '' : 'none';
  });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
