<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'GET') { http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']); exit; }

$jahr = (int)($_GET['jahr'] ?? date('Y'));
$erlaubt = getErlaubteHaushalte();

if (empty($erlaubt)) {
    echo json_encode(['jahr' => $jahr, 'haushalte' => []]);
    exit;
}

$ph = implode(',', array_fill(0, count($erlaubt), '?'));
$stmt = $db->prepare("SELECT * FROM haushalte WHERE id IN ($ph) ORDER BY name");
$stmt->execute($erlaubt);
$haushalte = $stmt->fetchAll(PDO::FETCH_ASSOC);

$ergebnis = [];
foreach ($haushalte as $h) {
    $hid = $h['id'];

    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM kategorien WHERE haushalt_id = ?');
    $stmt->execute([$hid]);
    $katCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM buchungen WHERE haushalt_id = ?');
    $stmt->execute([$hid]);
    $buchCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    $stmt = $db->prepare('SELECT COUNT(*) as cnt FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id WHERE b.haushalt_id = ?');
    $stmt->execute([$hid]);
    $zahlCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];

    $stmt = $db->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN b.betrag > 0 THEN z.betrag ELSE 0 END), 0) as einnahmen,
            COALESCE(SUM(CASE WHEN b.betrag < 0 THEN ABS(z.betrag) ELSE 0 END), 0) as ausgaben
        FROM zahlungen z LEFT JOIN buchungen b ON z.buchung_id = b.id
        WHERE b.haushalt_id = ? AND strftime('%Y', z.zahlungsdatum) = ?
    ");
    $stmt->execute([$hid, (string)$jahr]);
    $bilanz = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $db->prepare('SELECT betrag FROM kontostand WHERE haushalt_id = ? ORDER BY datum DESC LIMIT 1');
    $stmt->execute([$hid]);
    $ks = $stmt->fetch(PDO::FETCH_ASSOC);

    // Recht + Besitzer ermitteln
    $stmt = $db->prepare('SELECT recht FROM user_haushalte WHERE user_id = ? AND haushalt_id = ?');
    $stmt->execute([$_SESSION['user_id'], $hid]);
    $rechtRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $recht = $rechtRow ? $rechtRow['recht'] : (isAdmin() ? 'besitzer' : 'lesen');

    $stmt = $db->prepare('SELECT u.benutzername FROM user_haushalte uh JOIN users u ON uh.user_id = u.id WHERE uh.haushalt_id = ? AND uh.recht = ? LIMIT 1');
    $stmt->execute([$hid, 'besitzer']);
    $besitzerRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $besitzer = $besitzerRow ? $besitzerRow['benutzername'] : '-';

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
        'kontostand' => $ks ? $ks['betrag'] : null,
        'recht' => $recht,
        'besitzer' => $besitzer
    ];
}

echo json_encode(['jahr' => $jahr, 'haushalte' => $ergebnis]);
