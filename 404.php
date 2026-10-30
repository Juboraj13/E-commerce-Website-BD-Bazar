<?php
/**
 * 404.php — Page Not Found
 */
require_once __DIR__ . '/backend/config/config.php';

http_response_code(404);
$pageTitle = '404 Page Not Found';
require_once __DIR__ . '/backend/includes/header.php';
?>

<div class="container" style="padding: 5rem 1rem; text-align: center;">
    <div style="max-width: 540px; margin: 0 auto;">
        <div style="font-size: 6rem; font-weight: 800; color: var(--primary); font-family: var(--font-heading); line-height: 1;">404</div>
        <h1 style="font-size: 2rem; margin: 1rem 0 0.75rem 0;">Oops! Page Not Found</h1>
        <p style="color: var(--text-muted); font-size: 1.05rem; margin-bottom: 2rem;">
            The page or product you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?= asset_url('index.php') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-home"></i> Back to Homepage
            </a>
            <a href="<?= asset_url('products.php') ?>" class="btn btn-outline btn-lg">
                <i class="fas fa-bag-shopping"></i> Browse Catalog
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/backend/includes/footer.php'; ?>

