<?php
/**
 * Haushaltsplanung DB-Initialisierung
 * Erstellt die SQLite-Datenbank mit Schema und Beispiel-Daten
 */

$dbPath = '/var/www/sqlite/haushaltsplanung.db';

if (file_exists($dbPath)) {
    echo json_encode(['status' => 'already_initialized', 'message' => 'Datenbank existiert bereits']);
    exit;
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    // Tabellen anlegen
    $db->exec("
        CREATE TABLE IF NOT EXISTS kategorien (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            typ TEXT NOT NULL CHECK(typ IN ('einnahme', 'ausgabe')),
            art TEXT NOT NULL CHECK(art IN ('fix', 'variabel')),
            farbe TEXT DEFAULT '#4e73df',
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS buchungen (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            kategorie_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            beschreibung TEXT,
            intervall TEXT NOT NULL CHECK(intervall IN ('einmalig', 'woechentlich', 'monatlich', 'vierteljaehrlich', 'jaehrlich')),
            start_datum TEXT NOT NULL,
            end_datum TEXT,
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (kategorie_id) REFERENCES kategorien(id) ON DELETE CASCADE
        )
    ");

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

    // Beispiel-Kategorien
    $kategorien = [
        ['Gehalt', 'einnahme', 'fix', '#1cc88a'],
        ['Freelance', 'einnahme', 'variabel', '#36b9cc'],
        ['Zinsen/Dividenden', 'einnahme', 'variabel', '#f6c23e'],
        ['Miete', 'ausgabe', 'fix', '#e74a3b'],
        ['Internet/Telefon', 'ausgabe', 'fix', '#fd7e14'],
        ['Versicherung', 'ausgabe', 'fix', '#6f42c1'],
        ['Lebensmittel', 'ausgabe', 'variabel', '#20c9a7'],
        ['Transport', 'ausgabe', 'variabel', '#0dcaf0'],
        ['Freizeit', 'ausgabe', 'variabel', '#ffc107'],
        ['Kleidung', 'ausgabe', 'variabel', '#d63384'],
        ['Gesundheit', 'ausgabe', 'variabel', '#198754'],
    ];

    $stmt = $db->prepare('INSERT INTO kategorien (name, typ, art, farbe) VALUES (?, ?, ?, ?)');
    foreach ($kategorien as $k) {
        $stmt->execute($k);
    }

    // Beispiel-Buchungen
    $heute = date('Y-m-d');
    $jahresanfang = date('Y-01-01');

    $buchungen = [
        [1, 3000, 'Monatsgehalt', 'monatlich', $jahresanfang],
        [2, 500, 'Freelance-Auftrag', 'monatlich', $jahresanfang],
        [3, 50, 'Zinsen Girokonto', 'vierteljaehrlich', $jahresanfang],
        [4, -1200, 'Warmmiete', 'monatlich', $jahresanfang],
        [5, -40, 'Internet + Handy', 'monatlich', $jahresanfang],
        [6, -180, 'Krankenversicherung', 'vierteljaehrlich', $jahresanfang],
        [7, -400, 'Wocheneinkauf', 'monatlich', $jahresanfang],
        [8, -150, 'ÖPNV + Tanken', 'monatlich', $jahresanfang],
        [9, -100, 'Kino, Hobbys', 'monatlich', $jahresanfang],
        [10, -50, 'Kleidung', 'monatlich', $jahresanfang],
        [11, -30, 'Apotheke', 'monatlich', $jahresanfang],
    ];

    $stmt = $db->prepare('INSERT INTO buchungen (kategorie_id, betrag, beschreibung, intervall, start_datum) VALUES (?, ?, ?, ?, ?)');
    foreach ($buchungen as $b) {
        $stmt->execute($b);
    }

    // Beispiel-Zahlungen (letzte 3 Monate simulieren)
    $monate = [
        date('Y-m-d', strtotime('-3 months')),
        date('Y-m-d', strtotime('-2 months')),
        date('Y-m-d', strtotime('-1 months')),
    ];

    $stmtBuchung = $db->query('SELECT id, betrag, intervall FROM buchungen');
    $alleBuchungen = $stmtBuchung->fetchAll(PDO::FETCH_ASSOC);

    $stmtZahlung = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum) VALUES (?, ?, ?)');

    foreach ($monate as $monat) {
        foreach ($alleBuchungen as $b) {
            if ($b['intervall'] === 'monatlich' || $b['intervall'] === 'einmalig') {
                $datum = date('Y-m-d', strtotime($monat));
                $stmtZahlung->execute([$b['id'], $b['betrag'], $datum]);
            } elseif ($b['intervall'] === 'vierteljaehrlich') {
                $monatNum = (int)date('m', strtotime($monat));
                if ($monatNum % 3 === 1) {
                    $stmtZahlung->execute([$b['id'], $b['betrag'], $monat]);
                }
            }
        }
    }

    echo json_encode(['status' => 'success', 'message' => 'Datenbank erfolgreich initialisiert mit Beispiel-Daten']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }
}
