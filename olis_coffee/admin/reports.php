<?php
// admin/reports.php - Reports Page
session_start();
require_once '../includes/Auth.php';
require_once '../includes/db.php';

Auth::requireAdmin('../login.php');

$db = getDB();

// Most ordered menu items grouped by subcategory (category)
$popularItems = $db->query("
    SELECT m.subcategory,
           m.name AS item_name,
           m.price,
           SUM(oi.quantity) AS total_ordered
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    GROUP BY m.id, m.subcategory, m.name, m.price
    ORDER BY m.subcategory ASC, total_ordered DESC
");
$itemsByCategory = [];
$allItems = [];
if ($popularItems) {
    while ($row = $popularItems->fetch_assoc()) {
        $itemsByCategory[$row['subcategory']][] = $row;
        $allItems[] = $row;
    }
}
// Sort allItems by total_ordered descending for the "All" tab (top 10 only)
usort($allItems, fn($a, $b) => $b['total_ordered'] - $a['total_ordered']);
$allItems = array_slice($allItems, 0, 10);
$categories = array_keys($itemsByCategory);

// Daily Summary — single UNION query so reservation-only dates always appear
$dailyResult = $db->query("
    SELECT
        all_dates.summary_date,
        COALESCE(SUM(ord.subtotal), 0)           AS total_revenue,
        COUNT(DISTINCT ord.order_id)              AS total_orders,
        COALESCE(MAX(res.total_reservations), 0)  AS total_reservations,
        COALESCE(MAX(res.total_guests), 0)        AS total_guests
    FROM (
        SELECT DATE(o.created_at) AS summary_date FROM orders o
        UNION
        SELECT res_date AS summary_date FROM reservations WHERE status != 'cancelled'
    ) AS all_dates
    LEFT JOIN (
        SELECT oi.order_id, oi.subtotal, DATE(o.created_at) AS order_date
        FROM order_items oi
        JOIN orders o ON o.id = oi.order_id
    ) AS ord ON ord.order_date = all_dates.summary_date
    LEFT JOIN (
        SELECT res_date,
               COUNT(*) AS total_reservations,
               SUM(pax) AS total_guests
        FROM reservations
        WHERE status != 'cancelled'
        GROUP BY res_date
    ) AS res ON res.res_date = all_dates.summary_date
    GROUP BY all_dates.summary_date
    ORDER BY all_dates.summary_date DESC
    LIMIT 30
");

$dailyData = [];
if ($dailyResult) {
    while ($row = $dailyResult->fetch_assoc()) {
        $dailyData[] = $row;
    }
}

// Order Transactions Report (unchanged)
$orderReport = $db->query("
    SELECT o.id AS order_id,
           u.name AS customer_name,
           u.email,
           m.name AS item_name,
           m.subcategory,
           oi.quantity,
           oi.unit_price,
           oi.subtotal,
           o.status,
           o.payment_method,
           o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON oi.order_id = o.id
    JOIN menu_items m ON oi.menu_item_id = m.id
    ORDER BY o.created_at DESC
    LIMIT 20
");

$adminName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reports – Oli's Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <img src="../assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Admin Panel <span class="sub">Reports</span></div>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-white-50 small"><i class="bi bi-person-badge me-1"></i><?= $adminName ?></span>
      <a href="../logout.php" class="btn-logout nav-link"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
  </div>
</nav>

<div class="d-flex">
  <aside class="admin-sidebar py-3">
    <nav class="nav flex-column">
      <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link" href="menu_manage.php"><i class="bi bi-journal-text me-2"></i>Menu Management</a>
      <a class="nav-link" href="manage_reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
      <a class="nav-link active" href="reports.php"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>
      <hr style="border-color:rgba(255,255,255,0.1); margin: 8px 16px;">
      <a class="nav-link" href="../index.php"><i class="bi bi-shop me-2"></i>View Customer Side</a>
      <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
  </aside>

  <main class="admin-content">

    <h4 class="mb-1" style="font-family:'Playfair Display',serif; color:var(--green-dark);">Reports</h4>
    <p class="text-muted mb-4 small">Displays most ordered items in the menu and transaction history of customers.</p>

    <!-- REPORT 2: Most Ordered Items by Category -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header d-flex align-items-center justify-content-between" style="background:var(--green-dark); color:white;">
        <span><i class="bi bi-trophy me-2"></i>Most Ordered Items by Category</span>
      </div>

      <?php if (!empty($itemsByCategory)): ?>

      <!-- Category Filter Tabs -->
      <div style="background:#f8faf7; border-bottom: 1px solid #e8ede6; padding: 10px 16px; overflow-x:auto; white-space:nowrap;">
        <button
          class="category-filter-btn active"
          onclick="filterCategory('all', this)"
          style="display:inline-block; margin-right:6px; padding:5px 14px; border-radius:20px; border:none; font-size:0.78rem; font-weight:600; cursor:pointer; background:var(--green-dark); color:white; transition:all 0.2s;">
          All
        </button>
        <?php foreach ($categories as $cat): ?>
        <button
          class="category-filter-btn"
          onclick="filterCategory('<?= htmlspecialchars(addslashes($cat)) ?>', this)"
          style="display:inline-block; margin-right:6px; padding:5px 14px; border-radius:20px; border: 1.5px solid var(--green-dark); font-size:0.78rem; font-weight:600; cursor:pointer; background:white; color:var(--green-dark); transition:all 0.2s;">
          <?= htmlspecialchars($cat) ?>
        </button>
        <?php endforeach; ?>
      </div>

      <div class="card-body p-0">

        <!-- ALL tab: flat sorted list of every item -->
        <div id="cat-panel-all" class="cat-panel">
          <table class="table table-olis mb-0">
            <thead>
              <tr>
                <th style="width:40px;">#</th>
                <th>Item</th>
                <th>Category</th>
                <th>Price</th>
                <th>Times Ordered</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($allItems as $rank => $item): ?>
              <tr>
                <td>
                  <?php if ($rank === 0): ?>
                    <span style="font-size:1rem;">🥇</span>
                  <?php elseif ($rank === 1): ?>
                    <span style="font-size:1rem;">🥈</span>
                  <?php elseif ($rank === 2): ?>
                    <span style="font-size:1rem;">🥉</span>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:0.8rem;"><?= $rank + 1 ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><span style="font-size:0.75rem; background:#f0f4ef; color:var(--green-dark); border-radius:10px; padding:2px 8px; font-weight:600;"><?= htmlspecialchars($item['subcategory']) ?></span></td>
                <td>₱<?= number_format($item['price'], 2) ?></td>
                <td><span class="badge-available"><?= $item['total_ordered'] ?> orders</span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Per-category panels -->
        <?php foreach ($itemsByCategory as $category => $items): ?>
        <div id="cat-panel-<?= htmlspecialchars(preg_replace('/\s+/', '-', strtolower($category))) ?>" class="cat-panel" style="display:none;">
          <table class="table table-olis mb-0">
            <thead>
              <tr>
                <th style="width:40px;">#</th>
                <th>Item</th>
                <th>Price</th>
                <th>Times Ordered</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $rank => $item): ?>
              <tr>
                <td>
                  <?php if ($rank === 0): ?>
                    <span style="font-size:1rem;">🥇</span>
                  <?php elseif ($rank === 1): ?>
                    <span style="font-size:1rem;">🥈</span>
                  <?php elseif ($rank === 2): ?>
                    <span style="font-size:1rem;">🥉</span>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:0.8rem;"><?= $rank + 1 ?></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td>₱<?= number_format($item['price'], 2) ?></td>
                <td><span class="badge-available"><?= $item['total_ordered'] ?> orders</span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endforeach; ?>

      </div>

      <?php else: ?>
      <div class="card-body">
        <div class="text-center text-muted py-4">
          <i class="bi bi-inbox me-2"></i>No order data yet. Popular items will appear here once orders are placed.
        </div>
      </div>
      <?php endif; ?>

    </div>

    <script>
    function filterCategory(cat, btn) {
      // Hide all panels
      document.querySelectorAll('.cat-panel').forEach(p => p.style.display = 'none');

      // Show target panel
      if (cat === 'all') {
        document.getElementById('cat-panel-all').style.display = '';
      } else {
        const slug = cat.toLowerCase().replace(/\s+/g, '-');
        const panel = document.getElementById('cat-panel-' + slug);
        if (panel) panel.style.display = '';
      }

      // Update button styles
      document.querySelectorAll('.category-filter-btn').forEach(b => {
        b.style.background = 'white';
        b.style.color = 'var(--green-dark)';
        b.style.border = '1.5px solid var(--green-dark)';
      });
      btn.style.background = 'var(--green-dark)';
      btn.style.color = 'white';
      btn.style.border = '1.5px solid var(--green-dark)';
    }
    </script>

    <!-- REPORT 3: Order Transactions (unchanged) -->
    <div class="card border-0 shadow-sm">
      <div class="card-header" style="background:var(--green-dark); color:white;">
        <i class="bi bi-receipt me-2"></i>Order Transactions Report
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-olis mb-0">
            <thead>
              <tr>
                <th>Order#</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Item</th>
                <th>Subcategory</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($orderReport && $orderReport->num_rows > 0): ?>
                <?php while ($row = $orderReport->fetch_assoc()): ?>
                <tr>
                  <td>#<?= $row['order_id'] ?></td>
                  <td><?= htmlspecialchars($row['customer_name']) ?></td>
                  <td class="small text-muted"><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['item_name']) ?></td>
                  <td><?= htmlspecialchars($row['subcategory']) ?></td>
                  <td><?= $row['quantity'] ?></td>
                  <td>₱<?= number_format($row['unit_price'], 2) ?></td>
                  <td>₱<?= number_format($row['subtotal'], 2) ?></td>
                  <td><span class="badge-available"><?= ucfirst($row['status']) ?></span></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="9" class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-2"></i>No orders yet. Data will appear once orders are placed.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/script.js"></script>
</body>
</html>