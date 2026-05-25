<?php
// gcash_payment.php
// Simulated GCash payment page for demo/school project purposes.
// Place in root of olis_coffee/ folder.
//
// HOW IT WORKS:
//   1. book_reservation.php or advance_order.php redirects here with payment info in session
//   2. Customer sees a fake GCash UI, clicks "Pay Now"
//   3. After a short animation, we mark payment as paid in the DB
//   4. Redirect to order_confirmation.php or back to book_reservation.php

session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';

Auth::requireLogin();

$db = getDB();

// Must come from our own system (session guard)
$pending = $_SESSION['gcash_pending'] ?? null;
if (!$pending) {
    header("Location: book_reservation.php");
    exit();
}

$type      = $pending['type'];      // 'reservation' or 'order'
$recordId  = $pending['record_id'];
$amount    = $pending['amount'];
$reference = $pending['reference']; // e.g. "RES-00003" or "ORD-00007"

// ── Handle the simulated "Pay" POST ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_pay'])) {

    // Mark as paid in DB
    if ($type === 'reservation') {
        $stmt = $db->prepare("UPDATE reservations SET payment_status='paid' WHERE id=?");
        $stmt->bind_param("i", $recordId);
        $stmt->execute();
    } elseif ($type === 'order') {
        $stmt = $db->prepare("UPDATE orders SET payment_status='paid' WHERE id=?");
        $stmt->bind_param("i", $recordId);
        $stmt->execute();
        // Also update linked reservation
        $stmt2 = $db->prepare("UPDATE reservations SET payment_status='paid' WHERE order_id=?");
        $stmt2->bind_param("i", $recordId);
        $stmt2->execute();
    }

    // Clear the pending session
    unset($_SESSION['gcash_pending']);

    // Set order success session if it was saved
    if (!empty($pending['order_success'])) {
        $successData = $pending['order_success'];
        $successData['payment_status'] = 'paid';
        $_SESSION['order_success'] = $successData;
        header("Location: order_confirmation.php");
        exit();
    }

    // For reservation-only payments
    header("Location: book_reservation.php?paid=1");
    exit();
}

