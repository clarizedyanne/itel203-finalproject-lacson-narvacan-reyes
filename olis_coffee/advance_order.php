<?php
// advance_order.php - Advance Order (only accessible from reservation flow)
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';

Auth::requireLogin();

// GUARD: must arrive from reservation flow
if (empty($_SESSION['advance_order'])) {
    header("Location: book_reservation.php");
    exit();
}

$db        = getDB();
$userId    = $_SESSION['user_id'];
$advData   = $_SESSION['advance_order'];
$resId     = $advData['reservation_id'];
$resDate   = $advData['res_date'];
$resTime   = $advData['res_time'];
$pax       = $advData['pax'];
$payMethod = $advData['payment_method'];

$message = '';
$msgType = 'success';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $cartJson  = $_POST['cart_data'] ?? '[]';
    $orderNote = trim($_POST['order_notes'] ?? '');
    $cart      = json_decode($cartJson, true);

    if (empty($cart)) {
        $message = 'Your cart is empty. Please add at least one item.';
        $msgType = 'danger';
    } else {
        // Calculate total
        $total = 0;
        foreach ($cart as $item) {
            $total += floatval($item['price']) * intval($item['qty']);
        }
        $total += 100; // ₱100 reservation fee included

        // Insert into orders table
        $ordStmt = $db->prepare("
            INSERT INTO orders (user_id, reservation_id, total_amount, status, payment_method, payment_status, notes)
            VALUES (?, ?, ?, 'pending', ?, 'unpaid', ?)
        ");
        $ordStmt->bind_param("iidss", $userId, $resId, $total, $payMethod, $orderNote);

      if ($ordStmt->execute()) {
          $orderId = $db->insert_id;

          // Keep existing order_items insert loop here (unchanged)
          $itemStmt = $db->prepare("
              INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price, subtotal)
              VALUES (?, ?, ?, ?, ?)
          ");
          $allOk = true;
          foreach ($cart as $item) {
              $menuId   = intval($item['id']);
              $qty      = intval($item['qty']);
              $price    = floatval($item['price']);
              $subtotal = $price * $qty;
              $itemStmt->bind_param("iiidd", $orderId, $menuId, $qty, $price, $subtotal);
              if (!$itemStmt->execute()) { $allOk = false; }
          }

          // Keep existing reservation link (unchanged)
          $linkStmt = $db->prepare("UPDATE reservations SET order_id = ? WHERE id = ?");
          $linkStmt->bind_param("ii", $orderId, $resId);
          $linkStmt->execute();

          unset($_SESSION['advance_order']);

          $_SESSION['order_success'] = [
              'order_id'   => $orderId,
              'total'      => $total,
              'res_date'   => $resDate,
              'res_time'   => $resTime,
              'pay_method' => $payMethod,
          ];

          // ── GCash: redirect to simulated GCash page ─────────────────────────────
          // GCash: send to PayMongo payment link
          if ($payMethod === 'gcash') {
              require_once 'includes/paymongo.php';
              $reference  = 'ORD-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
              $baseUrl    = (isset($_SERVER['HTTPS']) ? 'https' : 'http')
                          . '://' . $_SERVER['HTTP_HOST']
                          . dirname($_SERVER['REQUEST_URI']);
              $paymongoResult = paymongo_create_link((float) $total, "Oli's Order - $reference (incl. reservation fee)",
                  $baseUrl . '/paymongo_wait.php');

              if ($paymongoResult) {
                  // Save order_success and link_id so return page can verify and restore
                  $_SESSION['paymongo_pending'] = [
                      'order_success' => $_SESSION['order_success'],
                      'link_id'       => $paymongoResult['link_id'],
                      'record_id'     => $orderId,
                      'checkout_url'  => $paymongoResult['checkout_url'],
                  ];

                  // Go to our waiting page which polls PayMongo
                  header("Location: paymongo_wait.php");
                  exit();
              }
              // Fallback: go to confirmation unpaid
          }

          // Cash or GCash fallback
          header("Location: order_confirmation.php");
          exit();

      } else {
          $message = 'Error placing order. Please try again.';
          $msgType = 'danger';
      }
    }
}

