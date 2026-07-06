<?php
/**
 * Datenbank-Migration
 * Fuehrt Schema-Aenderungen aus OHNE bestehende Daten zu loeschen.
 * Wird automatisch beim ersten Zugriff nach Update ausgefuehrt.
 */

function fuehreMigrationenAus($db) {
    // Migration-Tabelle anlegen falls nicht vorhanden
    $db->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL UNIQUE,
        executed_at TEXT DEFAULT (datetime('now'))
    )");

    $ausgefuehrt = [];
    $stmt = $db->query('SELECT name FROM migrations');
    $bereitsDa = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // === Migration 1: User-System ===
    if (!in_array('v2_1_users', $bereitsDa)) {
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            benutzername TEXT NOT NULL UNIQUE,
            passwort_hash TEXT NOT NULL,
            rolle TEXT NOT NULL DEFAULT 'benutzer' CHECK(rolle IN ('admin', 'benutzer')),
            email TEXT,
            aktiv INTEGER DEFAULT 1,
            created_at TEXT DEFAULT (datetime('now'))
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS user_haushalte (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            haushalt_id INTEGER NOT NULL,
            recht TEXT NOT NULL DEFAULT 'lesen' CHECK(recht IN ('lesen', 'schreiben', 'besitzer')),
            created_at TEXT DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (haushalt_id) REFERENCES haushalte(id) ON DELETE CASCADE,
            UNIQUE(user_id, haushalt_id)
        )");

        $db->exec("CREATE INDEX IF NOT EXISTS idx_user_haushalte_user ON user_haushalte(user_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_user_haushalte_haushalt ON user_haushalte(haushalt_id)");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_users_benutzername ON users(benutzername)");

        // Demo-User nur anlegen wenn noch keine vorhanden
        $stmt = $db->query('SELECT COUNT(*) as cnt FROM users');
        if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] == 0) {
            $adminHash = password_hash('admin123', PASSWORD_BCRYPT);
            $demoHash = password_hash('demo123', PASSWORD_BCRYPT);
            $db->exec("INSERT INTO users (benutzername, passwort_hash, rolle, email) VALUES
                ('admin', '$adminHash', 'admin', 'admin@localhost'),
                ('demo', '$demoHash', 'benutzer', 'demo@localhost')");
        }

        // Alle bestehenden Haushalte dem Admin zuordnen (falls noch nicht geschehen)
        $stmt = $db->query("SELECT h.id FROM haushalte h WHERE NOT EXISTS (SELECT 1 FROM user_haushalte uh WHERE uh.haushalt_id = h.id AND uh.user_id = 1)");
        while ($h = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $db->prepare("INSERT OR IGNORE INTO user_haushalte (user_id, haushalt_id, recht) VALUES (1, ?, 'besitzer')")->execute([$h['id']]);
        }

        $db->prepare("INSERT INTO migrations (name) VALUES (?)")->execute(['v2_1_users']);
        $ausgefuehrt[] = 'v2_1_users';
    }

    // === Hier weitere Migrationen einfuegen ===
    // if (!in_array('v2_2_FEATURE', $bereitsDa)) {
    //     $db->exec("...");
    //     $db->prepare("INSERT INTO migrations (name) VALUES (?)")->execute(['v2_2_FEATURE']);
    //     $ausgefuehrt[] = 'v2_2_FEATURE';
    // }

    return $ausgefuehrt;
}
