<?php
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $dir = dirname($scriptName);
    $appDirs = ['pages', 'api', 'setup', 'assets'];
    if ($dir === '/' || $dir === '.' || $dir === '' || in_array(basename($dir), $appDirs)) {
        define('BASE_URL', '');
    } else {
        define('BASE_URL', $dir);
    }
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

function getAktivenHaushalt() {
    global $db;
    if (isset($_SESSION['haushalt_id'])) {
        return (int)$_SESSION['haushalt_id'];
    }
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
