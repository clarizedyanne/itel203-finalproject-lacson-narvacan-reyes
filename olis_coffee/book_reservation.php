<?php
// book_reservation.php - Customer Seat Reservation (with optional Advance Order)
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';
require_once 'includes/mailer.php';

Auth::requireLogin();

$db     = getDB();
$userId = $_SESSION['user_id'];
$message = '';
$msgType = 'success';

// ── Auto-verify pending PayMongo payment when user returns to this page ────────
if (!empty($_SESSION['paymongo_pending_res'])) {
    $pending = $_SESSION['paymongo_pending_res'];
    $linkId  = $pending['link_id'];
    $resId   = $pending['record_id'];

    require_once 'includes/paymongo.php';

    $ch = curl_init(PAYMONGO_BASE_URL . '/links/' . $linkId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);
    $res     = curl_exec($ch);
    curl_close($ch);
    $data    = json_decode($res, true);
    $status  = $data['data']['attributes']['status'] ?? '';

    if ($status === 'paid') {
        $stmt = $db->prepare("UPDATE reservations SET payment_status='paid' WHERE id=?");
        $stmt->bind_param("i", $resId);
        $stmt->execute();
        unset($_SESSION['paymongo_pending_res']);
        $message = '✅ GCash payment confirmed! Your reservation is booked successfully.';
        $msgType = 'success';
    } else {
        $message = '⚠️ GCash payment not yet confirmed. If you already paid, please wait a moment and refresh the page.';
        $msgType = 'warning';
    }
}

// Handle manual redirect messages
if (isset($_GET['paid']) && $_GET['paid'] == 1) {
    $message = '✅ GCash payment confirmed! Your reservation is booked successfully.';
    $msgType = 'success';
} elseif (isset($_GET['payment']) && $_GET['payment'] === 'failed') {
    $message = '⚠️ GCash payment was not completed. Your reservation was saved but is unpaid. Please show proof of payment on arrival or try again.';
    $msgType = 'warning';
}

// Fetch user info
$stmt = $db->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ─────────────────────────────────────────────────────────────────────────────
// ─────────────────────────────────────────────────────────────────────────────

