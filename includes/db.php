<?php
/**
 * SQLite Datenbank-Verbindung
 */

$dbPath = '/var/www/sqlite/haushaltsplanung.db';

// DB initialisieren falls nicht vorhanden
if (!file_exists($dbPath)) {
    require_once __DIR__ . '/../setup/init_db.php';
    // Nach Init: Response beenden da init_db.php JSON ausgibt
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
