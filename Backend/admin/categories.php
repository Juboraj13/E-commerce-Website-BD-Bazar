<?php
/**
 * categories.php — Category Management & CRUD
 */
require_once __DIR__ . '/../config/config.php';

require_admin();

$adminTitle = 'Manage Categories';
$errors = [];

// 1. Handle Create Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_category') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/categories.php'));
    }

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || mb_strlen($name) < 2) {
        $errors['name'] = 'Category name must be at least 2 characters.';
    } else {
        $check = $pdo->prepare("SELECT category_id FROM `categories` WHERE `name` = ?");
        $check->execute([$name]);
        if ($check->fetch()) {
            $errors['name'] = 'A category with this name already exists.';
        }
    }

    if (empty($errors)) {
        try {
            $ins = $pdo->prepare("INSERT INTO `categories` (`name`, `description`) VALUES (?, ?)");
            $ins->execute([$name, $description]);
            set_flash('success', 'Category "' . e($name) . '" created successfully.');
            redirect(asset_url('backend/admin/categories.php'));
        } catch (PDOException $e) {
            error_log('Category create error: ' . $e->getMessage());
            $errors['general'] = 'Failed to create category.';
        }
    }
}

// 2. Handle Update Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_category') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/categories.php'));
    }

    $catId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name) || mb_strlen($name) < 2) {
        set_flash('error', 'Category name must be at least 2 characters.');
    } else {
        try {
            $up = $pdo->prepare("UPDATE `categories` SET `name` = ?, `description` = ? WHERE `category_id` = ?");
            $up->execute([$name, $description, $catId]);
            set_flash('success', 'Category updated successfully.');
        } catch (PDOException $e) {
            error_log('Category update error: ' . $e->getMessage());
            set_flash('error', 'Failed to update category. Name may already be taken.');
        }
    }
    redirect(asset_url('backend/admin/categories.php'));
}

// 3. Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/categories.php'));
    }

    $catId = filter_var($_POST['category_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($catId > 0) {
        try {
            $del = $pdo->prepare("DELETE FROM `categories` WHERE `category_id` = ?");
            $del->execute([$catId]);
            set_flash('success', 'Category deleted successfully. Associated products have been set to Uncategorized.');
        } catch (PDOException $e) {
            error_log('Category delete error: ' . $e->getMessage());
            set_flash('error', 'Failed to delete category.');
        }
    }
    redirect(asset_url('backend/admin/categories.php'));
}

// Fetch all categories with product counts
try {
    $categories = $pdo->query("
        SELECT c.*, COUNT(p.id) AS product_count 
        FROM `categories` c 
        LEFT JOIN `products` p ON c.category_id = p.category_id 
        GROUP BY c.category_id 
        ORDER BY c.name ASC
    ")->fetchAll();
} catch (PDOException $e) {
    error_log('Categories fetch error: ' . $e->getMessage());
    $categories = [];
}

require_once __DIR__ . '/admin_header.php';
?>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 2rem; align-items: start;">
    <!-- Create Category Card -->
    <div class="admin-card" style="padding: 1.75rem;">
        <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.35rem; color: #0f172a;">Add New Category</h3>
        <p style="font-size: 0.825rem; color: #64748b; margin-bottom: 1.5rem;">Create a category to group products in the shop.</p>

        <?php if (!empty($errors['name'])): ?>
            <div class="alert alert-error">
                <div class="alert-content">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= e($errors['name']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= asset_url('backend/admin/categories.php') ?>" method="POST" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_category">

            <div class="form-group">
                <label for="cat_name" class="form-label">Category Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="cat_name" name="name" class="form-control" placeholder="e.g. Smart Watches" required>
            </div>

            <div class="form-group">
                <label for="cat_desc" class="form-label">Description</label>
                <textarea id="cat_desc" name="description" rows="3" class="form-control" placeholder="Short description of items in this category"></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-plus"></i> Save Category
            </button>
        </form>
    </div>

    <!-- Categories List Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3 class="admin-card-title">Existing Categories (<?= count($categories) ?>)</h3>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><strong>#<?= (int)$cat['category_id'] ?></strong></td>
                            <td style="font-weight: 600; color: #0f172a;"><?= e($cat['name']) ?></td>
                            <td style="color: #64748b; font-size: 0.85rem; max-width: 250px;"><?= e($cat['description'] ?? '—') ?></td>
                            <td>
                                <span class="badge" style="background-color: #eff6ff; color: #2563eb; font-weight: 700;">
                                    <?= (int)$cat['product_count'] ?> items
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <!-- Edit Trigger Button -->
                                    <button type="button" class="action-btn" onclick="openEditCategory(<?= (int)$cat['category_id'] ?>, '<?= e(addslashes($cat['name'])) ?>', '<?= e(addslashes($cat['description'] ?? '')) ?>')" title="Edit">
                                        <i class="fas fa-pen-to-square"></i>
                                    </button>

                                    <!-- Delete Category Form -->
                                    <form action="<?= asset_url('backend/admin/categories.php') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete category \'<?= e(addslashes($cat['name'])) ?>\'? Assigned products will become Uncategorized.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_category">
                                        <input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Category Modal Dialog -->
<div id="editCatModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; padding: 2rem; box-shadow: var(--shadow-xl); position: relative;">
        <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Edit Category</h3>
        <form action="<?= asset_url('backend/admin/categories.php') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_category">
            <input type="hidden" id="edit_cat_id" name="category_id" value="">

            <div class="form-group">
                <label class="form-label">Category Name</label>
                <input type="text" id="edit_cat_name" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="edit_cat_desc" name="description" rows="3" class="form-control"></textarea>
            </div>

            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem;">
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditCategory(id, name, desc) {
    document.getElementById('edit_cat_id').value = id;
    document.getElementById('edit_cat_name').value = name;
    document.getElementById('edit_cat_desc').value = desc;
    const modal = document.getElementById('editCatModal');
    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editCatModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

