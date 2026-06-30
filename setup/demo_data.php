<?php
/**
 * Demo-Daten fuer einen Haushalt (nur wenn keine vorhanden)
 */
function ladeDemoDaten($db, $haushaltId) {
    // Pruefe ob schon Daten vorhanden
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM kategorien WHERE haushalt_id = ?');
    $stmt->execute([$haushaltId]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] > 0) {
        return; // Bereits Daten vorhanden
    }

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

    $stmt = $db->prepare('INSERT INTO kategorien (haushalt_id, name, typ, art, farbe) VALUES (?, ?, ?, ?, ?)');
    foreach ($kategorien as $k) {
        $stmt->execute([$haushaltId, $k[0], $k[1], $k[2], $k[3]]);
    }

    // Kategorie-IDs holen
    $stmt = $db->prepare('SELECT id FROM kategorien WHERE haushalt_id = ? ORDER BY id');
    $stmt->execute([$haushaltId]);
    $katIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $jahresanfang = date('Y-01-01');
    $buchungen = [
        [0, 3000, 'Monatsgehalt', 'monatlich', $jahresanfang],
        [1, 500, 'Freelance-Auftrag', 'monatlich', $jahresanfang],
        [2, 50, 'Zinsen Girokonto', 'vierteljaehrlich', $jahresanfang],
        [3, -1200, 'Warmmiete', 'monatlich', $jahresanfang],
        [4, -40, 'Internet + Handy', 'monatlich', $jahresanfang],
        [5, -180, 'Krankenversicherung', 'vierteljaehrlich', $jahresanfang],
        [6, -400, 'Wocheneinkauf', 'monatlich', $jahresanfang],
        [7, -150, 'OePNV + Tanken', 'monatlich', $jahresanfang],
        [8, -100, 'Kino, Hobbys', 'monatlich', $jahresanfang],
        [9, -50, 'Kleidung', 'monatlich', $jahresanfang],
        [10, -30, 'Apotheke', 'monatlich', $jahresanfang],
    ];

    $stmt = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($buchungen as $b) {
        $stmt->execute([$haushaltId, $katIds[$b[0]], $b[1], $b[2], $b[3], $b[4]]);
    }

    // Zahlungen (letzte 3 Monate)
    $monate = [
        date('Y-m-d', strtotime('-3 months')),
        date('Y-m-d', strtotime('-2 months')),
        date('Y-m-d', strtotime('-1 months')),
    ];

    $bStmt = $db->prepare('SELECT id, betrag, intervall FROM buchungen WHERE haushalt_id = ?');
    $bStmt->execute([$haushaltId]);
    $alleBuchungen = $bStmt->fetchAll(PDO::FETCH_ASSOC);

    $zStmt = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum) VALUES (?, ?, ?)');
    foreach ($monate as $monat) {
        foreach ($alleBuchungen as $b) {
            if ($b['intervall'] === 'monatlich') {
                $zStmt->execute([$b['id'], $b['betrag'], $monat]);
            } elseif ($b['intervall'] === 'vierteljaehrlich') {
                $monatNum = (int)date('m', strtotime($monat));
                if ($monatNum % 3 === 1) {
                    $zStmt->execute([$b['id'], $b['betrag'], $monat]);
                }
            }
        }
    }
}
