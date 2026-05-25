<?php
// menu.php - Full Menu Page (Redesigned: photo cards for food, list for drinks)
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';
Auth::requireLogin();
$userName = htmlspecialchars($_SESSION['user_name']);

// Fetch all menu items from DB, keyed by name (lowercase) for easy lookup
$db = getDB();
$result = $db->query("SELECT name, image FROM menu_items WHERE is_available = 1");
$dbImages = [];
while ($row = $result->fetch_assoc()) {
    $dbImages[strtolower(trim($row['name']))] = $row['image'];
}

// Helper: render image from DB image column, fallback to emoji placeholder
function menuImg(string $itemName, string $emoji, string $alt): string {
    global $dbImages;
    $key = strtolower(trim($itemName));
    $image = $dbImages[$key] ?? null;

    if ($image && file_exists('uploads/menu/' . $image)) {
        return '<img src="uploads/menu/' . htmlspecialchars($image) . '" alt="' . htmlspecialchars($alt) . '" class="food-card-img">';
    }
    return '<div class="food-card-placeholder">'
         . '<span class="ph-emoji">' . $emoji . '</span>'
         . '<span class="ph-label">Photo coming soon</span>'
         . '</div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Full Menu – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ── Sticky Category Nav ── */
    .cat-nav {
      position: sticky;
      top: 0;
      z-index: 100;
      background: white;
      padding: 10px 0;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      margin-bottom: 2.5rem;
    }

    /* ── Section Headers ── */
    .menu-section { margin-bottom: 3.5rem; }

    .menu-section-header {
      display: flex;
      align-items: flex-end;
      justify-content: space-between;
      margin-bottom: 1.2rem;
      flex-wrap: wrap;
      gap: 0.5rem;
    }
    .menu-section-title {
      font-family: 'Playfair Display', serif;
      font-size: 1.7rem;
      color: var(--green-dark);
      font-weight: 700;
      border-left: 4px solid var(--gold);
      padding-left: 14px;
      line-height: 1.2;
      margin: 0;
    }
    .menu-section-sub {
      font-size: 0.8rem;
      color: var(--text-muted);
      padding-left: 18px;
      margin: 4px 0 0;
    }

    .subsection-label {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--green-mid);
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin: 2rem 0 0.8rem;
      padding-bottom: 6px;
      border-bottom: 1px dashed #d1e7c8;
    }

    /* ── Food Photo Cards ── */
    .food-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 1.2rem;
    }

    .food-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 3px 16px rgba(45,74,30,0.09);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      display: flex;
      flex-direction: column;
    }
    .food-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 28px rgba(45,74,30,0.16);
    }

    .food-card-img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      display: block;
    }

    .food-card-placeholder {
      width: 100%;
      height: 150px;
      background: linear-gradient(135deg, #e8f5e0, #d0ead0);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }
    .ph-emoji { font-size: 2.4rem; line-height: 1; }
    .ph-label {
      font-size: 0.65rem;
      color: var(--green-mid);
      letter-spacing: 0.5px;
      font-family: 'Lato', sans-serif;
    }

    .food-card-body {
      padding: 0.85rem 1rem 1rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }
    .food-card-name {
      font-weight: 700;
      font-size: 0.88rem;
      color: var(--green-dark);
      line-height: 1.3;
      margin-bottom: 4px;
    }
    .food-card-desc {
      font-size: 0.72rem;
      color: var(--text-muted);
      line-height: 1.5;
      flex: 1;
      margin-bottom: 8px;
    }
    .food-card-price {
      font-weight: 800;
      font-size: 0.95rem;
      color: var(--brown);
      margin-top: auto;
    }
    .food-card-price .size-prices {
      font-size: 0.72rem;
      color: var(--text-muted);
      font-weight: 400;
      display: block;
      margin-top: 1px;
    }

    /* ── Drinks: list style (no photos) ── */
    .drinks-list-card {
      background: white;
      border-radius: 14px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.07);
      overflow: hidden;
      margin-bottom: 1.5rem;
    }
    .drinks-list-header {
      background: var(--green-dark);
      color: white;
      padding: 10px 18px;
      font-size: 0.8rem;
      font-weight: 700;
      letter-spacing: 0.5px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .drinks-list-header .size-hints {
      display: flex;
      gap: 1.5rem;
      font-weight: 400;
      font-size: 0.72rem;
      opacity: 0.75;
    }
    .drink-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 11px 18px;
      border-bottom: 1px solid #f0f0f0;
      transition: background 0.15s;
    }
    .drink-row:last-child { border-bottom: none; }
    .drink-row:hover { background: #f0fdf4; }
    .drink-name {
      font-weight: 600;
      color: var(--green-dark);
      font-size: 0.88rem;
    }
    .drink-prices {
      display: flex;
      gap: 1.5rem;
    }
    .drink-price-val {
      font-weight: 700;
      color: var(--brown);
      font-size: 0.88rem;
      min-width: 42px;
      text-align: right;
    }
    .drink-single-price {
      font-weight: 700;
      color: var(--brown);
      font-size: 0.88rem;
    }
    .addon-row {
      display: flex;
      justify-content: space-between;
      padding: 9px 18px;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.85rem;
    }
    .addon-row:last-child { border-bottom: none; }

    /* ── Pizza: wider card for sizes ── */
    .pizza-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 1.2rem;
    }
    .pizza-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 3px 16px rgba(45,74,30,0.09);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .pizza-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 28px rgba(45,74,30,0.16);
    }
    .pizza-card-body {
      padding: 0.9rem 1.1rem 1.1rem;
    }
    .pizza-card-name {
      font-weight: 700;
      font-size: 0.9rem;
      color: var(--green-dark);
      margin-bottom: 8px;
      line-height: 1.3;
    }
    .pizza-sizes {
      display: flex;
      gap: 0.8rem;
    }
    .pizza-size {
      background: #f0fdf4;
      border-radius: 8px;
      padding: 5px 10px;
      text-align: center;
      flex: 1;
    }
    .pizza-size-label {
      font-size: 0.62rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1px;
      display: block;
    }
    .pizza-size-price {
      font-weight: 800;
      color: var(--brown);
      font-size: 0.88rem;
    }
    .pizza-tier-badge {
      display: inline-block;
      font-size: 0.65rem;
      font-weight: 700;
      padding: 2px 9px;
      border-radius: 20px;
      margin-bottom: 6px;
      letter-spacing: 0.5px;
    }
    .badge-classic  { background: #fef9c3; color: #854d0e; }
    .badge-premium  { background: #fce7f3; color: #9d174d; }
    .badge-special  { background: #e0f2fe; color: #075985; }

    /* ── Drinks Filter Dropdown Bar ── */
  .drinks-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 1.5rem;
    align-items: center;
  }
  .drinks-filter-bar .filter-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-right: 4px;
  }
  .drink-filter-btn {
    background: white;
    border: 1.5px solid #d1e7c8;
    color: var(--green-dark);
    border-radius: 22px;
    padding: 6px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    font-family: 'Lato', sans-serif;
  }
  .drink-filter-btn:hover {
    background: #f0fdf4;
    border-color: var(--green-mid);
  }
  .drink-filter-btn.active {
    background: var(--green-dark);
    border-color: var(--green-dark);
    color: white;
  }
  .drink-category-block {
    display: none;
  }
  .drink-category-block.visible {
    display: block;
  }

  /* ── Wings table card ── */
    .wings-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 1.2rem;
    }
    .wings-card {
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 3px 16px rgba(45,74,30,0.09);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .wings-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 28px rgba(45,74,30,0.16);
    }
    .wings-card-body { padding: 0.9rem 1rem 1rem; }
    .wings-card-name {
      font-weight: 700;
      font-size: 0.88rem;
      color: var(--green-dark);
      margin-bottom: 10px;
    }
    .wings-sizes { display: flex; gap: 0.6rem; }
    .wings-size {
      flex: 1;
      background: #f0fdf4;
      border-radius: 8px;
      padding: 5px 8px;
      text-align: center;
    }
    .wings-size-label { font-size: 0.6rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; display: block; }
    .wings-size-price { font-weight: 800; color: var(--brown); font-size: 0.88rem; }
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
        <li class="nav-item"><a class="nav-link active" href="menu.php"><i class="bi bi-journal-text me-1"></i>Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item"><a class="nav-link" href="chatbot.php"><i class="bi bi-chat-dots me-1"></i>Ask Oli</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php"><i class="bi bi-geo-alt me-1"></i>Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person-circle me-1"></i>My Profile</a></li>
                <?php if (Auth::isAdmin()): ?>
        <li class="nav-item">
          <a class="nav-link" href="admin/dashboard.php"
             style="background:var(--gold);color:var(--green-dark);border-radius:20px;padding:5px 14px;font-weight:700;font-size:0.82rem;">
            <i class="bi bi-speedometer2 me-1"></i>Admin Panel
          </a>
        </li>
        <?php endif; ?>
        <li class="nav-item"><a class="btn-logout nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container position-relative">
    <p class="hero-sub">Explore Our Full Menu</p>
    <h1 class="hero-title">What Would You <span>Like Today?</span></h1>
    <p class="hero-desc mt-3">Snacks, Pasta, Burgers, Salads, Pizza, and refreshing Drinks — all in one place.</p>
    <a href="#snacks" class="btn mt-4" style="background:var(--gold); color:white; border-radius:25px; padding:10px 28px; font-weight:700;">
      <i class="bi bi-arrow-down me-2"></i>Browse Menu
    </a>
  </div>
