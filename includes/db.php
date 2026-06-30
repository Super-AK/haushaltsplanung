<?php
/**
 * Globale Konfiguration
 */

// Basis-URL automatisch erkennen (funktioniert mit und ohne Unterverzeichnis)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = preg_replace('#/(pages|api|setup|assets)/.*$#', '', $scriptDir);
if ($baseUrl === '/') $baseUrl = '';
define('BASE_URL', $baseUrl);

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
