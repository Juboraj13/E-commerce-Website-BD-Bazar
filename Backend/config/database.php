<?php
/**
 * database.php — PDO MySQL Database Connection
 * Provides a persistent or reusable PDO instance with prepared statements enabled.
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function get_db_connection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In development, show error; in production log it
            error_log('Database Connection Error: ' . $e->getMessage());
            die('Database connection failed. Please ensure MySQL is running in XAMPP and ecommerce_db is imported.');
        }
    }

    return $pdo;
}

// Global convenience variable
$pdo = get_db_connection();

