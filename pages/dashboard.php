<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$settings = getSettings($pdo);
$lang = in_array(trim($settings['language'] ?? ''), ['en','ar']) ? trim($settings['language']) : 'en';
$sym  = $settings['currency_symbol'] ?? '$';
$B    = BASE_URL;

// Stats — SQLite uses date('now') and strftime
$totalOrders  = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders")->fetchColumn();
$totalItems   = $pdo->query("SELECT COUNT(*) FROM items WHERE active=1")->fetchColumn();
$todayRevenue = $pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE date(created_at)=date('now')")->fetchColumn();
$todayOrders  = $pdo->query("SELECT COUNT(*) FROM orders WHERE date(created_at)=date('now')")->fetchColumn();

// Last 7 days chart — SQLite date arithmetic
$chartStmt = $pdo->query("
    SELECT date(created_at) as d,
           COALESCE(SUM(total),0) as rev,
           COUNT(*) as cnt
    FROM orders
    WHERE date(created_at) >= date('now','-6 days')
    GROUP BY date(created_at)
    ORDER BY d ASC
");
$chartRows = $chartStmt->fetchAll();
$dateMap   = [];
foreach ($chartRows as $r) { $dateMap[$r['d']] = $r; }
$chartLabels = []; $chartRevenue = []; $chartCounts = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[]  = date('d/m', strtotime($d));
    $chartRevenue[] = isset($dateMap[$d]) ? round($dateMap[$d]['rev'], 2) : 0;
    $chartCounts[]  = isset($dateMap[$d]) ? (int)$dateMap[$d]['cnt'] : 0;
}

