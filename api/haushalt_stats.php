<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') { http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']); exit; }

$jahr = (int)($_GET['jahr'] ?? date('Y'));

// Alle Haushalte laden
$stmt = $db->query('SELECT * FROM haushalte ORDER BY name');
$haushalte = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ergebnis = [];
foreach ($haushalte as $h) {
    $hid = $h['id'];

    // Kategorien-Anzahl
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM kategorien WHERE haushalt_id = ?');
    $stmt->execute([$hid]);
    $katCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Buchungen-Anzahl
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM buchungen WHERE haushalt_id = ?');
    $stmt->execute([$hid]);
    $buchCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Zahlungen-Anzahl
    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id WHERE b.haushalt_id = ?');
    $stmt->execute([$hid]);
    $zahlCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    // Jahresbilanz
    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
            COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
        FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id
        WHERE b.haushalt_id = ? AND strftime('%Y', z.zahlungsdatum) = ?
    ");
    $stmt->execute([$hid, (string)$jahr]);
    $bilanz = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kontostand
    $stmt = $db->prepare('SELECT betrag FROM kontostand WHERE haushalt_id = ? ORDER BY datum DESC LIMIT 1');
    $stmt->execute([$hid]);
    $ks = $stmt->fetch(PDO::FETCH_ASSOC);

    $ergebnis[] = [
        'id' => $hid,
        'name' => $h['name'],
        'ist_demo' => $h['ist_demo'],
        'created_at' => $h['created_at'],
        'kategorien' => $katCount,
        'buchungen' => $buchCount,
        'zahlungen' => $zahlCount,
        'einnahmen' => $bilanz['einnahmen'],
        'ausgaben' => $bilanz['ausgaben'],
        'bilanz' => $bilanz['einnahmen'] - $bilanz['ausgaben'],
        'kontostand' => $ks ? $ks['betrag'] : null
    ];
}

echo json_encode(['jahr' => $jahr, 'haushalte' => $ergebnis]);
