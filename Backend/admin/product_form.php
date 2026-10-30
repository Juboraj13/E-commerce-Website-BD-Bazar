<?php
/**
 * product_form.php — Add / Edit Product with Secure Image Upload
 */
require_once __DIR__ . '/../config/config.php';

require_admin();

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$isEdit = ($id && $id > 0);
$adminTitle = $isEdit ? 'Edit Product' : 'Add New Product';

$errors = [];
$name = '';
$categoryId = '';
$price = '';
$stock = '10';
$description = '';
$isFeatured = 0;
$currentImage = '';

// Load existing product if editing
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM `products` WHERE `id` = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            set_flash('error', 'Product not found.');
            redirect(asset_url('backend/admin/products.php'));
        }

        $name = $product['name'];
        $categoryId = $product['category_id'];
        $price = $product['price'];
        $stock = $product['stock'];
        $description = $product['description'];
        $isFeatured = (int)$product['is_featured'];
        $currentImage = $product['image'];
    } catch (PDOException $e) {
        error_log('Fetch product error: ' . $e->getMessage());
        redirect(asset_url('backend/admin/products.php'));
    }
}

// Fetch categories for dropdown
$categories = $pdo->query("SELECT * FROM `categories` ORDER BY `name` ASC")->fetchAll();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect($isEdit ? asset_url('backend/admin/product_form.php?id=' . $id) : asset_url('backend/admin/product_form.php'));
    }

    $name = trim($_POST['name'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '0');
    $description = trim($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Server-side validation
    if (empty($name) || mb_strlen($name) < 3) {
        $errors['name'] = 'Product name must be at least 3 characters.';
    }

    if (!is_numeric($price) || (float)$price < 0) {
        $errors['price'] = 'Please enter a valid non-negative price.';
    }

    if (!filter_var($stock, FILTER_VALIDATE_INT) && $stock !== '0') {
        $errors['stock'] = 'Stock must be an integer.';
    } elseif ((int)$stock < 0) {
        $errors['stock'] = 'Stock cannot be negative.';
    }

    // Process Image Upload
    $uploadedFilename = $currentImage;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image'];

        // 1. Check size limit (2 MB)
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $errors['image'] = 'Image size must be less than 2 MB.';
        }

        // 2. Validate Extension Whitelist
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($fileExt, $allowedExtensions, true)) {
            $errors['image'] = 'Invalid file extension. Only JPG, JPEG, PNG, and WEBP allowed.';
        }

        // 3. Validate MIME Type via finfo
        if (empty($errors['image'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowedMimes, true)) {
                $errors['image'] = 'Invalid file MIME type. The uploaded file is not a valid image.';
            }
        }

        // 4. Move to Uploads with Randomized Name
        if (empty($errors['image'])) {
            $newFileName = bin2hex(random_bytes(12)) . '.' . $fileExt;
            $destination = UPLOAD_PATH . DIRECTORY_SEPARATOR . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Remove old custom image if editing and exists
                if ($isEdit && !empty($currentImage)) {
                    $oldPath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $currentImage;
                    if (file_exists($oldPath) && is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $uploadedFilename = $newFileName;
            } else {
                $errors['image'] = 'Failed to move uploaded image to storage.';
            }
        }
    }

    // Save to Database
    if (empty($errors)) {
        try {
            if ($isEdit) {
                $updateStmt = $pdo->prepare("
                    UPDATE `products` 
                    SET `category_id` = ?, `name` = ?, `description` = ?, `price` = ?, `stock` = ?, `image` = ?, `is_featured` = ? 
                    WHERE `id` = ?
                ");
                $updateStmt->execute([
                    $categoryId,
                    $name,
                    $description,
                    (float)$price,
                    (int)$stock,
                    $uploadedFilename,
                    $isFeatured,
                    $id
                ]);
                set_flash('success', 'Product "' . e($name) . '" updated successfully!');
            } else {
                $insertStmt = $pdo->prepare("
                    INSERT INTO `products` 
                    (`category_id`, `name`, `description`, `price`, `stock`, `image`, `is_featured`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([
                    $categoryId,
                    $name,
                    $description,
                    (float)$price,
                    (int)$stock,
                    $uploadedFilename,
                    $isFeatured
                ]);
                set_flash('success', 'New product "' . e($name) . '" has been added to catalog!');
            }

            redirect(asset_url('backend/admin/products.php'));
        } catch (PDOException $e) {
            error_log('Save product error: ' . $e->getMessage());
            $errors['general'] = 'Failed to save product to database.';
        }
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.4rem; font-weight: 700; color: #0f172a;">
            <?= $isEdit ? 'Edit Product #' . $id : 'Create New Product' ?>
        </h2>
        <a href="<?= asset_url('backend/admin/products.php') ?>" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error">
            <div class="alert-content">
                <i class="fas fa-circle-exclamation"></i>
                <span><?= e($errors['general']) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="admin-card" style="padding: 2.25rem;">
        <form action="<?= $isEdit ? asset_url('backend/admin/product_form.php?id=' . $id) : asset_url('backend/admin/product_form.php') ?>" method="POST" enctype="multipart/form-data" novalidate>
            <?= csrf_field() ?>

            <!-- Product Name -->
            <div class="form-group">
                <label for="prod_name" class="form-label">Product Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="prod_name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="e.g. MacBook Air M2 8/256GB" value="<?= e($name) ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <span class="form-error-msg"><?= e($errors['name']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Category & Featured Flag -->
            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1.25rem; align-items: center;">
                <div class="form-group">
                    <label for="prod_cat" class="form-label">Category</label>
                    <select id="prod_cat" name="category_id" class="form-control">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['category_id'] ?>" <?= ($categoryId == $cat['category_id']) ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" style="padding-top: 1.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-weight: 600; font-size: 0.9rem; color: #0f172a;">
                        <input type="checkbox" name="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: #2563eb;">
                        <span>Show on Featured Grid</span>
                    </label>
                </div>
            </div>

            <!-- Price and Stock -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                <div class="form-group">
                    <label for="prod_price" class="form-label">Unit Price (৳) <span style="color:var(--danger)">*</span></label>
                    <input type="number" step="0.01" min="0" id="prod_price" name="price" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" placeholder="0.00" value="<?= e((string)$price) ?>" required>
                    <?php if (isset($errors['price'])): ?>
                        <span class="form-error-msg"><?= e($errors['price']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="prod_stock" class="form-label">Available Stock Quantity <span style="color:var(--danger)">*</span></label>
                    <input type="number" min="0" id="prod_stock" name="stock" class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" placeholder="e.g. 25" value="<?= e((string)$stock) ?>" required>
                    <?php if (isset($errors['stock'])): ?>
                        <span class="form-error-msg"><?= e($errors['stock']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="prod_desc" class="form-label">Product Description</label>
                <textarea id="prod_desc" name="description" rows="5" class="form-control" placeholder="Detailed product specifications, dimensions, features and warranty details..."><?= e($description) ?></textarea>
            </div>

            <!-- Product Image Upload & Live Preview -->
            <div class="form-group" style="border: 1px dashed #cbd5e1; border-radius: 8px; padding: 1.5rem; background-color: #f8fafc;">
                <label class="form-label" style="font-weight: 600;">Product Image</label>
                
                <div style="display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                    <div style="width: 100px; height: 100px; border-radius: 8px; overflow: hidden; background: #ffffff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <img id="imagePreview" src="<?= !empty($currentImage) ? product_image_url($currentImage) : asset_url('frontend/images/no-image.png') ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: contain;">
                    </div>

                    <div style="flex: 1; min-width: 220px;">
                        <input type="file" id="imageInput" name="image" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" accept=".jpg, .jpeg, .png, .webp">
                        <span style="font-size: 0.775rem; color: #64748b; display: block; margin-top: 0.35rem;">
                            Allowed types: JPG, PNG, WEBP. Maximum file size: 2 MB.
                        </span>
                        <?php if (isset($errors['image'])): ?>
                            <span class="form-error-msg"><?= e($errors['image']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                    <i class="fas fa-save"></i> <?= $isEdit ? 'Update Product' : 'Publish Product' ?>
                </button>
                <a href="<?= asset_url('backend/admin/products.php') ?>" class="btn btn-outline btn-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert('File exceeds 2 MB limit.');
            this.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(evt) {
            document.getElementById('imagePreview').src = evt.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

