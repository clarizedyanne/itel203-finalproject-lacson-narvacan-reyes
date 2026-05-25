<?php
// profile.php - Customer Edit Profile
session_start();
require_once 'includes/Auth.php';
require_once 'includes/db.php';

Auth::requireLogin();

$db = getDB();
$userId = $_SESSION['user_id'];
$message = '';
$msgType = 'success';

// Fetch current user data
$stmt = $db->prepare("SELECT id, name, email, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!$name || !$email) {
            $message = 'Name and email are required.';
            $msgType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $msgType = 'danger';
        } else {
            // Check if email is taken by another user
            $check = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check->bind_param("si", $email, $userId);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $message = 'That email is already in use by another account.';
                $msgType = 'danger';
            } else {
                $upd = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $upd->bind_param("ssi", $name, $email, $userId);
                if ($upd->execute()) {
                    $_SESSION['user_name'] = $name;
                    $user['name']  = $name;
                    $user['email'] = $email;
                    $message = 'Profile updated successfully!';
                } else {
                    $message = 'Error updating profile.';
                    $msgType = 'danger';
                }
            }
        }
    } elseif ($action === 'change_password') {
        $currentPw  = $_POST['current_password'] ?? '';
        $newPw      = $_POST['new_password'] ?? '';
        $confirmPw  = $_POST['confirm_password'] ?? '';

        // Fetch current hash
        $stmt2 = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();
        $hash = $stmt2->get_result()->fetch_assoc()['password'];

        if (!password_verify($currentPw, $hash)) {
            $message = 'Current password is incorrect.';
            $msgType = 'danger';
        } elseif (strlen($newPw) < 6) {
            $message = 'New password must be at least 6 characters.';
            $msgType = 'danger';
        } elseif ($newPw !== $confirmPw) {
            $message = 'New passwords do not match.';
            $msgType = 'danger';
        } else {
            $newHash = password_hash($newPw, PASSWORD_DEFAULT);
            $upd2 = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd2->bind_param("si", $newHash, $userId);
            if ($upd2->execute()) {
                $message = 'Password changed successfully!';
            } else {
                $message = 'Error changing password.';
                $msgType = 'danger';
            }
        }
    } elseif ($action === 'upload_photo') {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $file     = $_FILES['profile_pic'];
            $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize  = 2 * 1024 * 1024; // 2MB

            if (!in_array($file['type'], $allowed)) {
                $message = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
                $msgType = 'danger';
            } elseif ($file['size'] > $maxSize) {
                $message = 'Image must be under 2MB.';
                $msgType = 'danger';
            } else {
                $uploadDir = __DIR__ . '/uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    // Delete old photo if exists
                    if (!empty($user['profile_pic'])) {
                        $oldPath = __DIR__ . '/uploads/profiles/' . $user['profile_pic'];
                        if (file_exists($oldPath)) unlink($oldPath);
                    }
                    $upd = $db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    $upd->bind_param("si", $filename, $userId);
                    $upd->execute();
                    $user['profile_pic'] = $filename;
                    $message = 'Profile photo updated!';
                } else {
                    $message = 'Failed to upload image. Please try again.';
                    $msgType = 'danger';
                }
            }
        } else {
            $message = 'No file selected.';
            $msgType = 'danger';
        }
    } elseif ($action === 'remove_photo') {
        if (!empty($user['profile_pic'])) {
            $oldPath = __DIR__ . '/uploads/profiles/' . $user['profile_pic'];
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $upd = $db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
        $upd->bind_param("i", $userId);
        $upd->execute();
        $user['profile_pic'] = null;
        $message = 'Profile photo removed.';
    }
}

$userName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Profile – Oli's SelfieTea & Coffee</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <div class="brand-logo">☕</div>
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
        <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php"><i class="bi bi-geo-alt me-1"></i>Contact</a></li>
        <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="bi bi-person-circle me-1"></i>My Profile</a></li>
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

<!-- HERO MINI -->
<div style="background: linear-gradient(135deg, var(--green-dark), #1a3510); padding: 40px 0 30px; color: var(--cream);">
  <div class="container">
    <p style="font-size:0.8rem; letter-spacing:3px; text-transform:uppercase; color:rgba(245,240,232,0.6); margin-bottom:4px;">Account</p>
    <h2 style="font-family:'Playfair Display',serif; font-weight:700;">My Profile</h2>
  </div>
