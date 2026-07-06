<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $filter = '';
        $params = [];
        if (isset($_GET['buchung_id'])) {
            $filter = 'WHERE z.buchung_id = ?';
            $params[] = $_GET['buchung_id'];
        }
        $sql = "SELECT z.*, b.beschreibung as buchung_beschreibung, b.betrag as buchung_betrag,
                       k.name as kategorie_name, k.typ
                FROM zahlungen z
                LEFT JOIN buchungen b ON z.buchung_id = b.id
                LEFT JOIN kategorien k ON b.kategorie_id = k.id
                WHERE b.haushalt_id = ? " . ($filter ? str_replace('WHERE', 'AND', $filter) : '') . "
                ORDER BY z.zahlungsdatum DESC";
        array_unshift($params, $haushaltId);
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
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        $stmt = $db->prepare('DELETE FROM zahlungen WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['message' => 'Zahlung gelöscht']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
