<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$quelleId = $data['quelle_haushalt_id'] ?? null;
$kopKategorien = ($data['kategorien'] ?? 0) == 1;
$kopBuchungen = ($data['buchungen'] ?? 0) == 1;
$kopZahlungen = ($data['zahlungen'] ?? 0) == 1;

if (!$quelleId) { http_response_code(400); echo json_encode(['error' => 'quelle_haushalt_id erforderlich']); exit; }
if (!$kopKategorien && !$kopBuchungen && !$kopZahlungen) { http_response_code(400); echo json_encode(['error' => 'Mindestens eine Option waehlen']); exit; }

$stmt = $db->prepare('SELECT name FROM haushalte WHERE id = ?');
$stmt->execute([$quelleId]);
$quelle = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quelle) { http_response_code(404); echo json_encode(['error' => 'Quell-Haushalt nicht gefunden']); exit; }

$katCount = 0; $buchCount = 0; $zahlCount = 0; $skipKat = 0; $skipBuch = 0;

try {
    $db->beginTransaction();
    $katMap = [];
    $buchMap = [];

    // Kategorien kopieren (mit Dublikat-Check)
    if ($kopKategorien) {
        $stmt = $db->prepare('SELECT * FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellKat = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Bestehende Kategorien im Ziel laden
        $stmt2 = $db->prepare('SELECT name, typ FROM kategorien WHERE haushalt_id = ?');
        $stmt2->execute([$haushaltId]);
        $vorhanden = [];
        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) { $vorhanden[$r['name'] . '|' . $r['typ']] = true; }

        $insert = $db->prepare('INSERT INTO kategorien (haushalt_id, name, typ, art, farbe, aktiv) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($quellKat as $q) {
            $key = $q['name'] . '|' . $q['typ'];
            if (isset($vorhanden[$key])) { $skipKat++; continue; }
            $insert->execute([$haushaltId, $q['name'], $q['typ'], $q['art'], $q['farbe'], $q['aktiv']]);
            $katMap[$q['id']] = $db->lastInsertId();
            $vorhanden[$key] = true;
        }
        $katCount = count($quellKat) - $skipKat;
    }

    // Bestehende Kategorien-IDs auch fuer Buchungen-Zuordnung merken
    if ($kopBuchungen && empty($katMap)) {
        $stmt = $db->prepare('SELECT id, name, typ FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$haushaltId]);
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $katMap[$r['id']] = $r['id']; }
    }

    // Buchungen kopieren (mit Dublikat-Check)
    if ($kopBuchungen) {
        $stmt = $db->prepare('SELECT * FROM buchungen WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellBuch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Bestehende Buchungen im Ziel laden
        $stmt2 = $db->prepare('SELECT kategorie_id, betrag, intervall, start_datum FROM buchungen WHERE haushalt_id = ?');
        $stmt2->execute([$haushaltId]);
        $vorhandenB = [];
        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            // Kategorie-Name holen fuer Vergleich
            $vorhandenB[] = $r['kategorie_id'] . '|' . $r['betrag'] . '|' . $r['intervall'];
        }

        $insert = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum, aktiv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($quellBuch as $qb) {
            $neueKatId = $katMap[$qb['kategorie_id']] ?? null;
            if (!$neueKatId) { continue; }
            $key = $neueKatId . '|' . $qb['betrag'] . '|' . $qb['intervall'];
            if (in_array($key, $vorhandenB)) { $skipBuch++; continue; }
            $insert->execute([$haushaltId, $neueKatId, $qb['betrag'], $qb['beschreibung'], $qb['intervall'], $qb['start_datum'], $qb['end_datum'], $qb['aktiv']]);
            $buchMap[$qb['id']] = $db->lastInsertId();
            $vorhandenB[] = $key;
        }
        $buchCount = count($quellBuch) - $skipBuch;
    }

    // Zahlungen kopieren
    if ($kopZahlungen) {
        // Quelle fuer Zahlungen: entweder aus kopierten ODER bestehenden Buchungen
        if (!empty($buchMap)) {
            // Mapping Quell-Buchung-ID -> Ziel-Buchung-ID
            $mapping = $buchMap;
        } else {
            // Buchungen wurden nicht kopiert - mapping ueber Name+Betrag
            $stmt = $db->prepare('SELECT b.id as qid, z.id as zid FROM buchungen b JOIN buchungen z ON b.haushalt_id = ? AND z.haushalt_id = ? AND b.betrag = z.betrag AND b.intervall = z.intervall WHERE z.kategorie_id IN (SELECT id FROM kategorien WHERE haushalt_id = ?)');
            // Einfacher: Buchungen nach Name+Betrag matchen
            $mapping = [];
            $stmt = $db->prepare('SELECT qb.id as qid, zb.id as zid FROM (SELECT id, betrag, intervall, beschreibung FROM buchungen WHERE haushalt_id = ?) qb JOIN (SELECT id, betrag, intervall, beschreibung FROM buchungen WHERE haushalt_id = ?) zb ON qb.betrag = zb.betrag AND qb.intervall = zb.intervall');
            $stmt->execute([$quelleId, $haushaltId]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $mapping[$r['qid']] = $r['zid']; }
        }

        if (!empty($mapping)) {
            $platzhalter = implode(',', array_fill(0, count($mapping), '?'));
            $ids = array_keys($mapping);
            $stmt = $db->prepare("SELECT z.* FROM zahlungen z WHERE z.buchung_id IN ($platzhalter)");
            $stmt->execute($ids);
            $quellZahl = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $insert = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung) VALUES (?, ?, ?, ?)');
            foreach ($quellZahl as $qz) {
                $neueBuchId = $mapping[$qz['buchung_id']] ?? null;
                if ($neueBuchId) { $insert->execute([$neueBuchId, $qz['betrag'], $qz['zahlungsdatum'], $qz['bemerkung']]); $zahlCount++; }
            }
        }
    }

    $db->commit();

    $teile = [];
    if ($kopKategorien) $teile[] = $katCount . ' Kategorien' . ($skipKat > 0 ? " ($skipKat übersprungen)" : '');
    if ($kopBuchungen) $teile[] = $buchCount . ' Buchungen' . ($skipBuch > 0 ? " ($skipBuch übersprungen)" : '');
    if ($kopZahlungen) $teile[] = $zahlCount . ' Zahlungen';
    echo json_encode(['message' => 'Kopiert: ' . implode(', ', $teile), 'kategorien' => $katCount, 'buchungen' => $buchCount, 'zahlungen' => $zahlCount]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Fehler: ' . $e->getMessage()]);
}
