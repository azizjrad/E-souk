<?php
// Basic error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define essential paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CORE_PATH', ROOT_PATH . '/core');
define('PAGE_PATH', PUBLIC_PATH . '/pages');

// Asset paths
define('ASSETS_PATH', PUBLIC_PATH . '/assets');
define('UPLOADS_PATH', PUBLIC_PATH . '/root_uploads');
define('TEMPLATES_PATH', PUBLIC_PATH . '/templates');

// Define URLs
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$basePath = '/E-souk-main/';
define('ROOT_URL', 'http://localhost/E-souk-main/');
define('ASSETS_URL', ROOT_URL . 'public/assets/');
define('UPLOADS_URL', ROOT_URL . 'public/root_uploads/');


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple database connection
require_once ROOT_PATH . '/core/connection.php';
$db = Database::getInstance();

// Helper function to get asset URLs
function asset($path) {
    return ASSETS_URL . ltrim($path, '/');
}

// Helper function to get upload URLs
function upload($path) {
    return UPLOADS_URL . ltrim($path, '/');
}
