<?php
// includes/paymongo.php
define('PAYMONGO_SECRET_KEY', 'sk_test_YBtZHwubHcF1aXtmhscNAAxh');
define('PAYMONGO_BASE_URL',   'https://api.paymongo.com/v1');

/**
 * Create a PayMongo Payment Link.
 * Returns ['checkout_url' => ..., 'link_id' => ...] or null on failure.
 */
function paymongo_create_link(float $amount, string $description, string $successUrl): ?array
{
    $payload = [
        'data' => [
            'attributes' => [
                'amount'      => (int) round($amount * 100),
                'currency'    => 'PHP',
                'description' => $description,
                'remarks'     => "Oli's SelfieTea & Coffee",
                'redirect'    => [
                    'success' => $successUrl,
                    'failed'  => $successUrl,
                ],
                'redirect'    => [
                    'success' => $successUrl,
                    'failed'  => $successUrl,
                ],
            ]
        ]
    ];

    $ch = curl_init(PAYMONGO_BASE_URL . '/links');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("PayMongo create_link failed ($httpCode): $response");
        return null;
    }

    $data = json_decode($response, true);
    $checkoutUrl = $data['data']['attributes']['checkout_url'] ?? null;
    $linkId      = $data['data']['id'] ?? null;

    if (!$checkoutUrl || !$linkId) return null;

    return ['checkout_url' => $checkoutUrl, 'link_id' => $linkId];
}

/**
 * Issue a full refund on a PayMongo payment.
 * $paymentId  - the pay_xxx ID stored at confirmation time
 * $amountCents - amount in centavos (e.g. 10000 = PHP 100)
 * $reason     - 'others' | 'duplicate' | 'fraudulent'
 * Returns true on success, false on failure.
 */
function paymongo_refund(string $paymentId, int $amountCents, string $reason = 'others'): bool
{
    $payload = [
        'data' => [
            'attributes' => [
                'amount'     => $amountCents,
                'payment_id' => $paymentId,
                'reason'     => $reason,
                'notes'      => "Oli's reservation cancellation refund",
            ]
        ]
    ];

    $ch = curl_init(PAYMONGO_BASE_URL . '/refunds');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':'),
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("PayMongo refund failed ($httpCode): $response");
        return false;
    }

    return true;
}