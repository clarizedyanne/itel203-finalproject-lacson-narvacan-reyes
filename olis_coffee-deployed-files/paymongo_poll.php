<?php
// paymongo_poll.php - AJAX endpoint polled by paymongo_wait.php every 5 seconds
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';
require_once 'includes/paymongo.php';

Auth::requireLogin();
header('Content-Type: application/json');

$linkId   = $_GET['link_id']   ?? '';
$type     = $_GET['type']      ?? '';
$recordId = intval($_GET['record_id'] ?? 0);

if (!$linkId || !$type || !$recordId) {
    echo json_encode(['paid' => false, 'failed' => true]);
    exit();
}

// Check payment status with PayMongo API
// Using file_get_contents instead of curl (curl blocked on some hosts)
$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'header'  => implode("\r\n", [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ]),
        'ignore_errors' => true,
    ],
    'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
    ],
]);
$response = @file_get_contents(PAYMONGO_BASE_URL . '/links/' . $linkId, false, $context);
if ($response === false) {
    // fallback to curl if file_get_contents fails
    $ch = curl_init(PAYMONGO_BASE_URL . '/links/' . $linkId);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
}

$data   = json_decode($response, true);
$status = $data['data']['attributes']['status'] ?? '';

if ($status !== 'paid') {
    echo json_encode(['paid' => false, 'failed' => false]);
    exit();
}

// Payment confirmed — update DB
$db = getDB();

if ($type === 'reservation') {
    // Extract the payment_id from the link's payments array for future refunds
    $paymentId = $data['data']['attributes']['payments'][0]['id'] ?? null;
    $stmt = $db->prepare("UPDATE reservations SET payment_status='paid', paymongo_payment_id=? WHERE id=?");
    $stmt->bind_param("si", $paymentId, $recordId);
    $stmt->execute();

    // Fetch reservation details for confirmation page
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

    echo json_encode(['paid' => true, 'redirect' => 'reservation_confirmation.php']);
    exit();
}

if ($type === 'order') {
    $paymentId = $data['data']['attributes']['payments'][0]['id'] ?? null;
    $stmt = $db->prepare("UPDATE orders SET payment_status='paid', paymongo_payment_id=? WHERE id=?");
    $stmt->bind_param("si", $paymentId, $recordId);
    $stmt->execute();

    $stmt2 = $db->prepare("UPDATE reservations SET payment_status='paid', paymongo_payment_id=? WHERE order_id=?");
    $stmt2->bind_param("si", $paymentId, $recordId);
    $stmt2->execute();

    // Restore order_success session
    $pending = $_SESSION['paymongo_pending'] ?? null;
    if ($pending && !empty($pending['order_success'])) {
        $pending['order_success']['payment_status'] = 'paid';
        $_SESSION['order_success'] = $pending['order_success'];
    }
    unset($_SESSION['paymongo_pending']);

    echo json_encode(['paid' => true, 'redirect' => 'order_confirmation.php']);
    exit();
}

echo json_encode(['paid' => false, 'failed' => true]);