<?php
// includes/mailer.php - PHPMailer helper for Oli's SelfieTea & Coffee

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('SMTP_USER', 'ayis9626@gmail.com');
define('SMTP_PASS', 'qjkx hxsb utyd vamw');
define('SMTP_FROM_NAME', "Oli's SelfieTea & Coffee");

function getMail(): PHPMailer
{
    $autoload = __DIR__ . '/../vendor/autoload.php';
    $manual   = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';

    if (file_exists($autoload)) {
        require_once $autoload;
    } elseif (is_dir($manual)) {
        require_once $manual . 'Exception.php';
        require_once $manual . 'PHPMailer.php';
        require_once $manual . 'SMTP.php';
    } else {
        throw new Exception('PHPMailer not found.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
    $mail->isHTML(true);
    return $mail;
}

// ── RESERVATION CONFIRMATION EMAIL ───────────────────────────────────────────
function sendReservationEmail(array $to, array $details): bool
{
    try {
        $mail = getMail();
        $mail->addAddress($to['email'], $to['name']);
        $mail->Subject = "☕ Reservation Received – Oli's SelfieTea & Coffee";

        $name    = htmlspecialchars($to['name']);
        $resNum  = str_pad($details['id'], 5, '0', STR_PAD_LEFT);
        $resDate = date('F j, Y', strtotime($details['res_date']));
        $resTime = date('g:i A', strtotime($details['res_time']));
        $pax     = $details['pax'];
        $payment = ucfirst($details['payment_method']);

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;border-radius:12px;overflow:hidden;border:1px solid #e0e0e0;'>
          <div style='background:#2d4a1e;padding:28px 24px;text-align:center;'>
            <p style='color:#c8e6c9;font-size:13px;letter-spacing:2px;margin:0 0 6px;text-transform:uppercase;'>Oli's SelfieTea & Coffee</p>
            <h2 style='color:#ffffff;margin:0;font-size:22px;'>🎉 Reservation Received!</h2>
          </div>
          <div style='padding:28px 24px;background:#ffffff;'>
            <p style='color:#333;margin-top:0;'>Hi <strong>{$name}</strong>,</p>
            <p style='color:#555;line-height:1.6;'>Your reservation has been submitted and is pending admin confirmation. Here are your details:</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;'>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Reservation #</td><td style='padding:10px 4px;font-weight:700;color:#2d4a1e;'>{$resNum}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Date</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$resDate}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Time</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$resTime}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Guests (Pax)</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$pax}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Payment Method</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$payment}</td></tr>
              <tr><td style='padding:10px 4px;color:#888;'>Reservation Fee</td><td style='padding:10px 4px;font-weight:700;color:#8B4513;'>₱100.00</td></tr>
            </table>
            <div style='background:#f0fdf4;border-radius:10px;padding:14px 16px;margin-top:16px;font-size:13px;color:#2d4a1e;line-height:1.7;'>
              <strong>📌 What's next?</strong><br>
              Admin will confirm your slot. Please arrive 15 minutes before your scheduled time.
            </div>
          </div>
          <div style='background:#f8f8f8;padding:16px 24px;text-align:center;font-size:12px;color:#999;'>
            Oli's SelfieTea & Coffee · Est. 2019 · Questions? Message us on Facebook.
          </div>
        </div>";

        $mail->AltBody = "Hi {$name}, your reservation #{$resNum} on {$resDate} at {$resTime} for {$pax} guest(s) has been received. Reservation fee: ₱100.00 via {$payment}.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Reservation email error: ' . $e->getMessage());
        return false;
    }
}

// ── ORDER CONFIRMATION EMAIL ──────────────────────────────────────────────────
function sendOrderEmail(array $to, array $details): bool
{
    try {
        $mail = getMail();
        $mail->addAddress($to['email'], $to['name']);
        $mail->Subject = "☕ Order Placed – Oli's SelfieTea & Coffee";

        $name      = htmlspecialchars($to['name']);
        $ordNum    = str_pad($details['order_id'], 5, '0', STR_PAD_LEFT);
        $resDate   = date('F j, Y', strtotime($details['res_date']));
        $resTime   = date('g:i A', strtotime($details['res_time']));
        $payment   = ucfirst($details['pay_method']);
        $total     = number_format($details['total'], 2);

        $mail->Body = "
        <div style='font-family:Arial,sans-serif;max-width:520px;margin:auto;border-radius:12px;overflow:hidden;border:1px solid #e0e0e0;'>
          <div style='background:#2d4a1e;padding:28px 24px;text-align:center;'>
            <p style='color:#c8e6c9;font-size:13px;letter-spacing:2px;margin:0 0 6px;text-transform:uppercase;'>Oli's SelfieTea & Coffee</p>
            <h2 style='color:#ffffff;margin:0;font-size:22px;'>🎉 Order Placed!</h2>
          </div>
          <div style='padding:28px 24px;background:#ffffff;'>
            <p style='color:#333;margin-top:0;'>Hi <strong>{$name}</strong>,</p>
            <p style='color:#555;line-height:1.6;'>Your advance order has been received. Here's your order summary:</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:14px;'>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Order #</td><td style='padding:10px 4px;font-weight:700;color:#2d4a1e;'>{$ordNum}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Visit Date</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$resDate}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Visit Time</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$resTime}</td></tr>
              <tr style='border-bottom:1px solid #f0f0f0;'><td style='padding:10px 4px;color:#888;'>Payment</td><td style='padding:10px 4px;font-weight:700;color:#333;'>{$payment}</td></tr>
              <tr><td style='padding:10px 4px;color:#888;'>Total Amount</td><td style='padding:10px 4px;font-weight:700;color:#8B4513;'>₱{$total}</td></tr>
            </table>
            <div style='background:#f0fdf4;border-radius:10px;padding:14px 16px;margin-top:16px;font-size:13px;color:#2d4a1e;line-height:1.7;'>
              <strong>📌 What's next?</strong><br>
              Admin will review and confirm your reservation and order. Your food will be ready when you arrive!
              Please arrive 15 minutes early ahead of your reserved time.
            </div>
          </div>
          <div style='background:#f8f8f8;padding:16px 24px;text-align:center;font-size:12px;color:#999;'>
            Oli's SelfieTea & Coffee · Est. 2019 · Questions? Message us on Facebook.
          </div>
        </div>";

        $mail->AltBody = "Hi {$name}, your order #{$ordNum} on {$resDate} at {$resTime} has been placed. Total: ₱{$total} via {$payment}.";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Order email error: ' . $e->getMessage());
        return false;
    }
}