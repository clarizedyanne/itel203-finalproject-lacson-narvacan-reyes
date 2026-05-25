<?php
// paymongo_return.php
// PayMongo redirects here after the customer completes (or cancels) payment.
// We verify the payment link status via API, then update the DB.

session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';
require_once 'includes/paymongo.php';

Auth::requireLogin();

$db      = getDB();
$linkId  = $_GET['link_id']  ?? '';   // we pass this in the success URL
$type    = $_GET['type']     ?? '';   // 'reservation' or 'order'
$recId   = intval($_GET['record_id'] ?? 0);

// Fallback: if link_id is missing or was a placeholder, read from session
if ((!$linkId || $linkId === 'PENDING') && !empty($_SESSION['paymongo_pending']['link_id'])) {
    $linkId = $_SESSION['paymongo_pending']['link_id'];
}
if ((!$linkId || $linkId === 'PENDING') && !empty($_SESSION['paymongo_pending_res']['link_id'])) {
    $linkId = $_SESSION['paymongo_pending_res']['link_id'];
}
if (!$recId && !empty($_SESSION['paymongo_pending']['record_id'])) {
    $recId = intval($_SESSION['paymongo_pending']['record_id']);
}
if (!$recId && !empty($_SESSION['paymongo_pending_res']['record_id'])) {
    $recId = intval($_SESSION['paymongo_pending_res']['record_id']);
}

// ── Verify payment status with PayMongo API ───────────────────────────────────
$paid = false;

if ($linkId) {
    $ch = curl_init(PAYMONGO_BASE_URL . '/links/' . $linkId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data   = json_decode($response, true);
    $status = $data['data']['attributes']['status'] ?? '';

    // 'paid' means at least one payment was made
    if ($status === 'paid') {
        $paid = true;
    }
}

// ── Update DB if paid ─────────────────────────────────────────────────────────
if ($paid) {
    if ($type === 'reservation') {
        $stmt = $db->prepare("UPDATE reservations SET payment_status='paid' WHERE id=?");
        $stmt->bind_param("i", $recId);
        $stmt->execute();

        unset($_SESSION['paymongo_pending_res']);

        header("Location: book_reservation.php?paid=1");
        exit();

    } elseif ($type === 'order') {
        $stmt = $db->prepare("UPDATE orders SET payment_status='paid' WHERE id=?");
        $stmt->bind_param("i", $recId);
        $stmt->execute();

        $stmt2 = $db->prepare("UPDATE reservations SET payment_status='paid' WHERE order_id=?");
        $stmt2->bind_param("i", $recId);
        $stmt2->execute();

        // Restore order_success session for confirmation page
        $pending = $_SESSION['paymongo_pending'] ?? null;
        if ($pending && !empty($pending['order_success'])) {
            $pending['order_success']['payment_status'] = 'paid';
            $_SESSION['order_success'] = $pending['order_success'];
        }
        unset($_SESSION['paymongo_pending']);

        header("Location: order_confirmation.php");
        exit();
    }
}

// ── Payment not confirmed (cancelled or still pending) ────────────────────────
unset($_SESSION['paymongo_pending']);
unset($_SESSION['paymongo_pending_res']);

// Redirect to reservation page with a failed/cancelled message
header("Location: book_reservation.php?payment=failed");
exit();