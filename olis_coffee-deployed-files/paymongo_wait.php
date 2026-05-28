<?php
// paymongo_wait.php
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';
require_once 'includes/paymongo.php';

Auth::requireLogin();

$pending    = $_SESSION['paymongo_pending']     ?? null;
$pendingRes = $_SESSION['paymongo_pending_res'] ?? null;

if ($pending) {
    $linkId      = $pending['link_id']      ?? '';
    $recordId    = $pending['record_id']    ?? 0;
    $type        = 'order';
    $checkoutUrl = $pending['checkout_url'] ?? '';
} elseif ($pendingRes) {
    $linkId      = $pendingRes['link_id']      ?? '';
    $recordId    = $pendingRes['record_id']    ?? 0;
    $type        = 'reservation';
    $checkoutUrl = $pendingRes['checkout_url'] ?? '';
} else {
    header('Location: book_reservation.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <link rel="icon" type="image/png" href="assets/logo.png">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Waiting for Payment – Oli's SelfieTea & Coffee</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
  :root { --green:#2d5a27; }
  body  { background:#f8f5f0; font-family:'Segoe UI',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .card { border:none; border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.08); max-width:400px; width:100%; padding:2.5rem 2rem; text-align:center; }
  .spinner-border { width:2.5rem; height:2.5rem; border-width:.25em; color:var(--green); }
  .btn-pay { background:var(--green); color:white; border:none; border-radius:10px; padding:.6rem 1.8rem; font-weight:600; text-decoration:none; display:inline-block; margin-top:.5rem; }
  .btn-pay:hover { background:#1e3d1a; color:white; }
  .btn-back { color:#6b7280; font-size:.85rem; text-decoration:none; display:inline-block; margin-top:1rem; }
  .btn-back:hover { color:#374151; }
  .timer { color:#9ca3af; font-size:.82rem; margin-top:1rem; }
</style>
</head>
<body>
<div class="card">
  <img src="assets/logo.png" alt="Oli's" style="height:55px;object-fit:contain;margin-bottom:1.5rem;">

  <!-- Waiting -->
  <div id="waiting">
    <div class="spinner-border mb-3" role="status"></div>
    <h6 class="fw-bold mb-1">Waiting for payment…</h6>
    <p class="text-muted mb-3" style="font-size:.88rem;">Complete your GCash payment in the tab that opened.</p>
    <a href="<?= htmlspecialchars($checkoutUrl) ?>" target="_blank" rel="noopener" class="btn-pay">
      Open Payment Page
    </a>
    <div class="timer">Checking in <span id="countdown">10</span>s…</div>
  </div>

  <!-- Success -->
  <div id="success" class="d-none">
    <div style="font-size:3rem;">✅</div>
    <h6 class="fw-bold text-success mt-2 mb-1">Payment received!</h6>
    <p class="text-muted" style="font-size:.88rem;">Redirecting you now…</p>
  </div>

  <!-- Failed -->
  <div id="failed" class="d-none">
    <div style="font-size:3rem;">❌</div>
    <h6 class="fw-bold mt-2 mb-1">Payment not completed</h6>
    <p class="text-muted mb-3" style="font-size:.88rem;">You can try again or pay cash on arrival.</p>
    <a href="<?= htmlspecialchars($checkoutUrl) ?>" target="_blank" rel="noopener" class="btn-pay">Try Again</a>
    <br><a href="book_reservation.php" class="btn-back">← Back to Reservations</a>
  </div>
</div>

<script>
const LINK_ID      = <?= json_encode($linkId) ?>;
const TYPE         = <?= json_encode($type) ?>;
const RECORD_ID    = <?= json_encode($recordId) ?>;
const CHECKOUT_URL = <?= json_encode($checkoutUrl) ?>;

let attempts = 0;
const MAX_ATT = 72;
let countdownVal, countdownTimer;

function tick() {
  countdownVal--;
  document.getElementById('countdown').textContent = countdownVal;
  if (countdownVal <= 0) { clearInterval(countdownTimer); checkPayment(); }
}

function startCountdown(secs) {
  clearInterval(countdownTimer);
  countdownVal = secs;
  document.getElementById('countdown').textContent = secs;
  countdownTimer = setInterval(tick, 1000);
}

function checkPayment() {
  attempts++;
  fetch('paymongo_poll.php?link_id=' + encodeURIComponent(LINK_ID)
      + '&type=' + encodeURIComponent(TYPE)
      + '&record_id=' + encodeURIComponent(RECORD_ID))
    .then(r => r.json())
    .then(data => {
      if (data.paid) {
        document.getElementById('waiting').classList.add('d-none');
        document.getElementById('success').classList.remove('d-none');
        setTimeout(() => { window.location.href = data.redirect; }, 1800);
      } else if (data.failed || attempts >= MAX_ATT) {
        document.getElementById('waiting').classList.add('d-none');
        document.getElementById('failed').classList.remove('d-none');
      } else {
        startCountdown(5);
      }
    })
    .catch(() => {
      if (attempts < MAX_ATT) startCountdown(5);
      else {
        document.getElementById('waiting').classList.add('d-none');
        document.getElementById('failed').classList.remove('d-none');
      }
    });
}

window.addEventListener('load', () => {
  window.open(CHECKOUT_URL, '_blank', 'noopener');
  startCountdown(10);
});
</script>
</body>
</html>