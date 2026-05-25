<?php
// chatbot_api.php - Server-side proxy for OpenRouter API
session_start();
require_once 'includes/Auth.php';
Auth::requireLogin();

header('Content-Type: application/json');

$apiKey = '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';

$systemPrompt = "You are Oli Bot, a friendly and helpful AI assistant for Oli's SelfieTea & Coffee — a cozy coffee shop established in 2019.

Your job is to help customers with:
- Menu inquiries and recommendations
- Pricing information
- Reservation questions
- General shop information

Here is the FULL MENU:

SNACKS:
- Nachos — ₱198
- Chicken Fingers — ₱189
- Cheesy Bacon Fries — ₱198
- Fish & Fries — ₱198
- Flavored Fries (Barbecue/Cheese/Sour Cream) — ₱159
- Flavored Mojos (Barbecue/Cheese/Sour Cream) — ₱189

PASTA (Served with Garlic Bread):
- Gourmet Tuyo Pasta — ₱189
- Alfredo (White Sauce) — ₱194
- Meat Sauce Spaghetti — ₱189
- Lasagna — ₱194
- Aligue Pasta — ₱189
- Shrimp Aglio Olio — ₱194
- Chicken Oriental Pasta — ₱189

BURGERS/SANDWICHES (Served with Fries):
- Pulled Pork BBQ — ₱189
- Dori Fish Burger — ₱189
- Cheeseburger — ₱194
- Bacon Cheeseburger — ₱209
- Crispy Chicken Burger — ₱194
- Clubhouse Sandwich — ₱194

MAIN MENU - FOR SHARING (Flavored Boneless Chicken Bites):
- Yangnyeom (Spicy Korean) — ₱279
- Garlic Parmesan — ₱279
- Hickory Barbecue — ₱279
- Spicy Salted Egg — ₱279

MAIN MENU - RICE MEAL (Served with buttered vegetables):
- Chicken Fingers w/ Rice — ₱189
- Burger Steak w/ Egg — ₱194
- 2pcs Grilled Porkchop w/ Mushroom Gravy — ₱214
- Chicken Fillet Ala King — ₱199
- Breaded Porkchop w/ Egg — ₱194
- Fish Fillet w/ Rice in Tartar Sauce — ₱194
- Flavored Chicken Bites w/ Rice — ₱199
- 4pcs Chicken Wings w/ Rice — ₱199

CHICKEN WINGS:
- Yangnyeom/Garlic Parmesan/Hickory BBQ/Spicy Salted Egg — 6pcs: ₱239 | 12pcs: ₱459

SALADS/HEALTHY OPTIONS:
- Macaroni Salad — ₱169
- Kani Salad (Lettuce, Cucumber, Carrots, Mango, Crab Sticks, Roasted Sesame dressing) — ₱189
- Chicken Caesar Salad (Romaine Lettuce, Chicken breast, Croutons, Parmesan, Caesar dressing, bacon bits) — ₱209

PIZZA (New York Style):
Classic (12\"/16\"): All Cheese ₱329/₱449 | American Ham & Cheese ₱349/₱469 | Hawaiian ₱359/₱479
Premium (12\"/16\"): NY Pepperoni ₱389/₱499 | Hawaiian Supreme ₱399/₱509 | All Meat ₱399/₱509 | NY Special ₱399/₱509 | Carbonara ₱399/₱509 | Pulled Pork BBQ ₱399/₱509
Latest Special (12\"/16\"): 4 Cheese Pizza ₱409/₱529 | Garlic Shrimp Pizza ₱409/₱529
Add-ons: Mozzarella/Pepperoni/American Ham/Bacon ₱60 | Pineapple ₱30

DRINKS:
Artisan Tea (16oz/22oz, Free Pearl Sinker): Pearl Milk Tea ₱95/₱105 | Earl Grey ₱105/₱115 | Ceylon ₱105/₱115 | Sun Moon ₱105/₱115 | Jasmine ₱105/₱115 | Cookies & Cream ₱105/₱115
Milk Tea (16oz/22oz): Wintermelon/Okinawa/Taro ₱85/₱95 | Dark Choco/Red Velvet/Matcha/Brown Sugar ₱95/₱105
Hot Tea (12oz/16oz): Earl Grey/Ceylon/Sun Moon/Jasmine ₱95/₱105
Cheesecake series (16oz/22oz): Classic/Earl Grey/Sun Moon/Red Velvet/Dark Choco/Oreo/Okinawa/Taro/Matcha ₱125/₱140
Rock Salt & Cheese (16oz/22oz): Classic/Earl Grey/SunMoon/Okinawa/Dark Choco ₱125/₱140
Hot Drinks (12oz/16oz): Americano ₱105/₱120 | Latte ₱120/₱135 | Cappuccino ₱120/₱135 | Hot Choco ₱125/₱140 | Green Tea Latte ₱125/₱140 | Mocha/Caramel Macchiato/Hazelnut/Vanilla Latte ₱130/₱145
Iced Drinks (16oz/22oz): Iced Americano ₱105/₱120 | Iced Latte ₱125/₱140 | and many more ₱130/₱145
Ice Blended (16oz/22oz): Choco Chip Mocha/Mocha/Espresso Hazelnut/Caramel/Java Chip ₱130/₱145 | Coffee Jelly Frappe/Dark Choco Espresso ₱135/₱150
Cream Based (16oz/22oz): Chocolate/Vanilla Milkshake ₱115/₱130 | Various milkshakes ₱125/₱140
Add-ons: Selfie Print ₱20 | Syrup ₱20 | Extra shot ₱30 | Cheesecake/RSC ₱30 | Pearl ₱15 | Nata/Fruit Jelly ₱15 | Coffee Jelly ₱20 | Popping Boba ₱20

RESERVATIONS:
- 2nd floor only
- 4 tables, 5 seats each (max 20 pax per time slot)
- ₱100 non-refundable reservation fee
- Pay via GCash or Cash on arrival
- Advance booking required (at least 1 day ahead)

STORE INFO:
- Name: Oli's SelfieTea & Coffee
- Established: 2019
- GCash payment accepted

Be warm, helpful, and concise. Use emojis occasionally to be friendly. If you are unsure about something not in this info, politely say you are not sure and suggest they contact the shop directly.";

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Parse incoming request
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['messages']) || !is_array($input['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

// Build messages array (OpenRouter uses same format as OpenAI)
$messages = [
    ['role' => 'system', 'content' => $systemPrompt]
];

foreach ($input['messages'] as $msg) {
    if (empty($msg['role']) || empty($msg['content'])) continue;
    if (!in_array($msg['role'], ['user', 'assistant'])) continue;
    $messages[] = [
        'role'    => $msg['role'],
        'content' => substr(strip_tags($msg['content']), 0, 2000),
    ];
}

if (count($messages) <= 1) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid messages provided']);
    exit();
}

// Call OpenRouter API
$payload = json_encode([
    'model'    => 'openrouter/free',
    'messages' => $messages,
    'max_tokens' => 800,
]);

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: http://localhost/olis_coffee',
        'X-Title: Olis SelfieTea Chatbot',
    ],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed. Please try again.']);
    exit();
}

$data  = json_decode($response, true);
$reply = $data['choices'][0]['message']['content'] ?? null;

if ($httpCode !== 200 || !$reply) {
    http_response_code(500);
    $errMsg = $data['error']['message'] ?? 'API error. Please try again.';
    echo json_encode(['error' => $errMsg]);
    exit();
}

echo json_encode(['reply' => $reply]);