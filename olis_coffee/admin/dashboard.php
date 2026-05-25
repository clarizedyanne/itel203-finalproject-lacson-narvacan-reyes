<?php
// admin/dashboard.php
session_start();
require_once '../includes/Auth.php';
require_once '../includes/db.php';

Auth::requireAdmin('../login.php');

$db = getDB();

// Stats
$totalMenuItems = $db->query("SELECT COUNT(*) as c FROM menu_items")->fetch_assoc()['c'];
$totalUsers     = $db->query("SELECT COUNT(*) as c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$availableItems = $db->query("SELECT COUNT(*) as c FROM menu_items WHERE is_available=1")->fetch_assoc()['c'];

// Stats per category
$catStats = $db->query("SELECT category, COUNT(*) as cnt FROM menu_items GROUP BY category ORDER BY category");

// JOIN Query: orders with customer info
$joinResult = $db->query("
    SELECT o.id, u.name AS customer, u.email,
           COUNT(oi.id) AS total_items,
           o.total_amount, o.status, o.payment_method, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT 10
");

$adminName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard – Oli's</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <img src="../assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Admin Panel <span class="sub">Dashboard</span></div>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-white-50 small"><i class="bi bi-person-badge me-1"></i><?= $adminName ?></span>
    </div>
  </div>
</nav>

<div class="d-flex">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar py-3">
    <nav class="nav flex-column">
      <a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link" href="menu_manage.php"><i class="bi bi-journal-text me-2"></i>Menu Management</a>
      <a class="nav-link" href="manage_reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
      <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>
      <hr style="border-color:rgba(255,255,255,0.1); margin: 8px 16px;">
      <a class="nav-link" href="../index.php"><i class="bi bi-shop me-2"></i>View Customer Side</a>
      <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
  </aside>

  <!-- CONTENT -->
  <main class="admin-content">
    <h4 class="mb-1" style="font-family:'Playfair Display',serif; color:var(--green-dark);">
      Dashboard Overview
    </h4>
    <p class="text-muted mb-4 small">Welcome back, <?= $adminName ?>!</p>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="stat-card">
          <h3><?= $totalMenuItems ?></h3>
          <p><i class="bi bi-journal-text me-1"></i>Total Menu Items</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card" style="border-color: var(--gold);">
          <h3><?= $availableItems ?></h3>
          <p><i class="bi bi-check-circle me-1"></i>Available Items</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card" style="border-color: var(--brown);">
          <h3><?= $totalUsers ?></h3>
          <p><i class="bi bi-people me-1"></i>Registered Customers</p>
        </div>
      </div>
    </div>

    <!-- ITEMS BY CATEGORY -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header" style="background:var(--green-dark); color:white;">
        <i class="bi bi-pie-chart me-2"></i>Menu Items by Category
      </div>
      <div class="card-body p-3">
        <div class="row g-2">
          <?php
          $catColors = [
            'Main'    => 'var(--green-mid)',
            'Snacks'  => 'var(--gold)',
            'Pasta'   => '#e67e22',
            'Burgers' => '#c0392b',
            'Salads'  => '#27ae60',
            'Pizza'   => '#8e44ad',
            'Drinks'  => '#2980b9',
          ];
          while ($row = $catStats->fetch_assoc()):
            $color = $catColors[$row['category']] ?? 'var(--text-muted)';
          ?>
          <div class="col-6 col-md-3">
            <div style="background:white; border-radius:10px; padding:12px 16px;
                        border-left: 4px solid <?= $color ?>; box-shadow: 0 2px 8px rgba(0,0,0,0.07);">
              <div style="font-size:1.4rem; font-weight:700; color:var(--green-dark);"><?= $row['cnt'] ?></div>
              <div style="font-size:0.78rem; color:var(--text-muted);"><?= htmlspecialchars($row['category']) ?></div>
            </div>
          </div>
          <?php endwhile; ?>
        </div>
        <div class="mt-3">
          <a href="menu_manage.php" class="btn btn-sm" style="background:var(--green-dark); color:white; border-radius:8px;">
            <i class="bi bi-journal-text me-1"></i>Manage Menu Items
          </a>
          <a href="reports.php" class="ms-2 btn btn-sm btn-outline-secondary" style="border-radius:8px;">
            <i class="bi bi-bar-chart-line me-1"></i>View Reports
          </a>
        </div>
      </div>
    </div>

    <!-- JOIN QUERY RESULTS -->
    <div class="card border-0 shadow-sm">
      <div class="card-header" style="background:var(--green-dark); color:white;">
        <i class="bi bi-link-45deg me-2"></i>Recent Orders
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-olis mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($joinResult && $joinResult->num_rows > 0): ?>
                <?php while ($row = $joinResult->fetch_assoc()): ?>
                <tr>
                  <td>#<?= $row['id'] ?></td>
                  <td><?= htmlspecialchars($row['customer']) ?></td>
                  <td class="text-muted small"><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= $row['total_items'] ?></td>
                  <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                  <td><span class="badge-available"><?= ucfirst($row['status']) ?></span></td>
                  <td><?= ucfirst($row['payment_method']) ?></td>
                  <td class="text-muted small"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">
                    <i class="bi bi-inbox me-2"></i>No orders yet.
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
