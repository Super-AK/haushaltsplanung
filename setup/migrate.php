<?php
/**
 * Datenbank-Migration
 * Fuehrt Schema-Aenderungen aus OHNE bestehende Daten zu loeschen.
 */

function fuehreMigrationenAus($db) {
    // Pruefe ob Kern-Tabellen vorhanden sind
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='haushalte'");
    if (!$stmt->fetch()) {
        // Haushalte-Tabelle fehlt - init_db.php muss laufen
        // Aber wir sind schon NACH init_db, also hier erzeugen
        $db->exec("CREATE TABLE IF NOT EXISTS haushalte (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            ist_demo INTEGER DEFAULT 0,
            created_at TEXT DEFAULT (datetime('now'))
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS kategorien (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            haushalt_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            typ TEXT NOT NULL,
            art TEXT NOT NULL,
            farbe TEXT DEFAULT '#4e73df',
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS buchungen (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            haushalt_id INTEGER NOT NULL,
            kategorie_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            beschreibung TEXT,
            intervall TEXT NOT NULL,
            start_datum TEXT NOT NULL,
            end_datum TEXT,
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE,
            FOREIGN KEY (kategorie_id) REFERENCES kategorien(id) ON DELETE CASCADE
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS zahlungen (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            buchung_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            zahlungsdatum TEXT NOT NULL,
            bemerkung TEXT,
            FOREIGN KEY (buchung_id) REFERENCES buchungen(id) ON DELETE CASCADE
        )");
        $db->exec("CREATE TABLE IF NOT EXISTS kontostand (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            haushalt_id INTEGER NOT NULL,
            betrag REAL NOT NULL,
            datum TEXT NOT NULL,
            bemerkung TEXT,
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE
        )");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_kategorien_haushalt ON kategorien(haushalt_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_buchungen_haushalt ON buchungen(haushalt_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_kontostand_haushalt ON kontostand(haushalt_id)");
    }

    // Migration-Tabelle anlegen
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        executed_at TEXT DEFAULT (datetime('now'))
    )");

    $stmt = $db->query('SELECT name FROM migrations');
    $bereitsDa = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // === Migration 1: User-System ===
    if (!in_array('v2_1_users', $bereitsDa)) {
        // Pruefe ob users-Tabelle schon existiert
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if (!$stmt->fetch()) {
            $db->exec("CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                benutzername TEXT NOT NULL UNIQUE,
                passwort_hash TEXT NOT NULL,
                rolle TEXT NOT NULL DEFAULT 'benutzer',
                email TEXT,
                aktiv INTEGER DEFAULT 1,
                created_at TEXT DEFAULT (datetime('now'))
            )");

            $db->exec("CREATE TABLE user_haushalte (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                haushalt_id INTEGER NOT NULL,
                recht TEXT NOT NULL DEFAULT 'lesen',
                created_at TEXT DEFAULT (datetime('now')),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE,
                UNIQUE(user_id, haushalt_id)
            )");

            $db->exec("CREATE INDEX IF NOT EXISTS idx_user_haushalte_user ON user_haushalte(user_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_user_haushalte_haushalt ON user_haushalte(haushalt_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_users_benutzername ON users(benutzername)");
        }

        // Demo-User nur anlegen wenn keine vorhanden
        $stmt = $db->query('SELECT COUNT(*) as cnt FROM users');
        if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] == 0) {
            $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
            $demoHash = password_hash('demo123', PASSWORD_BCRYPT);
            $db->exec("INSERT INTO users (benutzername, passwort_hash, rolle, email) VALUES
                ('admin', '$adminHash', 'admin', 'admin@localhost'),
                ('demo', '$demoHash', 'benutzer', 'demo@localhost')");
        }

        // Haushalte dem Admin zuordnen
        $stmt = $db->query("SELECT h.id FROM haushalte h WHERE NOT EXISTS (SELECT 1 FROM user_haushalte uh WHERE uh.haushalt_id = h.id AND uh.user_id = 1)");
        while ($h = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $db->prepare("INSERT OR IGNORE INTO user_haushalte (user_id, haushalt_id, recht) VALUES (1, ?, 'besitzer')")->execute([$h['id']]);
        }

        $db->prepare("INSERT OR IGNORE INTO migrations (name) VALUES (?)")->execute(['v2_1_users']);
    }

    // === Hier weitere Migrationen einfuegen ===

    return [];
}
