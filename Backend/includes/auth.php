<?php
/**
 * auth.php — Access Guards & Authorization Helpers
 * Enforces role-based route protection across the application.
 */

/**
 * Require a signed-in user
 */
function require_login(string $redirectTo = 'login.php'): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        // Store intended URL if GET request
        if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        }
        redirect(asset_url($redirectTo));
    }
}

/**
 * Require an administrator user
 */
function require_admin(string $redirectTo = 'admin/login.php'): void {
    if (!is_logged_in()) {
        set_flash('error', 'Administrator login required.');
        $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        redirect(asset_url($redirectTo));
    }

    if (!is_admin()) {
        http_response_code(403);
        set_flash('error', 'Access denied. You do not have administrator permissions.');
        redirect(asset_url('index.php'));
    }
}

/**
 * Require guest (unauthenticated user)
 */
function require_guest(string $redirectTo = 'index.php'): void {
    if (is_logged_in()) {
        if (is_admin()) {
            redirect(asset_url('backend/admin/dashboard.php'));
        }
        redirect(asset_url($redirectTo));
    }
}

