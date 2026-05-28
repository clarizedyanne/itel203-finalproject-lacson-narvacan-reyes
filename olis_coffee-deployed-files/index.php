<?php
// index.php - Customer Main Page (Redesigned with Best Sellers)
session_start();
require_once 'includes/Auth.php';
require_once 'includes/MenuItem.php';

Auth::requireLogin();

$userName = htmlspecialchars($_SESSION['user_name']);

// Best seller items to feature (by name — fetched from DB or hardcoded with fallback)
$bestSellers = [
    [
        'name'        => 'New York\'s Special',
        'category'    => 'Pizza',
        'description' => 'Premium New York-style pizza loaded with our signature toppings.',
        'price'       => '₱399 / ₱509',
        'badge'       => '🍕 Pizza',
        'emoji'       => '🍕',
    ],
    [
        'name'        => 'Bacon Cheeseburger',
        'category'    => 'Burgers',
        'description' => 'Juicy burger stacked with crispy bacon, melted cheese, and served with fries.',
        'price'       => '₱209',
        'badge'       => '🍔 Burger',
        'emoji'       => '🍔',
    ],
    [
        'name'        => 'Flavored Mojos',
        'category'    => 'Snacks',
        'description' => 'Crispy seasoned potato mojos tossed in smoky barbecue flavor.',
        'price'       => '₱189',
        'badge'       => '🥔 Snack',
        'emoji'       => '🥔',
    ],
    [
        'name'        => 'Lasagna',
        'category'    => 'Pasta',
        'description' => 'Classic layered pasta with rich meat sauce, béchamel, and melted cheese. Served with garlic bread.',
        'price'       => '₱194',
        'badge'       => '🍝 Pasta',
        'emoji'       => '🍝',
    ],
    [
        'name'        => 'Iced Caramel Macchiato',
        'category'    => 'Drinks',
        'description' => 'Smooth espresso layered over cold milk with sweet caramel drizzle on ice.',
        'price'       => '₱130 / ₱145',
        'badge'       => '☕ Drinks',
        'emoji'       => '☕',
        'force_image' => 'salad/iced_caramel.jpg',
    ],
];

