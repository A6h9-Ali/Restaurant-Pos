<?php
// includes/config.php
session_start();

// ── Paths ──────────────────────────────────────────────────────────────────
define('DB_PATH',      __DIR__ . '/../database/restaurant_pos.db');
define('INSTALL_LOCK', __DIR__ . '/../database/installed.lock');
define('URL_CONFIG',   __DIR__ . '/../database/base_url.php');

// ── Redirect to installer if not installed ────────────────────────────────
if (!file_exists(INSTALL_LOCK)) {
    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path   = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/\\');
    header('Location: ' . $scheme . '://' . $host . $path . '/install.php');
    exit;
}

// ── Load BASE_URL saved by installer ──────────────────────────────────────
if (file_exists(URL_CONFIG)) {
    require_once URL_CONFIG;   // defines CONFIGURED_BASE_URL
    define('BASE_URL', rtrim(CONFIGURED_BASE_URL, '/'));
} else {
    // Fallback: auto-detect (should not happen after a proper install)
    $scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
    $appRoot = dirname(__DIR__);
    $rel     = str_replace('\\', '/', str_replace($docRoot, '', $appRoot));
    define('BASE_URL', $scheme . '://' . $host . '/' . ltrim($rel, '/'));
}

// ── Connect to SQLite ──────────────────────────────────────────────────────
try {
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec('PRAGMA journal_mode=WAL');
} catch (Exception $e) {
    die('<h3>Database Error</h3><p>' . htmlspecialchars($e->getMessage()) . '</p>');
}

// ── Helper functions ───────────────────────────────────────────────────────
function getSettings(PDO $pdo): array {
    $stmt = $pdo->query("SELECT key, value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[trim($row['key'])] = trim((string)$row['value']);
    }
    return $settings;
}

function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function sanitize(string $str): string {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function generateOrderNumber(): string {
    return 'ORD-' . strtoupper(substr(uniqid(), -6));
}
