<?php
/**
 * admin/index.php — Admin Root Entry Point
 * Routes authenticated administrators to dashboard or redirects to admin login.
 */
require_once __DIR__ . '/../backend/config/config.php';

if (is_admin()) {
    redirect(asset_url('backend/admin/dashboard.php'));
} else {
    redirect(asset_url('admin/login.php'));
}

