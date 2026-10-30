<?php
/**
 * admin/register.php — Dedicated Administrator Registration Portal
 * Accessible strictly via /admin/register.php.
 */
require_once __DIR__ . '/../backend/config/config.php';

// If already signed in as admin, redirect to dashboard
if (is_admin()) {
    redirect(asset_url('backend/admin/dashboard.php'));
}

$pageTitle = 'Register Administrator';
$errors = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please try again.');
        redirect(asset_url('admin/register.php'));
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || mb_strlen($name) < 3) {
        $errors['name'] = 'Full name must be at least 3 characters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        $checkStmt = $pdo->prepare("SELECT user_id FROM `users` WHERE `email` = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors['email'] = 'An account with this email address already exists.';
        }
    }

    if (empty($password) || mb_strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must include uppercase, lowercase, and a number.';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO `users` (`name`, `email`, `password`, `phone`, `role`) 
                VALUES (?, ?, ?, ?, 'admin')
            ");
            $stmt->execute([$name, $email, $hashedPassword, $phone]);

            set_flash('success', 'Administrator account created successfully! Please sign in below.');
            redirect(asset_url('admin/login.php'));
        } catch (PDOException $e) {
            error_log('Admin registration error: ' . $e->getMessage());
            $errors['general'] = 'Failed to create administrator account.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Administrator — <?= e(SITE_NAME) ?></title>
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset_url('frontend/css/style.css') ?>">
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2.5rem 1rem;">

    <div style="width: 100%; max-width: 480px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); padding: 2.5rem 2.25rem;">
        
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="width: 60px; height: 60px; border-radius: 14px; background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.65rem; margin: 0 auto 1rem auto;">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1 style="font-size: 1.55rem; font-weight: 700; color: #0f172a; margin-bottom: 0.35rem; font-family: var(--font-heading);">
                Register Administrator
            </h1>
            <p style="color: #64748b; font-size: 0.885rem; margin: 0;">Create an authorized management account</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error" style="margin-bottom: 1.25rem;">
                <div class="alert-content">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= e($errors['general']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= asset_url('admin/register.php') ?>" method="POST" id="adminRegisterForm" novalidate>
            <?= csrf_field() ?>

            <!-- Full Name -->
            <div class="form-group">
                <label for="adm_name" class="form-label" style="font-weight: 600;">Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="adm_name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="e.g. John Doe" value="<?= e($name) ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <span class="form-error-msg"><?= e($errors['name']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="adm_email" class="form-label" style="font-weight: 600;">Admin Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" id="adm_email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="name@bdbazar.com" value="<?= e($email) ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error-msg"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label for="adm_phone" class="form-label" style="font-weight: 600;">Direct Phone</label>
                <input type="tel" id="adm_phone" name="phone" class="form-control" placeholder="+88017XXXXXXXX" value="<?= e($phone) ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="reg_password" class="form-label" style="font-weight: 600;">Password <span style="color:var(--danger)">*</span></label>
                <div class="password-input-wrap">
                    <input type="password" id="reg_password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Min 8 chars, mixed case & numbers" required>
                    <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                </div>
                <div class="password-meter"><div class="password-meter-bar" id="passwordMeterBar"></div></div>
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error-msg"><?= e($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="adm_confirm_password" class="form-label" style="font-weight: 600;">Confirm Password <span style="color:var(--danger)">*</span></label>
                <div class="password-input-wrap">
                    <input type="password" id="adm_confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Confirm password" required>
                    <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="form-error-msg"><?= e($errors['confirm_password']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-secondary btn-block btn-lg" style="background-color: #0f172a; border-color: #0f172a; font-weight: 600; margin-top: 1.5rem;">
                <i class="fas fa-shield-check"></i> Register as Administrator
            </button>
        </form>

        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #e2e8f0; text-align: center; font-size: 0.885rem;">
            Already have an admin account? <a href="<?= asset_url('admin/login.php') ?>" style="color: #2563eb; font-weight: 600;">Sign in here</a>
        </div>
    </div>

    <script src="<?= asset_url('frontend/js/validation.js') ?>"></script>
</body>
</html>