// Fetch all available menu items grouped by category & subcategory
$menuResult = $db->query("
    SELECT id, name, category, subcategory, description, price, price_variant, image
    FROM menu_items
    WHERE is_available = 1
    ORDER BY category, subcategory, name, price
");
$menuItems = [];
while ($row = $menuResult->fetch_assoc()) {
    $menuItems[$row['category']][$row['subcategory']][] = $row;
}

$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Order in Advance – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ── Layout ── */
    .page-wrapper { display: flex; gap: 0; min-height: calc(100vh - 200px); }
    .menu-col {
      flex: 1;
      padding: 2rem;
      overflow-y: auto;
      max-height: calc(100vh - 160px);
      position: sticky;
      top: 0;
    }
    .cart-col {
      width: 340px;
      flex-shrink: 0;
      background: white;
      border-left: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: calc(100vh - 160px);
    }
    @media (max-width: 900px) {
      .page-wrapper { flex-direction: column; }
      .menu-col { max-height: none; padding: 1rem; }
      .cart-col { width: 100%; height: auto; border-left: none; border-top: 2px solid var(--green-mid); }
    }

    /* ── Category Nav ── */
    .cat-nav-adv {
      position: sticky;
      top: 0;
      z-index: 50;
      background: white;
      padding: 8px 0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.07);
      margin-bottom: 1.5rem;
    }
    .cat-tab-adv {
      display: inline-block;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 700;
      color: var(--green-dark);
      background: #f0fdf4;
      text-decoration: none;
      margin: 2px;
      transition: all 0.2s;
      border: 1.5px solid transparent;
    }
    .cat-tab-adv:hover, .cat-tab-adv.active {
      background: var(--green-dark);
      color: white;
    }

    /* ── Menu Cards ── */
    .menu-section-adv { margin-bottom: 2.5rem; }
    .section-heading {
      font-family: 'Playfair Display', serif;
      font-size: 1.3rem;
      color: var(--green-dark);
      font-weight: 700;
      border-left: 4px solid var(--gold);
      padding-left: 12px;
      margin-bottom: 1rem;
    }
    .subsection-heading {
      font-size: 0.75rem;
      font-weight: 700;
      color: var(--green-mid);
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin: 1.2rem 0 0.6rem;
      padding-bottom: 4px;
      border-bottom: 1px dashed #d1e7c8;
    }
    .item-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
      gap: 1rem;
    }
    .item-card {
      background: white;
      border-radius: 14px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(45,74,30,0.08);
      transition: transform 0.2s, box-shadow 0.2s;
      cursor: pointer;
      border: 2px solid transparent;
      position: relative;
    }
    .item-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(45,74,30,0.14);
    }
    .item-card.in-cart {
      border-color: var(--green-mid);
    }
    .item-card-img {
      width: 100%; height: 110px;
      object-fit: cover;
    }
    .item-card-placeholder {
      width: 100%; height: 110px;
      background: linear-gradient(135deg, #e8f5e0, #d0ead0);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 2px;
    }
    .item-card-body { padding: 0.65rem 0.75rem 0.75rem; }
    .item-card-name {
      font-weight: 700;
      font-size: 0.8rem;
      color: var(--green-dark);
      line-height: 1.3;
      margin-bottom: 2px;
    }
    .item-card-variant {
      font-size: 0.67rem;
      color: var(--text-muted);
      margin-bottom: 6px;
    }
    .item-card-price {
      font-weight: 800;
      font-size: 0.9rem;
      color: var(--brown);
    }
    .add-btn {
      position: absolute;
      top: 8px; right: 8px;
      width: 28px; height: 28px;
      background: var(--green-dark);
      color: white;
      border: none;
      border-radius: 50%;
      font-size: 1.1rem;
      line-height: 1;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      transition: background 0.2s, transform 0.15s;
    }
    .add-btn:hover { background: var(--green-mid); transform: scale(1.1); }
    .item-badge {
      position: absolute;
      top: 8px; left: 8px;
      background: var(--green-dark);
      color: white;
      border-radius: 50%;
      width: 22px; height: 22px;
      font-size: 0.7rem;
      font-weight: 700;
      display: flex; align-items: center; justify-content: center;
    }

    /* ── Drinks Filter Dropdown ── */
    .drinks-filter-bar-adv {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 1.2rem;
      flex-wrap: wrap;
    }
    .drinks-filter-bar-adv .filter-label {
      font-size: 0.72rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1.5px;
      white-space: nowrap;
    }
    .drink-filter-select {
      appearance: none;
      -webkit-appearance: none;
      background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16'%3E%3Cpath fill='%232d4a1e' d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 12px center;
      border: 1.5px solid #d1e7c8;
      color: var(--green-dark);
      border-radius: 22px;
      padding: 7px 36px 7px 16px;
      font-size: 0.82rem;
      font-weight: 600;
      cursor: pointer;
      transition: border-color 0.18s, box-shadow 0.18s;
      font-family: 'Lato', sans-serif;
      min-width: 200px;
    }
    .drink-filter-select:focus {
      outline: none;
      border-color: var(--green-mid);
      box-shadow: 0 0 0 3px rgba(74,124,53,0.15);
    }
    .drink-filter-select:hover {
      border-color: var(--green-mid);
      background-color: #f0fdf4;
    }
    .drink-subcat-block { display: none; }
    .drink-subcat-block.visible { display: block; }

    /* ── Active category tab ── */
    .cat-tab-adv.active {
      background: var(--green-dark);
      color: white;
      border-color: var(--green-dark);
    }

    /* ── Drink list layout ── */
    .item-list { display: flex; flex-direction: column; gap: 0; }
    .item-card-drink {
      display: flex;
      align-items: center;
      border-radius: 10px;
      padding: 0;
      border: 1.5px solid #e8f5e0;
      background: white;
      margin-bottom: 6px;
      position: relative;
    }
    .item-card-drink:hover {
      transform: none;
      box-shadow: 0 2px 10px rgba(45,74,30,0.1);
      border-color: var(--green-light);
    }
    .item-card-drink.in-cart { border-color: var(--green-mid); background: #f0fdf4; }
    .item-card-drink .item-card-body {
      display: flex;
      align-items: center;
      gap: 10px;
      flex: 1;
      padding: 9px 44px 9px 12px;
    }
    .item-card-drink .item-card-name { flex: 1; font-size: 0.83rem; margin-bottom: 0; }
    .item-card-drink .item-card-variant { margin-bottom: 0; }
    .item-card-drink .item-card-price { font-size: 0.85rem; white-space: nowrap; }
    .item-card-drink .add-btn { top: 50%; right: 8px; transform: translateY(-50%); }
    .item-card-drink .item-badge { top: 50%; left: 8px; transform: translateY(-50%); }

    /* ── Cart ── */
    .cart-header {
      background: var(--green-dark);
      color: white;
      padding: 14px 18px;
      font-weight: 700;
      font-size: 0.95rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .cart-items {
      flex: 1;
      overflow-y: auto;
      padding: 12px;
    }
    .cart-empty {
      text-align: center;
      padding: 2.5rem 1rem;
      color: var(--text-muted);
    }
    .cart-item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item-name {
      flex: 1;
      font-size: 0.82rem;
      font-weight: 600;
      color: var(--green-dark);
      line-height: 1.3;
    }
    .cart-item-variant {
      font-size: 0.68rem;
      color: var(--text-muted);
      font-weight: 400;
    }
    .cart-item-price {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--brown);
      min-width: 55px;
      text-align: right;
    }
    .qty-ctrl {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .qty-btn {
      width: 22px; height: 22px;
      border: 1.5px solid #d1e7c8;
      background: white;
      border-radius: 50%;
      font-size: 0.85rem;
      line-height: 1;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      transition: background 0.15s;
      color: var(--green-dark);
      font-weight: 700;
      padding: 0;
    }
    .qty-btn:hover { background: #dcfce7; }
    .qty-val {
      font-size: 0.82rem;
      font-weight: 700;
      min-width: 18px;
      text-align: center;
    }
    .cart-footer {
      padding: 14px 16px;
      border-top: 2px solid #f0f0f0;
      background: #fafafa;
    }
    .cart-subtotal {
      display: flex;
      justify-content: space-between;
      font-size: 0.85rem;
      color: var(--text-muted);
      margin-bottom: 4px;
    }
    .cart-total {
      display: flex;
      justify-content: space-between;
      font-weight: 800;
      color: var(--green-dark);
      font-size: 1rem;
      margin-bottom: 4px;
    }
    .cart-fee-note {
      font-size: 0.72rem;
      color: var(--text-muted);
      margin-bottom: 12px;
    }
    .place-order-btn {
      width: 100%;
      background: var(--green-dark);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 0.7rem;
      font-weight: 700;
      font-size: 0.9rem;
      transition: opacity 0.2s;
    }
    .place-order-btn:hover { opacity: 0.87; color: white; }
    .place-order-btn:disabled { opacity: 0.45; cursor: not-allowed; }

    .skip-link {
      display: block;
      text-align: center;
      font-size: 0.78rem;
      color: var(--text-muted);
      margin-top: 8px;
      text-decoration: none;
    }
    .skip-link:hover { color: var(--green-dark); }

    /* ── Reservation Banner ── */
    .res-banner {
      background: linear-gradient(90deg, #f0fdf4, #dcfce7);
      border: 1.5px solid #bbf7d0;
      border-radius: 12px;
      padding: 12px 18px;
      margin-bottom: 1.5rem;
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .res-banner-icon { font-size: 1.8rem; }
    .res-banner-detail { font-size: 0.83rem; color: var(--green-dark); }
    .res-banner-detail strong { font-size: 0.95rem; }
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
        <li class="nav-item"><a class="nav-link" href="book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item">
          <a class="btn-logout nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- MINI HERO -->
<div style="background: linear-gradient(135deg, var(--green-dark), #1a3510); padding:35px 0 28px; color:var(--cream);">
  <div class="container">
    <p style="font-size:0.75rem; letter-spacing:3px; text-transform:uppercase; color:rgba(245,240,232,0.6); margin-bottom:4px;">Step 2 of 2</p>
    <h2 style="font-family:'Playfair Display',serif; font-weight:700;">Choose Your Dishes 🍽️</h2>
    <p style="color:rgba(245,240,232,0.75); margin-top:4px; font-size:0.92rem;">
      Browse the menu and add items — your food will be ready when you arrive!
    </p>
  </div>
</div>

<?php if ($message): ?>
<div class="container mt-3">
  <div class="alert alert-<?= $msgType ?> alert-dismissible fade show">
    <?= htmlspecialchars($message) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endif; ?>

<div class="page-wrapper">

  <!-- ══ MENU COLUMN ══ -->
  <div class="menu-col">

    <!-- Reservation Banner -->
    <div class="res-banner">
      <div class="res-banner-icon">📅</div>
      <div class="res-banner-detail">
        <strong>Your Reservation</strong><br>
        <?= date('F j, Y', strtotime($resDate)) ?> at <?= date('g:i A', strtotime($resTime)) ?> · <?= $pax ?> guests
        <span style="margin-left:10px; background:var(--green-mid); color:white; border-radius:20px; font-size:0.7rem; padding:2px 10px; font-weight:700;">
          #<?= $resId ?>
        </span>
      </div>
    </div>

    <!-- Category Nav -->
    <div class="cat-nav-adv">
      <?php foreach (array_keys($menuItems) as $cat): ?>
        <?php
          $icons = ['Main'=>'🍗','Snacks'=>'🍟','Pasta'=>'🍝','Burgers'=>'🍔','Salads'=>'🥗','Pizza'=>'🍕','Drinks'=>'🧋'];
          $icon = $icons[$cat] ?? '🍴';
        ?>
        <a href="#cat-<?= strtolower($cat) ?>" class="cat-tab-adv"><?= $icon ?> <?= htmlspecialchars($cat) ?></a>
      <?php endforeach; ?>
    </div>

    <!-- Menu Sections -->
    <?php foreach ($menuItems as $category => $subcategories): ?>
    <div class="menu-section-adv" id="cat-<?= strtolower($category) ?>">
      <?php
        $catIcons = ['Main'=>'🍗','Snacks'=>'🍟','Pasta'=>'🍝','Burgers'=>'🍔','Salads'=>'🥗','Pizza'=>'🍕','Drinks'=>'🧋'];
        $catIcon  = $catIcons[$category] ?? '🍴';
      ?>
      <div class="section-heading"><?= $catIcon ?> <?= htmlspecialchars($category) ?></div>

      <?php
      // For Drinks: render filter buttons once, then wrap each subcat in a filterable block
      if ($category === 'Drinks'):
        $drinkSubcats = array_keys($subcategories);
        $drinkIcons   = [
          'Artisan Tea'      => '🫖',
          'Milk Tea'         => '🧋',
          'Hot Tea'          => '☕',
          'Cheesecake'       => '🧁',
          'Rock Salt & Cheese' => '🧂',
          'Hot Drinks'       => '🔥',
          'Iced Drinks'      => '🧊',
          'Ice Blended'      => '🥤',
          'Cream Based'      => '🍦',
          'Add-ons'          => '➕',
        ];
      ?>
        <!-- Drink filter dropdown -->
        <div class="drinks-filter-bar-adv">
          <span class="filter-label"><i class="bi bi-funnel me-1"></i>Drinks:</span>
          <select class="drink-filter-select" onchange="filterDrinksAdv(this.value)">
            <option value="all">🧋 All Drinks</option>
            <?php foreach ($drinkSubcats as $ds): ?>
              <?php
                $di  = $drinkIcons[$ds] ?? '🍹';
                $key = preg_replace('/[^a-z0-9]/i','_',$ds);
              ?>
              <option value="<?= htmlspecialchars($key) ?>"><?= $di ?> <?= htmlspecialchars($ds) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php foreach ($subcategories as $subcat => $items): ?>
          <?php $subcatKey = preg_replace('/[^a-z0-9]/i','_',$subcat); ?>
          <div class="drink-subcat-block visible" data-subcat="<?= htmlspecialchars($subcatKey) ?>">
            <div class="subsection-heading"><?= htmlspecialchars($subcat) ?></div>
            <div class="item-list">
              <?php foreach ($items as $item): ?>
              <div class="item-card item-card-drink" id="card-<?= $item['id'] ?>"
                   data-id="<?= $item['id'] ?>"
                   data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>"
                   data-variant="<?= htmlspecialchars($item['price_variant'] ?? '', ENT_QUOTES) ?>"
                   data-price="<?= floatval($item['price']) ?>">
                <div class="item-card-body">
                  <div class="item-card-name"><?= htmlspecialchars($item['name']) ?></div>
                  <?php if ($item['price_variant']): ?>
                    <div class="item-card-variant"><?= htmlspecialchars($item['price_variant']) ?></div>
                  <?php endif; ?>
                  <div class="item-card-price">₱<?= number_format($item['price'], 0) ?></div>
                </div>
                <button class="add-btn" type="button" title="Add to order">+</button>
                <span class="item-badge" id="badge-<?= $item['id'] ?>" style="display:none;"></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

      <?php else: // Non-drinks categories ?>
      <?php foreach ($subcategories as $subcat => $items): ?>
        <?php if (count($subcategories) > 1): ?>
          <div class="subsection-heading"><?= htmlspecialchars($subcat) ?></div>
        <?php endif; ?>

        <div class="item-grid">
          <?php foreach ($items as $item): ?>
          <?php
            $isdrinks = ($item['category'] === 'Drinks');
            if (!$isdrinks) {
              $imgHtml = '';
              if ($item['image'] && file_exists('uploads/menu/' . $item['image'])) {
                $imgHtml = '<img src="uploads/menu/' . htmlspecialchars($item['image']) . '" alt="' . htmlspecialchars($item['name']) . '" class="item-card-img">';
              } else {
                $emojis = ['Main'=>'🍗','Snacks'=>'🍟','Pasta'=>'🍝','Burgers'=>'🍔','Salads'=>'🥗','Pizza'=>'🍕'];
                $e = $emojis[$item['category']] ?? '🍴';
                $imgHtml = '<div class="item-card-placeholder"><span style="font-size:2.2rem;">' . $e . '</span><span style="font-size:0.62rem;color:var(--green-mid);">Photo coming soon</span></div>';
              }
            }
          ?>
          <div class="item-card<?= $isdrinks ? ' item-card-drink' : '' ?>" id="card-<?= $item['id'] ?>"
               data-id="<?= $item['id'] ?>"
               data-name="<?= htmlspecialchars($item['name'], ENT_QUOTES) ?>"
               data-variant="<?= htmlspecialchars($item['price_variant'] ?? '', ENT_QUOTES) ?>"
               data-price="<?= floatval($item['price']) ?>">
            <?php if (!$isdrinks): ?>
            <?= $imgHtml ?>
            <?php endif; ?>
            <div class="item-card-body">
              <div class="item-card-name"><?= htmlspecialchars($item['name']) ?></div>
              <?php if ($item['price_variant']): ?>
                <div class="item-card-variant"><?= htmlspecialchars($item['price_variant']) ?></div>
              <?php endif; ?>
              <div class="item-card-price">₱<?= number_format($item['price'], 0) ?></div>
            </div>
            <button class="add-btn" type="button" title="Add to order">+</button>
            <span class="item-badge" id="badge-<?= $item['id'] ?>" style="display:none;"></span>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
      <?php endif; // end non-drinks else ?>
    </div>
    <?php endforeach; ?>

  </div><!-- end menu-col -->

  <!-- ══ CART COLUMN ══ -->
  <div class="cart-col">
    <div class="cart-header">
      <i class="bi bi-bag-check-fill"></i>
      Your Order
      <span id="cartCount" class="ms-auto badge rounded-pill" style="background:var(--gold);">0</span>
    </div>

    <div class="cart-items" id="cartItems">
      <div class="cart-empty" id="cartEmpty">
        <i class="bi bi-bag" style="font-size:2.5rem; display:block; margin-bottom:10px; opacity:0.4;"></i>
        <p style="font-size:0.85rem;">No items yet.<br>Browse the menu and add dishes!</p>
      </div>
    </div>

    <div class="cart-footer">
      <div class="cart-subtotal">
        <span>Food subtotal</span>
        <span id="cartSubtotal">₱0</span>
      </div>
      <div class="cart-subtotal">
        <span>Reservation fee</span>
        <span>₱100</span>
      </div>
      <div class="cart-total">
        <span>Total</span>
        <span id="cartTotal">₱100</span>
      </div>
      <div class="cart-fee-note">
        <i class="bi bi-info-circle me-1"></i>
        Pay via <?= ucfirst($payMethod) ?> · Admin will confirm order
      </div>

      <form method="POST" id="orderForm">
        <input type="hidden" name="place_order" value="1">
        <input type="hidden" name="cart_data" id="cartDataInput">
        <div class="mb-2">
          <textarea name="order_notes" class="form-control form-control-sm"
                    rows="2" placeholder="Special requests (optional)..."
                    style="font-size:0.8rem; border-radius:8px;"></textarea>
        </div>
        <button type="submit" class="place-order-btn btn" id="placeOrderBtn" disabled>
          <i class="bi bi-check-circle me-2"></i>Place Advance Order
        </button>
      </form>

      <a href="book_reservation.php" class="skip-link">
        <i class="bi bi-x me-1"></i>Skip ordering, just reserve
      </a>
    </div>
  </div><!-- end cart-col -->

</div><!-- end page-wrapper -->

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Cart state: { id: { id, name, variant, price, qty } }
let cart = {};

// Event delegation: handle all add-btn clicks
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.add-btn');
  if (!btn) return;
  e.stopPropagation();
  const card = btn.closest('.item-card');
  if (!card) return;
  addToCart({
    id:      parseInt(card.dataset.id),
    name:    card.dataset.name,
    variant: card.dataset.variant || '',
    price:   parseFloat(card.dataset.price),
  });
});

function addToCart(item) {
  if (cart[item.id]) {
    cart[item.id].qty++;
  } else {
    cart[item.id] = { ...item, qty: 1 };
  }
  renderCart();
}

function changeQty(id, delta) {
  if (!cart[id]) return;
  cart[id].qty += delta;
  if (cart[id].qty <= 0) {
    delete cart[id];
  }
  renderCart();
}

function renderCart() {
  const cartItems  = document.getElementById('cartItems');
  const cartEmpty  = document.getElementById('cartEmpty');
  const cartCount  = document.getElementById('cartCount');
  const cartSub    = document.getElementById('cartSubtotal');
  const cartTotal  = document.getElementById('cartTotal');
  const placeBtn   = document.getElementById('placeOrderBtn');
  const cartInput  = document.getElementById('cartDataInput');

  const ids = Object.keys(cart);
  cartCount.textContent = ids.reduce((s, k) => s + cart[k].qty, 0);

  // Update card badges
  document.querySelectorAll('.item-card').forEach(card => {
    card.classList.remove('in-cart');
    const badge = card.querySelector('.item-badge');
    if (badge) badge.style.display = 'none';
  });
  ids.forEach(id => {
    const card  = document.getElementById('card-' + id);
    const badge = document.getElementById('badge-' + id);
    if (card) card.classList.add('in-cart');
    if (badge) {
      badge.textContent = cart[id].qty;
      badge.style.display = 'flex';
    }
  });

  if (ids.length === 0) {
    cartItems.innerHTML = '';
    cartItems.appendChild(document.getElementById('cartEmpty') || createEmpty());
    document.getElementById('cartEmpty').style.display = '';
    placeBtn.disabled = true;
    cartSub.textContent  = '₱0';
    cartTotal.textContent = '₱100';
    cartInput.value = '[]';
    return;
  }

  // Hide empty state
  const emptyEl = document.getElementById('cartEmpty');
  if (emptyEl) emptyEl.style.display = 'none';

  // Build cart HTML
  let html = '';
  let subtotal = 0;
  ids.forEach(id => {
    const it = cart[id];
    subtotal += it.price * it.qty;
    html += `
      <div class="cart-item">
        <div class="cart-item-name">
          ${escHtml(it.name)}
          ${it.variant ? '<span class="cart-item-variant"> · ' + escHtml(it.variant) + '</span>' : ''}
        </div>
        <div class="qty-ctrl">
          <button class="qty-btn" onclick="changeQty(${id}, -1)">−</button>
          <span class="qty-val">${it.qty}</span>
          <button class="qty-btn" onclick="changeQty(${id}, 1)">+</button>
        </div>
        <div class="cart-item-price">₱${(it.price * it.qty).toLocaleString()}</div>
      </div>`;
  });

  // Preserve empty div then set rest
  const wrapper = document.createElement('div');
  wrapper.innerHTML = html;
  // Clear and rebuild
  while (cartItems.firstChild) cartItems.removeChild(cartItems.firstChild);
  if (emptyEl) { emptyEl.style.display = 'none'; cartItems.appendChild(emptyEl); }
  cartItems.insertAdjacentHTML('beforeend', html);

  const total = subtotal + 100;
  cartSub.textContent   = '₱' + subtotal.toLocaleString();
  cartTotal.textContent = '₱' + total.toLocaleString();
  placeBtn.disabled = false;

  // Build cart data for form
  const cartArray = ids.map(id => ({
    id:    parseInt(id),
    name:  cart[id].name,
    price: cart[id].price,
    qty:   cart[id].qty,
  }));
  cartInput.value = JSON.stringify(cartArray);
}

function escHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Drink subcategory filter
function filterDrinksAdv(subcat) {
  document.querySelectorAll('.drink-subcat-block').forEach(block => {
    if (subcat === 'all' || block.dataset.subcat === subcat) {
      block.classList.add('visible');
    } else {
      block.classList.remove('visible');
    }
  });
}

// Category nav: smooth scroll + active highlight
const catTabs = document.querySelectorAll('.cat-tab-adv');
const menuSections = document.querySelectorAll('.menu-section-adv');

catTabs.forEach(link => {
  link.addEventListener('click', e => {
    e.preventDefault();
    const target = document.querySelector(link.getAttribute('href'));
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    catTabs.forEach(t => t.classList.remove('active'));
    link.classList.add('active');
  });
});

// Highlight active category on scroll
const menuCol = document.querySelector('.menu-col');
menuCol.addEventListener('scroll', () => {
  let current = '';
  menuSections.forEach(section => {
    if (section.offsetTop - menuCol.scrollTop <= 80) {
      current = section.id;
    }
  });
  catTabs.forEach(tab => {
    const href = tab.getAttribute('href').replace('#','');
    tab.classList.toggle('active', href === current);
  });
});

// Confirm on skip
document.querySelector('.skip-link').addEventListener('click', function(e) {
  const ids = Object.keys(cart);
  if (ids.length > 0) {
    if (!confirm('You have items in your cart. Are you sure you want to skip ordering?')) {
      e.preventDefault();
    }
  }
});
</script>
</body>
</html>