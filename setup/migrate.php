<?php
/**
 * Datenbank-Migration
 * Fuehrt Schema-Aenderungen aus OHNE bestehende Daten zu loeschen.
 */

function fuehreMigrationenAus($db) {
    // Kurzzeitiges Warten auf konkurrierende Zugriffe (php-fpm-Pool),
    // damit parallele Requests die Migration nicht zerstoeren.
    $db->exec('PRAGMA busy_timeout = 20000');

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

    // === Migration 2: Demo-User Zugriff auf Demo-Haushalt ===
    // Demo-User (benutzername='demo') erhaelt Schreibzugriff auf alle
    // Haushalte mit ist_demo=1, damit er den Demo-Haushalt sehen und testen kann.
    if (!in_array('v2_3_demo_haushalt_zugriff', $bereitsDa)) {
        $stmt = $db->query("SELECT uh.haushalt_id FROM user_haushalte uh
            JOIN users u ON uh.user_id = u.id
            JOIN haushalte h ON uh.haushalt_id = h.id
            WHERE u.benutzername = 'demo' AND h.ist_demo = 1
            LIMIT 1");
        if (!$stmt->fetch()) {
            $db->exec("INSERT OR IGNORE INTO user_haushalte (user_id, haushalt_id, recht)
                SELECT u.id, h.id, 'schreiben'
                FROM users u, haushalte h
                WHERE u.benutzername = 'demo' AND h.ist_demo = 1");
        }
        $db->prepare("INSERT OR IGNORE INTO migrations (name) VALUES (?)")->execute(['v2_3_demo_haushalt_zugriff']);
    }

    // === Migration 3: Halbjaehrliches Intervall + automatische Zahlungen ===
    if (!in_array('v2_4_hj_auto_zahlungen', $bereitsDa)) {
        // Seriell gegen konkurrierende Requests absichern: Die Migration
        // laeuft in einer IMMEDIATE-Transaktion. foreign_keys wird VOR dem
        // Transaktionsstart deaktiviert, damit ALTER TABLE ... RENAME die
        // FK-Referenzen der Kind-Tabelle nicht auf den Alt-Namen umbiegt.
        $db->exec('PRAGMA foreign_keys=OFF');
        $db->exec('BEGIN IMMEDIATE');
        try {
            // 3a: buchungen-Tabelle neu aufbauen, damit das CHECK-Constraint
            //     das Intervall 'halbjaehrlich' erlaubt (SQLite kann CHECK nicht aendern).
            $stmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='buchungen'");
            $schema = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($schema && strpos($schema['sql'], 'halbjaehrlich') === false) {
                $db->exec('ALTER TABLE buchungen RENAME TO buchungen_alt');
                $db->exec("CREATE TABLE buchungen (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    haushalt_id INTEGER NOT NULL,
                    kategorie_id INTEGER NOT NULL,
                    betrag REAL NOT NULL,
                    beschreibung TEXT,
                    intervall TEXT NOT NULL CHECK(intervall IN ('einmalig', 'woechentlich', 'monatlich', 'vierteljaehrlich', 'halbjaehrlich', 'jaehrlich')),
                    start_datum TEXT NOT NULL,
                    end_datum TEXT,
                    aktiv INTEGER DEFAULT 1,
                    created_at TEXT DEFAULT (datetime('now')),
                    FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE,
                    FOREIGN KEY (kategorie_id) REFERENCES kategorien(id) ON DELETE CASCADE
                )");
                $db->exec("INSERT INTO buchungen (id, haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum, aktiv, created_at)
                    SELECT id, haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum, aktiv, created_at FROM buchungen_alt");
                $db->exec('DROP TABLE buchungen_alt');
                $db->exec('CREATE INDEX IF NOT EXISTS idx_buchungen_haushalt ON buchungen(haushalt_id)');
                $db->exec('CREATE INDEX IF NOT EXISTS idx_buchungen_kategorie ON buchungen(kategorie_id)');
            } else {
                // Rebuild bereits erfolgt (z.B. unterbrochener Lauf): Alt-Tabelle aufraeumen
                $db->exec('DROP TABLE IF EXISTS buchungen_alt');
            }

            // 3a2: FK-Reparatur - falls ein frueherer (unterbrochener) Lauf
            //      die FK-Referenz von zahlungen auf buchungen_alt umgebogen hat.
            $fkSql = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='zahlungen'")->fetch(PDO::FETCH_ASSOC);
            if ($fkSql && strpos($fkSql['sql'], 'buchungen_alt') !== false) {
                $db->exec('ALTER TABLE zahlungen RENAME TO zahlungen_alt2');
                $db->exec("CREATE TABLE zahlungen (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    buchung_id INTEGER NOT NULL,
                    betrag REAL NOT NULL,
                    zahlungsdatum TEXT NOT NULL,
                    bemerkung TEXT,
                    automatisch INTEGER DEFAULT 0,
                    FOREIGN KEY (buchung_id) REFERENCES buchungen(id) ON DELETE CASCADE
                )");
                $db->exec('INSERT INTO zahlungen (id, buchung_id, betrag, zahlungsdatum, bemerkung, automatisch)
                    SELECT id, buchung_id, betrag, zahlungsdatum, bemerkung, automatisch FROM zahlungen_alt2');
                $db->exec('DROP TABLE zahlungen_alt2');
            }

            // 3b: automatisch-Spalte in zahlungen ergaenzen
            $cols = $db->query('PRAGMA table_info(zahlungen)')->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('automatisch', $cols)) {
                $db->exec('ALTER TABLE zahlungen ADD COLUMN automatisch INTEGER DEFAULT 0');
            }

            // 3c: Fehlende Zahlungen fuer alle aktiven Buchungen nachziehen
            $stmt = $db->query('SELECT id FROM buchungen WHERE aktiv = 1');
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                erzeugeAutomatischeZahlungen($db, (int)$row['id']);
            }

            $db->prepare("INSERT OR IGNORE INTO migrations (name) VALUES (?)")->execute(['v2_4_hj_auto_zahlungen']);
            $db->exec('COMMIT');
            $db->exec('PRAGMA foreign_keys=ON');
        } catch (Throwable $e) {
            if ($db->inTransaction()) { $db->exec('ROLLBACK'); }
            $db->exec('PRAGMA foreign_keys=ON');
            throw $e;
        }
    }

    // === Hier weitere Migrationen einfuegen ===

    return [];
}
