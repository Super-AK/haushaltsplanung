<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $db->prepare('SELECT * FROM kontostand WHERE haushalt_id = ? ORDER BY datum DESC');
        $stmt->execute([$haushaltId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['betrag']) || empty($data['datum'])) {
            http_response_code(400);
            echo json_encode(['error' => 'betrag und datum sind erforderlich']);
            exit;
        }
        $stmt = $db->prepare('INSERT INTO kontostand (haushalt_id, betrag, datum, bemerkung) VALUES (?, ?, ?, ?)');
        $stmt->execute([$haushaltId, $data['betrag'], $data['datum'], $data['bemerkung'] ?? null]);
        echo json_encode(['id' => $db->lastInsertId(), 'message' => 'Kontostand gespeichert']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        $stmt = $db->prepare('DELETE FROM kontostand WHERE id = ? AND haushalt_id = ?');
        $stmt->execute([$id, $haushaltId]);
        echo json_encode(['message' => 'Kontostand gelöscht']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
