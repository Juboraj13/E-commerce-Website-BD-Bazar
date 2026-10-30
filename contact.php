<?php
/**
 * contact.php — Contact & Feedback Page
 * Allows visitors and customers to send inquiries stored in the database.
 */
require_once __DIR__ . '/backend/config/config.php';

$pageTitle = 'Contact Us';
$errors = [];
$name = '';
$email = '';
$subject = '';
$message = '';

// Pre-fill fields if user is authenticated
if (is_logged_in()) {
    $name = $_SESSION['user']['name'] ?? '';
    $email = $_SESSION['user']['email'] ?? '';
}

// Handle POST Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Invalid security token. Please try again.');
        redirect(asset_url('contact.php'));
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Server-side validation
    if (empty($name) || mb_strlen($name) < 2) {
        $errors['name'] = 'Please enter your full name (at least 2 characters).';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please provide a valid email address.';
    }
    if (empty($subject) || mb_strlen($subject) < 3) {
        $errors['subject'] = 'Please specify a subject (at least 3 characters).';
    }
    if (empty($message) || mb_strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters long.';
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `messages` (`user_name`, `email`, `subject`, `message`, `is_read`) 
                VALUES (?, ?, ?, ?, 0)
            ");
            $stmt->execute([$name, $email, $subject, $message]);

            set_flash('success', 'Thank you! Your message has been received. Our team will contact you shortly.');
            redirect(asset_url('contact.php'));
        } catch (PDOException $e) {
            error_log('Error saving contact message: ' . $e->getMessage());
            $errors['general'] = 'Failed to send your message. Please try again later.';
        }
    }
}

require_once __DIR__ . '/backend/includes/header.php';
?>

<!-- Contact Hero -->
<section style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 3.5rem 0;">
    <div class="container" style="text-align: center; max-width: 650px;">
        <span class="section-tag" style="color: #60a5fa;">Get In Touch</span>
        <h1 style="font-size: 2.5rem; color: #ffffff; margin-bottom: 0.75rem;">We're Here to Help</h1>
        <p style="font-size: 1rem; color: #cbd5e1;">
            Have questions regarding our products, corporate orders, delivery timelines or warranty? Send us a note or call our direct helpline.
        </p>
    </div>
</section>

<!-- Contact Info & Form Section -->
<section class="section">
    <div class="container">
        <div class="contact-grid" style="display: grid; grid-template-columns: 1fr 1.35fr; gap: 3.5rem; align-items: start;">
            <!-- Contact Details -->
            <div>
                <h2 style="font-size: 1.8rem; margin-bottom: 1rem; color: var(--secondary);">Contact Information</h2>
                <p style="color: var(--text-muted); margin-bottom: 2rem;">
                    Reach out to our customer care team anytime between 9:00 AM and 9:00 PM, Saturday through Thursday.
                </p>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-location-dot"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.2rem;">Headquarters</h4>
                            <p style="font-size: 0.885rem; color: var(--text-muted);"><?= e(SITE_ADDRESS) ?></p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #ecfdf5; color: var(--success); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.2rem;">Direct Helpline</h4>
                            <p style="font-size: 0.885rem; color: var(--text-muted);"><?= e(SITE_PHONE) ?></p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #fffbeb; color: var(--warning); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.2rem;">Email Support</h4>
                            <p style="font-size: 0.885rem; color: var(--text-muted);"><?= e(SITE_EMAIL) ?></p>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem; align-items: flex-start;">
                        <div style="width: 48px; height: 48px; border-radius: var(--radius-md); background: #fdf2f8; color: #ec4899; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: 0.2rem;">Operational Hours</h4>
                            <p style="font-size: 0.885rem; color: var(--text-muted);">Saturday – Thursday: 9:00 AM – 9:00 PM<br>Friday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div style="background-color: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2.25rem; box-shadow: var(--shadow-md);">
                <h3 style="font-size: 1.45rem; font-weight: 700; margin-bottom: 0.35rem;">Send Us a Message</h3>
                <p style="font-size: 0.885rem; color: var(--text-muted); margin-bottom: 1.5rem;">Fill out the form below and we'll reply within 24 hours.</p>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-error">
                        <div class="alert-content">
                            <i class="fas fa-circle-exclamation"></i>
                            <span><?= e($errors['general']) ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= asset_url('contact.php') ?>" method="POST" id="contactForm" novalidate>
                    <?= csrf_field() ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label for="contact_name" class="form-label">Full Name <span style="color:var(--danger)">*</span></label>
                            <input type="text" id="contact_name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" placeholder="e.g. Sarah Rahman" value="<?= e($name) ?>" required>
                            <?php if (isset($errors['name'])): ?>
                                <span class="form-error-msg"><?= e($errors['name']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="contact_email" class="form-label">Email Address <span style="color:var(--danger)">*</span></label>
                            <input type="email" id="contact_email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="name@domain.com" value="<?= e($email) ?>" required>
                            <?php if (isset($errors['email'])): ?>
                                <span class="form-error-msg"><?= e($errors['email']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact_subject" class="form-label">Subject <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="contact_subject" name="subject" class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" placeholder="What is this inquiry about?" value="<?= e($subject) ?>" required>
                        <?php if (isset($errors['subject'])): ?>
                            <span class="form-error-msg"><?= e($errors['subject']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="contact_message" class="form-label">Message Details <span style="color:var(--danger)">*</span></label>
                        <textarea id="contact_message" name="message" class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" rows="5" placeholder="Write your message here (minimum 10 characters)..." required><?= e($message) ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <span class="form-error-msg"><?= e($errors['message']) ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 1rem;">
                        <i class="fas fa-paper-plane"></i> Send Inquiry
                    </button>
                </form>
            </div>
        </div>

        <!-- Google Maps Embed -->
        <div style="margin-top: 4rem; border-radius: var(--radius-lg); overflow: hidden; border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14602.70031976077!2d90.395724!3d23.794582!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c70c15ea1de1%3A0x97856381e3399a0e!2sBanani%2C%20Dhaka!5e0!3m2!1sen!2sbd!4v1700000000000!5m2!1sen!2sbd" 
                width="100%" 
                height="380" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<?php 
$extraJs = '<script src="' . asset_url('frontend/js/validation.js') . '"></script>';
require_once __DIR__ . '/backend/includes/footer.php'; 
?>

