<?php
/**
 * Globale Konfiguration
 */

// BASE_URL auto-erkennen: funktioniert mit und ohne Unterverzeichnis
if (!defined('BASE_URL')) {
    // Prüfe ob wir in einem Unterverzeichnis sind
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    
    // Entferne führenden Slash und Script-Namen
    $path = parse_url($requestUri, PHP_URL_PATH);
    $path = rtrim($path, '/');
    
    // Wenn index.php am Ende, entfernen
    if (substr($path, -9) === '/index.php') {
        $path = substr($path, 0, -9);
    }
    
    // Wenn Seite aus pages/ Verzeichnis, Basis-Pfad finden
    if (preg_match('#^(/.+?)/pages/#', $path, $m)) {
        $path = $m[1];
    } elseif (preg_match('#^(/.+?)/setup/#', $path, $m)) {
        $path = $m[1];
    } elseif (preg_match('#^(/.+?)/api/#', $path, $m)) {
        $path = $m[1];
    }
    
    // Root-Fall: nur /
    if ($path === '' || $path === '/') {
        $path = '';
    }
    
    define('BASE_URL', $path);
}

// Datenbank-Pfad (relativ zum Projekt)
$dbPath = __DIR__ . '/../sqlite/haushaltsplanung.db';

// DB initialisieren falls nicht vorhanden
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
    echo json_encode(['error' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()]);
    exit;
}
