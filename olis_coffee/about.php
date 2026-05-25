<?php
// about.php - About Project & Developers Page
session_start();
require_once 'includes/Auth.php';
Auth::requireLogin();
$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>About – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    .dev-card {
      background: white;
      border-radius: 16px;
      padding: 2rem 1.5rem;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      transition: transform 0.25s, box-shadow 0.25s;
      border-top: 4px solid var(--green-mid);
      height: 100%;
    }
    .dev-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.13);
    }
    .dev-avatar {
      width: 80px; height: 80px;
      background: linear-gradient(135deg, var(--green-dark), var(--green-mid));
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 2rem;
      margin: 0 auto 1rem;
      box-shadow: 0 4px 16px rgba(45,74,30,0.25);
    }
    .dev-name {
      font-family: 'Playfair Display', serif;
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--green-dark);
      margin-bottom: 4px;
    }
    .dev-role {
      font-size: 0.78rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 10px;
    }
    .dev-badge {
      display: inline-block;
      background: #f0fdf4;
      color: var(--green-mid);
      border: 1px solid #bbf7d0;
      border-radius: 20px;
      font-size: 0.72rem;
      padding: 3px 10px;
      margin: 2px;
      font-weight: 600;
    }
    .feature-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: white;
      border: 1.5px solid #d1fae5;
      color: var(--green-dark);
      border-radius: 25px;
      padding: 6px 14px;
      font-size: 0.82rem;
      font-weight: 600;
      margin: 4px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .subject-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.07);
      border-left: 4px solid var(--gold);
      height: 100%;
    }
    .subject-card h6 {
      font-family: 'Playfair Display', serif;
      color: var(--green-dark);
      font-weight: 700;
      margin-bottom: 0.8rem;
    }
    .subject-card li {
      font-size: 0.85rem;
      color: var(--text-dark);
      margin-bottom: 4px;
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
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house me-1"></i>Home</a></li>
        <li class="nav-item"><a class="nav-link" href="menu.php"><i class="bi bi-journal-text me-1"></i>Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="book_reservation.php"><i class="bi bi-calendar-check me-1"></i>Reservations</a></li>
        <li class="nav-item"><a class="nav-link" href="chatbot.php"><i class="bi bi-chat-dots me-1"></i>Ask Oli</a></li>
        <li class="nav-item"><a class="nav-link active" href="about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
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
    <p class="hero-sub">Learn More</p>
    <h1 class="hero-title">About <span>The System</span></h1>
    <p class="hero-desc mt-3">A web-based  system built for Oli's SelfieTea & Coffee tailored for efficiency, convenience, and innovation.</p>
  </div>
</section>

<div class="container py-5">

  <!-- ABOUT THE SYSTEM -->
  <div class="row align-items-center mb-5 g-4">
    <div class="col-lg-6">
      <span class="section-tag">About the Project</span>
      <h2 class="section-title mt-2">Oli's SelfieTea & Coffee System</h2>
      <div class="section-divider"></div>
      <p style="color:var(--text-muted); line-height:1.8;">
        Oli's SelfieTea & Coffee is a small but growing coffee shop that offers a variety of milk teas,
        coffee, and snacks. As the number of customers increased, the shop faced challenges in managing
        daily operations efficiently — relying on traditional, manual inventory, revenue, and customer count methods.
      </p>
      <p style="color:var(--text-muted); line-height:1.8; margin-top:0.8rem;">
        This system was developed to address those challenges — allowing customers to browse the menu,
        make reservations, and interact with an AI chatbot, while administrators can manage menu items,
        reservations, and view reports all in one place.
      </p>
    </div>
    <div class="col-lg-6">
      <div class="row g-3">
        <div class="col-6">
          <div class="stat-card text-center">
            <h3 style="font-size:2.5rem;">3</h3>
            <p>Subjects Integrated</p>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-card text-center" style="border-color:var(--gold);">
            <h3 style="font-size:2.5rem;">4</h3>
            <p>Database Tables</p>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-card text-center" style="border-color:var(--brown);">
            <h3 style="font-size:2.5rem;">2019</h3>
            <p>Shop Est. Year</p>
          </div>
        </div>
        <div class="col-6">
          <div class="stat-card text-center" style="border-color:var(--green-light);">
            <h3 style="font-size:2.5rem;">AI</h3>
            <p>Powered Chatbot</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SUBJECTS -->
  <div class="mb-5">
    <div class="text-center mb-4">
      <span class="section-tag">Academic Requirements</span>
      <h2 class="section-title mt-2">Three Subjects, One System</h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-3">
      <div class="col-md-4">
        <div class="subject-card">
          <h6><i class="bi bi-globe me-2" style="color:var(--gold);"></i>ITEL 203 - Web Systems and Technologies</h6>
          <ul class="list-unstyled mb-0">
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Bootstrap 5 UI</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Custom CSS & Animations</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>JavaScript Interactivity</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>PHP OOP (Login, Auth, CRUD)</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Session-based Authentication</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Password Hashing</li>
          </ul>
        </div>
      </div>
      <div class="col-md-4">
        <div class="subject-card" style="border-left-color: var(--green-mid);">
          <h6><i class="bi bi-cpu me-2" style="color:var(--green-mid);"></i>ITEP 206 - Integrative Programming Technologies 1</h6>
          <ul class="list-unstyled mb-0">
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>AI Chatbot (Claude API)</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>GCash Payment Integration</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Customer Inquiry Automation</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Online Reservation System</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Real-time AI Responses</li>
          </ul>
        </div>
      </div>
      <div class="col-md-4">
        <div class="subject-card" style="border-left-color: var(--brown);">
          <h6><i class="bi bi-database me-2" style="color:var(--brown);"></i>ITEP 204 - Advanced Database Systems</h6>
          <ul class="list-unstyled mb-0">
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>MySQL Relational Database</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>4-Table JOIN Queries</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Foreign Keys</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>One-to-Many Relationships</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Full CRUD Operations</li>
            <li><i class="bi bi-check2 me-2" style="color:var(--green-mid);"></i>Auto Backup Event</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- KEY FEATURES -->
  <div class="mb-5 text-center">
    <span class="section-tag">What It Can Do</span>
    <h2 class="section-title mt-2">Key Features</h2>
    <div class="section-divider mx-auto"></div>
    <div class="mt-3">
      <span class="feature-badge"><i class="bi bi-person-check" style="color:var(--green-mid);"></i> Login & Logout</span>
      <span class="feature-badge"><i class="bi bi-journal-text" style="color:var(--green-mid);"></i> Full Menu Display</span>
      <span class="feature-badge"><i class="bi bi-calendar-check" style="color:var(--green-mid);"></i> Seat Reservation</span>
      <span class="feature-badge"><i class="bi bi-chat-dots" style="color:var(--green-mid);"></i> AI Chatbot</span>
      <span class="feature-badge"><i class="bi bi-person-gear" style="color:var(--green-mid);"></i> Edit Profile</span>
      <span class="feature-badge"><i class="bi bi-pencil-square" style="color:var(--green-mid);"></i> Menu CRUD</span>
      <span class="feature-badge"><i class="bi bi-bar-chart-line" style="color:var(--green-mid);"></i> Reports</span>
      <span class="feature-badge"><i class="bi bi-shield-lock" style="color:var(--green-mid);"></i> Password Hashing</span>
      <span class="feature-badge"><i class="bi bi-link-45deg" style="color:var(--green-mid);"></i> JOIN Queries</span>
      <span class="feature-badge"><i class="bi bi-phone" style="color:var(--green-mid);"></i> GCash Payment</span>
    </div>
  </div>

  <!-- DEVELOPERS -->
  <div class="mb-5">
    <div class="text-center mb-4">
      <span class="section-tag">The Team</span>
      <h2 class="section-title mt-2">Meet the Developers</h2>
      <div class="section-divider mx-auto"></div>
    </div>
    <div class="row g-4 justify-content-center">

      <div class="col-md-4 col-sm-6">
        <div class="dev-card">
          <div class="dev-avatar">
            <img src="assets/eper.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
          </div>
          <div class="dev-name">Lacson, Jennifer P.</div>
          <div class="dev-role">Developer 1</div>
          <div>
            <span class="dev-badge">PHP Backend</span>
            <span class="dev-badge">Database</span>
            <span class="dev-badge">OOP</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="dev-card">
          <div class="dev-avatar">
            <img src="assets/lea.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
          </div>
          <div class="dev-name">Narvacan, Lea Chelsy K.</div>
          <div class="dev-role">Developer 2</div>
          <div>
            <span class="dev-badge">Bootstrap</span>
            <span class="dev-badge">CSS</span>
            <span class="dev-badge">JavaScript</span>
          </div>
        </div>
      </div>

      <div class="col-md-4 col-sm-6">
        <div class="dev-card">
          <div class="dev-avatar">
            <img src="assets/claire.jpg" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
          </div>
          <div class="dev-name">Reyes, Clarize Dyanne R.</div>
          <div class="dev-role">Developer 3</div>
          <div>
            <span class="dev-badge">MySQL</span>
            <span class="dev-badge">JOIN Queries</span>
            <span class="dev-badge">Reports</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- TOOLS USED -->
  <div class="text-center">
    <span class="section-tag">Built With</span>
    <h2 class="section-title mt-2">Development Tools</h2>
    <div class="section-divider mx-auto"></div>
    <div class="mt-3">
      <span class="feature-badge">PHP 8+</span>
      <span class="feature-badge">MySQL</span>
      <span class="feature-badge">Bootstrap 5</span>
      <span class="feature-badge">JavaScript</span>
      <span class="feature-badge">HTML & CSS</span>
      <span class="feature-badge">XAMPP</span>
      <span class="feature-badge">phpMyAdmin</span>
      <span class="feature-badge">Visual Studio Code</span>
      <span class="feature-badge">GitHub</span>
      <span class="feature-badge">Claude AI API</span>
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