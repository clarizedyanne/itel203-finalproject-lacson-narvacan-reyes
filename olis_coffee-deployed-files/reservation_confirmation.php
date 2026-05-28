<?php
// reservation_confirmation.php - Reservation Only Success Page
session_start();
require_once 'includes/Auth.php';
Auth::requireLogin();

// Must arrive from reservation flow
if (empty($_SESSION['reservation_success'])) {
    header("Location: book_reservation.php");
    exit();
}

$data    = $_SESSION['reservation_success'];
$resId   = $data['reservation_id'];
$resDate = $data['res_date'];
$resTime = $data['res_time'];
$pax     = $data['pax'];
$payMethod = $data['payment_method'];

unset($_SESSION['reservation_success']);
$userName = htmlspecialchars($_SESSION['user_name']);

// Send reservation confirmation email
require_once 'includes/db.php';
require_once 'includes/mailer.php';
$db = getDB();
$userStmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$userRow = $userStmt->get_result()->fetch_assoc();
if ($userRow) {
    sendReservationEmail(
        ['name' => $userRow['name'], 'email' => $userRow['email']],
        ['id' => $resId, 'res_date' => $resDate, 'res_time' => $resTime,
         'pax' => $pax, 'payment_method' => $payMethod]
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="assets/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservation Confirmed – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .confirm-card {
      max-width: 520px;
      margin: 3rem auto;
      background: white;
      border-radius: 20px;
      box-shadow: 0 8px 40px rgba(45,74,30,0.13);
      overflow: hidden;
    }
    .confirm-header {
      background: linear-gradient(135deg, var(--green-dark), #1a3510);
      padding: 2.5rem 2rem 2rem;
      text-align: center;
      color: white;
    }
    .confirm-icon {
      font-size: 4rem;
      margin-bottom: 0.5rem;
      animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes popIn {
      0% { transform: scale(0); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
    .confirm-body { padding: 2rem; }
    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.88rem;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label { color: var(--text-muted); }
    .detail-value { font-weight: 700; color: var(--green-dark); }
    .action-btn {
      display: block;
      width: 100%;
      background: var(--green-dark);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 0.75rem;
      font-weight: 700;
      text-align: center;
      text-decoration: none;
      transition: opacity 0.2s;
      margin-bottom: 10px;
    }
    .action-btn:hover { opacity: 0.88; color: white; }
    .action-btn.outline {
      background: transparent;
      border: 2px solid var(--green-dark);
      color: var(--green-dark);
    }
    .action-btn.outline:hover { background: var(--green-dark); color: white; }
  </style>
</head>
<body style="background:#f8fdf5;">

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Oli's SelfieTea & Coffee <span class="sub">· Est. 2019</span></div>
    </a>
    <?php if (Auth::isAdmin()): ?>
    <div class="ms-auto">
      <a href="admin/dashboard.php"
         style="background:var(--gold);color:var(--green-dark);border-radius:20px;padding:5px 14px;font-weight:700;font-size:0.82rem;text-decoration:none;">
        <i class="bi bi-speedometer2 me-1"></i>Admin Panel
      </a>
    </div>
    <?php endif; ?>
  </div>
</nav>

<div class="container py-4">
  <div class="confirm-card">
    <div class="confirm-header">
      <div class="confirm-icon">🎉</div>
      <h3 style="font-family:'Playfair Display',serif; font-weight:700; margin-bottom:4px;">
        Reservation Received!
      </h3>
      <p style="opacity:0.8; font-size:0.9rem; margin:0;">
        Your seat has been reserved. See you soon!
      </p>
    </div>

    <div class="confirm-body">
      <p style="font-size:0.88rem; color:var(--text-muted); margin-bottom:1.2rem; text-align:center;">
        Hi <strong><?= $userName ?></strong>! Here's your reservation summary:
      </p>

      <div>
        <div class="detail-row">
          <span class="detail-label"><i class="bi bi-hash me-1"></i>Reservation #</span>
          <span class="detail-value"><?= str_pad($resId, 5, '0', STR_PAD_LEFT) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="bi bi-calendar3 me-1"></i>Visit Date</span>
          <span class="detail-value"><?= date('F j, Y', strtotime($resDate)) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="bi bi-clock me-1"></i>Visit Time</span>
          <span class="detail-value"><?= date('g:i A', strtotime($resTime)) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="bi bi-people me-1"></i>Guests (Pax)</span>
          <span class="detail-value"><?= $pax ?> <?= $pax == 1 ? 'person' : 'people' ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="bi bi-credit-card me-1"></i>Payment</span>
          <span class="detail-value"><?= ucfirst($payMethod) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label"><i class="bi bi-cash-coin me-1"></i>Reservation Fee</span>
          <span class="detail-value" style="color:var(--brown);">₱100.00</span>
        </div>
      </div>

      <div style="background:#f0fdf4; border-radius:12px; padding:14px 16px; margin: 1.2rem 0; font-size:0.83rem; color:var(--green-dark); line-height:1.7;">
        <i class="bi bi-info-circle me-1"></i>
        <strong>What's next?</strong><br>
        Admin will review and confirm your reservation. Please pay the ₱100 reservation fee
        via <strong><?= ucfirst($payMethod) ?></strong> on arrival.
        Please arrive 15 minutes early ahead of your reserved time!
      </div>

      <a href="book_reservation.php" class="action-btn">
        <i class="bi bi-calendar-check me-2"></i>View My Reservations
      </a>
      <a href="index.php" class="action-btn outline">
        <i class="bi bi-house me-2"></i>Back to Home
      </a>
    </div>
  </div>
</div>

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>