</div>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?> alert-olis alert-dismissible fade show mb-4">
          <i class="bi bi-<?= $msgType === 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
          <?= htmlspecialchars($message) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <!-- PROFILE INFO CARD -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header" style="background:var(--green-dark); color:white;">
          <i class="bi bi-person-fill me-2"></i>Personal Information
        </div>
        <div class="card-body p-4">

          <!-- Avatar + Photo Upload -->
          <div class="text-center mb-4">
            <?php $picSrc = !empty($user['profile_pic']) ? 'uploads/profiles/' . htmlspecialchars($user['profile_pic']) : ''; ?>
            <div style="position:relative; display:inline-block;">
              <div id="avatarCircle" style="width:100px; height:100px; border-radius:50%; overflow:hidden;
                          background: linear-gradient(135deg, var(--green-dark), var(--green-mid));
                          display:flex; align-items:center; justify-content:center;
                          font-size:2.8rem; margin:0 auto; box-shadow:0 4px 20px rgba(45,74,30,0.3);
                          border: 3px solid var(--gold);">
                <?php if ($picSrc): ?>
                  <img src="<?= $picSrc ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                  👤
                <?php endif; ?>
              </div>
              <!-- Camera button -->
              <label for="photoInput" title="Change photo"
                style="position:absolute; bottom:2px; right:2px; width:30px; height:30px;
                       background:var(--green-dark); border-radius:50%; cursor:pointer;
                       display:flex; align-items:center; justify-content:center;
                       box-shadow:0 2px 8px rgba(0,0,0,0.3); border:2px solid white;">
                <i class="bi bi-camera-fill" style="color:white; font-size:0.75rem;"></i>
              </label>
            </div>

            <h5 class="mt-3 mb-0" style="font-family:'Playfair Display',serif; color:var(--green-dark);">
              <?= htmlspecialchars($user['name']) ?>
            </h5>
            <small class="text-muted">Customer Account</small>

            <!-- Hidden upload form -->
            <form method="POST" enctype="multipart/form-data" id="photoForm">
              <input type="hidden" name="action" value="upload_photo">
              <input type="file" name="profile_pic" id="photoInput" accept="image/*"
                     style="display:none;" onchange="previewPhoto(this)">
            </form>

            <!-- Preview + confirm/cancel (hidden until a photo is chosen) -->
            <div id="photoActions" style="display:none; margin-top:12px;">
              <p class="text-muted" style="font-size:0.78rem; margin-bottom:8px;">Save this photo?</p>
              <button onclick="document.getElementById('photoForm').submit();"
                class="btn btn-sm btn-success me-1" style="border-radius:20px; font-size:0.78rem;">
                <i class="bi bi-check me-1"></i>Save
              </button>
              <button onclick="cancelPhoto()"
                class="btn btn-sm btn-outline-secondary" style="border-radius:20px; font-size:0.78rem;">
                Cancel
              </button>
              <?php if (!empty($user['profile_pic'])): ?>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Remove your profile photo?')">
                <input type="hidden" name="action" value="remove_photo">
                <button type="submit" class="btn btn-sm btn-outline-danger ms-1" style="border-radius:20px; font-size:0.78rem;">
                  <i class="bi bi-trash me-1"></i>Remove
                </button>
              </form>
              <?php endif; ?>
            </div>
          </div>

          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="name" class="form-control" required
                     value="<?= htmlspecialchars($user['name']) ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Email Address</label>
              <input type="email" name="email" class="form-control" required
                     value="<?= htmlspecialchars($user['email']) ?>">
            </div>
            <button type="submit" class="btn w-100" style="background:var(--green-dark); color:white; border-radius:10px; padding:0.65rem;">
              <i class="bi bi-save me-2"></i>Save Changes
            </button>
          </form>
        </div>
      </div>

      <!-- CHANGE PASSWORD CARD -->
      <div class="card border-0 shadow-sm">
        <div class="card-header" style="background:var(--brown); color:white;">
          <i class="bi bi-shield-lock-fill me-2"></i>Change Password
        </div>
        <div class="card-body p-4">
          <form method="POST">
            <input type="hidden" name="action" value="change_password">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <div class="input-group">
                <input type="password" name="current_password" id="curPw" class="form-control" required placeholder="••••••••">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePw('curPw', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password <small class="text-muted">(min. 6 characters)</small></label>
              <div class="input-group">
                <input type="password" name="new_password" id="newPw" class="form-control" required placeholder="••••••••">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePw('newPw', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <div class="mb-4">
              <label class="form-label">Confirm New Password</label>
              <div class="input-group">
                <input type="password" name="confirm_password" id="conPw" class="form-control" required placeholder="••••••••">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePw('conPw', this)">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>
            <button type="submit" class="btn w-100" style="background:var(--brown); color:white; border-radius:10px; padding:0.65rem;">
              <i class="bi bi-lock me-2"></i>Change Password
            </button>
          </form>
        </div>
      </div>

      <div class="text-center mt-4">
        <a href="index.php" class="btn btn-outline-secondary" style="border-radius:25px; padding:8px 24px;">
          <i class="bi bi-arrow-left me-2"></i>Back to Menu
        </a>
      </div>

    </div>
  </div>
</div>

<footer>
  <strong>Oli's SelfieTea & Coffee</strong> · Est. 2019 · All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
<script>
function previewPhoto(input) {
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = function(e) {
    const circle = document.getElementById('avatarCircle');
    circle.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
    document.getElementById('photoActions').style.display = 'block';
  };
  reader.readAsDataURL(input.files[0]);
}

function cancelPhoto() {
  // Reset file input
  const input = document.getElementById('photoInput');
  input.value = '';
  // Restore original avatar
  <?php if (!empty($user['profile_pic'])): ?>
  document.getElementById('avatarCircle').innerHTML =
    '<img src="uploads/profiles/<?= htmlspecialchars($user['profile_pic']) ?>" style="width:100%;height:100%;object-fit:cover;">';
  <?php else: ?>
  document.getElementById('avatarCircle').innerHTML = '👤';
  <?php endif; ?>
  document.getElementById('photoActions').style.display = 'none';
}

function togglePw(id, btn) {
  const input = document.getElementById(id);
  const isText = input.type === 'text';
  input.type = isText ? 'password' : 'text';
  btn.innerHTML = isText ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
}
</script>
</body>
</html>