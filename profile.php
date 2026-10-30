<?php
/**
 * profile.php — Customer Profile Management & Security Settings
 * Allows customers to update contact details, shipping addresses and change passwords.
 */
require_once __DIR__ . '/backend/config/config.php';

require_login();

$userId = $_SESSION['user']['user_id'];
$pageTitle = 'My Profile';

$errorsProfile = [];
$errorsPassword = [];

// Fetch fresh user profile from DB
try {
    $stmt = $pdo->prepare("SELECT * FROM `users` WHERE `user_id` = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        redirect(asset_url('logout.php'));
    }
} catch (PDOException $e) {
    error_log('Profile fetch error: ' . $e->getMessage());
    redirect(asset_url('index.php'));
}

// 1. Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please try again.');
        redirect(asset_url('profile.php'));
    }

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name) || mb_strlen($name) < 3) {
        $errorsProfile['name'] = 'Full name must be at least 3 characters.';
    }

    if (!empty($phone) && !preg_match('/^[\d\+\-\s]{10,18}$/', $phone)) {
        $errorsProfile['phone'] = 'Please enter a valid phone number.';
    }

    if (empty($errorsProfile)) {
        try {
            $upStmt = $pdo->prepare("UPDATE `users` SET `name` = ?, `phone` = ?, `address` = ? WHERE `user_id` = ?");
            $upStmt->execute([$name, $phone, $address, $userId]);

            // Update session cache
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;

            set_flash('success', 'Your profile details have been updated successfully.');
            redirect(asset_url('profile.php'));
        } catch (PDOException $e) {
            error_log('Profile update error: ' . $e->getMessage());
            $errorsProfile['general'] = 'Failed to update profile. Please try again.';
        }
    }
}