</section>

<!-- STICKY CATEGORY NAV -->
<div class="cat-nav">
  <div class="container">
    <div class="cat-tabs mb-0">
      <a href="#snacks"  class="cat-tab">🍟 Snacks</a>
      <a href="#pasta"   class="cat-tab">🍝 Pasta</a>
      <a href="#burgers" class="cat-tab">🍔 Burgers</a>
      <a href="#main"    class="cat-tab">🍗 Main</a>
      <a href="#salads"  class="cat-tab">🥗 Salads</a>
      <a href="#pizza"   class="cat-tab">🍕 Pizza</a>
      <a href="#drinks"  class="cat-tab">🧋 Drinks</a>
    </div>
  </div>
</div>

<div class="container pb-5">

  <!-- ══ SNACKS ══ -->
  <div class="menu-section" id="snacks">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🍟 Snacks</h2>
        <p class="menu-section-sub">Perfect for sharing or solo munching</p>
      </div>
    </div>
    <div class="food-grid">

      <?php
      $snacks = [
        ['Nachos',             '',                                  '₱198', 'nachos',      '🧀'],
        ['Chicken Fingers',    '',                                  '₱189', 'chicken-fingers', '🍗'],
        ['Cheesy Bacon Fries', '',                                  '₱198', 'cheesy-bacon-fries', '🍟'],
        ['Fish & Fries',       '',                                  '₱198', 'fish-fries',  '🐟'],
        ['Flavored Fries',     'Barbecue · Cheese · Sour Cream',   '₱159', 'flavored-fries', '🍟'],
        ['Flavored Mojos',     'Barbecue · Cheese · Sour Cream',   '₱189', 'flavored-mojos', '🥔'],
      ];
      foreach ($snacks as [$name, $desc, $price, $slug, $emoji]): ?>
      <div class="food-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="food-card-body">
          <div class="food-card-name"><?= htmlspecialchars($name) ?></div>
          <?php if ($desc): ?><div class="food-card-desc"><?= htmlspecialchars($desc) ?></div><?php endif; ?>
          <div class="food-card-price"><?= $price ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ PASTA ══ -->
  <div class="menu-section" id="pasta">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🍝 Pasta</h2>
        <p class="menu-section-sub">Served with Garlic Bread</p>
      </div>
    </div>
    <div class="food-grid">
      <?php
      $pasta = [
        ['Gourmet Tuyo Pasta',    '₱189', 'gourmet-tuyo-pasta',    '🍝'],
        ['Alfredo (White Sauce)', '₱194', 'alfredo-pasta',          '🍝'],
        ['Meat Sauce Spaghetti',  '₱189', 'meat-sauce-spaghetti',   '🍝'],
        ['Lasagna',               '₱194', 'lasagna',                '🫙'],
        ['Aligue Pasta',          '₱189', 'aligue-pasta',           '🦀'],
        ['Shrimp Aglio Olio',     '₱194', 'shrimp-aglio-olio',      '🍤'],
        ['Chicken Oriental Pasta','₱189', 'chicken-oriental-pasta', '🍝'],
      ];
      foreach ($pasta as [$name, $price, $slug, $emoji]): ?>
      <div class="food-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="food-card-body">
          <div class="food-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="food-card-price"><?= $price ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ BURGERS / SANDWICHES ══ -->
  <div class="menu-section" id="burgers">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🍔 Burgers / Sandwiches</h2>
        <p class="menu-section-sub">Served with Fries</p>
      </div>
    </div>
    <div class="food-grid">
      <?php
      $burgers = [
        ['Pulled Pork BBQ',       '₱189', 'pulled-pork-bbq',       '🥩'],
        ['Dori Fish Burger',      '₱189', 'dori-fish-burger',       '🐟'],
        ['Cheeseburger',          '₱194', 'cheeseburger',           '🍔'],
        ['Bacon Cheeseburger',    '₱209', 'bacon-cheeseburger',     '🥓'],
        ['Crispy Chicken Burger', '₱194', 'crispy-chicken-burger',  '🍔'],
        ['Clubhouse Sandwich',    '₱194', 'clubhouse-sandwich',     '🥪'],
      ];
      foreach ($burgers as [$name, $price, $slug, $emoji]): ?>
      <div class="food-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="food-card-body">
          <div class="food-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="food-card-price"><?= $price ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ MAIN ══ -->
  <div class="menu-section" id="main">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🍗 Main</h2>
        <p class="menu-section-sub">For Sharing · Rice Meal · Chicken Wings</p>
      </div>
    </div>

    <!-- For Sharing -->
    <div class="subsection-label">Flavored Boneless Chicken Bites — For Sharing</div>
    <div class="food-grid">
      <?php
      $sharing = [
        ['Yangnyeom (Spicy Korean)', '₱279', 'yangnyeom', '🌶️'],
        ['Garlic Parmesan',          '₱279', 'garlic-parmesan', '🧄'],
        ['Hickory Barbecue',         '₱279', 'hickory-barbecue', '🍖'],
        ['Spicy Salted Egg',         '₱279', 'spicy-salted-egg', '🥚'],
      ];
      foreach ($sharing as [$name, $price, $slug, $emoji]): ?>
      <div class="food-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="food-card-body">
          <div class="food-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="food-card-price"><?= $price ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Rice Meal -->
    <div class="subsection-label mt-4">Rice Meal — Served with Buttered Vegetables</div>
    <div class="food-grid">
      <?php
      $rice = [
        ['Chicken Fingers w/ Rice',              '₱189', 'chicken-fingers-rice',     '🍗'],
        ['Burger Steak w/ Egg',                  '₱194', 'burger-steak-egg',         '🍳'],
        ['2pcs Grilled Porkchop w/ Mushroom Gravy','₱214','grilled-porkchop',        '🥩'],
        ['Chicken Fillet Ala King',              '₱199', 'chicken-fillet-ala-king',  '🍛'],
        ['Breaded Porkchop w/ Egg',              '₱194', 'breaded-porkchop',         '🥩'],
        ['Fish Fillet w/ Rice in Tartar Sauce',  '₱194', 'fish-fillet-rice',         '🐟'],
        ['Flavored Chicken Bites w/ Rice',       '₱199', 'chicken-bites-rice',       '🍗'],
        ['4pcs Chicken Wings w/ Rice',           '₱199', 'chicken-wings-rice',       '🍗'],
      ];
      foreach ($rice as [$name, $price, $slug, $emoji]): ?>
      <div class="food-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="food-card-body">
          <div class="food-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="food-card-price"><?= $price ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Chicken Wings -->
    <div class="subsection-label mt-4">Chicken Wings</div>
    <div class="wings-grid">
      <?php
      $wings = [
        ['Yangnyeom (Spicy Korean)', 239, 459, 'yangnyeom-wings',  '🌶️'],
        ['Garlic Parmesan',          239, 459, 'garlic-parmesan-wings', '🧄'],
        ['Hickory Barbecue',         239, 459, 'hickory-wings',    '🍖'],
        ['Spicy Salted Egg',         239, 459, 'salted-egg-wings', '🥚'],
      ];
      foreach ($wings as [$name, $p6, $p12, $slug, $emoji]): ?>
      <div class="wings-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="wings-card-body">
          <div class="wings-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="wings-sizes">
            <div class="wings-size">
              <span class="wings-size-label">6 pcs</span>
              <span class="wings-size-price">₱<?= number_format($p6) ?></span>
            </div>
            <div class="wings-size">
              <span class="wings-size-label">12 pcs</span>
              <span class="wings-size-price">₱<?= number_format($p12) ?></span>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ SALADS ══ -->
  <div class="menu-section" id="salads">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🥗 Salads</h2>
        <p class="menu-section-sub">Fresh &amp; healthy options</p>
      </div>
    </div>
    <div class="food-grid">
      <?php
      $salads = [
        ['Macaroni Salad',      '',                                                                         '₱169', 'macaroni-salad',       '🥗'],
        ['Kani Salad',          'Lettuce, Cucumber, Carrots, Mango, Crab Sticks, Roasted Sesame dressing',  '₱189', 'kani-salad',           '🦀'],
        ['Chicken Caesar Salad','Romaine Lettuce, Chicken, Croutons, Parmesan, Caesar dressing, bacon bits', '₱209', 'chicken-caesar-salad', '🥗'],
      ];
      foreach ($salads as [$name, $desc, $price, $slug, $emoji]): ?>
      <div class="food-card fade-in-up">
        <?= menuImg($name, $emoji, $name) ?>
        <div class="food-card-body">
          <div class="food-card-name"><?= htmlspecialchars($name) ?></div>
          <?php if ($desc): ?><div class="food-card-desc"><?= htmlspecialchars($desc) ?></div><?php endif; ?>
          <div class="food-card-price"><?= $price ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ PIZZA ══ -->
  <div class="menu-section" id="pizza">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🍕 New York Style Pizza</h2>
        <p class="menu-section-sub">Available in 12" and 16"</p>
      </div>
    </div>

    <!-- Classic -->
    <div class="subsection-label">Classic</div>
    <div class="pizza-grid">
      <?php
      $classic = [
        ['All Cheese',              329, 449, 'pizza-all-cheese'],
        ['American Ham and Cheese', 349, 469, 'pizza-ham-cheese'],
        ['Hawaiian',                359, 479, 'pizza-hawaiian'],
      ];
      foreach ($classic as [$name, $p12, $p16, $slug]): ?>
      <div class="pizza-card fade-in-up">
        <?= menuImg($name, '🍕', $name) ?>
        <div class="pizza-card-body">
          <span class="pizza-tier-badge badge-classic">Classic</span>
          <div class="pizza-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="pizza-sizes">
            <div class="pizza-size"><span class="pizza-size-label">12"</span><span class="pizza-size-price">₱<?= number_format($p12) ?></span></div>
            <div class="pizza-size"><span class="pizza-size-label">16"</span><span class="pizza-size-price">₱<?= number_format($p16) ?></span></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Premium -->
    <div class="subsection-label mt-4">Premium</div>
    <div class="pizza-grid">
      <?php
      $premium = [
        ["New York's Pepperoni",             389, 499, 'pizza-pepperoni'],
        ['Hawaiian Supreme',                 399, 509, 'pizza-hawaiian-supreme'],
        ['All Meat',                         399, 509, 'pizza-all-meat'],
        ["New York's Special",               399, 509, 'pizza-ny-special'],
        ['Carbonara Pizza (White Sauce)',     399, 509, 'pizza-carbonara'],
        ['Pulled Pork BBQ Pizza',            399, 509, 'pizza-pulled-pork'],
      ];
      foreach ($premium as [$name, $p12, $p16, $slug]): ?>
      <div class="pizza-card fade-in-up">
        <?= menuImg($name, '🍕', $name) ?>
        <div class="pizza-card-body">
          <span class="pizza-tier-badge badge-premium">Premium</span>
          <div class="pizza-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="pizza-sizes">
            <div class="pizza-size"><span class="pizza-size-label">12"</span><span class="pizza-size-price">₱<?= number_format($p12) ?></span></div>
            <div class="pizza-size"><span class="pizza-size-label">16"</span><span class="pizza-size-price">₱<?= number_format($p16) ?></span></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Latest Special -->
    <div class="subsection-label mt-4">Latest Special 🆕</div>
    <div class="pizza-grid">
      <?php
      $special = [
        ['4 Cheese Pizza',     409, 529, 'pizza-4-cheese'],
        ['Garlic Shrimp Pizza',409, 529, 'pizza-garlic-shrimp'],
      ];
      foreach ($special as [$name, $p12, $p16, $slug]): ?>
      <div class="pizza-card fade-in-up">
        <?= menuImg($name, '🍕', $name) ?>
        <div class="pizza-card-body">
          <span class="pizza-tier-badge badge-special">New!</span>
          <div class="pizza-card-name"><?= htmlspecialchars($name) ?></div>
          <div class="pizza-sizes">
            <div class="pizza-size"><span class="pizza-size-label">12"</span><span class="pizza-size-price">₱<?= number_format($p12) ?></span></div>
            <div class="pizza-size"><span class="pizza-size-label">16"</span><span class="pizza-size-price">₱<?= number_format($p16) ?></span></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pizza Add-ons -->
    <div class="subsection-label mt-4">Pizza Add-ons</div>
    <div class="drinks-list-card" style="max-width: 420px;">
      <?php
      $paddons = [['Mozzarella Cheese',60],['Pepperoni',60],['American Ham',60],['Bacon',60],['Pineapple',30]];
      foreach ($paddons as [$name, $price]): ?>
      <div class="addon-row">
        <span><?= htmlspecialchars($name) ?></span>
        <span style="font-weight:700; color:var(--brown);">+₱<?= $price ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ══ DRINKS ══ (list view — no photos) -->
  <div class="menu-section" id="drinks">
    <div class="menu-section-header">
      <div>
        <h2 class="menu-section-title">🧋 Drinks</h2>
        <p class="menu-section-sub">Free Pearl Sinker on milk teas · Add-ons available</p>
      </div>
    </div>

    <!-- Drink Filter Buttons -->
    <div class="drinks-filter-bar">
      <span class="filter-label"><i class="bi bi-funnel me-1"></i>Filter:</span>
      <button class="drink-filter-btn active" onclick="filterDrinks('all', this)">🧋 All</button>
      <button class="drink-filter-btn" onclick="filterDrinks('artisan', this)">🫖 Artisan Tea</button>
      <button class="drink-filter-btn" onclick="filterDrinks('milktea', this)">🧋 Milk Tea</button>
      <button class="drink-filter-btn" onclick="filterDrinks('hottea', this)">☕ Hot Tea</button>
      <button class="drink-filter-btn" onclick="filterDrinks('cheesecake', this)">🧁 Cheesecake</button>
      <button class="drink-filter-btn" onclick="filterDrinks('rsc', this)">🧂 Rock Salt & Cheese</button>
      <button class="drink-filter-btn" onclick="filterDrinks('hot', this)">🔥 Hot Drinks</button>
      <button class="drink-filter-btn" onclick="filterDrinks('iced', this)">🧊 Iced Drinks</button>
      <button class="drink-filter-btn" onclick="filterDrinks('blended', this)">🥤 Ice Blended</button>
      <button class="drink-filter-btn" onclick="filterDrinks('cream', this)">🍦 Cream Based</button>
      <button class="drink-filter-btn" onclick="filterDrinks('addons', this)">➕ Add-ons</button>
    </div>

    <!-- Artisan Tea -->
    <div class="drink-category-block visible" data-category="artisan">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Artisan Tea — Free Pearl Sinker</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $artisan = [
        ['Pearl Milk Tea',68,95,105],['Earl Grey Milk Tea',105,115],['Ceylon Milk Tea',105,115],
        ['Sun Moon Milk Tea',105,115],['Jasmine Milk Tea',105,115],['Cookies and Cream',105,115],
      ];
      foreach ($artisan as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end artisan block -->

    <!-- Milk Tea -->
    <div class="drink-category-block visible" data-category="milktea">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Milk Tea — Free Pearl Sinker</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $milktea = [
        ['Wintermelon',85,95],['Okinawa',85,95],['Taro',85,95],
        ['Dark Chocolate',95,105],['Red Velvet Milk Tea',95,105],
        ['Matcha Milk Tea',95,105],['Brown Sugar Milk',95,105],
      ];
      foreach ($milktea as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end milktea block -->

    <!-- Hot Tea -->
    <div class="drink-category-block visible" data-category="hottea">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Hot Tea</span>
        <div class="size-hints"><span>12oz</span><span>16oz</span></div>
      </div>
      <?php
      $hottea = [
        ['Earl Grey Hot Tea',95,105],['Ceylon Hot Tea',95,105],
        ['Sun Moon Hot Tea',95,105],['Jasmine Hot Tea',95,105],
      ];
      foreach ($hottea as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end hottea block -->

    <!-- Cheesecake -->
    <div class="drink-category-block visible" data-category="cheesecake">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Cheesecake — Free Pearl Sinker</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $cake = ['Classic Cheesecake','Earl Grey Cheesecake','Sun Moon Cheesecake',
               'Red Velvet Cheesecake','Dark Choco Cheesecake','Oreo Cheesecake',
               'Okinawa Cheesecake','Taro Cheesecake','Matcha Cheesecake'];
      foreach ($cake as $name): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($name) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱125</span>
          <span class="drink-price-val">₱140</span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end cheesecake block -->

    <!-- Rock Salt & Cheese -->
    <div class="drink-category-block visible" data-category="rsc">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Rock Salt &amp; Cheese — Free Pearl Sinker</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $rsc = ['Classic RSC','Earl Grey RSC','SunMoon RSC','Okinawa RSC','Dark Choco RSC'];
      foreach ($rsc as $name): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($name) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱125</span>
          <span class="drink-price-val">₱140</span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end rsc block -->

    <!-- Hot Drinks -->
    <div class="drink-category-block visible" data-category="hot">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Hot Drinks</span>
        <div class="size-hints"><span>12oz</span><span>16oz</span></div>
      </div>
      <?php
      $hot = [
        ['Americano',105,120],['Latte',120,135],['Cappuccino',120,135],
        ['Hot Choco',125,140],['Green Tea Latte',125,140],['Mocha',130,145],
        ['Caramel Macchiato',130,145],['Hazelnut Latte',130,145],['Vanilla Latte',130,145],
      ];
      foreach ($hot as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end hot block -->

    <!-- Iced Drinks -->
    <div class="drink-category-block visible" data-category="iced">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Iced Drinks</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $iced = [
        ['Iced Americano',105,120],['Iced Latte',125,140],
        ['Iced Caramel Macchiato',130,145],['Iced Mocha',130,145],
        ['Iced Hazelnut',130,145],['Iced Coffee Vanilla',130,145],
        ['Iced Brown Sugar Coffee',130,145],['Iced Matcha Latte',130,145],
        ['Iced Strawberry Latte',130,145],['Iced Blueberry Latte',130,145],
        ['Iced Taro Latte',130,145],['Milo Dinosaur',130,145],
      ];
      foreach ($iced as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end iced block -->

    <!-- Ice Blended -->
    <div class="drink-category-block visible" data-category="blended">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Ice Blended</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $blended = [
        ['Chocolate Chip Mocha',130,145],['Mocha',130,145],['Espresso Hazelnut',130,145],
        ['Caramel',130,145],['Java Chip',130,145],
        ['Coffee Jelly Frappe',135,150],['Dark Choco Espresso',135,150],
      ];
      foreach ($blended as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end blended block -->

    <!-- Cream Based -->
    <div class="drink-category-block visible" data-category="cream">
    <div class="drinks-list-card">
      <div class="drinks-list-header">
        <span>Cream Based</span>
        <div class="size-hints"><span>16oz</span><span>22oz</span></div>
      </div>
      <?php
      $cream = [
        ['Chocolate Milkshake',115,130],['Vanilla Milkshake',115,130],
        ['Oreo Cream Frappe',125,140],['Strawberry Milkshake',125,140],
        ['Blueberry Milkshake',125,140],['Mango Milkshake',125,140],
        ['Taro Milkshake',125,140],['Choco Chip Cream',125,140],
        ['Green Tea Cream',125,140],
      ];
      foreach ($cream as $r): ?>
      <div class="drink-row">
        <span class="drink-name"><?= htmlspecialchars($r[0]) ?></span>
        <div class="drink-prices">
          <span class="drink-price-val">₱<?= $r[1] ?></span>
          <span class="drink-price-val">₱<?= $r[2] ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end cream block -->

    <!-- Drink Add-ons -->
    <div class="drink-category-block visible" data-category="addons">
    <div class="drinks-list-card" style="max-width: 420px;">
      <div class="drinks-list-header"><span>Drink Add-ons</span></div>
      <?php
      $daddons = [
        ['Selfie Print',20],['Syrup',20],['Extra Shot',30],['Cheesecake / RSC',30],
        ['Pearl',15],['Nata / Fruit Jelly',15],['Coffee Jelly',20],['Popping Boba',20],
      ];
      foreach ($daddons as [$n, $p]): ?>
      <div class="addon-row">
        <span><?= htmlspecialchars($n) ?></span>
        <span style="font-weight:700; color:var(--brown);">+₱<?= $p ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    </div><!-- end addons block -->

  </div><!-- end drinks -->

</div><!-- end container -->

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<!-- Back to Top Button -->
<button id="backToTop" onclick="window.scrollTo({top:0, behavior:'smooth'})" title="Back to top">
  <i class="bi bi-arrow-up"></i>
</button>

<style>
  #backToTop {
    position: fixed;
    bottom: 32px;
    right: 32px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: var(--green-dark, #2d4a1e);
    color: white;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s, transform 0.3s;
    z-index: 999;
  }
  #backToTop.visible {
    opacity: 1;
    visibility: visible;
  }
  #backToTop:hover {
    background: var(--gold, #c8921a);
    transform: translateY(-3px);
  }
  .food-card.highlighted,
  .pizza-card.highlighted,
  .wings-card.highlighted,
  .drink-row.highlighted,
  .drink-item.highlighted {
    outline: 3px solid var(--gold, #c8921a);
    box-shadow: 0 0 0 6px rgba(200,146,26,0.2), 0 8px 32px rgba(200,146,26,0.3);
    transform: translateY(-4px) scale(1.02);
    transition: all 0.4s ease;
    position: relative;
    z-index: 2;
  }
  .highlight-badge {
    position: absolute;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--gold, #c8921a);
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 12px;
    border-radius: 20px;
    white-space: nowrap;
    z-index: 3;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
// ── Highlight item from "See in Menu" link ────────────────────────────────────
(function() {
  const hash = window.location.hash; // e.g. "#pizza&item=New+York%27s+Special"
  if (!hash || hash.indexOf('item=') === -1) return;

  const itemParam = hash.split('&item=')[1];
  if (!itemParam) return;

  const targetName = decodeURIComponent(itemParam.replace(/\+/g, ' ')).toLowerCase().trim();

  window.addEventListener('load', () => {
    // Search all possible name elements across all card types
    const nameSelectors = [
      '.food-card-name',
      '.pizza-card-name',
      '.wings-card-name',
      '.drink-name'
    ];

    let foundName = null;
    let foundCard = null;

    nameSelectors.forEach(sel => {
      document.querySelectorAll(sel).forEach(nameEl => {
        if (nameEl.textContent.toLowerCase().trim().includes(targetName)) {
          foundName = nameEl;
          // Walk up to find the parent card
          foundCard = nameEl.closest('.food-card, .pizza-card, .wings-card, .drink-row, .drink-item');
          if (!foundCard) foundCard = nameEl.parentElement?.parentElement;
        }
      });
    });

    if (foundName && foundCard) {
      // Add highlight badge
      const badge = document.createElement('div');
      badge.className = 'highlight-badge';
      badge.textContent = '⭐ Best Seller';
      foundCard.style.position = 'relative';
      foundCard.appendChild(badge);
      foundCard.classList.add('highlighted');

      // Scroll to it smoothly after section anchor scroll settles
      setTimeout(() => {
        foundCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 500);

      // Remove highlight after 4 seconds
      setTimeout(() => {
        foundCard.classList.remove('highlighted');
        badge.remove();
      }, 4500);
    }
  });
})();

// Back to top visibility
window.addEventListener('scroll', () => {
  document.getElementById('backToTop').classList.toggle('visible', window.scrollY > 300);
});

function filterDrinks(category, btn) {
  // Update active button
  document.querySelectorAll('.drink-filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  // Show/hide blocks
  document.querySelectorAll('.drink-category-block').forEach(block => {
    if (category === 'all' || block.dataset.category === category) {
      block.classList.add('visible');
    } else {
      block.classList.remove('visible');
    }
  });
}
</script>
</body>
</html>