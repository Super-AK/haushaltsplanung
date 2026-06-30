<?php
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $appDirs = ['pages', 'api', 'setup', 'assets'];
    $parts = explode('/', trim($scriptName, '/'));
    $base = '';
    foreach ($parts as $i => $part) {
        if (in_array($part, $appDirs)) {
            $base = $i > 0 ? '/' . implode('/', array_slice($parts, 0, $i)) : '';
            break;
        }
    }
    if ($base === '' && !empty($parts) && !in_array($parts[0], $appDirs) && count($parts) > 1) {
        $base = '/' . implode('/', array_slice($parts, 0, -1));
    }
    define('BASE_URL', $base);
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$dbPath = __DIR__ . '/../sqlite/haushaltsplanung.db';
if (!file_exists($dbPath)) { require_once __DIR__ . '/../setup/init_db.php'; exit; }

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
    // 1. Versuche Session
    if (!empty($_SESSION['haushalt_id'])) {
        // Pruefe ob Haushalt noch existiert
        $stmt = $db->prepare('SELECT id FROM haushalte WHERE id = ?');
        $stmt->execute([$_SESSION['haushalt_id']]);
        if ($stmt->fetch()) {
            return (int)$_SESSION['haushalt_id'];
        }
    }
    // 2. Cookie als Fallback
    if (!empty($_COOKIE['haushalt_id'])) {
        $stmt = $db->prepare('SELECT id FROM haushalte WHERE id = ?');
        $stmt->execute([$_COOKIE['haushalt_id']]);
        if ($stmt->fetch()) {
            $_SESSION['haushalt_id'] = (int)$_COOKIE['haushalt_id'];
            return $_SESSION['haushalt_id'];
        }
    }
    // 3. Ersten Haushalt nehmen
    $stmt = $db->query('SELECT id FROM haushalte ORDER BY id LIMIT 1');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $_SESSION['haushalt_id'] = (int)$row['id'];
        setcookie('haushalt_id', $row['id'], time() + 86400 * 30, '/');
        return $_SESSION['haushalt_id'];
    }
    return null;
}

function setAktivenHaushalt($id) {
    $_SESSION['haushalt_id'] = (int)$id;
    setcookie('haushalt_id', $id, time() + 86400 * 30, '/');
}
