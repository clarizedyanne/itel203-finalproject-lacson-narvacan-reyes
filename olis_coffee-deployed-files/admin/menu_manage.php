<?php
// admin/menu_manage.php - Full CRUD for Menu Items (organized by category)
session_start();
require_once '../includes/Auth.php';
require_once '../includes/MenuItem.php';

Auth::requireAdmin('../login.php');

$menuItem = new MenuItem();
$message  = '';
$msgType  = 'success';

// Allowed image types
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$uploadDir    = dirname(__DIR__) . '/uploads/menu/';

// Helper: handle image upload
function handleImageUpload(string $uploadDir, array $allowedTypes): ?string {
    if (empty($_FILES['image']['name'])) return null;
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) return null;

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $_FILES['image']['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes)) return null;

    $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('dish_', true) . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
        return $filename;
    }
    return null;
}

// ── HANDLE FORM ACTIONS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Category determines if image upload applies (no images for Drinks)
    $category = trim($_POST['category'] ?? '');

    if ($action === 'create') {
        $data = [
            'name'          => trim($_POST['name']),
            'category'      => $category,
            'subcategory'   => trim($_POST['subcategory']),
            'description'   => trim($_POST['description']),
            'price'         => floatval($_POST['price']),
            'price_variant' => trim($_POST['price_variant']),
            'is_available'  => isset($_POST['is_available']) ? 1 : 0,
            'image'         => null,
        ];
        if ($category !== 'Drinks') {
            $uploaded = handleImageUpload($uploadDir, $allowedTypes);
            if ($uploaded) $data['image'] = $uploaded;
        }
        if ($menuItem->create($data)) {
            $message = 'Menu item added successfully!';
        } else {
            $message = 'Error adding item.'; $msgType = 'danger';
        }

    } elseif ($action === 'update') {
        $id       = intval($_POST['id']);
        $existing = $menuItem->getById($id);
        $data = [
            'name'          => trim($_POST['name']),
            'category'      => $category,
            'subcategory'   => trim($_POST['subcategory']),
            'description'   => trim($_POST['description']),
            'price'         => floatval($_POST['price']),
            'price_variant' => trim($_POST['price_variant']),
            'is_available'  => isset($_POST['is_available']) ? 1 : 0,
            'image'         => $existing['image'] ?? null,
        ];
        // Handle new image upload (non-drinks)
        if ($category !== 'Drinks') {
            $uploaded = handleImageUpload($uploadDir, $allowedTypes);
            if ($uploaded) {
                // Delete old image
                if (!empty($existing['image'])) {
                    $old = $uploadDir . basename($existing['image']);
                    if (file_exists($old)) unlink($old);
                }
                $data['image'] = $uploaded;
            }
        }
        // Handle image removal
        if (isset($_POST['remove_image']) && $category !== 'Drinks') {
            if (!empty($existing['image'])) {
                $old = $uploadDir . basename($existing['image']);
                if (file_exists($old)) unlink($old);
            }
            $data['image'] = null;
        }
        if ($menuItem->update($id, $data)) {
            $message = 'Menu item updated!';
        } else {
            $message = 'Error updating item.'; $msgType = 'danger';
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($menuItem->delete($id)) {
            $message = 'Item deleted.';
        } else {
            $message = 'Error deleting item.'; $msgType = 'danger';
        }

    } elseif ($action === 'toggle') {
        $id = intval($_POST['id']);
        $menuItem->toggleAvailability($id);
        $message = 'Availability updated.';
    }
}

// Fetch for edit
$editItem = null;
if (isset($_GET['edit'])) {
    $editItem = $menuItem->getById(intval($_GET['edit']));
}

// Active tab from GET or default to first category
$activeTab = $_GET['tab'] ?? ($editItem ? $editItem['category'] : 'Main');

$grouped  = $menuItem->getAllGrouped();
$allCategories = ['Main', 'Snacks', 'Pasta', 'Burgers', 'Salads', 'Pizza', 'Drinks'];

// Category → subcategories map for dynamic form dropdown
$subcatMap = [
    'Main'    => ['For Sharing', 'Rice Meal', 'Chicken Wings'],
    'Snacks'  => ['Snacks'],
    'Pasta'   => ['Pasta'],
    'Burgers' => ['Burgers/Sandwiches'],
    'Salads'  => ['Salads'],
    'Pizza'   => ['Classic', 'Premium', 'Latest Special'],
    'Drinks'  => ['Artisan Tea', 'Milk Tea', 'Hot Tea', 'Cheesecake', 'Rock Salt & Cheese', 'Hot Drinks', 'Iced Drinks', 'Ice Blended', 'Cream Based'],
];