$userName = htmlspecialchars($_SESSION['user_name']);
$amountFormatted = number_format($amount, 2);
// Generate a fake transaction reference number
$fakeRef = 'GC' . strtoupper(substr(md5($reference . time()), 0, 10));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>GCash Payment</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: #f0f0f0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
    }

    /* ── Phone shell (makes it look like a mobile app) ── */
    .phone-shell {
      width: 100%;
      max-width: 390px;
      background: white;
      border-radius: 40px;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.25);
      min-height: 700px;
      display: flex;
      flex-direction: column;
    }

    /* ── GCash header ── */
    .gcash-header {
      background: #0070e0;
      padding: 20px 20px 24px;
      color: white;
      position: relative;
    }

    .gcash-logo {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
    }

    .gcash-logo-icon {
      width: 36px; height: 36px;
      background: white;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 900;
      color: #0070e0;
      font-size: 1rem;
      letter-spacing: -1px;
    }

    .gcash-logo-text {
      font-size: 1.4rem;
      font-weight: 800;
      letter-spacing: -0.5px;
    }

    .gcash-header-label {
      font-size: 0.78rem;
      opacity: 0.8;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 6px;
    }

    .gcash-merchant {
      font-size: 1.15rem;
      font-weight: 700;
      margin-bottom: 2px;
    }

    .gcash-ref {
      font-size: 0.75rem;
      opacity: 0.7;
    }

    /* ── Amount display ── */
    .amount-section {
      background: #0060c8;
      padding: 20px;
      text-align: center;
      color: white;
    }

    .amount-label {
      font-size: 0.75rem;
      opacity: 0.75;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 6px;
    }

    .amount-value {
      font-size: 2.8rem;
      font-weight: 800;
      letter-spacing: -1px;
      line-height: 1;
    }

    .amount-currency {
      font-size: 1.4rem;
      font-weight: 400;
      vertical-align: super;
      margin-right: 4px;
    }

    /* ── Body ── */
    .gcash-body {
      flex: 1;
      padding: 24px 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    /* ── Detail rows ── */
    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 16px;
      background: #f8f9fa;
      border-radius: 12px;
    }

    .detail-row-label {
      font-size: 0.82rem;
      color: #666;
    }

    .detail-row-value {
      font-size: 0.88rem;
      font-weight: 700;
      color: #1a1a1a;
      text-align: right;
      max-width: 55%;
    }

    /* ── Balance (fake) ── */
    .balance-section {
      background: #e8f4fd;
      border: 1.5px solid #b3d9f7;
      border-radius: 12px;
      padding: 14px 16px;
    }

    .balance-top {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 4px;
    }

    .balance-label {
      font-size: 0.78rem;
      color: #0070e0;
      font-weight: 600;
    }

    .balance-icon {
      width: 28px; height: 28px;
      background: #0070e0;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: white;
      font-size: 0.8rem;
    }

    .balance-amount {
      font-size: 1.3rem;
      font-weight: 800;
      color: #0070e0;
    }

    .balance-sub {
      font-size: 0.72rem;
      color: #888;
      margin-top: 2px;
    }

    /* ── Secure badge ── */
    .secure-badge {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      font-size: 0.75rem;
      color: #888;
    }

    /* ── Pay button ── */
    .gcash-footer {
      padding: 16px 20px 28px;
      background: white;
    }

    .pay-btn {
      width: 100%;
      background: #0070e0;
      color: white;
      border: none;
      border-radius: 16px;
      padding: 18px;
      font-size: 1.05rem;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.2s;
      letter-spacing: 0.3px;
      position: relative;
      overflow: hidden;
    }

    .pay-btn:hover { background: #0060c8; transform: translateY(-1px); }
    .pay-btn:active { transform: translateY(0); }
    .pay-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .cancel-link {
      display: block;
      text-align: center;
      margin-top: 14px;
      color: #0070e0;
      font-size: 0.85rem;
      font-weight: 600;
      text-decoration: none;
    }

    .cancel-link:hover { text-decoration: underline; }

    /* ── Processing overlay ── */
    .processing-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 100;
      align-items: center;
      justify-content: center;
    }

    .processing-overlay.show { display: flex; }

    .processing-card {
      background: white;
      border-radius: 24px;
      padding: 2.5rem 2rem;
      text-align: center;
      width: 280px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .spinner {
      width: 60px; height: 60px;
      border: 5px solid #e0edfc;
      border-top-color: #0070e0;
      border-radius: 50%;
      animation: spin 0.8s linear infinite;
      margin: 0 auto 1.2rem;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .processing-title {
      font-size: 1rem;
      font-weight: 800;
      color: #1a1a1a;
      margin-bottom: 6px;
    }

    .processing-sub {
      font-size: 0.8rem;
      color: #888;
    }

    /* ── Success overlay ── */
    .success-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 101;
      align-items: center;
      justify-content: center;
    }

    .success-overlay.show { display: flex; }

    .success-card {
      background: white;
      border-radius: 24px;
      padding: 2.5rem 2rem;
      text-align: center;
      width: 280px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }

    .success-icon {
      width: 72px; height: 72px;
      background: #0070e0;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      margin: 0 auto 1rem;
      animation: popIn 0.4s cubic-bezier(0.34,1.56,0.64,1);
    }

    @keyframes popIn {
      from { transform: scale(0); opacity: 0; }
      to   { transform: scale(1); opacity: 1; }
    }

    .success-title {
      font-size: 1.1rem;
      font-weight: 800;
      color: #1a1a1a;
      margin-bottom: 4px;
    }

    .success-amount {
      font-size: 1.5rem;
      font-weight: 800;
      color: #0070e0;
      margin: 6px 0;
    }

    .success-ref {
      font-size: 0.72rem;
      color: #888;
      margin-bottom: 1.2rem;
    }

    /* ── Demo ribbon ── */
    .demo-ribbon {
      background: #fef3c7;
      border-bottom: 1px solid #fcd34d;
      padding: 6px 16px;
      font-size: 0.72rem;
      color: #92400e;
      text-align: center;
      font-weight: 600;
    }
  </style>
</head>
<body>

<!-- Processing overlay -->
<div class="processing-overlay" id="processingOverlay">
  <div class="processing-card">
    <div class="spinner"></div>
    <div class="processing-title">Processing Payment</div>
    <div class="processing-sub">Please don't close this window…</div>
  </div>
</div>

<!-- Success overlay -->
<div class="success-overlay" id="successOverlay">
  <div class="success-card">
    <div class="success-icon">✓</div>
    <div class="success-title">Payment Successful!</div>
    <div class="success-amount">₱<?= $amountFormatted ?></div>
    <div class="success-ref">Ref: <?= $fakeRef ?></div>
    <div style="font-size:0.78rem; color:#888;">Redirecting you back…</div>
  </div>
</div>

<!-- GCash Phone UI -->
<div class="phone-shell">

  <div class="demo-ribbon">
    🎓 DEMO MODE · Simulated GCash Payment · For School Project Only
  </div>

  <!-- Header -->
  <div class="gcash-header">
    <div class="gcash-logo">
      <div class="gcash-logo-icon">G</div>
      <div class="gcash-logo-text">GCash</div>
    </div>
    <div class="gcash-header-label">Pay to merchant</div>
    <div class="gcash-merchant">☕ Oli's SelfieTea & Coffee</div>
    <div class="gcash-ref">Ref: <?= htmlspecialchars($reference) ?></div>
  </div>

  <!-- Amount -->
  <div class="amount-section">
    <div class="amount-label">Amount to Pay</div>
    <div class="amount-value">
      <span class="amount-currency">₱</span><?= $amountFormatted ?>
    </div>
  </div>

  <!-- Body -->
  <div class="gcash-body">

    <!-- Payment details -->
    <div class="detail-row">
      <span class="detail-row-label">Payment for</span>
      <span class="detail-row-value"><?= htmlspecialchars($reference) ?></span>
    </div>

    <div class="detail-row">
      <span class="detail-row-label">Customer</span>
      <span class="detail-row-value"><?= $userName ?></span>
    </div>

    <div class="detail-row">
      <span class="detail-row-label">Payment method</span>
      <span class="detail-row-value" style="color:#0070e0;">
        <i class="bi bi-wallet2 me-1"></i>GCash Wallet
      </span>
    </div>

    <!-- Fake balance -->
    <div class="balance-section">
      <div class="balance-top">
        <span class="balance-label">GCash Balance</span>
        <div class="balance-icon"><i class="bi bi-wallet2"></i></div>
      </div>
      <div class="balance-amount">₱1,500.00</div>
      <div class="balance-sub">Sufficient balance · Payment will be deducted</div>
    </div>

    <!-- Secure badge -->
    <div class="secure-badge">
      <i class="bi bi-shield-fill-check" style="color:#0070e0;"></i>
      Secured by GCash · 256-bit encryption
    </div>

  </div>

  <!-- Footer with Pay button -->
  <div class="gcash-footer">
    <form method="POST" id="payForm">
      <input type="hidden" name="confirm_pay" value="1">
      <button type="submit" class="pay-btn" id="payBtn" onclick="startPayment(event)">
        Pay ₱<?= $amountFormatted ?>
      </button>
    </form>
    <a href="book_reservation.php" class="cancel-link">Cancel payment</a>
  </div>

</div>

<script>
function startPayment(e) {
  e.preventDefault();

  const btn = document.getElementById('payBtn');
  btn.disabled = true;
  btn.textContent = 'Processing…';

  // Show processing overlay
  document.getElementById('processingOverlay').classList.add('show');

  // After 2s, show success overlay
  setTimeout(() => {
    document.getElementById('processingOverlay').classList.remove('show');
    document.getElementById('successOverlay').classList.add('show');
  }, 2000);

  // After 3.5s, submit the form for real (updates DB + redirects)
  setTimeout(() => {
    document.getElementById('payForm').submit();
  }, 3500);
}
</script>
</body>
</html>