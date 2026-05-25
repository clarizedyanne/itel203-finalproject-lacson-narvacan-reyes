<?php
// check_availability.php - AJAX endpoint: seat availability per time slot for a given date
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';

Auth::requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty($_GET['date'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing date']);
    exit();
}

$date = $_GET['date'];
// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid date']);
    exit();
}

$db = getDB();

// Sum actual pax per time slot for this date (only non-cancelled)
// Max 20 seats per slot (4 tables x 5 seats each)
$stmt = $db->prepare("
    SELECT res_time, COALESCE(SUM(pax), 0) as booked_seats
    FROM reservations
    WHERE res_date = ? AND status != 'cancelled'
    GROUP BY res_time
");
$stmt->bind_param("s", $date);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Build a map: time => seats booked
$bookedMap = [];
foreach ($rows as $row) {
    // Normalize time to H:i (strip seconds if present)
    $t = substr($row['res_time'], 0, 5);
    $bookedMap[$t] = (int)$row['booked_seats'];
}

$maxSeats = 20;
$allSlots = ['11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00'];

$result = [];
foreach ($allSlots as $slot) {
    $booked    = $bookedMap[$slot] ?? 0;
    $available = $maxSeats - $booked;
    $result[$slot] = [
        'booked'    => $booked,
        'available' => max(0, $available),
        'full'      => $available <= 0,
    ];
}

echo json_encode($result);