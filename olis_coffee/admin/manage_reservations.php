<?php
// admin/manage_reservations.php - Manage Reservations
session_start();
require_once '../includes/Auth.php';
require_once '../includes/db.php';

Auth::requireAdmin('../login.php');

$db = getDB();
$message = '';
$msgType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = intval($_POST['id'] ?? 0);
    $status    = $_POST['status'] ?? '';
    $payStatus = $_POST['payment_status'] ?? '';

    $allowed = ['pending','confirmed','cancelled'];
    if ($id > 0 && in_array($status, $allowed)) {
        $upd = $db->prepare("UPDATE reservations SET status=?, payment_status=? WHERE id=?");
        $upd->bind_param("ssi", $status, $payStatus, $id);
        if ($upd->execute()) {
            $message = 'Reservation #' . $id . ' updated successfully!';
        } else {
            $message = 'Error updating reservation.';
            $msgType = 'danger';
        }
    }
}

// Fetch all reservations with user info
$result = $db->query("
    SELECT r.*, u.name AS customer_name, u.email
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.res_date ASC, r.res_time ASC
");
$reservations = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Stats
$total     = count($reservations);
$pending   = count(array_filter($reservations, fn($r) => $r['status'] === 'pending'));
$confirmed = count(array_filter($reservations, fn($r) => $r['status'] === 'confirmed'));
$totalPax  = array_sum(array_column(array_filter($reservations, fn($r) => $r['status'] !== 'cancelled'), 'pax'));

$adminName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservations – Oli's Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <img src="../assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Admin Panel <span class="sub">Reservations</span></div>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="text-white-50 small"><i class="bi bi-person-badge me-1"></i><?= $adminName ?></span>
      <a href="../logout.php" class="btn-logout nav-link"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
  </div>
</nav>

<div class="d-flex">
  <!-- SIDEBAR -->
  <aside class="admin-sidebar py-3">
    <nav class="nav flex-column">
      <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link" href="menu_manage.php"><i class="bi bi-journal-text me-2"></i>Menu Management</a>
      <a class="nav-link active" href="manage_reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
      <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>
      <hr style="border-color:rgba(255,255,255,0.1); margin: 8px 16px;">
      <a class="nav-link" href="../index.php"><i class="bi bi-shop me-2"></i>View Customer Side</a>
      <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
  </aside>

  <main class="admin-content">
    <h4 class="mb-1" style="font-family:'Playfair Display',serif; color:var(--green-dark);">Reservation Management</h4>
    <p class="text-muted mb-4 small">Approve, confirm, or cancel customer seat reservations.</p>

    <?php if ($message): ?>
      <div class="alert alert-<?= $msgType ?> alert-olis alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- STATS -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card">
          <h3><?= $total ?></h3>
          <p><i class="bi bi-calendar3 me-1"></i>Total Reservations</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="border-color: #f59e0b;">
          <h3><?= $pending ?></h3>
          <p><i class="bi bi-hourglass-split me-1"></i>Pending Approval</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="border-color: var(--green-mid);">
          <h3><?= $confirmed ?></h3>
          <p><i class="bi bi-check-circle me-1"></i>Confirmed</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card" style="border-color: var(--brown);">
          <h3><?= $totalPax ?></h3>
          <p><i class="bi bi-people me-1"></i>Total Guests</p>
        </div>
      </div>
    </div>

    <!-- RESERVATIONS TABLE -->
    <div class="card border-0 shadow-sm">
      <div class="card-header" style="background:var(--green-dark); color:white;">
        <i class="bi bi-table me-2"></i>All Reservations (<?= $total ?>)
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-olis mb-0">
            <thead>
              <tr>
                <th>#</th><th>Customer</th><th>Date</th><th>Time</th><th>Pax</th>
                <th>Phone</th><th>Payment</th><th>Status</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($reservations)): ?>
                <tr>
                  <td colspan="9" class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                    No reservations yet.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($reservations as $res): ?>
                <tr>
                  <td>#<?= $res['id'] ?></td>
                  <td>
                    <strong><?= htmlspecialchars($res['customer_name']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($res['email']) ?></small>
                  </td>
                  <td><?= date('M d, Y', strtotime($res['res_date'])) ?></td>
                  <td><?= date('h:i A', strtotime($res['res_time'])) ?></td>
                  <td><?= $res['pax'] ?></td>
                  <td><?= htmlspecialchars($res['phone']) ?></td>
                  <td>
                    <span class="<?= $res['payment_status'] === 'paid' ? 'badge-available' : 'badge-unavailable' ?>">
                      <?= ucfirst($res['payment_method']) ?> · <?= ucfirst($res['payment_status']) ?>
                    </span>
                  </td>
                  <td>
                    <?php
                      $cls = match($res['status']) {
                        'confirmed' => 'badge bg-success',
                        'cancelled' => 'badge bg-danger',
                        default     => 'badge bg-warning text-dark'
                      };
                    ?>
                    <span class="<?= $cls ?>" style="font-size:0.72rem;"><?= ucfirst($res['status']) ?></span>
                  </td>
                  <td>
                    <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:0.73rem;"
                            data-bs-toggle="modal" data-bs-target="#editModal"
                            data-id="<?= $res['id'] ?>"
                            data-status="<?= $res['status'] ?>"
                            data-pay="<?= $res['payment_status'] ?>"
                            data-name="<?= htmlspecialchars($res['customer_name']) ?>">
                      <i class="bi bi-pencil"></i> Update
                    </button>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header" style="background:var(--green-dark); color:white;">
        <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Update Reservation</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body p-4">
          <p class="mb-3 text-muted" id="modalCustomer"></p>
          <input type="hidden" name="id" id="modalId">

          <div class="mb-3">
            <label class="form-label">Reservation Status</label>
            <select name="status" id="modalStatus" class="form-select">
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Status</label>
            <select name="payment_status" id="modalPay" class="form-select">
              <option value="unpaid">Unpaid</option>
              <option value="paid">Paid</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn" style="background:var(--green-dark);color:white;">
            <i class="bi bi-save me-1"></i>Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/script.js"></script>
<script>
document.getElementById('editModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('modalId').value      = btn.dataset.id;
  document.getElementById('modalStatus').value  = btn.dataset.status;
  document.getElementById('modalPay').value     = btn.dataset.pay;
  document.getElementById('modalCustomer').textContent = 'Customer: ' + btn.dataset.name + ' · Reservation #' + btn.dataset.id;
});

</script>
</body>
</html>