// 2. Handle Password Change POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please try again.');
        redirect(asset_url('profile.php'));
    }

    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Verify current password
    if (empty($currentPassword) || !password_verify($currentPassword, $user['password'])) {
        $errorsPassword['current_password'] = 'Current password is incorrect.';
    }

    // Validate new password rules
    if (empty($newPassword) || mb_strlen($newPassword) < 8) {
        $errorsPassword['new_password'] = 'New password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $errorsPassword['new_password'] = 'Must contain uppercase, lowercase letters and at least one number.';
    }

    if ($newPassword !== $confirmPassword) {
        $errorsPassword['confirm_password'] = 'New passwords do not match.';
    }

    if (empty($errorsPassword)) {
        try {
            $newHashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $pwStmt = $pdo->prepare("UPDATE `users` SET `password` = ? WHERE `user_id` = ?");
            $pwStmt->execute([$newHashed, $userId]);

            set_flash('success', 'Your password has been changed successfully.');
            redirect(asset_url('profile.php'));
        } catch (PDOException $e) {
            error_log('Password change error: ' . $e->getMessage());
            $errorsPassword['general'] = 'Failed to update password. Please try again.';
        }
    }
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Profile Header -->
<div style="background-color: var(--secondary); color: #ffffff; padding: 2.25rem 0;">
    <div class="container">
        <div style="display: flex; align-items: center; gap: 1.25rem;">
            <div style="width: 64px; height: 64px; border-radius: 50%; background-color: var(--primary); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.85rem; font-weight: 700;">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="font-size: 1.85rem; font-weight: 700; color: #ffffff; margin-bottom: 0.2rem;"><?= e($user['name']) ?></h1>
                <p style="color: #94a3b8; font-size: 0.885rem; margin: 0;">
                    Member since <?= date('F Y', strtotime($user['registration_date'])) ?> &nbsp;&bull;&nbsp;
                    <span class="badge" style="background-color: rgba(255,255,255,0.15); color: #ffffff; text-transform: capitalize;">
                        <?= e($user['role']) ?>
                    </span>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding-top: 2.5rem; padding-bottom: 4.5rem;">
    <div style="display: grid; grid-template-columns: 260px 1fr; gap: 2.5rem; align-items: start;" class="profile-layout">
        
        <!-- Sidebar Navigation -->
        <aside style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="padding: 1rem 1.25rem; background: var(--bg-subtle); border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.9rem; color: var(--text-secondary);">
                Account Menu
            </div>
            <div style="display: flex; flex-direction: column;">
                <a href="#personal-info" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: var(--primary); font-weight: 600; background-color: var(--primary-light); border-left: 3px solid var(--primary);">
                    <i class="fas fa-user"></i> Personal Info
                </a>
                <a href="#change-password" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: var(--text-secondary); border-bottom: 1px solid var(--border);">
                    <i class="fas fa-key"></i> Security & Password
                </a>
                <a href="<?= asset_url('orders.php') ?>" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: var(--text-secondary); border-bottom: 1px solid var(--border);">
                    <i class="fas fa-box-open"></i> My Orders
                </a>
                <?php if (is_admin()): ?>
                    <a href="<?= asset_url('backend/admin/dashboard.php') ?>" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: var(--accent); font-weight: 600; border-bottom: 1px solid var(--border);">
                        <i class="fas fa-gauge-high"></i> Admin Dashboard
                    </a>
                <?php endif; ?>
                <a href="<?= asset_url('logout.php') ?>" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: var(--danger);">
                    <i class="fas fa-right-from-bracket"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Form Area -->
        <div style="display: flex; flex-direction: column; gap: 2.5rem;">
            
            <!-- Section 1: Edit Profile Information -->
            <div id="personal-info" style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--secondary);">
                    Personal & Delivery Details
                </h2>
                <p style="color: var(--text-muted); font-size: 0.885rem; margin-bottom: 1.75rem;">
                    Update your name, contact phone number and default delivery address.
                </p>

                <?php if (!empty($errorsProfile['general'])): ?>
                    <div class="alert alert-error">
                        <div class="alert-content">
                            <i class="fas fa-circle-exclamation"></i>
                            <span><?= e($errorsProfile['general']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= asset_url('profile.php') ?>" method="POST" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="prof_name" class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                            <input type="text" id="prof_name" name="name" class="form-control <?= isset($errorsProfile['name']) ? 'is-invalid' : '' ?>" value="<?= e($user['name']) ?>" required>
                            <?php if (isset($errorsProfile['name'])): ?>
                                <span class="form-error-msg"><?= e($errorsProfile['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="prof_email" class="form-label">Email Address (Registered)</label>
                            <input type="email" id="prof_email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background-color: var(--bg-subtle); cursor: not-allowed;">
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Email address cannot be changed</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="prof_phone" class="form-label">Contact Phone Number</label>
                        <input type="tel" id="prof_phone" name="phone" class="form-control <?= isset($errorsProfile['phone']) ? 'is-invalid' : '' ?>" placeholder="+88017XXXXXXXX" value="<?= e($user['phone'] ?? '') ?>">
                        <?php if (isset($errorsProfile['phone'])): ?>
                            <span class="form-error-msg"><?= e($errorsProfile['phone']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="prof_address" class="form-label">Default Shipping Address</label>
                        <textarea id="prof_address" name="address" rows="3" class="form-control" placeholder="House, Road, Area, City"><?= e($user['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        <i class="fas fa-save"></i> Save Profile Changes
                    </button>
                </form>
            </div>

            <!-- Section 2: Change Password -->
            <div id="change-password" style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; box-shadow: var(--shadow-sm);">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--secondary);">
                    Security & Change Password
                </h2>
                <p style="color: var(--text-muted); font-size: 0.885rem; margin-bottom: 1.75rem;">
                    Ensure your account is protected with a strong, unique password.
                </p>

                <?php if (!empty($errorsPassword['general'])): ?>
                    <div class="alert alert-error">
                        <div class="alert-content">
                            <i class="fas fa-circle-exclamation"></i>
                            <span><?= e($errorsPassword['general']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= asset_url('profile.php') ?>" method="POST" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label for="cur_password" class="form-label">Current Password <span style="color:var(--danger)">*</span></label>
                        <div class="password-input-wrap">
                            <input type="password" id="cur_password" name="current_password" class="form-control <?= isset($errorsPassword['current_password']) ? 'is-invalid' : '' ?>" placeholder="Enter current password" required>
                            <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                        </div>
                        <?php if (isset($errorsPassword['current_password'])): ?>
                            <span class="form-error-msg"><?= e($errorsPassword['current_password']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password <span style="color:var(--danger)">*</span></label>
                            <div class="password-input-wrap">
                                <input type="password" id="new_password" name="new_password" class="form-control <?= isset($errorsPassword['new_password']) ? 'is-invalid' : '' ?>" placeholder="Min 8 chars with mixed case & number" required>
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                            </div>
                            <?php if (isset($errorsPassword['new_password'])): ?>
                                <span class="form-error-msg"><?= e($errorsPassword['new_password']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="new_confirm_password" class="form-label">Confirm New Password <span style="color:var(--danger)">*</span></label>
                            <div class="password-input-wrap">
                                <input type="password" id="new_confirm_password" name="confirm_password" class="form-control <?= isset($errorsPassword['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Repeat new password" required>
                                <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                            </div>
                            <?php if (isset($errorsPassword['confirm_password'])): ?>
                                <span class="form-error-msg"><?= e($errorsPassword['confirm_password']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline" style="margin-top: 0.5rem; border-color: var(--primary); color: var(--primary);">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
$extraJs = '<script src="' . asset_url('frontend/js/validation.js') . '"></script>';
require_once __DIR__ . '/backend/includes/footer.php'; 
?>

