<?php
if (!defined('BASE_URL')) {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $path = rtrim($path, '/');
    if (preg_match('#^(/.+?)/(?:pages|api|setup|assets)/#', $path, $m)) {
        $path = $m[1];
    } elseif (substr($path, -9) === '/index.php') {
        $path = substr($path, 0, -9);
    }
    define('BASE_URL', ($path === '/' || $path === '') ? '' : $path);
}

$dbPath = __DIR__ . '/../sqlite/haushaltsplanung.db';

if (!file_exists($dbPath)) {
    require_once __DIR__ . '/../setup/init_db.php';
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB-Fehler: ' . $e->getMessage()]);
    exit;
}
