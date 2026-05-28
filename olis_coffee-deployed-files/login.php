<?php
// login.php — Login + Register
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';

// Already logged in?
if (Auth::check()) {
    header("Location: " . (Auth::isAdmin() ? "admin/dashboard.php" : "index.php"));
    exit();
}

$loginError  = '';
$regError    = '';
$regSuccess  = '';
$activeTab   = 'login'; // which tab to show on reload

// ── LOGIN ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $auth   = new Auth();
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header("Location: " . ($result['role'] === 'admin' ? "admin/dashboard.php" : "index.php"));
            exit();
        } else {
            $loginError = $result['message'];
            $activeTab  = 'login';
        }
    } else {
        $loginError = 'Please fill in all fields.';
        $activeTab  = 'login';
    }
}

// ── REGISTER ─────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $activeTab = 'register';

    $name      = trim($_POST['reg_name']     ?? '');
    $email     = trim($_POST['reg_email']    ?? '');
    $password  = $_POST['reg_password']      ?? '';
    $confirm   = $_POST['reg_confirm']       ?? '';

    if (!$name || !$email || !$password || !$confirm) {
        $regError = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $regError = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $regError = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $regError = 'Passwords do not match.';
    } else {
        $db = getDB();

        // Check if email already taken
        $chk = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $chk->bind_param("s", $email);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $regError = 'That email is already registered. Please log in instead.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins  = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')");
            $ins->bind_param("sss", $name, $email, $hash);
            if ($ins->execute()) {
                // Auto-login after registration
                $auth   = new Auth();
                $result = $auth->login($email, $password);
                if ($result['success']) {
                    header("Location: index.php");
                    exit();
                }
                $regSuccess = '✅ Account created! You can now log in.';
                $activeTab  = 'login';
            } else {
                $regError = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" type="image/x-icon" href="../assets/logo.png">
  <style>
    /* ── Tab switcher ── */
    .auth-tabs {
      display: flex;
      border-bottom: 2px solid #e5e7eb;
      margin-bottom: 1.4rem;
    }
    .auth-tab-btn {
      flex: 1;
      background: none;
      border: none;
      padding: 10px 0;
      font-weight: 700;
      font-size: 0.88rem;
      color: var(--text-muted, #6b7280);
      cursor: pointer;
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
      transition: color 0.2s, border-color 0.2s;
      letter-spacing: 0.5px;
    }
    .auth-tab-btn.active {
      color: var(--green-dark, #2d4a1e);
      border-bottom-color: var(--green-dark, #2d4a1e);
    }
    .auth-tab-btn:hover:not(.active) {
      color: #374151;
    }
    .auth-pane { display: none; }
    .auth-pane.active { display: block; }

    /* ── Strength bar ── */
    #strengthBar {
      height: 4px;
      border-radius: 3px;
      transition: width 0.3s, background 0.3s;
      margin-top: 5px;
    }
    #strengthText {
      font-size: 0.7rem;
      margin-top: 2px;
    }
  </style>
</head>
<body>
<div class="login-wrapper">
  <div class="login-card">

    <div class="login-logo">
      <img src="assets/logo.png" alt="Oli's SelfieTea &amp; Coffee" style="width:140px;height:140px;object-fit:contain;border-radius:50%;">
      <h2>Oli's SelfieTea</h2>
      <p>&amp; Coffee</p>
    </div>

    <!-- ── TAB BUTTONS ── -->
    <div class="auth-tabs">
      <button class="auth-tab-btn <?= $activeTab === 'login'    ? 'active' : '' ?>"
              onclick="switchTab('login')" type="button">
        <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
      </button>
      <button class="auth-tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>"
              onclick="switchTab('register')" type="button">
        <i class="bi bi-person-plus me-1"></i>Create Account
      </button>
    </div>

    <!-- ════════════════════════════════════════════
         LOGIN PANE
    ════════════════════════════════════════════ -->
    <div class="auth-pane <?= $activeTab === 'login' ? 'active' : '' ?>" id="paneLogin">

      <?php if ($loginError): ?>
        <div class="alert alert-danger alert-olis alert-dismissible fade show">
          <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($loginError) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($regSuccess): ?>
        <div class="alert alert-success alert-olis alert-dismissible fade show">
          <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($regSuccess) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <input type="hidden" name="action" value="login">

        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="you@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password" class="form-control"
                   placeholder="••••••••" required>
            <button type="button" class="btn btn-outline-secondary" onclick="togglePw('password', this)">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-login">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </button>
      </form>

      <div class="demo-creds mt-3">
        <strong><i class="bi bi-info-circle me-1"></i>For PROJECT purposes ONLY. DO NOT ENGAGE if you are not aware of the existence of this website.</strong><br>
      </div>

      <p class="text-center mt-3 mb-0" style="font-size:0.83rem; color:var(--text-muted);">
        Don't have an account?
        <a href="#" onclick="switchTab('register'); return false;"
           style="color:var(--green-dark); font-weight:700; text-decoration:none;">
          Create one free →
        </a>
      </p>
    </div>

    <!-- ════════════════════════════════════════════
         REGISTER PANE
    ════════════════════════════════════════════ -->
    <div class="auth-pane <?= $activeTab === 'register' ? 'active' : '' ?>" id="paneRegister">

      <?php if ($regError): ?>
        <div class="alert alert-danger alert-olis alert-dismissible fade show">
          <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($regError) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form method="POST" action="" id="regForm">
        <input type="hidden" name="action" value="register">

        <div class="mb-3">
          <label for="reg_name" class="form-label">Full Name</label>
          <input type="text" id="reg_name" name="reg_name" class="form-control"
                 placeholder="Juan dela Cruz"
                 value="<?= htmlspecialchars($_POST['reg_name'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label for="reg_email" class="form-label">Email Address</label>
          <input type="email" id="reg_email" name="reg_email" class="form-control"
                 placeholder="you@email.com"
                 value="<?= htmlspecialchars($_POST['reg_email'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
          <label for="reg_password" class="form-label">
            Password <small class="text-muted">(min. 6 characters)</small>
          </label>
          <div class="input-group">
            <input type="password" id="reg_password" name="reg_password" class="form-control"
                   placeholder="••••••••" required minlength="6"
                   oninput="checkStrength(this.value)">
            <button type="button" class="btn btn-outline-secondary"
                    onclick="togglePw('reg_password', this)">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <!-- Strength bar -->
          <div style="background:#e5e7eb; border-radius:3px; height:4px; margin-top:6px;">
            <div id="strengthBar" style="width:0%; height:4px; border-radius:3px; background:#e5e7eb;"></div>
          </div>
          <div id="strengthText" class="text-muted"></div>
        </div>

        <div class="mb-4">
          <label for="reg_confirm" class="form-label">Confirm Password</label>
          <div class="input-group">
            <input type="password" id="reg_confirm" name="reg_confirm" class="form-control"
                   placeholder="••••••••" required oninput="checkMatch()">
            <button type="button" class="btn btn-outline-secondary"
                    onclick="togglePw('reg_confirm', this)">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div id="matchMsg" style="font-size:0.72rem; margin-top:4px;"></div>
        </div>

        <button type="submit" class="btn btn-login" style="background:var(--green-mid);">
          <i class="bi bi-person-check me-2"></i>Create Account
        </button>
      </form>

      <p class="text-center mt-3 mb-0" style="font-size:0.83rem; color:var(--text-muted);">
        Already have an account?
        <a href="#" onclick="switchTab('login'); return false;"
           style="color:var(--green-dark); font-weight:700; text-decoration:none;">
          Sign in →
        </a>
      </p>
    </div>

  </div><!-- /login-card -->
</div><!-- /login-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
// ── Tab switcher ──────────────────────────────────────────────────────────────
function switchTab(tab) {
  document.querySelectorAll('.auth-pane').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.auth-tab-btn').forEach(b => b.classList.remove('active'));
  document.getElementById('pane' + tab.charAt(0).toUpperCase() + tab.slice(1)).classList.add('active');
  document.querySelectorAll('.auth-tab-btn')[tab === 'login' ? 0 : 1].classList.add('active');
}

// ── Show/hide password ────────────────────────────────────────────────────────
function togglePw(id, btn) {
  const input = document.getElementById(id);
  const show  = input.type === 'password';
  input.type  = show ? 'text' : 'password';
  btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}

// ── Password strength bar ─────────────────────────────────────────────────────
function checkStrength(val) {
  const bar  = document.getElementById('strengthBar');
  const txt  = document.getElementById('strengthText');
  let score  = 0;
  if (val.length >= 6)  score++;
  if (val.length >= 10) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const levels = [
    { w: '0%',   color: '#e5e7eb', label: '' },
    { w: '25%',  color: '#ef4444', label: '🔴 Weak' },
    { w: '50%',  color: '#f59e0b', label: '🟡 Fair' },
    { w: '75%',  color: '#3b82f6', label: '🔵 Good' },
    { w: '100%', color: '#16a34a', label: '🟢 Strong' },
  ];
  const lvl = levels[Math.min(score, 4)];
  bar.style.width      = lvl.w;
  bar.style.background = lvl.color;
  txt.textContent      = lvl.label;
  txt.style.color      = lvl.color;
}

// ── Password match indicator ──────────────────────────────────────────────────
function checkMatch() {
  const pw  = document.getElementById('reg_password').value;
  const cfm = document.getElementById('reg_confirm').value;
  const msg = document.getElementById('matchMsg');
  if (!cfm) { msg.textContent = ''; return; }
  if (pw === cfm) {
    msg.textContent = '✅ Passwords match';
    msg.style.color = '#16a34a';
  } else {
    msg.textContent = '❌ Passwords do not match';
    msg.style.color = '#dc2626';
  }
}

// ── Prevent register submit if passwords don't match ─────────────────────────
document.getElementById('regForm').addEventListener('submit', function(e) {
  const pw  = document.getElementById('reg_password').value;
  const cfm = document.getElementById('reg_confirm').value;
  if (pw !== cfm) {
    e.preventDefault();
    document.getElementById('matchMsg').textContent = '❌ Passwords do not match';
    document.getElementById('matchMsg').style.color = '#dc2626';
  }
});
</script>
</body>
</html>