<?php
/**
 * Demo-Daten für einen Haushalt
 */

function ladeDemoDaten($db, $haushaltId) {
    // Kategorien
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

    // Buchungen
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

    // Kategorie-IDs für diesen Haushalt holen
    $katStmt = $db->prepare('SELECT id FROM kategorien WHERE haushalt_id = ? ORDER BY id');
    $katStmt->execute([$haushaltId]);
    $katIds = $katStmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($buchungen as $i => $b) {
        $stmt->execute([$haushaltId, $katIds[$i], $b[1], $b[2], $b[3], $b[4]]);
    }

    // Zahlungen (letzte 3 Monate)
    $monate = [
        date('Y-m-d', strtotime('-3 months')),
        date('Y-m-d', strtotime('-2 months')),
        date('Y-m-d', strtotime('-1 months')),
    ];

    $bStmt = $db->query('SELECT id, betrag, intervall FROM buchungen WHERE haushalt_id = ' . (int)$haushaltId);
    $alleBuchungen = $bStmt->fetchAll(PDO::FETCH_ASSOC);

    $zStmt = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum) VALUES (?, ?, ?)');
    foreach ($monate as $monat) {
        foreach ($alleBuchungen as $b) {
            if ($b['intervall'] === 'monatlich' || $b['intervall'] === 'einmalig') {
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
