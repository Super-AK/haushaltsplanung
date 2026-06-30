<?php
/**
 * Haushaltsplanung DB-Initialisierung
 */

$dbDir = __DIR__ . '/../sqlite';
$dbPath = $dbDir . '/haushaltsplanung.db';

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0775, true);
}

if (file_exists($dbPath)) {
    echo json_encode(['status' => 'already_initialized', 'message' => 'Datenbank existiert bereits']);
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    // Haushalte
    $db->exec("
        CREATE TABLE IF NOT EXISTS haushalte (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            ist_demo INTEGER DEFAULT 0,
            created_at TEXT DEFAULT (datetime('now'))
        )
    ");

    // Demo-Haushalt anlegen
    $db->exec("INSERT INTO haushalte (name, ist_demo) VALUES ('Demo-Haushalt', 1)");
    $haushaltId = $db->lastInsertId();

    // Kategorien
    $db->exec("
        CREATE TABLE IF NOT EXISTS kategorien (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            haushalt_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            typ TEXT NOT NULL CHECK(typ IN ('einnahme', 'ausgabe')),
            art TEXT NOT NULL CHECK(art IN ('fix', 'variabel')),
            farbe TEXT DEFAULT '#4e73df',
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE
        )
    ");

    // Buchungen
    $db->exec("
        CREATE TABLE IF NOT EXISTS buchungen (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            haushalt_id INTEGER NOT NULL,
            kategorie_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            beschreibung TEXT,
            intervall TEXT NOT NULL CHECK(intervall IN ('einmalig', 'woechentlich', 'monatlich', 'vierteljaehrlich', 'jaehrlich')),
            start_datum TEXT NOT NULL,
            end_datum TEXT,
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE,
            FOREIGN KEY (kategorie_id) REFERENCES kategorien(id) ON DELETE CASCADE
        )
    ");

    // Zahlungen
    $db->exec("
        CREATE TABLE IF NOT EXISTS zahlungen (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            buchung_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            zahlungsdatum TEXT NOT NULL,
            bemerkung TEXT,
            FOREIGN KEY (buchung_id) REFERENCES buchungen(id) ON DELETE CASCADE
        )
    ");

    // Kontostand
    $db->exec("
        CREATE TABLE IF NOT EXISTS kontostand (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            haushalt_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            datum TEXT NOT NULL,
            bemerkung TEXT,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE
        )
    ");

    // Indexe
    $db->exec("CREATE INDEX IF NOT EXISTS idx_kategorien_haushalt ON kategorien(haushalt_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_buchungen_haushalt ON buchungen(haushalt_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_kontostand_haushalt ON kontostand(haushalt_id)");

    // Beispiel-Daten laden
    require_once __DIR__ . '/demo_data.php';
    ladeDemoDaten($db, $haushaltId);

    echo json_encode(['status' => 'success', 'message' => 'Datenbank erfolgreich initialisiert']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }
}
