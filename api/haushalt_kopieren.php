<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
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
    $buchMap = []; // Quell-BuchID => Ziel-BuchID (alle: kopierte + bereits vorhandene)

    // === KATEGORIEN ===
    if ($kopKategorien) {
        $stmt = $db->prepare('SELECT * FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellKat = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    // === BUCHUNGEN ===
    if ($kopBuchungen) {
        // Quell-Kategorien holen
        $stmt = $db->prepare('SELECT id, name, typ FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellKats = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $quellKats[$r['id']] = $r; }

        // Ziel-Kategorien holen (auch wenn gerade erst kopiert)
        $stmt = $db->prepare('SELECT id, name, typ FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$haushaltId]);
        $zielKats = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $zielKats[$r['name'] . '|' . $r['typ']] = $r['id']; }

        $stmt = $db->prepare('SELECT * FROM buchungen WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellBuch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Bestehende Buchungen im Ziel (fuer Dublikat-Check)
        $stmt2 = $db->prepare('SELECT b.kategorie_id, b.betrag, b.intervall, k.name, k.typ FROM buchungen b JOIN kategorien k ON b.kategorie_id = k.id WHERE b.haushalt_id = ?');
        $stmt2->execute([$haushaltId]);
        $vorhandenB = [];
        while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $vorhandenB[$r['name'] . '|' . $r['typ'] . '|' . $r['betrag'] . '|' . $r['intervall']] = true;
        }

        $insert = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum, aktiv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($quellBuch as $qb) {
            $katName = $quellKats[$qb['kategorie_id']]['name'] ?? '';
            $katTyp = $quellKats[$qb['kategorie_id']]['typ'] ?? '';
            $zielKatId = $zielKats[$katName . '|' . $katTyp] ?? null;
            if (!$zielKatId) { continue; }

            $key = $katName . '|' . $katTyp . '|' . $qb['betrag'] . '|' . $qb['intervall'];
            if (isset($vorhandenB[$key])) {
                // Bereits vorhanden - ID aus Ziel finden fuer Zahlungen
                $stmt3 = $db->prepare('SELECT b.id FROM buchungen b JOIN kategorien k ON b.kategorie_id = k.id WHERE b.haushalt_id = ? AND k.name = ? AND k.typ = ? AND b.betrag = ? AND b.intervall = ?');
                $stmt3->execute([$haushaltId, $katName, $katTyp, $qb['betrag'], $qb['intervall']]);
                $existing = $stmt3->fetch(PDO::FETCH_ASSOC);
                if ($existing) { $buchMap[$qb['id']] = $existing['id']; }
                $skipBuch++;
                continue;
            }

            $insert->execute([$haushaltId, $zielKatId, $qb['betrag'], $qb['beschreibung'], $qb['intervall'], $qb['start_datum'], $qb['end_datum'], $qb['aktiv']]);
            $buchMap[$qb['id']] = $db->lastInsertId();
            $vorhandenB[$key] = true;
        }
        $buchCount = count($quellBuch) - $skipBuch;
    }

    // === ZAHLUNGEN ===
    if ($kopZahlungen) {
        // Mapping aufbauen: Wenn Buchungen nicht kopiert wurden, ueber Name+Betrag matchen
        if (empty($buchMap)) {
            // Quell-Buchungen -> Name+Betrag, Ziel-Buchungen -> Name+Betrag -> ID
            $stmt = $db->prepare('SELECT id, betrag, intervall, beschreibung FROM buchungen WHERE haushalt_id = ?');
            $stmt->execute([$quelleId]);
            $quellB = [];
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) { $quellB[$r['id']] = $r; }

            $stmt = $db->prepare('SELECT id, betrag, intervall, beschreibung FROM buchungen WHERE haushalt_id = ?');
            $stmt->execute([$haushaltId]);
            while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                foreach ($quellB as $qid => $qb) {
                    if ($qb['betrag'] == $r['betrag'] && $qb['intervall'] == $r['intervall']) {
                        $buchMap[$qid] = $r['id'];
                        break;
                    }
                }
            }
        }

        if (!empty($buchMap)) {
            $ids = array_keys($buchMap);
            $platzhalter = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT * FROM zahlungen WHERE buchung_id IN ($platzhalter)");
            $stmt->execute($ids);
            $quellZahl = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Vorhandene Zahlungen pruefen
            $zielBuchIds = array_values($buchMap);
            $platzhalter2 = implode(',', array_fill(0, count($zielBuchIds), '?'));
            $stmt2 = $db->prepare("SELECT buchung_id, betrag, zahlungsdatum FROM zahlungen WHERE buchung_id IN ($platzhalter2)");
            $stmt2->execute($zielBuchIds);
            $vorhandenZ = [];
            while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $vorhandenZ[$r['buchung_id'] . '|' . $r['betrag'] . '|' . $r['zahlungsdatum']] = true;
            }

            $insert = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung) VALUES (?, ?, ?, ?)');
            foreach ($quellZahl as $qz) {
                $neueBuchId = $buchMap[$qz['buchung_id']] ?? null;
                if (!$neueBuchId) { continue; }
                $zKey = $neueBuchId . '|' . $qz['betrag'] . '|' . $qz['zahlungsdatum'];
                if (isset($vorhandenZ[$zKey])) { continue; }
                $insert->execute([$neueBuchId, $qz['betrag'], $qz['zahlungsdatum'], $qz['bemerkung']]);
                $zahlCount++;
            }
        }
    }

    $db->commit();

    $teile = [];
    if ($kopKategorien) $teile[] = $katCount . ' Kategorien' . ($skipKat > 0 ? " ($skipKat Duplikate)" : '');
    if ($kopBuchungen) $teile[] = $buchCount . ' Buchungen' . ($skipBuch > 0 ? " ($skipBuch Duplikate)" : '');
    if ($kopZahlungen) $teile[] = $zahlCount . ' Zahlungen';
    echo json_encode(['message' => 'Kopiert: ' . implode(', ', $teile), 'kategorien' => $katCount, 'buchungen' => $buchCount, 'zahlungen' => $zahlCount]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Fehler: ' . $e->getMessage()]);
}
