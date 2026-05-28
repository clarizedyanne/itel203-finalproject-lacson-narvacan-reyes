<?php
// paymongo_return.php
// PayMongo redirects here after payment (success or failed).
// We update the DB and redirect to the appropriate confirmation page.
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';
require_once 'includes/paymongo.php';

Auth::requireLogin();

$db = getDB();

// Get pending session
$pending    = $_SESSION['paymongo_pending']     ?? null;
$pendingRes = $_SESSION['paymongo_pending_res'] ?? null;

if ($pending) {
    $linkId   = $pending['link_id']   ?? '';
    $recordId = $pending['record_id'] ?? 0;
    $type     = 'order';
} elseif ($pendingRes) {
    $linkId   = $pendingRes['link_id']   ?? '';
    $recordId = $pendingRes['record_id'] ?? 0;
    $type     = 'reservation';
} else {
    header('Location: book_reservation.php');
    exit();
}

// Check payment status via PayMongo API
$paid      = false;
$paymentId = null;

// Try file_get_contents first (works on some hosts that block curl)
$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => "Accept: application/json\r\nAuthorization: Basic " . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        'ignore_errors' => true,
        'timeout' => 10,
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
]);
$response = @file_get_contents(PAYMONGO_BASE_URL . '/links/' . $linkId, false, $context);

// Fallback to curl
if (!$response) {
    $ch = curl_init(PAYMONGO_BASE_URL . '/links/' . $linkId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
}

if ($response) {
    $data   = json_decode($response, true);
    $status = $data['data']['attributes']['status'] ?? '';
    if ($status === 'paid') {
        $paid      = true;
        $paymentId = $data['data']['attributes']['payments'][0]['id'] ?? null;
    }
}

if (!$paid) {
    // Payment not confirmed — go back to waiting page
    header('Location: paymongo_wait.php');
    exit();
}

// Payment confirmed — update DB and redirect
if ($type === 'order') {
    $stmt = $db->prepare("UPDATE orders SET payment_status='paid', paymongo_payment_id=? WHERE id=?");
    $stmt->bind_param("si", $paymentId, $recordId);
    $stmt->execute();

    $stmt2 = $db->prepare("UPDATE reservations SET payment_status='paid', paymongo_payment_id=? WHERE order_id=?");
    $stmt2->bind_param("si", $paymentId, $recordId);
    $stmt2->execute();

    $p = $_SESSION['paymongo_pending'] ?? null;
    if ($p && !empty($p['order_success'])) {
        $p['order_success']['payment_status'] = 'paid';
        $_SESSION['order_success'] = $p['order_success'];
    }
    unset($_SESSION['paymongo_pending']);

    header('Location: order_confirmation.php');
    exit();
}

if ($type === 'reservation') {
    $stmt = $db->prepare("UPDATE reservations SET payment_status='paid', paymongo_payment_id=? WHERE id=?");
    $stmt->bind_param("si", $paymentId, $recordId);
    $stmt->execute();

    $resStmt = $db->prepare("SELECT res_date, res_time, pax, payment_method FROM reservations WHERE id=?");
    $resStmt->bind_param("i", $recordId);
    $resStmt->execute();
    $resRow = $resStmt->get_result()->fetch_assoc();

    $_SESSION['reservation_success'] = [
        'reservation_id' => $recordId,
        'res_date'       => $resRow['res_date'] ?? '',
        'res_time'       => $resRow['res_time'] ?? '',
        'pax'            => $resRow['pax'] ?? '',
        'payment_method' => $resRow['payment_method'] ?? 'gcash',
    ];
    unset($_SESSION['paymongo_pending_res']);

    header('Location: reservation_confirmation.php');
    exit();
}

header('Location: book_reservation.php');
exit();