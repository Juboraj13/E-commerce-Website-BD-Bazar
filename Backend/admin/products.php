<?php
/**
 * products.php — Admin Product Management & CRUD
 * View, search, filter, edit and delete catalog items.
 */
require_once __DIR__ . '/../config/config.php';

require_admin();

$adminTitle = 'Products Catalog';

// 1. Handle Product Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/products.php'));
    }

    $deleteId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
    if ($deleteId > 0) {
        try {
            // Find existing image
            $findStmt = $pdo->prepare("SELECT image FROM `products` WHERE `id` = ?");
            $findStmt->execute([$deleteId]);
            $prod = $findStmt->fetch();

            if ($prod) {
                // Delete product row
                $delStmt = $pdo->prepare("DELETE FROM `products` WHERE `id` = ?");
                $delStmt->execute([$deleteId]);

                // Remove image file if stored in uploads directory
                if (!empty($prod['image'])) {
                    $filePath = UPLOAD_PATH . DIRECTORY_SEPARATOR . $prod['image'];
                    if (file_exists($filePath) && is_file($filePath)) {
                        @unlink($filePath);
                    }
                }

                set_flash('success', 'Product #' . $deleteId . ' has been removed from catalog.');
            }
        } catch (PDOException $e) {
            error_log('Admin delete product error: ' . $e->getMessage());
            set_flash('error', 'Failed to delete product. It may be linked to active orders.');
        }
    }
    redirect(asset_url('backend/admin/products.php'));
}

// 2. Filters
$search = trim($_GET['q'] ?? '');
$filterCategory = !empty($_GET['category']) ? (int)$_GET['category'] : null;

try {
    $categories = $pdo->query("SELECT * FROM `categories` ORDER BY `name` ASC")->fetchAll();

    $where = [];
    $params = [];

    if (!empty($search)) {
        $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }

    if ($filterCategory !== null) {
        $where[] = "p.category_id = ?";
        $params[] = $filterCategory;
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name 
        FROM `products` p 
        LEFT JOIN `categories` c ON p.category_id = c.category_id 
        $whereSql 
        ORDER BY p.id DESC
    ");
    $stmt->execute($params);
    $products = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Admin products fetch error: ' . $e->getMessage());
    $products = [];
    $categories = [];
}

require_once __DIR__ . '/admin_header.php';
?>

<!-- Header Actions & Search -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <!-- Search and Filter Form -->
    <form action="<?= asset_url('backend/admin/products.php') ?>" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; flex: 1; max-width: 600px;">
        <input type="text" name="q" class="form-control" placeholder="Search product name..." value="<?= e($search) ?>" style="flex: 1; min-width: 180px;">
        
        <select name="category" class="form-control" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['category_id'] ?>" <?= ($filterCategory === (int)$cat['category_id']) ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        <?php if (!empty($search) || $filterCategory !== null): ?>
            <a href="<?= asset_url('backend/admin/products.php') ?>" class="btn btn-outline btn-sm" style="color: #ef4444;">Reset</a>
        <?php endif; ?>
    </form>

    <a href="<?= asset_url('backend/admin/product_form.php') ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Product
    </a>
</div>

<!-- Products Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">All Catalog Items (<?= count($products) ?>)</h3>
    </div>

    <div class="admin-table-wrap">
        <?php if (empty($products)): ?>
            <div style="padding: 3.5rem; text-align: center; color: #64748b;">
                <i class="fas fa-boxes-stacked" style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #cbd5e1;"></i>
                <p>No products found matching your search criteria.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Image</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <div style="width: 52px; height: 52px; border-radius: 8px; overflow: hidden; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                    <img src="<?= product_image_url($p['image']) ?>" alt="" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #0f172a; font-size: 0.95rem;">
                                    <a href="<?= asset_url('product.php?id=' . (int)$p['id']) ?>" target="_blank" style="color: #0f172a;" title="View in store">
                                        <?= e($p['name']) ?> <i class="fas fa-arrow-up-right-from-square" style="font-size: 0.75rem; color: #94a3b8;"></i>
                                    </a>
                                </div>
                                <div style="font-size: 0.775rem; color: #64748b; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($p['description']) ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background-color: #f1f5f9; color: #334155; font-size: 0.75rem;">
                                    <?= e($p['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td style="font-weight: 700; color: #2563eb;">
                                <?= format_price($p['price']) ?>
                            </td>
                            <td>
                                <?php if ($p['stock'] > 10): ?>
                                    <span style="color: #16a34a; font-weight: 600;"><?= (int)$p['stock'] ?> in stock</span>
                                <?php elseif ($p['stock'] > 0): ?>
                                    <span style="color: #ea580c; font-weight: 600;">Low: <?= (int)$p['stock'] ?> left</span>
                                <?php else: ?>
                                    <span style="color: #dc2626; font-weight: 700;">Out of Stock</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['is_featured']): ?>
                                    <span class="badge badge-featured">Yes</span>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.8rem;">No</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.4rem;">
                                    <a href="<?= asset_url('backend/admin/product_form.php?id=' . (int)$p['id']) ?>" class="action-btn" title="Edit Product">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    
                                    <form action="<?= asset_url('backend/admin/products.php') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete \'<?= e(addslashes($p['name'])) ?>\'? This action cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_product">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete Product">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

