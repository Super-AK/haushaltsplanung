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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Haushalt-Helper
function getAktivenHaushalt() {
    global $db;
    if (isset($_SESSION['haushalt_id'])) {
        return (int)$_SESSION['haushalt_id'];
    }
    // Ersten verfügbaren Haushalt nehmen
    $stmt = $db->query('SELECT id FROM haushalte ORDER BY id LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $_SESSION['haushalt_id'] = (int)$row['id'];
        return $_SESSION['haushalt_id'];
    }
    return null;
}

function setAktivenHaushalt($id) {
    $_SESSION['haushalt_id'] = (int)$id;
}
