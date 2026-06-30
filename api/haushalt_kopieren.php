<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']); exit; }

$data = json_decode(file_get_contents('php://input'), true);
$quelleId = $data['quelle_haushalt_id'] ?? null;
$kopiereKategorien = ($data['kategorien'] ?? 1) == 1;
$kopiereBuchungen = ($data['buchungen'] ?? 1) == 1;
$kopiereZahlungen = ($data['zahlungen'] ?? 1) == 1;

if (!$quelleId) { http_response_code(400); echo json_encode(['error' => 'quelle_haushalt_id erforderlich']); exit; }

$stmt = $db->prepare('SELECT id, name FROM haushalte WHERE id = ?');
$stmt->execute([$quelleId]);
$quelle = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quelle) { http_response_code(404); echo json_encode(['error' => 'Quell-Haushalt nicht gefunden']); exit; }

$katCount = 0; $buchCount = 0; $zahlCount = 0;

try {
    $db->beginTransaction();
    $katMap = [];
    $buchMap = [];

    // Kategorien
    if ($kopiereKategorien) {
        $stmt = $db->prepare('SELECT * FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellKat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $insert = $db->prepare('INSERT INTO kategorien (haushalt_id, name, typ, art, farbe, aktiv) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($quellKat as $q) {
            $insert->execute([$haushaltId, $q['name'], $q['typ'], $q['art'], $q['farbe'], $q['aktiv']]);
            $katMap[$q['id']] = $db->lastInsertId();
        }
        $katCount = count($quellKat);
    } else {
        // Bestehende Kategorien-IDs merken fuer Buchungen-Zuordnung
        $stmt = $db->prepare('SELECT id FROM kategorien WHERE haushalt_id = ?');
        $stmt->execute([$haushaltId]);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { $katMap[$row['id']] = $row['id']; }
    }

    // Buchungen
    if ($kopiereBuchungen) {
        $stmt = $db->prepare('SELECT * FROM buchungen WHERE haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellBuch = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $insert = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum, aktiv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($quellBuch as $qb) {
            $neueKatId = $katMap[$qb['kategorie_id']] ?? null;
            if ($neueKatId) {
                $insert->execute([$haushaltId, $neueKatId, $qb['betrag'], $qb['beschreibung'], $qb['intervall'], $qb['start_datum'], $qb['end_datum'], $qb['aktiv']]);
                $buchMap[$qb['id']] = $db->lastInsertId();
            }
        }
        $buchCount = count($quellBuch);
    }

    // Zahlungen
    if ($kopiereZahlungen && ($kopiereBuchungen || !empty($buchMap))) {
        $stmt = $db->prepare('SELECT z.* FROM zahlungen z JOIN buchungen b ON z.buchung_id = b.id WHERE b.haushalt_id = ?');
        $stmt->execute([$quelleId]);
        $quellZahl = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $insert = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung) VALUES (?, ?, ?, ?)');
        foreach ($quellZahl as $qz) {
            $neueBuchId = $buchMap[$qz['buchung_id']] ?? null;
            if ($neueBuchId) { $insert->execute([$neueBuchId, $qz['betrag'], $qz['zahlungsdatum'], $qz['bemerkung']]); }
        }
        $zahlCount = count($quellZahl);
    }

    $db->commit();
    $msg = 'Kopiert aus "' . $quelle['name'] . '": ';
    $teile = [];
    if ($kopiereKategorien) $teile[] = $katCount . ' Kategorien';
    if ($kopiereBuchungen) $teile[] = $buchCount . ' Buchungen';
    if ($kopiereZahlungen) $teile[] = $zahlCount . ' Zahlungen';
    echo json_encode(['message' => $msg . implode(', ', $teile), 'kategorien' => $katCount, 'buchungen' => $buchCount, 'zahlungen' => $zahlCount]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Fehler: ' . $e->getMessage()]);
}
