<?php
/**
 * register.php — Customer Registration Page
 * Validates user data, prevents duplicate emails, hashes password, and creates account.
 */
require_once __DIR__ . '/backend/config/config.php';

// Redirect if already logged in
require_guest();

$pageTitle = 'Create an Account';
$errors = [];
$name = '';
$email = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token expired. Please try again.');
        redirect(asset_url('register.php'));
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);

    // Validation
    if (empty($name) || mb_strlen($name) < 3) {
        $errors['name'] = 'Full name must be at least 3 characters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } else {
        // Check for duplicate email
        $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            $errors['email'] = 'An account with this email address already exists.';
        }
    }

    if (!empty($phone) && !preg_match('/^[\d\+\-\s]{10,18}$/', $phone)) {
        $errors['phone'] = 'Please provide a valid phone number.';
    }

    if (empty($password) || mb_strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must include uppercase, lowercase, and a number.';
    }

    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if (!$terms) {
        $errors['terms'] = 'You must agree to the Terms of Service and Privacy Policy.';
    }

    // If no errors, create user
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("
                INSERT INTO `users` (`name`, `email`, `password`, `phone`, `address`, `role`) 
                VALUES (?, ?, ?, ?, ?, 'customer')
            ");
            $insertStmt->execute([$name, $email, $hashedPassword, $phone, $address]);

            set_flash('success', 'Your account has been created successfully! Please sign in with your credentials.');
            redirect(asset_url('login.php'));
        } catch (PDOException $e) {
            error_log('Registration Error: ' . $e->getMessage());
            $errors['general'] = 'Failed to create your account. Please try again.';
        }
    }
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 3rem;">
    <div class="form-card" style="max-width: 560px;">
        <h1 class="form-title">Join <?= e(SITE_NAME) ?></h1>
        <p class="form-subtitle">Create an account to track orders and save your delivery details</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <div class="alert-content">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?= e($errors['general']) ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= asset_url('register.php') ?>" method="POST" id="registerForm" novalidate>
            <?= csrf_field() ?>

            <!-- Full Name -->
            <div class="form-group">
                <label for="reg_name" class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                <input type="text" id="reg_name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="e.g. Sarah Rahman" value="<?= e($name) ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <span class="form-error-msg"><?= e($errors['name']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="reg_email" class="form-label">Email Address <span style="color:var(--danger)">*</span></label>
                <input type="email" id="reg_email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="name@domain.com" value="<?= e($email) ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="form-error-msg"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Phone & Address Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="reg_phone" class="form-label">Phone Number</label>
                    <input type="tel" id="reg_phone" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" placeholder="+88017XXXXXXXX" value="<?= e($phone) ?>">
                    <?php if (isset($errors['phone'])): ?>
                        <span class="form-error-msg"><?= e($errors['phone']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="reg_address" class="form-label">Delivery Address</label>
                    <input type="text" id="reg_address" name="address" class="form-control" placeholder="House, Road, Area, City" value="<?= e($address) ?>">
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="reg_password" class="form-label">Password <span style="color:var(--danger)">*</span></label>
                <div class="password-input-wrap">
                    <input type="password" id="reg_password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Min 8 chars, uppercase, lowercase & number" required>
                    <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                </div>
                <div class="password-meter"><div class="password-meter-bar" id="passwordMeterBar"></div></div>
                <?php if (isset($errors['password'])): ?>
                    <span class="form-error-msg"><?= e($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="reg_confirm_password" class="form-label">Confirm Password <span style="color:var(--danger)">*</span></label>
                <div class="password-input-wrap">
                    <input type="password" id="reg_confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Re-type your password" required>
                    <button type="button" class="password-toggle-btn" aria-label="Toggle password visibility"><i class="fas fa-eye"></i></button>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="form-error-msg"><?= e($errors['confirm_password']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Terms -->
            <div class="form-group" style="margin-top: 1rem;">
                <label style="display: flex; align-items: flex-start; gap: 0.6rem; font-size: 0.865rem; color: var(--text-secondary); cursor: pointer;">
                    <input type="checkbox" id="reg_terms" name="terms" style="margin-top: 0.2rem;" required>
                    <span>I agree to the <a href="<?= asset_url('about.php') ?>" target="_blank">Terms of Service</a> and <a href="<?= asset_url('about.php') ?>" target="_blank">Privacy Policy</a></span>
                </label>
                <?php if (isset($errors['terms'])): ?>
                    <span class="form-error-msg"><?= e($errors['terms']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1.5rem;">
                <i class="fas fa-user-plus"></i> Create My Account
            </button>
        </form>

        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted); border-top: 1px solid var(--border); padding-top: 1.25rem;">
            Already have an account? <a href="<?= asset_url('login.php') ?>" style="font-weight: 600;">Sign in here</a>
        </div>
    </div>
</div>

<?php 
$extraJs = '<script src="' . asset_url('frontend/js/validation.js') . '"></script>';
require_once __DIR__ . '/backend/includes/footer.php'; 
?>

