<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $filter = 'WHERE k.haushalt_id = ?';
        $params = [$haushaltId];
        if (isset($_GET['typ'])) { $filter .= ' AND k.typ = ?'; $params[] = $_GET['typ']; }
        if (isset($_GET['aktiv'])) { $filter .= ' AND k.aktiv = ?'; $params[] = (int)$_GET['aktiv']; }
        $stmt = $db->prepare("SELECT k.* FROM kategorien k $filter ORDER BY k.typ, k.name");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name']) || empty($data['typ']) || empty($data['art'])) {
            http_response_code(400); echo json_encode(['error' => 'Name, Typ und Art sind erforderlich']); exit;
        }
        $stmt = $db->prepare('INSERT INTO kategorien (haushalt_id, name, typ, art, farbe) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$haushaltId, $data['name'], $data['typ'], $data['art'], $data['farbe'] ?? '#4e73df']);
        echo json_encode(['id' => $db->lastInsertId(), 'message' => 'Kategorie erstellt']);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        $fields = []; $params = [];
        foreach (['name', 'typ', 'art', 'farbe', 'aktiv'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) { http_response_code(400); echo json_encode(['error' => 'Keine Felder']); exit; }
        $params[] = $id; $params[] = $haushaltId;
        $stmt = $db->prepare('UPDATE kategorien SET ' . implode(', ', $fields) . ' WHERE id = ? AND haushalt_id = ?');
        $stmt->execute($params);
        echo json_encode(['message' => 'Kategorie aktualisiert']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        $ids = $_GET['ids'] ?? null;
        if ($ids) {
            // Massen-Loeschung
            $idList = array_map('intval', explode(',', $ids));
            $platzhalter = implode(',', array_fill(0, count($idList), '?'));
            $params = array_merge($idList, [$haushaltId]);
            $stmt = $db->prepare("DELETE FROM kategorien WHERE id IN ($platzhalter) AND haushalt_id = ?");
            $stmt->execute($params);
            echo json_encode(['message' => count($idList) . ' Kategorien geloescht']);
        } elseif ($id) {
            $stmt = $db->prepare('DELETE FROM kategorien WHERE id = ? AND haushalt_id = ?');
            $stmt->execute([$id, $haushaltId]);
            echo json_encode(['message' => 'Kategorie geloescht']);
        } else {
            http_response_code(400); echo json_encode(['error' => 'ID(s) erforderlich']);
        }
        break;

    default:
        http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']);
}
