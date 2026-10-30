<?php
/**
 * users.php — Customer & Account Management
 */
require_once __DIR__ . '/../config/config.php';

require_admin();

$adminTitle = 'Registered Users';

// Handle User Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/users.php'));
    }

    $delId = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
    $currentAdminId = (int)$_SESSION['user']['user_id'];

    if ($delId === $currentAdminId) {
        set_flash('error', 'Security protection: You cannot delete your own active administrator account.');
    } elseif ($delId > 0) {
        try {
            $delStmt = $pdo->prepare("DELETE FROM `users` WHERE `user_id` = ?");
            $delStmt->execute([$delId]);
            set_flash('success', 'User account #' . $delId . ' has been removed.');
        } catch (PDOException $e) {
            error_log('Delete user error: ' . $e->getMessage());
            set_flash('error', 'Failed to delete user account.');
        }
    }
    redirect(asset_url('backend/admin/users.php'));
}

// Handle Role Change (Promote to Admin / Demote to Customer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_role') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired.');
        redirect(asset_url('backend/admin/users.php'));
    }

    $targetId = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
    $currentAdminId = (int)$_SESSION['user']['user_id'];

    if ($targetId === $currentAdminId) {
        set_flash('error', 'You cannot change your own role.');
    } elseif ($targetId > 0) {
        try {
            $stmt = $pdo->prepare("SELECT role FROM `users` WHERE `user_id` = ?");
            $stmt->execute([$targetId]);
            $currentRole = $stmt->fetchColumn();

            $newRole = ($currentRole === 'admin') ? 'customer' : 'admin';
            $upStmt = $pdo->prepare("UPDATE `users` SET `role` = ? WHERE `user_id` = ?");
            $upStmt->execute([$newRole, $targetId]);
            set_flash('success', 'User role updated to ' . ucfirst($newRole) . '.');
        } catch (PDOException $e) {
            error_log('Toggle role error: ' . $e->getMessage());
            set_flash('error', 'Failed to update user role.');
        }
    }
    redirect(asset_url('backend/admin/users.php'));
}

// Fetch all users
try {
    $users = $pdo->query("SELECT * FROM `users` ORDER BY `registration_date` DESC")->fetchAll();
} catch (PDOException $e) {
    error_log('Fetch users error: ' . $e->getMessage());
    $users = [];
}

require_once __DIR__ . '/admin_header.php';
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">All Registered Accounts (<?= count($users) ?>)</h3>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Delivery Address</th>
                    <th>Role</th>
                    <th>Registered</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><strong>#<?= (int)$u['user_id'] ?></strong></td>
                        <td style="font-weight: 600; color: #0f172a;"><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['phone'] ?? '—') ?></td>
                        <td style="max-width: 200px; font-size: 0.85rem; color: #64748b;"><?= e($u['address'] ?? '—') ?></td>
                        <td>
                            <span class="badge" style="background-color: <?= ($u['role'] === 'admin') ? '#eff6ff; color:#2563eb;' : '#f1f5f9; color:#475569;' ?> font-size: 0.75rem;">
                                <?= ucfirst(e($u['role'])) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($u['registration_date'])) ?></td>
                        <td style="text-align: right;">
                            <?php if ((int)$u['user_id'] !== (int)$_SESSION['user']['user_id']): ?>
                                <div style="display: inline-flex; gap: 0.35rem;">
                                    <!-- Toggle Role -->
                                    <form action="<?= asset_url('backend/admin/users.php') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_role">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                        <button type="submit" class="action-btn" title="<?= ($u['role'] === 'admin') ? 'Demote to Customer' : 'Promote to Admin' ?>" style="<?= ($u['role'] === 'admin') ? 'color: #f59e0b;' : 'color: #10b981;' ?>">
                                            <i class="fas <?= ($u['role'] === 'admin') ? 'fa-user-minus' : 'fa-user-shield' ?>"></i>
                                        </button>
                                    </form>

                                    <!-- Delete User -->
                                    <form action="<?= asset_url('backend/admin/users.php') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Delete user account \'<?= e(addslashes($u['name'])) ?>\'?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                        <button type="submit" class="action-btn action-btn-danger" title="Delete User">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="font-size: 0.775rem; color: #94a3b8; font-style: italic;">You</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>

