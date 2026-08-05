<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $where = ['b.haushalt_id = ?'];
        $params = [$haushaltId];
        if (isset($_GET['buchung_id']) && $_GET['buchung_id'] !== '') { $where[] = 'z.buchung_id = ?'; $params[] = $_GET['buchung_id']; }
        if (isset($_GET['kategorie_id']) && $_GET['kategorie_id'] !== '') { $where[] = 'b.kategorie_id = ?'; $params[] = $_GET['kategorie_id']; }
        if (isset($_GET['typ']) && $_GET['typ'] !== '') { $where[] = 'k.typ = ?'; $params[] = $_GET['typ']; }
        if (isset($_GET['von']) && $_GET['von'] !== '') { $where[] = 'z.zahlungsdatum >= ?'; $params[] = $_GET['von']; }
        if (isset($_GET['bis']) && $_GET['bis'] !== '') { $where[] = 'z.zahlungsdatum <= ?'; $params[] = $_GET['bis']; }
        $sql = "SELECT z.*, b.beschreibung as buchung_beschreibung, b.betrag as buchung_betrag,
                       k.name as kategorie_name, k.typ
                FROM zahlungen z
                LEFT JOIN buchungen b ON z.buchung_id = b.id
                LEFT JOIN kategorien k ON b.kategorie_id = k.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY z.zahlungsdatum DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['buchung_id']) || !isset($data['betrag']) || empty($data['zahlungsdatum'])) {
            http_response_code(400);
            echo json_encode(['error' => 'buchung_id, betrag und zahlungsdatum sind erforderlich']);
            exit;
        }
        $stmt = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['buchung_id'], $data['betrag'], $data['zahlungsdatum'], $data['bemerkung'] ?? null]);
        echo json_encode(['id' => $db->lastInsertId(), 'message' => 'Zahlung erfasst']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        $ids = $_GET['ids'] ?? null;
        if ($ids) {
            $idList = array_map('intval', explode(',', $ids));
            $platzhalter = implode(',', array_fill(0, count($idList), '?'));
            $stmt = $db->prepare("DELETE FROM zahlungen WHERE id IN ($platzhalter)");
            $stmt->execute($idList);
            echo json_encode(['message' => count($idList) . ' Zahlungen geloescht']);
        } elseif ($id) {
            $stmt = $db->prepare('DELETE FROM zahlungen WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['message' => 'Zahlung gelöscht']);
        } else {
            http_response_code(400); echo json_encode(['error' => 'ID(s) erforderlich']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