// ─────────────────────────────────────────────────────────────────────────────
// HANDLE FORM SUBMISSION
// ─────────────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── CUSTOMER CANCEL ──────────────────────────────────────────────────────
    if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
        $cancelId = intval($_POST['id'] ?? 0);
        if ($cancelId > 0) {
            // Make sure this reservation belongs to the logged-in user
            $checkOwner = $db->prepare("
                SELECT r.id, r.status, r.payment_status, r.paymongo_payment_id,
                       o.paymongo_payment_id AS order_payment_id,
                       o.total_amount
                FROM reservations r
                LEFT JOIN orders o ON o.reservation_id = r.id
                WHERE r.id = ? AND r.user_id = ?
            ");
            $checkOwner->bind_param("ii", $cancelId, $userId);
            $checkOwner->execute();
            $owned = $checkOwner->get_result()->fetch_assoc();

            if ($owned && $owned['status'] !== 'cancelled') {
                $cancelStmt = $db->prepare("UPDATE reservations SET status='cancelled' WHERE id = ? AND user_id = ?");
                $cancelStmt->bind_param("ii", $cancelId, $userId);

                if ($cancelStmt->execute()) {
                    $refundMsg = '';

                    // Attempt refund if this reservation was paid via GCash
                    if ($owned['payment_status'] === 'paid') {
                        require_once 'includes/paymongo.php';

                        // Prefer the order payment_id (covers fee + food), else reservation fee only
                        $paymentId  = $owned['order_payment_id'] ?? $owned['paymongo_payment_id'] ?? null;
                        $orderTotal = $owned['total_amount'] ?? null;

                        // Amount to refund: full order total (food + fee) or just the ₱100 reservation fee
                        $refundCents = $orderTotal ? (int) round((float)$orderTotal * 100) : 10000;

                        if ($paymentId && paymongo_refund($paymentId, $refundCents)) {
                            // Mark payment as refunded in DB
                            $refStmt = $db->prepare("UPDATE reservations SET payment_status='refunded' WHERE id=?");
                            $refStmt->bind_param("i", $cancelId);
                            $refStmt->execute();
                            if ($owned['order_payment_id']) {
                                $refStmt2 = $db->prepare("UPDATE orders SET payment_status='refunded' WHERE reservation_id=?");
                                $refStmt2->bind_param("i", $cancelId);
                                $refStmt2->execute();
                            }
                            $refundMsg = ' Your GCash payment has been refunded — it may take 5–10 business days to appear.';
                        } else {
                            $refundMsg = ' We could not process your refund automatically. Please contact us for assistance.';
                        }
                    }

                    $message = '✅ Reservation #' . $cancelId . ' has been cancelled.' . $refundMsg;
                    $msgType = $refundMsg && str_contains($refundMsg, 'could not') ? 'warning' : 'success';
                } else {
                    $message = 'Error cancelling reservation. Please try again.';
                    $msgType = 'danger';
                }
            } else {
                $message = 'Invalid reservation or it is already cancelled.';
                $msgType = 'warning';
            }
        }

    // ── NEW RESERVATION ───────────────────────────────────────────────────────
    } else {
        $resDate        = $_POST['res_date'] ?? '';
        $resTime        = $_POST['res_time'] ?? '';
        $pax            = intval($_POST['pax'] ?? 0);
        $phone          = trim($_POST['phone'] ?? '');
        $notes          = trim($_POST['notes'] ?? '');
        $payment        = $_POST['payment_method'] ?? 'gcash';
        $orderInAdvance = isset($_POST['order_in_advance']) && $_POST['order_in_advance'] === '1';

        if (!$resDate || !$resTime || !$pax || !$phone) {
            $message = 'Please fill in all required fields.';
            $msgType = 'danger';
        } elseif ($pax < 1 || $pax > 20) {
            $message = 'Number of guests must be between 1 and 20.';
            $msgType = 'danger';
        } elseif (strtotime($resDate) < strtotime(date('Y-m-d'))) {
            $message = 'Please select a future date.';
            $msgType = 'danger';
        } else {
            // Double booking check: same user, same date + time
            $dupStmt = $db->prepare("
                SELECT id FROM reservations
                WHERE user_id = ? AND res_date = ? AND res_time = ? AND status != 'cancelled'
                LIMIT 1
            ");
            $dupStmt->bind_param("iss", $userId, $resDate, $resTime);
            $dupStmt->execute();

            if ($dupStmt->get_result()->num_rows > 0) {
                $message = '⚠️ You already have a reservation on that date and time. Please choose a different slot or cancel your existing booking first.';
                $msgType = 'danger';
            } else {
                // Check total seats available for that slot
                $checkStmt = $db->prepare("
                    SELECT COALESCE(SUM(pax), 0) as booked
                    FROM reservations
                    WHERE res_date = ? AND res_time = ? AND status != 'cancelled'
                ");
                $checkStmt->bind_param("ss", $resDate, $resTime);
                $checkStmt->execute();
                $booked = $checkStmt->get_result()->fetch_assoc()['booked'];

                if (($booked + $pax) > 20) {
                    $available = 20 - $booked;
                    $message   = "Sorry, only $available seat(s) left for that slot. Please choose a different time or reduce your party size.";
                    $msgType   = 'danger';
                } else {
                    $insStmt = $db->prepare("
                        INSERT INTO reservations (user_id, res_date, res_time, pax, phone, notes, payment_method, status, payment_status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'unpaid')
                    ");
                    $insStmt->bind_param("ississs", $userId, $resDate, $resTime, $pax, $phone, $notes, $payment);

                    if ($insStmt->execute()) {
                        $reservationId = $db->insert_id;

                        sendReservationEmail(
                            ['name' => $user['name'], 'email' => $user['email']],
                            ['id' => $reservationId, 'res_date' => $resDate,
                            'res_time' => $resTime, 'pax' => $pax, 'payment_method' => $payment]
                        );

                        if ($orderInAdvance) {
                            $_SESSION['advance_order'] = [
                                'reservation_id' => $reservationId,
                                'res_date'       => $resDate,
                                'res_time'       => $resTime,
                                'pax'            => $pax,
                                'payment_method' => $payment,
                            ];
                            header("Location: advance_order.php");
                            exit();
                        }

                        // Cash reservation only — redirect to dedicated confirmation page
                        if ($payment === 'cash') {
                            $_SESSION['reservation_success'] = [
                                'reservation_id' => $reservationId,
                                'res_date'       => $resDate,
                                'res_time'       => $resTime,
                                'pax'            => $pax,
                                'payment_method' => $payment,
                            ];
                            header("Location: reservation_confirmation.php");
                            exit();
                        }

                        if ($payment === 'gcash') {
                            require_once 'includes/paymongo.php';
                            $reference  = 'RES-' . str_pad($reservationId, 5, '0', STR_PAD_LEFT);
                            $baseUrl    = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                                        . '://' . $_SERVER['HTTP_HOST']
                                        . dirname($_SERVER['REQUEST_URI']);
                            $paymongoResult = paymongo_create_link(100.00, "Oli's Reservation Fee - $reference",
                                $baseUrl . '/paymongo_return.php?type=reservation&record_id=' . $reservationId . '&link_id=PENDING');

                            if ($paymongoResult) {
                                $_SESSION['paymongo_pending_res'] = [
                                    'link_id'      => $paymongoResult['link_id'],
                                    'record_id'    => $reservationId,
                                    'checkout_url' => $paymongoResult['checkout_url'],
                                ];
                                // Go to our waiting page which polls PayMongo
                                header("Location: paymongo_wait.php");
                                exit();
                            }
                            $message = '⚠️ Could not create GCash payment link. Please try again.';
                            $msgType = 'warning';
                        }

                    } else {
                        $message = 'Error saving reservation. Please try again.';
                        $msgType = 'danger';
                    }
                }
            }
        }
    }
}

// Fetch user's reservations (all, split into upcoming/past in PHP)
$myRes = $db->prepare("
    SELECT r.*,
           CASE WHEN o.id IS NOT NULL THEN 1 ELSE 0 END as has_order,
           o.total_amount as order_total,
           o.status as order_status
    FROM reservations r
    LEFT JOIN orders o ON o.reservation_id = r.id
    WHERE r.user_id = ?
    ORDER BY r.res_date DESC, r.res_time DESC
");
if (!$myRes) {
    $myRes = $db->prepare("SELECT * FROM reservations WHERE user_id = ? ORDER BY res_date DESC, res_time DESC");
}
$myRes->bind_param("i", $userId);
$myRes->execute();
$allReservations = $myRes->get_result()->fetch_all(MYSQLI_ASSOC);

// Split: upcoming = today or future, past = older, cancelled = separate
$today = date('Y-m-d');
$upcomingRes   = [];
$pastRes       = [];
$cancelledRes  = [];
foreach ($allReservations as $r) {
    if ($r['status'] === 'cancelled') {
        $cancelledRes[] = $r;
        continue;
    }
    if ($r['res_date'] >= $today) {
        $upcomingRes[] = $r;
    } else {
        $pastRes[] = $r;
    }
}
// Show only the 10 most recent upcoming reservations in the main table
$myReservations = array_slice($upcomingRes, 0, 10);

$userName = htmlspecialchars($_SESSION['user_name']);
$minDate  = date('Y-m-d', strtotime('+1 day'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seat Reservation – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ── Advance order toggle ── */
    .advance-order-panel {
      background: #f0fdf4;
      border: 2px solid var(--green-mid);
      border-radius: 14px;
      padding: 1.2rem 1.4rem;
      margin-top: 0.75rem;
      display: none;
    }
    .advance-order-panel.show { display: block; }

    .toggle-label {
      display: flex; align-items: center; gap: 10px;
      cursor: pointer; font-weight: 700; color: var(--green-dark);
      font-size: 0.95rem; padding: 11px 14px;
      background: #f0fdf4; border: 1.5px dashed var(--green-mid);
      border-radius: 10px; transition: background 0.2s; user-select: none;
    }
    .toggle-label:hover { background: #dcfce7; }
    .toggle-label input[type="checkbox"] { display: none; }
    .toggle-switch {
      width: 42px; height: 22px; background: #cbd5e1;
      border-radius: 20px; position: relative; transition: background 0.25s; flex-shrink: 0;
    }
    .toggle-switch::after {
      content: ''; position: absolute; width: 16px; height: 16px;
      background: white; border-radius: 50%; top: 3px; left: 3px;
      transition: left 0.25s; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
    }
    .toggle-label input:checked ~ .toggle-switch { background: var(--green-mid); }
    .toggle-label input:checked ~ .toggle-switch::after { left: 23px; }

    /* ── Submit button ── */
    .submit-btn {
      background: var(--green-dark); color: white; border: none;
      border-radius: 10px; padding: 0.75rem; font-weight: 700;
      width: 100%; font-size: 0.97rem; transition: opacity 0.2s, background 0.2s;
    }
    .submit-btn:hover { background: #1a3010; color: white; }
    .submit-btn.with-order:hover { background: #6b3000; }
    .submit-btn.with-order { background: var(--brown); }

    /* ── Availability panel ── */
    #availPanel {
      display: block;
      background: white;
      border: 1.5px solid #d1e7c8;
      border-radius: 14px;
      padding: 1rem 1.2rem;
      margin-top: 10px;
    }
    #availPanel.show { display: block; }
    .avail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
      gap: 8px;
      margin-top: 10px;
    }
    .avail-slot {
      border-radius: 10px;
      padding: 9px 10px;
      font-size: 0.8rem;
      font-weight: 700;
      text-align: center;
      border: 1.5px solid transparent;
      cursor: pointer;
      transition: all 0.18s;
      position: relative;
    }
    .avail-slot.open {
      background: #f0fdf4;
      border-color: #86efac;
      color: var(--green-dark);
    }
    .avail-slot.open:hover {
      background: #dcfce7;
      border-color: var(--green-mid);
      transform: translateY(-1px);
    }
    .avail-slot.open.selected {
      background: var(--green-dark);
      border-color: var(--green-dark);
      color: white;
    }
    .avail-slot.limited {
      background: #fffbeb;
      border-color: #fcd34d;
      color: #92400e;
    }
    .avail-slot.limited:hover {
      background: #fef3c7;
      border-color: #f59e0b;
      transform: translateY(-1px);
    }
    .avail-slot.limited.selected {
      background: #d97706;
      border-color: #d97706;
      color: white;
    }
    .avail-slot.full {
      background: #fef2f2;
      border-color: #fca5a5;
      color: #991b1b;
      cursor: not-allowed;
      opacity: 0.7;
    }
    .avail-slot .slot-time { font-size: 0.85rem; margin-bottom: 3px; }
    .avail-slot .slot-seats { font-size: 0.7rem; font-weight: 600; opacity: 0.85; }
    .avail-seat-bar {
      height: 5px;
      border-radius: 3px;
      background: #e5e7eb;
      margin-top: 6px;
      overflow: hidden;
    }
    .avail-seat-fill {
      height: 100%;
      border-radius: 3px;
      transition: width 0.4s;
    }
    .legend-dot {
      display: inline-block;
      width: 10px; height: 10px;
      border-radius: 50%;
      margin-right: 4px;
    }
    #availLoading {
      text-align: center;
      padding: 14px 0;
      color: var(--text-muted);
      font-size: 0.85rem;
      display: none;
    }
    select option.slot-full { color: #b91c1c; }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Oli's SelfieTea & Coffee <span class="sub">· Est. 2019</span></div>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php"><i class="bi bi-journal-text me-1"></i>Menu</a></li>
        <li class="nav-item"><a class="nav-link active" href="book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item"><a class="nav-link" href="chatbot.php"><i class="bi bi-chat-dots me-1"></i>Ask Oli</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php"><i class="bi bi-geo-alt me-1"></i>Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-1"></i>My Profile</a></li>
        <?php if (Auth::isAdmin()): ?>
        <li class="nav-item">
          <a class="nav-link" href="admin/dashboard.php"
             style="background:var(--gold);color:var(--green-dark);border-radius:20px;padding:5px 14px;font-weight:700;font-size:0.82rem;">
            <i class="bi bi-speedometer2 me-1"></i>Admin Panel
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="btn-logout nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<div style="background: linear-gradient(135deg, var(--green-dark), #1a3510); padding:50px 0 35px; color:var(--cream);">
  <div class="container">
    <p style="font-size:0.8rem; letter-spacing:3px; text-transform:uppercase; color:rgba(245,240,232,0.6); margin-bottom:4px;">Book Your Spot</p>
    <h2 style="font-family:'Playfair Display',serif; font-weight:700;">Seat Reservation</h2>
    <p style="color:rgba(245,240,232,0.75); margin-top:6px; font-size:0.95rem;">
      Reserve a table on the 2nd floor · 4 tables · 5 seats each · ₱100 reservation fee
    </p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4">

    <!-- ── FORM ── -->
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header" style="background:var(--green-dark); color:white;">
          <i class="bi bi-calendar-plus me-2"></i>New Reservation
        </div>
        <div class="card-body p-4">

          <?php if ($message): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
              <?= $message ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>

          <form method="POST" id="reservationForm">
            <input type="hidden" name="order_in_advance" id="orderInAdvanceInput" value="0">

            <!-- Name & Email (read-only from profile) -->
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" readonly style="background:#f8f9fa;">
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background:#f8f9fa;">
            </div>
            <div class="mb-3">
              <label class="form-label">Contact Number *</label>
              <input type="tel" name="phone" class="form-control" required placeholder="09XX-XXX-XXXX" pattern="[0-9]{10,11}">
            </div>

            <!-- Date → triggers availability fetch -->
            <div class="mb-3">
              <label class="form-label">Date *</label>
              <input type="date" name="res_date" id="resDate" class="form-control" required min="<?= $minDate ?>">
            </div>

            <!-- ── LIVE AVAILABILITY PANEL ── -->
            <div id="availPanel">
              <div style="font-size:0.78rem; color:var(--text-muted); margin-bottom:6px; display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <strong style="color:var(--green-dark); font-size:0.82rem;">
                  <i class="bi bi-grid-3x3-gap me-1"></i>Available Time Slots
                </strong>
                <span><span class="legend-dot" style="background:#16a34a;"></span>Open</span>
                <span><span class="legend-dot" style="background:#d97706;"></span>Limited</span>
                <span><span class="legend-dot" style="background:#dc2626;"></span>Full</span>
              </div>
              <div id="availLoading"><i class="bi bi-arrow-repeat me-1"></i>Checking availability…</div>
              <div class="avail-grid" id="availGrid">
                <p class="text-muted" style="font-size:0.82rem; grid-column:1/-1; text-align:center; padding:10px 0;">
                  <i class="bi bi-calendar2-event me-1"></i>Pick a date above to see available time slots
                </p>
              </div>
            </div>

            <!-- Time select (synced with availability panel clicks) -->
            <div class="mb-3 mt-3">
              <label class="form-label">Time *
                <small class="text-muted ms-1" id="timeHint" style="font-size:0.72rem;"></small>
              </label>
              <select name="res_time" id="resTime" class="form-select" required>
                <option value="">— Pick a date first, then choose time —</option>
                <option value="11:00">11:00 AM</option>
                <option value="12:00">12:00 PM</option>
                <option value="13:00">1:00 PM</option>
                <option value="14:00">2:00 PM</option>
                <option value="15:00">3:00 PM</option>
                <option value="16:00">4:00 PM</option>
                <option value="17:00">5:00 PM</option>
                <option value="18:00">6:00 PM</option>
                <option value="19:00">7:00 PM</option>
                <option value="20:00">8:00 PM</option>
                <option value="21:00">9:00 PM</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Number of Guests (Pax) *</label>
              <input type="number" name="pax" id="resPax" class="form-control" required min="1" max="20" placeholder="1–20">
              <div class="form-text" id="seatsLeft">Max 20 guests per time slot (4 tables × 5 seats)</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Special Notes <small class="text-muted">(optional)</small></label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Occasion, dietary needs, etc."></textarea>
            </div>

            <div class="mb-4">
              <label class="form-label">Pay ₱100 Reservation Fee via</label>
              <div class="d-flex align-items-center gap-2 mt-1" style="background:#f0f7ee;border:1px solid #c3dbbe;border-radius:8px;padding:10px 14px;">
                <img src="assets/gcash logo.png" height="22" alt="GCash">
                <span style="font-weight:600;color:#2d5a27;">GCash</span>
                <span class="text-muted" style="font-size:.85rem;">— you’ll be redirected to PayMongo to complete payment</span>
              </div>
              <input type="hidden" name="payment_method" value="gcash">
            </div>

            <!-- Advance order toggle -->
            <div class="mb-4">
              <label class="toggle-label" for="advanceOrderToggle">
                <input type="checkbox" id="advanceOrderToggle">
                <span class="toggle-switch"></span>
                <i class="bi bi-bag-plus" style="color:var(--green-mid); font-size:1.1rem;"></i>
                Order food in advance?
                <span class="ms-auto badge rounded-pill" style="background:var(--gold); color:white; font-size:0.68rem; padding:4px 9px;">Optional</span>
              </label>
              <div class="advance-order-panel" id="advancePanel">
                <p class="mb-0" style="font-size:0.84rem; color:var(--green-dark); line-height:1.6;">
                  <i class="bi bi-arrow-right-circle me-1"></i>
                  After submitting, you'll choose your dishes from our menu. Your food will be ready when you arrive!
                </p>
              </div>
            </div>

            <button type="submit" class="submit-btn btn" id="submitBtn">
              <i class="bi bi-calendar-check me-2" id="submitIcon"></i>
              <span id="submitBtnText">Submit Reservation</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- ── RIGHT COLUMN ── -->
    <div class="col-lg-6">

      <!-- Info card -->
      <div class="card border-0 shadow-sm mb-3" style="border-left: 4px solid var(--gold) !important;">
        <div class="card-body p-3" style="background:#fffbf0;">
          <h6 class="fw-bold mb-2" style="color:var(--brown);"><i class="bi bi-info-circle me-2"></i>Reservation Info</h6>
          <ul class="mb-0" style="font-size:0.85rem; color:var(--text-dark); padding-left:1.2rem;">
            <li>2nd floor only · 4 tables · 5 seats each (max 20 pax per slot)</li>
            <li>₱100 non-refundable reservation fee per booking</li>
            <li>A confirmation email will be sent to your registered email</li>
            <li>Admin will confirm your reservation after reviewing</li>
            <li>Arrive within 15 minutes of your time slot</li>
            <li>Advance orders will be ready when you arrive!</li>
            <li>You can choose to order online or in person upon arrival</li>
          </ul>
        </div>
      </div>

      <!-- Floor layout visual -->
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header" style="background:var(--green-dark); color:white;">
          <i class="bi bi-diagram-2 me-2"></i>2nd Floor Layout
        </div>
        <div class="card-body p-3">
          <p style="font-size:0.78rem; color:var(--text-muted); margin-bottom:12px; text-align:center;">
            4 tables · 5 seats each · Total 20 seats
          </p>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <?php for ($t = 1; $t <= 4; $t++): ?>
            <div style="background:#f0fdf4; border:1.5px solid #bbf7d0; border-radius:10px; padding:12px; text-align:center;">
              <div style="font-size:1.4rem; margin-bottom:4px;">🪑</div>
              <div style="font-size:0.78rem; font-weight:700; color:var(--green-dark);">Table <?= $t ?></div>
              <div style="font-size:0.7rem; color:var(--text-muted);">5 seats</div>
            </div>
            <?php endfor; ?>
          </div>
        </div>
      </div>

      <!-- My Reservations -->
      <div class="card border-0 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between" style="background:var(--green-dark); color:white;">
          <span><i class="bi bi-list-check me-2"></i>My Reservations</span>
          <?php if (!empty($upcomingRes)): ?>
            <span class="badge bg-light text-dark" style="font-size:.75rem;"><?= count($upcomingRes) ?> upcoming</span>
          <?php endif; ?>
        </div>
        <div class="card-body p-0">
          <?php if (empty($allReservations)): ?>
            <div class="text-center py-5 text-muted">
              <i class="bi bi-calendar-x" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
              No reservations yet.
            </div>
          <?php else: ?>

            <?php
            // Reusable row renderer
            function renderResRow($res) { ?>
              <tr>
                <td><?= date('M d, Y', strtotime($res['res_date'])) ?></td>
                <td><?= date('h:i A', strtotime($res['res_time'])) ?></td>
                <td><?= $res['pax'] ?></td>
                <td>
                  <?php $cls = ['pending'=>'badge bg-warning text-dark','confirmed'=>'badge bg-success','cancelled'=>'badge bg-danger'][$res['status']] ?? 'badge bg-secondary'; ?>
                  <span class="<?= $cls ?>" style="font-size:0.7rem;"><?= ucfirst($res['status']) ?></span>
                </td>
                <td>
                  <?php
                    $pBadge = $res['payment_status'] === 'paid' ? 'badge-available'
                            : ($res['payment_status'] === 'refunded' ? 'badge bg-info text-dark'
                            : 'badge-unavailable');
                  ?>
                  <span class="<?= $pBadge ?>">
                    <?= ucfirst($res['payment_status']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($res['status'] !== 'cancelled'): ?>
                    <button class="btn btn-sm btn-outline-danger" style="border-radius:6px;font-size:0.73rem;"
                            data-bs-toggle="modal" data-bs-target="#cancelModal"
                            data-id="<?= $res['id'] ?>"
                            data-date="<?= date('M d, Y', strtotime($res['res_date'])) ?>"
                            data-time="<?= date('h:i A', strtotime($res['res_time'])) ?>">
                      <i class="bi bi-x-circle"></i> Cancel
                    </button>
                  <?php else: ?>
                    <span class="text-muted" style="font-size:0.75rem;">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php } ?>

            <!-- Upcoming reservations (today + future, max 10) -->
            <?php if (!empty($myReservations)): ?>
            <div class="table-responsive">
              <table class="table table-olis mb-0">
                <thead>
                  <tr><th>Date</th><th>Time</th><th>Pax</th><th>Status</th><th>Payment</th><th>Action</th></tr>
                </thead>
                <tbody>
                  <?php foreach ($myReservations as $res): renderResRow($res); endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4 text-muted" style="font-size:.88rem;">
              <i class="bi bi-calendar-check me-1"></i>No upcoming reservations.
            </div>
            <?php endif; ?>

            <!-- Past reservations (paginated) -->
            <?php if (!empty($pastRes)): ?>
            <div style="border-top:2px solid #f0ece6; background:#faf8f5;">
              <div class="d-flex align-items-center justify-content-between px-3 pt-3 pb-2">
                <span style="font-weight:600; font-size:.85rem; color:var(--green-dark);">
                  <i class="bi bi-clock-history me-1"></i>Past Reservations
                </span>
                <span class="text-muted" style="font-size:.78rem;"><?= count($pastRes) ?> total</span>
              </div>
              <div class="table-responsive">
                <table class="table table-olis mb-0" style="background:transparent;">
                  <thead style="background:#f0ece6;">
                    <tr><th>Date</th><th>Time</th><th>Pax</th><th>Status</th><th>Payment</th><th>Action</th></tr>
                  </thead>
                  <tbody id="pastResBody">
                    <?php foreach ($pastRes as $res): renderResRow($res); endforeach; ?>
                  </tbody>
                </table>
              </div>
              <!-- Pagination -->
              <div class="d-flex align-items-center justify-content-between px-3 py-2" id="pastPagination" style="border-top:1px solid #e8e2da;">
                <span class="text-muted" id="pastPageInfo" style="font-size:.8rem;"></span>
                <div class="d-flex gap-1" id="pastPageBtns"></div>
              </div>
            </div>
            <?php endif; ?>

            <!-- Cancelled reservations (collapsible) -->
            <?php if (!empty($cancelledRes)): ?>
            <div style="border-top:2px solid #f0ece6; background:#fdf9f9;">
              <button class="w-100 d-flex align-items-center justify-content-between px-3 py-2"
                      onclick="toggleCancelled()"
                      style="background:none;border:none;cursor:pointer;text-align:left;">
                <span style="font-weight:600;font-size:.85rem;color:#b91c1c;">
                  <i class="bi bi-x-circle me-1"></i>Cancelled Reservations
                  <span class="badge bg-danger ms-1" style="font-size:.72rem;"><?= count($cancelledRes) ?></span>
                </span>
                <i class="bi bi-chevron-down text-muted" id="cancelledChevron" style="font-size:.85rem;transition:transform .2s;"></i>
              </button>
              <div id="cancelledSection" style="display:none;">
                <div class="table-responsive">
                  <table class="table table-olis mb-0" style="background:transparent;">
                    <thead style="background:#fce8e8;">
                      <tr><th>Date</th><th>Time</th><th>Pax</th><th>Status</th><th>Payment</th><th></th></tr>
                    </thead>
                    <tbody id="cancelledResBody">
                      <?php foreach ($cancelledRes as $res): renderResRow($res); endforeach; ?>
                    </tbody>
                  </table>
                </div>
                <!-- Pagination for cancelled -->
                <div class="d-flex align-items-center justify-content-between px-3 py-2" style="border-top:1px solid #f3d0d0;">
                  <span class="text-muted" id="cancelledPageInfo" style="font-size:.8rem;"></span>
                  <div class="d-flex gap-1" id="cancelledPageBtns"></div>
                </div>
              </div>
            </div>
            <?php endif; ?>

          <?php endif; ?>
        </div>
      </div>

    </div><!-- /col -->
  </div><!-- /row -->
</div>

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<!-- ── Cancel Reservation Modal ── -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header" style="background:#dc2626; color:white;">
        <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Cancel Reservation</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4 text-center">
        <div style="font-size:3rem; margin-bottom:0.75rem;">😔</div>
        <p class="mb-1" style="font-size:0.95rem; color:var(--text-dark);">
          Are you sure you want to cancel this reservation?
        </p>
        <p class="mb-0 mt-2" id="cancelModalInfo" style="font-size:0.85rem; color:var(--text-muted);"></p>
        <div class="mt-3 p-3" style="background:#fef2f2; border-radius:10px; font-size:0.8rem; color:#991b1b;">
          <i class="bi bi-exclamation-triangle me-1"></i>The ₱100 reservation fee is non-refundable.
        </div>
      </div>
      <form method="POST">
        <input type="hidden" name="action" value="cancel">
        <input type="hidden" name="id" id="cancelModalId">
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Reservation</button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i>Yes, Cancel It
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
// ── Advance order toggle ──────────────────────────────────────────────────────
const aoToggle  = document.getElementById('advanceOrderToggle');
const aoPanel   = document.getElementById('advancePanel');
const aoHidden  = document.getElementById('orderInAdvanceInput');
const btnText   = document.getElementById('submitBtnText');
const btnIcon   = document.getElementById('submitIcon');
const submitBtn = document.getElementById('submitBtn');

aoToggle.addEventListener('change', function () {
  const on = this.checked;
  aoPanel.classList.toggle('show', on);
  aoHidden.value = on ? '1' : '0';
  btnText.textContent = on ? 'Submit & Choose My Order' : 'Submit Reservation';
  btnIcon.className   = on ? 'bi bi-bag-plus me-2' : 'bi bi-calendar-check me-2';
  submitBtn.classList.toggle('with-order', on);
});

// ── Cancel modal ──────────────────────────────────────────────────────────────
document.getElementById('cancelModal').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('cancelModalId').value = btn.dataset.id;
  document.getElementById('cancelModalInfo').textContent =
    'Reservation #' + btn.dataset.id + ' · ' + btn.dataset.date + ' at ' + btn.dataset.time;
});

// ── Live availability checker ─────────────────────────────────────────────────
const slotLabels = {
  '11:00':'11:00 AM','12:00':'12:00 PM',
  '13:00':'1:00 PM', '14:00':'2:00 PM', '15:00':'3:00 PM',
  '16:00':'4:00 PM', '17:00':'5:00 PM', '18:00':'6:00 PM',
  '19:00':'7:00 PM', '20:00':'8:00 PM', '21:00':'9:00 PM'
};

const dateInput  = document.getElementById('resDate');
const timeSelect = document.getElementById('resTime');
const paxInput   = document.getElementById('resPax');
const availPanel = document.getElementById('availPanel');
const availGrid  = document.getElementById('availGrid');
const availLoad  = document.getElementById('availLoading');
const seatsLeft  = document.getElementById('seatsLeft');
const timeHint   = document.getElementById('timeHint');

let availData = {};

dateInput.addEventListener('change', function () {
  const date = this.value;
  if (!date) { availGrid.innerHTML = '<p class="text-muted" style="font-size:0.82rem; grid-column:1/-1; text-align:center; padding:10px 0;"><i class="bi bi-calendar2-event me-1"></i>Pick a date above to see available time slots</p>'; return; }
  fetchAvailability(date);
});

timeSelect.addEventListener('change', function () {
  updateSeatsLeftHint();
  highlightGrid(this.value);
});

paxInput.addEventListener('input', updateSeatsLeftHint);

async function fetchAvailability(date) {
  availPanel.classList.add('show');
  availGrid.innerHTML = '';
  availLoad.style.display = 'block';

  try {
    const res  = await fetch(`check_availability.php?date=${encodeURIComponent(date)}`);
    availData  = await res.json();
  } catch (e) {
    availLoad.style.display = 'none';
    availGrid.innerHTML = '<p class="text-muted" style="font-size:0.82rem;grid-column:1/-1;">Could not load availability.</p>';
    return;
  }

  availLoad.style.display = 'none';
  availGrid.innerHTML = '';

  Array.from(timeSelect.options).forEach(opt => {
    if (!opt.value) return;
    const info = availData[opt.value];
    if (!info) return;
    opt.disabled = info.full;
    opt.text = slotLabels[opt.value] + (info.full ? ' — FULL' : info.available <= 5 ? ` (${info.available} left)` : '');
  });

  Object.entries(slotLabels).forEach(([time, label]) => {
    const info  = availData[time] || { booked: 0, available: 20, full: false };
    const pct   = Math.round((info.booked / 20) * 100);
    const isLtd = !info.full && info.available <= 5;

    const cls = info.full ? 'full' : isLtd ? 'limited' : 'open';
    const fillColor = info.full ? '#ef4444' : isLtd ? '#f59e0b' : '#16a34a';
    const seatTxt  = info.full
      ? '🔴 Full'
      : `🟢 ${info.available} seat${info.available !== 1 ? 's' : ''} left`;

    const div = document.createElement('div');
    div.className = `avail-slot ${cls}`;
    div.dataset.time = time;
    div.innerHTML = `
      <div class="slot-time">${label}</div>
      <div class="slot-seats">${seatTxt}</div>
      <div class="avail-seat-bar">
        <div class="avail-seat-fill" style="width:${pct}%; background:${fillColor};"></div>
      </div>`;

    if (!info.full) {
      div.addEventListener('click', () => {
        timeSelect.value = time;
        highlightGrid(time);
        updateSeatsLeftHint();
        timeSelect.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
    }
    availGrid.appendChild(div);
  });

  if (timeSelect.value) {
    highlightGrid(timeSelect.value);
    updateSeatsLeftHint();
  }
}

function highlightGrid(selectedTime) {
  document.querySelectorAll('.avail-slot').forEach(el => {
    el.classList.toggle('selected', el.dataset.time === selectedTime);
  });
}

function updateSeatsLeftHint() {
  const time = timeSelect.value;
  const pax  = parseInt(paxInput.value) || 0;
  if (!time || !availData[time]) { seatsLeft.textContent = 'Max 20 guests per time slot (4 tables × 5 seats)'; timeHint.textContent = ''; return; }

  const info = availData[time];
  const avail = info.available;

  if (info.full) {
    timeHint.textContent = '🔴 This slot is fully booked';
    timeHint.style.color = '#dc2626';
    seatsLeft.textContent = 'No seats available. Please choose another time.';
  } else {
    timeHint.textContent = `${avail} seat${avail !== 1 ? 's' : ''} available`;
    timeHint.style.color = avail <= 5 ? '#d97706' : '#16a34a';
    if (pax > avail) {
      seatsLeft.textContent = `⚠️ Only ${avail} seat(s) left — reduce your party size.`;
      seatsLeft.style.color = '#dc2626';
    } else {
      seatsLeft.textContent = `Max 20 per slot · ${avail} seat${avail !== 1 ? 's' : ''} remaining for this time.`;
      seatsLeft.style.color = '';
    }
  }
}

// ── Past reservations pagination ──────────────────────────────────────────
(function() {
  const tbody = document.getElementById('pastResBody');
  const info  = document.getElementById('pastPageInfo');
  const btns  = document.getElementById('pastPageBtns');
  if (!tbody) return;

  const PER_PAGE = 5;
  const rows = Array.from(tbody.querySelectorAll('tr'));
  let page = 1;
  const total = rows.length;
  const pages = Math.ceil(total / PER_PAGE);

  function render() {
    const start = (page - 1) * PER_PAGE;
    const end   = start + PER_PAGE;
    rows.forEach((r, i) => r.style.display = (i >= start && i < end) ? '' : 'none');

    info.textContent = 'Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total;

    btns.innerHTML = '';

    // Prev button
    const prev = document.createElement('button');
    prev.className = 'btn btn-sm btn-outline-secondary';
    prev.style.cssText = 'font-size:.75rem;padding:2px 8px;';
    prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
    prev.disabled = page === 1;
    prev.onclick = () => { page--; render(); };
    btns.appendChild(prev);

    // Page number buttons
    const range = 2;
    for (let p = 1; p <= pages; p++) {
      if (p === 1 || p === pages || (p >= page - range && p <= page + range)) {
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm ' + (p === page ? 'btn-success' : 'btn-outline-secondary');
        btn.style.cssText = 'font-size:.75rem;padding:2px 8px;min-width:30px;';
        btn.textContent = p;
        btn.onclick = ((_p) => () => { page = _p; render(); })(p);
        btns.appendChild(btn);
      } else if (p === page - range - 1 || p === page + range + 1) {
        const dots = document.createElement('span');
        dots.textContent = '...';
        dots.className = 'text-muted align-self-center px-1';
        dots.style.fontSize = '.8rem';
        btns.appendChild(dots);
      }
    }

    // Next button
    const next = document.createElement('button');
    next.className = 'btn btn-sm btn-outline-secondary';
    next.style.cssText = 'font-size:.75rem;padding:2px 8px;';
    next.innerHTML = '<i class="bi bi-chevron-right"></i>';
    next.disabled = page === pages;
    next.onclick = () => { page++; render(); };
    btns.appendChild(next);
  }

  if (pages > 1) render();
  else if (total > 0) {
    info.textContent = total + ' past reservation' + (total !== 1 ? 's' : '');
    btns.style.display = 'none';
  }
})();


// -- Cancelled reservations toggle & pagination --------------------------------
function toggleCancelled() {
  const section  = document.getElementById('cancelledSection');
  const chevron  = document.getElementById('cancelledChevron');
  const isHidden = section.style.display === 'none';
  section.style.display = isHidden ? '' : 'none';
  chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
  if (isHidden) initCancelledPagination();
}

function initCancelledPagination() {
  const tbody = document.getElementById('cancelledResBody');
  const info  = document.getElementById('cancelledPageInfo');
  const btns  = document.getElementById('cancelledPageBtns');
  if (!tbody || tbody.dataset.init) return;
  tbody.dataset.init = '1';

  const PER_PAGE = 5;
  const rows  = Array.from(tbody.querySelectorAll('tr'));
  const total = rows.length;
  const pages = Math.ceil(total / PER_PAGE);
  let page = 1;

  function render() {
    const start = (page - 1) * PER_PAGE;
    const end   = start + PER_PAGE;
    rows.forEach((r, i) => r.style.display = (i >= start && i < end) ? '' : 'none');
    info.textContent = 'Showing ' + (start + 1) + '–' + Math.min(end, total) + ' of ' + total;
    btns.innerHTML = '';

    const prev = document.createElement('button');
    prev.className = 'btn btn-sm btn-outline-secondary';
    prev.style.cssText = 'font-size:.75rem;padding:2px 8px;';
    prev.innerHTML = '<i class="bi bi-chevron-left"></i>';
    prev.disabled = page === 1;
    prev.onclick = () => { page--; render(); };
    btns.appendChild(prev);

    const range = 2;
    for (let p = 1; p <= pages; p++) {
      if (p === 1 || p === pages || (p >= page - range && p <= page + range)) {
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm ' + (p === page ? 'btn-danger' : 'btn-outline-secondary');
        btn.style.cssText = 'font-size:.75rem;padding:2px 8px;min-width:30px;';
        btn.textContent = p;
        btn.onclick = ((_p) => () => { page = _p; render(); })(p);
        btns.appendChild(btn);
      } else if (p === page - range - 1 || p === page + range + 1) {
        const dots = document.createElement('span');
        dots.textContent = '...';
        dots.className = 'text-muted align-self-center px-1';
        dots.style.fontSize = '.8rem';
        btns.appendChild(dots);
      }
    }

    const next = document.createElement('button');
    next.className = 'btn btn-sm btn-outline-secondary';
    next.style.cssText = 'font-size:.75rem;padding:2px 8px;';
    next.innerHTML = '<i class="bi bi-chevron-right"></i>';
    next.disabled = page === pages;
    next.onclick = () => { page++; render(); };
    btns.appendChild(next);
  }

  if (pages > 1) render();
  else {
    info.textContent = total + ' cancelled reservation' + (total !== 1 ? 's' : '');
    btns.style.display = 'none';
  }
}

</script>
</body>
</html>