// Recent orders
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Top items
$topItems = $pdo->query("
    SELECT oi.item_name,
           SUM(oi.quantity) as total_qty,
           SUM(oi.quantity * oi.item_price) as revenue
    FROM order_items oi
    GROUP BY oi.item_name
    ORDER BY total_qty DESC
    LIMIT 5
")->fetchAll();

$translations_d = [
  'en' => [
    'title'        => 'Dashboard',
    'today_rev'    => "Today's Revenue",
    'today_orders' => "Today's Orders",
    'total_rev'    => 'Total Revenue',
    'total_orders' => 'Total Orders',
    'active_items' => 'Active Items',
    'recent'       => 'Recent Orders',
    'top_items'    => 'Top Selling Items',
    'order_no'     => 'Order #',
    'date'         => 'Date',
    'total'        => 'Total',
    'item'         => 'Item',
    'qty'          => 'Sold',
    'revenue'      => 'Revenue',
    'last7'        => 'Last 7 Days',
    'no_orders'    => 'No orders yet',
    'new_order'    => 'New Order →',
  ],
  'ar' => [
    'title'        => 'لوحة التحكم',
    'today_rev'    => 'إيرادات اليوم',
    'today_orders' => 'طلبات اليوم',
    'total_rev'    => 'إجمالي الإيرادات',
    'total_orders' => 'إجمالي الطلبات',
    'active_items' => 'العناصر النشطة',
    'recent'       => 'آخر الطلبات',
    'top_items'    => 'الأكثر مبيعاً',
    'order_no'     => 'رقم الطلب',
    'date'         => 'التاريخ',
    'total'        => 'الإجمالي',
    'item'         => 'الصنف',
    'qty'          => 'المُباع',
    'revenue'      => 'الإيراد',
    'last7'        => 'آخر 7 أيام',
    'no_orders'    => 'لا طلبات بعد',
    'new_order'    => '← طلب جديد',
  ],
];
$t = $translations_d[$lang] ?? $translations_d['en'];

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <h1 class="page-title"><?= $t['title'] ?></h1>
  <a href="<?= $B ?>/pages/orders.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>
    <?= $lang === 'ar' ? 'طلب جديد' : 'New Order' ?>
  </a>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <?php
  $stats = [
    ['icon'=>'bi-sun',           'label'=>$t['today_rev'],    'value'=> $sym . number_format($todayRevenue, 2),  'color'=>'#C8860A', 'bg'=>'#fff8ee'],
    ['icon'=>'bi-receipt',       'label'=>$t['today_orders'], 'value'=> $todayOrders,                             'color'=>'#2563eb', 'bg'=>'#eff6ff'],
    ['icon'=>'bi-graph-up-arrow','label'=>$t['total_rev'],    'value'=> $sym . number_format($totalRevenue, 2),  'color'=>'#16a34a', 'bg'=>'#f0fdf4'],
    ['icon'=>'bi-bag-check',     'label'=>$t['total_orders'], 'value'=> $totalOrders,                             'color'=>'#7c3aed', 'bg'=>'#f5f3ff'],
    ['icon'=>'bi-grid',          'label'=>$t['active_items'], 'value'=> $totalItems,                              'color'=>'#0891b2', 'bg'=>'#ecfeff'],
  ];
  foreach ($stats as $s): ?>
  <div class="col-6 col-lg">
    <div class="card h-100">
      <div class="card-body" style="padding:18px 20px;">
        <div style="width:40px;height:40px;border-radius:10px;background:<?= $s['bg'] ?>;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
          <i class="<?= $s['icon'] ?>" style="color:<?= $s['color'] ?>;font-size:1.1rem;"></i>
        </div>
        <div style="font-size:.75rem;color:#999;font-weight:500;margin-bottom:4px;"><?= $s['label'] ?></div>
        <div style="font-size:1.4rem;font-weight:700;color:#1a1a1a;"><?= $s['value'] ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Chart + Top Items -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header"><?= $t['last7'] ?></div>
      <div class="card-body">
        <div style="position:relative;height:240px;">
          <canvas id="revenueChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header"><?= $t['top_items'] ?></div>
      <div class="card-body p-0">
        <?php if (empty($topItems)): ?>
          <div class="text-center py-4 text-muted" style="font-size:.85rem;"><?= $t['no_orders'] ?></div>
        <?php else: ?>
          <?php foreach ($topItems as $i => $item): ?>
          <div style="display:flex;align-items:center;gap:12px;padding:12px 18px;border-bottom:1px solid #f5f4f0;">
            <div style="width:26px;height:26px;border-radius:50%;background:#f5f4f0;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:700;color:#999;flex-shrink:0;"><?= $i+1 ?></div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.84rem;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= sanitize($item['item_name']) ?></div>
              <div style="font-size:.75rem;color:#aaa;"><?= $t['qty'] ?>: <?= $item['total_qty'] ?></div>
            </div>
            <div style="font-size:.84rem;font-weight:600;color:var(--primary);flex-shrink:0;"><?= $sym . number_format($item['revenue'], 2) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Recent Orders -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span><?= $t['recent'] ?></span>
    <a href="<?= $B ?>/pages/history.php" style="font-size:.8rem;color:var(--primary);text-decoration:none;">
      <?= $lang === 'ar' ? 'عرض الكل' : 'View all' ?> →
    </a>
  </div>
  <div class="card-body p-0">
    <?php if (empty($recentOrders)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-receipt" style="font-size:2.5rem;opacity:.25;display:block;margin-bottom:10px;"></i>
        <p style="font-size:.875rem;margin-bottom:16px;"><?= $t['no_orders'] ?></p>
        <a href="<?= $B ?>/pages/orders.php" class="btn btn-primary btn-sm"><?= $t['new_order'] ?></a>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:#faf9f7;">
          <tr>
            <th style="padding-<?= $lang==='ar'?'right':'left' ?>:22px;"><?= $t['order_no'] ?></th>
            <th><?= $t['date'] ?></th>
            <th><?= $lang==='ar'?'المجموع':'Subtotal' ?></th>
            <th><?= $lang==='ar'?'الخصم':'Discount' ?></th>
            <th><?= $t['total'] ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $o): ?>
          <tr onclick="window.location='<?= $B ?>/pages/history.php'" style="cursor:pointer;">
            <td style="padding-<?= $lang==='ar'?'right':'left' ?>:22px;">
              <span class="badge" style="background:#f0ede8;color:#666;font-weight:500;font-size:.78rem;"><?= sanitize($o['order_number']) ?></span>
            </td>
            <td style="font-size:.83rem;color:#888;"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
            <td style="font-size:.85rem;"><?= $sym . number_format($o['subtotal'], 2) ?></td>
            <td style="font-size:.85rem;color:#e53935;">-<?= $sym . number_format($o['discount'], 2) ?></td>
            <td><strong><?= $sym . number_format($o['total'], 2) ?></strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels  = <?= json_encode($chartLabels) ?>;
const revenue = <?= json_encode($chartRevenue) ?>;
const counts  = <?= json_encode($chartCounts) ?>;
const sym     = <?= json_encode($sym) ?>;

new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      {
        label: <?= json_encode($lang==='ar'?'الإيرادات':'Revenue') ?>,
        data: revenue,
        backgroundColor: 'rgba(200,134,10,0.15)',
        borderColor: '#C8860A',
        borderWidth: 2,
        borderRadius: 6,
        yAxisID: 'y',
      },
      {
        label: <?= json_encode($lang==='ar'?'الطلبات':'Orders') ?>,
        data: counts,
        type: 'line',
        borderColor: '#2563eb',
        backgroundColor: 'transparent',
        borderWidth: 2,
        pointBackgroundColor: '#2563eb',
        pointRadius: 4,
        tension: 0.4,
        yAxisID: 'y1',
      }
    ]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => ctx.datasetIndex === 0
            ? sym + ctx.parsed.y.toFixed(2)
            : ctx.parsed.y + ' orders'
        }
      }
    },
    scales: {
      x: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#999', font: { size: 11 } } },
      y: {
        position: 'left',
        grid: { color: 'rgba(0,0,0,0.05)' },
        ticks: { color: '#999', font: { size: 11 }, callback: v => sym + v }
      },
      y1: {
        position: 'right',
        grid: { drawOnChartArea: false },
        ticks: { color: '#2563eb', font: { size: 11 } }
      }
    }
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
