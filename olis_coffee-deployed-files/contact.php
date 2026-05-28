<?php
// contact.php - Contact Info Page
session_start();
require_once 'includes/Auth.php';
Auth::requireLogin();
$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/png" href="assets/logo.png">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .contact-card {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      height: 100%;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .contact-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    .contact-icon {
      width: 60px; height: 60px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
      margin-bottom: 1rem;
    }
    .contact-label {
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: var(--text-muted);
      margin-bottom: 4px;
    }
    .contact-value {
      font-size: 1rem;
      font-weight: 700;
      color: var(--green-dark);
    }
    .contact-value a {
      color: var(--green-dark);
      text-decoration: none;
    }
    .contact-value a:hover { color: var(--gold); }
    .hours-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #f0f0f0;
      font-size: 0.9rem;
    }
    .hours-row:last-child { border-bottom: none; }
    .hours-day { color: var(--text-dark); font-weight: 600; }
    .hours-time { color: var(--green-mid); font-weight: 700; }
    .hours-closed { color: #ef4444; font-weight: 700; }
    .map-placeholder {
      background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
      border-radius: 12px;
      height: 300px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: var(--green-dark);
      font-size: 1rem;
      border: 2px dashed var(--green-light);
    }
    .social-btn {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 700;
      font-size: 0.9rem;
      transition: all 0.2s;
      margin-bottom: 10px;
    }
    .social-btn:hover { transform: translateX(4px); }
    .social-fb { background: #1877f220; color: #1877f2; }
    .social-fb:hover { background: #1877f2; color: white; }
    .social-ig { background: #e1306c20; color: #e1306c; }
    .social-ig:hover { background: #e1306c; color: white; }
    .social-gcash { background: #0070e020; color: #0070e0; }
    .social-gcash:hover { background: #0070e0; color: white; }
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
        <li class="nav-item"><a class="nav-link" href="menu.php"><i class="bi bi-journal-text me-1"></i>Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item"><a class="nav-link" href="chat/ask_oli.php"><i class="bi bi-chat-dots me-1"></i>Ask Oli</a></li>
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
        <li class="nav-item"><a class="nav-link active" href="contact.php"><i class="bi bi-geo-alt me-1"></i>Contact</a></li>
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
    <p class="hero-sub">Get In Touch</p>
    <h1 class="hero-title">Contact <span>Information</span></h1>
    <p class="hero-desc mt-3">Find us, call us, or reach out online — we'd love to hear from you!</p>
  </div>
</section>

<div class="container py-5">

  <!-- CONTACT CARDS -->
  <div class="row g-4 mb-5">

    <div class="col-md-4">
      <div class="contact-card">
        <div class="contact-icon" style="background:#f0fdf4;">📍</div>
        <div class="contact-label">Address</div>
        <div class="contact-value">
          Oli's SelfieTea & Coffee<br>
          <span style="font-weight:400; font-size:0.9rem; color:var(--text-muted);">
            58 Paseo de Escudero,<br>San Pablo City, Philippines
          </span>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="contact-card">
        <div class="contact-icon" style="background:#fff7ed;">📞</div>
        <div class="contact-label">Phone</div>
        <div class="contact-value">
          <a href="tel:+639XXXXXXXXX">+63 976 112 5193</a>
        </div>
        <div class="mt-3">
          <div class="contact-label">Email</div>
          <div class="contact-value">
            <a href="mailto:oliscoffee@email.com">olisselfieteaandcoffee@gmail.com</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="contact-card">
        <div class="contact-icon" style="background:#fdf4ff;">🕐</div>
        <div class="contact-label">Store Hours</div>
        <div class="hours-row">
          <span class="hours-day">Monday – Friday</span>
          <span class="hours-time">11:00 AM - 9:00 PM</span>
        </div>
        <div class="hours-row">
          <span class="hours-day">Saturday</span>
          <span class="hours-time">11:00 AM - 9:00 PM</span>
        </div>
        <div class="hours-row">
          <span class="hours-day">Sunday</span>
          <span class="hours-time">11:00 AM - 9:00 PM</span>
        </div>
        <p class="mt-3 mb-0" style="font-size:0.78rem; color:var(--text-muted);">
        </p>
      </div>
    </div>

  </div>

  <div class="row g-4">

    <!-- MAP -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-header" style="background:var(--green-dark); color:white;">
          <i class="bi bi-geo-alt-fill me-2"></i>Our Location
        </div>
        <div class="card-body p-3">
          <div class="map-placeholder">
            <i class="bi bi-map" style="font-size:3rem; margin-bottom:12px; opacity:0.5;"></i>
            <p class="mb-1 fw-bold">Interactive Map</p>
            <a href="https://www.google.com/maps/place/Oli's+SelfieTea+and+Coffee/@14.0715569,121.324806,19z/data=!3m1!4b1!4m6!3m5!1s0x33bd5d08c967c8c1:0xb4bb51a264aed693!8m2!3d14.0715556!4d121.3254497!16s%2Fg%2F11j3x3r6hd?entry=ttu&g_ep=EgoyMDI2MDUwMi4wIKXMDSoASAFQAw%3D%3D" target="_blank"
               class="btn btn-sm mt-2" style="background:var(--green-dark); color:white; border-radius:20px;">
              <i class="bi bi-box-arrow-up-right me-1"></i>Open in Google Maps
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- SOCIAL & PAYMENT -->
    <div class="col-lg-5">

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header" style="background:var(--green-dark); color:white;">
          <i class="bi bi-share me-2"></i>Follow Us
        </div>
        <div class="card-body">
          <a href="https://www.facebook.com/OlisSelfietea" class="social-btn social-fb">
            <i class="bi bi-facebook" style="font-size:1.2rem;"></i>
            Facebook Page
          </a>
          <a href="https://www.instagram.com/olisselfietea?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="social-btn social-ig">
            <i class="bi bi-instagram" style="font-size:1.2rem;"></i>
            Instagram
          </a>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header" style="background:var(--green-dark); color:white;">
          <i class="bi bi-credit-card me-2"></i>Payment Methods
        </div>
        <div class="card-body">
          <a href="#" class="social-btn social-gcash">
            <img src="assets/gcash logo.png"height="22" alt="GCash">GCash
          </a>
          <div class="social-btn" style="background:#f0fdf4; color:var(--green-dark);">
            <i class="bi bi-cash-coin" style="font-size:1.2rem;"></i>
            Cash In-Store 
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>