// Try to fetch images from DB for best sellers
$menuItem = new MenuItem();
$imageMap = [];
try {
    $db = getDB();
    $names = array_map(fn($i) => $i['name'], $bestSellers);
    $placeholders = implode(',', array_fill(0, count($names), '?'));
    $stmt = $db->prepare("SELECT name, image, price FROM menu_items WHERE name IN ($placeholders)");
    $stmt->bind_param(str_repeat('s', count($names)), ...$names);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $imageMap[$row['name']] = $row['image'];
    }
} catch (Exception $e) {
    // silently fall through — images just won't show
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="assets/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ─── Best Sellers Section ─── */
    .bestsellers-section {
      background: var(--cream, #faf7f2);
      padding: 5rem 0 4rem;
    }

    .bs-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1.5rem;
      margin-top: 2.5rem;
    }

    .bs-card {
      flex: 0 1 calc(33.333% - 1rem);
      min-width: 260px;
    }

    @media (max-width: 991px) {
      .bs-grid .bs-card {
        flex: 0 1 calc(50% - 0.75rem);
      }
    }
    @media (max-width: 575px) {
      .bs-grid .bs-card {
        flex: 0 1 100%;
      }
    }

    .bs-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 4px 24px rgba(45,74,30,0.08);
      transition: transform 0.28s ease, box-shadow 0.28s ease;
      display: flex;
      flex-direction: column;
      position: relative;
    }
    .bs-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 14px 40px rgba(45,74,30,0.16);
    }

    .bs-card-img {
      width: 100%;
      height: 210px;
      object-fit: cover;
      display: block;
    }

    .bs-card-placeholder {
      width: 100%;
      height: 210px;
      background: linear-gradient(135deg, #e8f5e0, #c8e6c0);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
      gap: 6px;
    }
    .bs-card-placeholder span {
      font-size: 0.78rem;
      color: var(--green-mid, #4a7c35);
      font-family: 'Lato', sans-serif;
      letter-spacing: 1px;
    }

    .bs-card-badge {
      position: absolute;
      top: 14px;
      left: 14px;
      background: rgba(255,255,255,0.95);
      color: var(--green-dark, #2d4a1e);
      font-size: 0.72rem;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 20px;
      backdrop-filter: blur(4px);
      letter-spacing: 0.5px;
    }

    .bs-bestseller-tag {
      position: absolute;
      top: 14px;
      right: 14px;
      background: var(--gold, #c8921a);
      color: white;
      font-size: 0.68rem;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 20px;
      letter-spacing: 0.5px;
    }

    .bs-card-body {
      padding: 1.2rem 1.4rem 1.4rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .bs-card-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--green-dark, #2d4a1e);
      margin-bottom: 6px;
      line-height: 1.3;
    }

    .bs-card-desc {
      font-size: 0.83rem;
      color: var(--text-muted, #6b7280);
      line-height: 1.6;
      flex: 1;
      margin-bottom: 12px;
    }

    .bs-card-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-top: 1px solid #f0f0f0;
      padding-top: 10px;
      margin-top: auto;
    }

    .bs-card-price {
      font-size: 1.05rem;
      font-weight: 800;
      color: var(--green-dark, #2d4a1e);
      font-family: 'Lato', sans-serif;
    }

    .bs-view-btn {
      font-size: 0.75rem;
      padding: 5px 14px;
      border-radius: 20px;
      background: var(--green-dark, #2d4a1e);
      color: white;
      border: none;
      font-weight: 600;
      text-decoration: none;
      transition: background 0.2s;
    }
    .bs-view-btn:hover {
      background: var(--green-mid, #4a7c35);
      color: white;
    }

    /* ─── Feature Strip ─── */
    .feature-strip {
      background: var(--green-dark, #2d4a1e);
      color: white;
      padding: 2.5rem 0;
    }
    .feature-item {
      text-align: center;
      padding: 0 1rem;
    }
    .feature-item i {
      font-size: 2rem;
      color: var(--gold, #c8921a);
      margin-bottom: 10px;
      display: block;
    }
    .feature-item h6 {
      font-family: 'Playfair Display', serif;
      font-weight: 700;
      font-size: 0.95rem;
      margin-bottom: 4px;
    }
    .feature-item p {
      font-size: 0.78rem;
      opacity: 0.7;
      margin: 0;
    }

    /* ─── CTA Section ─── */
    .cta-section {
      background: linear-gradient(135deg, #f9f5ee 60%, #e8f5df);
      padding: 4rem 0;
    }
    .cta-box {
      background: white;
      border-radius: 24px;
      padding: 3rem 2.5rem;
      box-shadow: 0 6px 32px rgba(45,74,30,0.1);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 2rem;
      flex-wrap: wrap;
    }
    .cta-box h3 {
      font-family: 'Playfair Display', serif;
      color: var(--green-dark, #2d4a1e);
      font-weight: 700;
      margin-bottom: 8px;
    }
    .cta-box p {
      color: var(--text-muted, #6b7280);
      font-size: 0.9rem;
      margin: 0;
    }
    .cta-actions {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }
    .btn-cta-primary {
      background: var(--green-dark, #2d4a1e);
      color: white;
      border: none;
      border-radius: 25px;
      padding: 10px 26px;
      font-weight: 700;
      font-size: 0.9rem;
      text-decoration: none;
      white-space: nowrap;
      transition: opacity 0.2s;
    }
    .btn-cta-primary:hover { opacity: 0.85; color: white; }

    .btn-cta-outline {
      background: transparent;
      color: var(--green-dark, #2d4a1e);
      border: 2px solid var(--green-dark, #2d4a1e);
      border-radius: 25px;
      padding: 10px 26px;
      font-weight: 700;
      font-size: 0.9rem;
      text-decoration: none;
      white-space: nowrap;
      transition: all 0.2s;
    }
    .btn-cta-outline:hover {
      background: var(--green-dark, #2d4a1e);
      color: white;
    }

    /* ─── Number counter strip ─── */
    .stats-strip {
      padding: 2.5rem 0;
      background: white;
      border-bottom: 1px solid #eee;
    }
    .stat-item {
      text-align: center;
    }
    .stat-item .num {
      font-family: 'Playfair Display', serif;
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--green-dark, #2d4a1e);
      line-height: 1;
    }
    .stat-item .label {
      font-size: 0.8rem;
      color: var(--text-muted, #6b7280);
      margin-top: 4px;
    }
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
        <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-house me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php"><i class="bi bi-journal-text me-1"></i>Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item"><a class="nav-link" href="chat/ask_oli.php"><i class="bi bi-chat-dots me-1"></i>Ask Oli</a></li>
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
        <li class="nav-item">
          <a class="btn-logout nav-link" href="logout.php">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="container position-relative">
    <p class="hero-sub">Welcome back, <?= $userName ?>!</p>
    <h1 class="hero-title">Your Favourite <span>Cozy Spot</span></h1>
    <p class="hero-desc mt-3">Milk teas, artisan coffee, hearty meals & more — all waiting for you at Oli's. Est. 2019.</p>
    <div class="d-flex gap-3 flex-wrap mt-4">
      <a href="#bestsellers" class="btn" style="background:var(--gold); color:white; border-radius:25px; padding:10px 28px; font-weight:700;">
        <i class="bi bi-star-fill me-2"></i>Best Sellers
      </a>
      <a href="menu.php" class="btn" style="background:transparent; color:var(--cream,#faf7f2); border:2px solid rgba(245,240,232,0.5); border-radius:25px; padding:10px 28px; font-weight:700;">
        <i class="bi bi-journal-text me-2"></i>Full Menu
      </a>
    </div>
  </div>
</section>

<!-- STATS STRIP -->
<div class="stats-strip">
  <div class="container">
    <div class="row g-3 text-center">
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">100+</div>
          <div class="label">Menu Items</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">2019</div>
          <div class="label">Established</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">20</div>
          <div class="label">Seats Available</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="num">AI</div>
          <div class="label">Chatbot Powered</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- BEST SELLERS -->
<section class="bestsellers-section" id="bestsellers">
  <div class="container">
    <div class="row align-items-end mb-1">
      <div class="col">
        <span class="section-tag">Customer Favorites</span>
        <h2 class="section-title mt-2">Our Best Sellers</h2>
        <div class="section-divider"></div>
        <p class="mt-2" style="color:var(--text-muted,#6b7280); font-size:0.9rem; max-width:480px;">
          The dishes and drinks our customers order again and again. Don't miss out on these crowd favourites!
        </p>
      </div>
      <div class="col-auto">
        <a href="menu.php" class="btn" style="background:var(--green-dark,#2d4a1e); color:white; border-radius:25px; padding:9px 22px; font-weight:700; font-size:0.85rem;">
          <i class="bi bi-journal-text me-1"></i>View Full Menu
        </a>
      </div>
    </div>

    <div class="bs-grid">
      <?php foreach ($bestSellers as $item): ?>
      <?php
          $imgFile  = $item['force_image'] ?? ($imageMap[$item['name']] ?? '');
          $imgPath  = !empty($imgFile) ? 'uploads/menu/' . basename($imgFile) : '';
          $hasImage = !empty($imgPath) && file_exists($imgPath);
      ?>
      <div class="bs-card fade-in-up">
        <?php if ($hasImage): ?>
          <img class="bs-card-img" src="<?= htmlspecialchars($imgPath) ?>"
               alt="<?= htmlspecialchars($item['name']) ?>">
        <?php else: ?>
          <div class="bs-card-placeholder">
            <span style="font-size:3.5rem;"><?= $item['emoji'] ?></span>
            <span>Photo coming soon</span>
          </div>
        <?php endif; ?>

        <span class="bs-card-badge"><?= htmlspecialchars($item['badge']) ?></span>
        <span class="bs-bestseller-tag">⭐ Best Seller</span>

        <div class="bs-card-body">
          <div class="bs-card-name"><?= htmlspecialchars($item['name']) ?></div>
          <div class="bs-card-desc"><?= htmlspecialchars($item['description']) ?></div>
          <div class="bs-card-footer">
            <div class="bs-card-price"><?= htmlspecialchars($item['price']) ?></div>
            <a href="menu.php#<?= strtolower($item['category']) ?>&item=<?= urlencode($item['name']) ?>" class="bs-view-btn">See in Menu →</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
  <div class="container">
    <div class="cta-box">
      <div>
        <h3>Ready to visit us?</h3>
        <p>Reserve your spot upstairs or ask Oli Bot anything about our menu — we're here for you.</p>
      </div>
      <div class="cta-actions">
        <a href="book_reservation.php" class="btn-cta-primary">
          <i class="bi bi-calendar-check me-2"></i>Book a Table
        </a>
        <a href="chat/ask_oli.php" class="btn-cta-outline">
          <i class="bi bi-chat-dots me-2"></i>Ask Oli
        </a>
      </div>
    </div>
  </div>
</section>

<!-- FEATURE STRIP -->
<footer>
  <div class="feature-strip">
  <div class="container">
    <div class="row g-4">
      <div class="col-6 col-md-3">
        <div class="feature-item">
          <i class="bi bi-cup-hot-fill"></i>
          <h6>Artisan Drinks</h6>
          <p>Crafted with care, every sip</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-item">
          <i class="bi bi-calendar-check-fill"></i>
          <h6>Easy Reservations</h6>
          <p>Book your table online</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-item">
          <i class="bi bi-chat-dots-fill"></i>
          <h6>Ask Oli AI</h6>
          <p>Get instant menu help</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="feature-item">
          <i class="bi bi-geo-alt-fill"></i>
          <h6>Cozy 2nd Floor</h6>
          <p>4 tables · 5 seats each</p>
        </div>
      </div>
    </div>
  </div>
</div>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>