$adminName = htmlspecialchars($_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Menu Management – Oli's Admin</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" type="image/x-icon" href="../assets/logo.png">
  <style>
    .category-tab-nav .nav-link {
      color: var(--green-dark) !important;
      background: white;
      border: 2px solid #e5e7eb;
      border-radius: 8px !important;
      font-weight: 700;
      font-size: 0.82rem;
      padding: 6px 14px !important;
      margin: 2px;
      transition: all 0.2s;
    }
    .category-tab-nav .nav-link.active,
    .category-tab-nav .nav-link:hover {
      background: var(--green-dark) !important;
      color: white !important;
      border-color: var(--green-dark);
    }
    .subcategory-header {
      background: #f0fdf4;
      border-left: 4px solid var(--green-mid);
      padding: 8px 14px;
      font-weight: 700;
      font-size: 0.85rem;
      color: var(--green-dark);
      margin: 0;
    }
    .menu-thumb {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #e5e7eb;
    }
    .menu-thumb-placeholder {
      width: 48px;
      height: 48px;
      background: #f3f4f6;
      border-radius: 8px;
      border: 1px dashed #d1d5db;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #9ca3af;
      font-size: 1.1rem;
    }
    .img-preview-wrap {
      position: relative;
      display: inline-block;
    }
    .img-preview-wrap img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #d1e7c8;
    }
    #imagePreview {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      border: 2px solid #d1e7c8;
      display: none;
    }
    .upload-area {
      border: 2px dashed #d1e7c8;
      border-radius: 10px;
      padding: 14px;
      text-align: center;
      cursor: pointer;
      background: #f8fdf5;
      transition: all 0.2s;
      font-size: 0.82rem;
      color: var(--text-muted);
    }
    .upload-area:hover { border-color: var(--green-mid); background: #f0fdf4; }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">
      <img src="../assets/logo.png" alt="Oli's SelfieTea & Coffee" style="height:50px;width:auto;">
      <div>Admin Panel <span class="sub">Menu Management</span></div>
    </a>
    <div class="ms-auto">
      <a href="../logout.php" class="btn-logout nav-link"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>
  </div>
</nav>

<div class="d-flex">
  <aside class="admin-sidebar py-3">
    <nav class="nav flex-column">
      <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      <a class="nav-link active" href="menu_manage.php"><i class="bi bi-journal-text me-2"></i>Menu Management</a>
      <a class="nav-link" href="manage_reservations.php"><i class="bi bi-calendar-check me-2"></i>Reservations</a>
      <a class="nav-link" href="reports.php"><i class="bi bi-bar-chart-line me-2"></i>Reports</a>
      <hr style="border-color:rgba(255,255,255,0.1); margin: 8px 16px;">
      <a class="nav-link" href="../index.php"><i class="bi bi-shop me-2"></i>View Customer Side</a>
      <a class="nav-link" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </nav>
  </aside>

  <main class="admin-content">

    <?php if ($message): ?>
      <div class="alert alert-<?= $msgType ?> alert-olis alert-dismissible fade show">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- ADD / EDIT FORM -->
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header" style="background:var(--green-dark); color:white;">
        <i class="bi bi-<?= $editItem ? 'pencil' : 'plus-circle' ?> me-2"></i>
        <?= $editItem ? 'Edit Menu Item' : 'Add New Menu Item' ?>
      </div>
      <div class="card-body">
        <form method="POST" enctype="multipart/form-data" id="itemForm">
          <input type="hidden" name="action" value="<?= $editItem ? 'update' : 'create' ?>">
          <?php if ($editItem): ?>
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
          <?php endif; ?>

          <div class="row g-3">
            <!-- Name -->
            <div class="col-md-4">
              <label class="form-label">Item Name *</label>
              <input type="text" name="name" class="form-control" required
                     value="<?= htmlspecialchars($editItem['name'] ?? '') ?>">
            </div>

            <!-- Category -->
            <div class="col-md-2">
              <label class="form-label">Category</label>
              <select name="category" id="formCategory" class="form-select" onchange="updateSubcats(this.value)">
                <?php foreach ($allCategories as $cat): ?>
                  <option value="<?= $cat ?>" <?= ($editItem['category'] ?? 'Main') === $cat ? 'selected' : '' ?>>
                    <?= $cat ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Subcategory -->
            <div class="col-md-2">
              <label class="form-label">Subcategory</label>
              <select name="subcategory" id="formSubcat" class="form-select">
                <?php
                  $curCat = $editItem['category'] ?? 'Main';
                  $curSub = $editItem['subcategory'] ?? '';
                  foreach ($subcatMap[$curCat] as $sub):
                ?>
                  <option value="<?= $sub ?>" <?= $curSub === $sub ? 'selected' : '' ?>><?= $sub ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Price -->
            <div class="col-md-2">
              <label class="form-label">Price (₱) *</label>
              <input type="number" name="price" class="form-control" step="0.01" required
                     value="<?= htmlspecialchars($editItem['price'] ?? '') ?>">
            </div>

            <!-- Variant -->
            <div class="col-md-2">
              <label class="form-label">Variant <small class="text-muted">(e.g. 6pcs, 12")</small></label>
              <input type="text" name="price_variant" class="form-control"
                     value="<?= htmlspecialchars($editItem['price_variant'] ?? '') ?>">
            </div>

            <!-- Description -->
            <div class="col-md-6">
              <label class="form-label">Description</label>
              <input type="text" name="description" class="form-control"
                     value="<?= htmlspecialchars($editItem['description'] ?? '') ?>">
            </div>

            <!-- Image Upload (hidden for Drinks) -->
            <div class="col-md-4" id="imageUploadWrap"
                 style="<?= ($editItem['category'] ?? 'Main') === 'Drinks' ? 'display:none' : '' ?>">
              <label class="form-label">Dish Photo <small class="text-muted">(JPG/PNG/WebP)</small></label>
              <div class="d-flex align-items-center gap-3">
                <?php if (!empty($editItem['image'])): ?>
                  <div class="img-preview-wrap">
                    <img src="../uploads/menu/<?= htmlspecialchars(basename($editItem['image'])) ?>"
                         id="currentImg" alt="Current">
                  </div>
                  <div>
                    <label class="form-check-label small text-muted d-block mb-1">
                      <input type="checkbox" name="remove_image" value="1" class="form-check-input me-1">
                      Remove current photo
                    </label>
                  </div>
                <?php else: ?>
                  <img src="" id="imagePreview" alt="Preview">
                <?php endif; ?>
                <div class="flex-grow-1">
                  <label class="upload-area d-block" for="imageInput">
                    <i class="bi bi-cloud-upload me-1"></i>
                    <?= !empty($editItem['image']) ? 'Replace photo' : 'Click to upload photo' ?>
                  </label>
                  <input type="file" name="image" id="imageInput" accept="image/*" class="d-none"
                         onchange="previewImage(this)">
                </div>
              </div>
            </div>

            <!-- Available + Submit -->
            <div class="col-md-2 d-flex flex-column justify-content-end">
              <div class="form-check mb-2">
                <input type="checkbox" name="is_available" id="isAvail" class="form-check-input"
                       <?= ($editItem['is_available'] ?? 1) ? 'checked' : '' ?>>
                <label for="isAvail" class="form-check-label">Available</label>
              </div>
              <button type="submit" class="btn" style="background:var(--green-dark); color:white; border-radius:8px;">
                <i class="bi bi-save me-1"></i><?= $editItem ? 'Update' : 'Add Item' ?>
              </button>
              <?php if ($editItem): ?>
                <a href="menu_manage.php?tab=<?= urlencode($editItem['category']) ?>"
                   class="btn btn-outline-secondary mt-1" style="border-radius:8px;">Cancel</a>
              <?php endif; ?>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- CATEGORY TABS -->
    <div class="card border-0 shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center"
           style="background:var(--green-dark); color:white;">
        <span><i class="bi bi-table me-2"></i>All Menu Items</span>
        <span class="badge bg-light text-dark"><?= array_sum(array_map(fn($s) => array_sum(array_map('count', $s)), $grouped)) ?> total</span>
      </div>

      <div class="card-body p-3">
        <!-- Tab nav -->
        <ul class="nav category-tab-nav flex-wrap gap-1 mb-3" id="categoryTabs">
          <?php foreach ($allCategories as $cat):
            $count = 0;
            if (isset($grouped[$cat])) {
                foreach ($grouped[$cat] as $sub) $count += count($sub);
            }
          ?>
          <li class="nav-item">
            <a class="nav-link <?= $activeTab === $cat ? 'active' : '' ?>"
               href="?tab=<?= urlencode($cat) ?>"
               data-cat="<?= $cat ?>">
              <?= $cat ?>
              <span class="badge ms-1 <?= $activeTab === $cat ? 'bg-light text-dark' : 'bg-secondary' ?>"
                    style="font-size:0.65rem;"><?= $count ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>

        <!-- Table content per active tab -->
        <?php if (empty($grouped[$activeTab])): ?>
          <div class="text-center text-muted py-5">
            <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
            No items in this category yet.
          </div>
        <?php else: ?>
          <?php foreach ($grouped[$activeTab] as $subcatName => $items): ?>
            <div class="subcategory-header mb-0">
              <?= htmlspecialchars($subcatName) ?>
              <span class="text-muted fw-normal" style="font-size:0.75rem;">(<?= count($items) ?> items)</span>
            </div>
            <div class="table-responsive mb-3">
              <table class="table table-olis mb-0">
                <thead>
                  <tr>
                    <th style="width:56px;">Photo</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Variant</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($items as $item): ?>
                  <tr>
                    <td>
                      <?php if ($activeTab !== 'Drinks' && !empty($item['image'])): ?>
                        <img src="../uploads/menu/<?= htmlspecialchars(basename($item['image'])) ?>"
                             class="menu-thumb" alt="<?= htmlspecialchars($item['name']) ?>">
                      <?php elseif ($activeTab !== 'Drinks'): ?>
                        <div class="menu-thumb-placeholder">
                          <i class="bi bi-image"></i>
                        </div>
                      <?php else: ?>
                        <span class="text-muted" style="font-size:0.75rem;">—</span>
                      <?php endif; ?>
                    </td>
                    <td class="fw-semibold"><?= htmlspecialchars($item['name']) ?></td>
                    <td>₱<?= number_format($item['price'], 2) ?></td>
                    <td><?= htmlspecialchars($item['price_variant'] ?? '—') ?></td>
                    <td class="text-muted" style="font-size:0.82rem; max-width:200px;">
                      <?= htmlspecialchars($item['description'] ?: '—') ?>
                    </td>
                    <td>
                      <?php if ($item['is_available']): ?>
                        <span class="badge-available">Available</span>
                      <?php else: ?>
                        <span class="badge-unavailable">Unavailable</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="?edit=<?= $item['id'] ?>&tab=<?= urlencode($activeTab) ?>"
                         class="btn btn-sm btn-outline-primary" style="border-radius:6px; font-size:0.73rem;">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <button onclick="toggleAvail(<?= $item['id'] ?>, '<?= urlencode($activeTab) ?>')"
                              class="btn btn-sm btn-outline-warning ms-1" style="border-radius:6px; font-size:0.73rem;"
                              title="Toggle availability">
                        <i class="bi bi-toggle-on"></i>
                      </button>
                      <form method="POST" class="delete-form d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger ms-1"
                                style="border-radius:6px; font-size:0.73rem;">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/script.js"></script>
<script>
// Subcategory map (mirrors PHP)
const subcatMap = <?= json_encode($subcatMap) ?>;

function updateSubcats(cat) {
  const subcats  = subcatMap[cat] || [];
  const sel      = document.getElementById('formSubcat');
  sel.innerHTML  = subcats.map(s => `<option value="${s}">${s}</option>`).join('');

  // Toggle image upload section
  const imgWrap = document.getElementById('imageUploadWrap');
  if (imgWrap) {
    imgWrap.style.display = cat === 'Drinks' ? 'none' : '';
  }
}

function previewImage(input) {
  const preview = document.getElementById('imagePreview');
  if (!preview) return;
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// Init subcats on page load (for edit mode the server renders the right options, but keep JS in sync)
document.addEventListener('DOMContentLoaded', () => {
  const catSel = document.getElementById('formCategory');
  if (catSel) updateSubcats(catSel.value);
});

// Override toggleAvail to preserve tab
function toggleAvail(id, tab) {
  if (!confirm('Toggle availability for this item?')) return;
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = 'menu_manage.php?tab=' + tab;
  const i1 = document.createElement('input'); i1.name = 'action'; i1.value = 'toggle';
  const i2 = document.createElement('input'); i2.name = 'id';     i2.value = id;
  form.appendChild(i1); form.appendChild(i2);
  document.body.appendChild(form);
  form.submit();
}
</script>
</body>
</html>
