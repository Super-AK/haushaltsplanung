<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Methode nicht erlaubt']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$quelleId = $data['quelle_haushalt_id'] ?? null;

if (!$quelleId) {
    http_response_code(400);
    echo json_encode(['error' => 'quelle_haushalt_id erforderlich']);
    exit;
}

// Prüfe ob Quelle existiert
$stmt = $db->prepare('SELECT id, name FROM haushalte WHERE id = ?');
$stmt->execute([$quelleId]);
$quelle = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$quelle) {
    http_response_code(404);
    echo json_encode(['error' => 'Quell-Haushalt nicht gefunden']);
    exit;
}

try {
    $db->beginTransaction();

    // Kategorien kopieren
    $stmt = $db->prepare('SELECT * FROM kategorien WHERE haushalt_id = ?');
    $stmt->execute([$quelleId]);
    $quellKategorien = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $katMap = []; // alte ID => neue ID
    $insertKat = $db->prepare('INSERT INTO kategorien (haushalt_id, name, typ, art, farbe, aktiv) VALUES (?, ?, ?, ?, ?, ?)');

    foreach ($quellKategorien as $qk) {
        $insertKat->execute([$haushaltId, $qk['name'], $qk['typ'], $qk['art'], $qk['farbe'], $qk['aktiv']]);
        $katMap[$qk['id']] = $db->lastInsertId();
    }

    // Buchungen kopieren (mit Zuordnung zu neuen Kategorien)
    $stmt = $db->prepare('SELECT * FROM buchungen WHERE haushalt_id = ?');
    $stmt->execute([$quelleId]);
    $quellBuchungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $buchMap = [];
    $insertBuch = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum, aktiv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

    foreach ($quellBuchungen as $qb) {
        $neueKatId = $katMap[$qb['kategorie_id']] ?? null;
        if ($neueKatId) {
            $insertBuch->execute([$haushaltId, $neueKatId, $qb['betrag'], $qb['beschreibung'], $qb['intervall'], $qb['start_datum'], $qb['end_datum'], $qb['aktiv']]);
            $buchMap[$qb['id']] = $db->lastInsertId();
        }
    }

    // Zahlungen kopieren
    $stmt = $db->prepare('SELECT z.* FROM zahlungen z JOIN buchungen b ON z.buchung_id = b.id WHERE b.haushalt_id = ?');
    $stmt->execute([$quelleId]);
    $quellZahlungen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertZahl = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung) VALUES (?, ?, ?, ?)');

    foreach ($quellZahlungen as $qz) {
        $neueBuchId = $buchMap[$qz['buchung_id']] ?? null;
        if ($neueBuchId) {
            $insertZahl->execute([$neueBuchId, $qz['betrag'], $qz['zahlungsdatum'], $qz['bemerkung']]);
        }
    }

    $db->commit();

    echo json_encode([
        'message' => 'Daten aus "' . $quelle['name'] . '" kopiert',
        'kategorien' => count($quellKategorien),
        'buchungen' => count($quellBuchungen),
        'zahlungen' => count($quellZahlungen)
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Fehler beim Kopieren: ' . $e->getMessage()]);
}
