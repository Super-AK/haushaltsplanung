<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $db->query('SELECT * FROM haushalte ORDER BY name');
        $haushalte = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $aktiveId = getAktivenHaushalt();
        foreach ($haushalte as &$h) { $h['aktiv'] = ($h['id'] == $aktiveId); }
        echo json_encode($haushalte);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['name'])) { http_response_code(400); echo json_encode(['error' => 'Name erforderlich']); exit; }
        $istDemo = $data['ist_demo'] ?? 0;
        $stmt = $db->prepare('INSERT INTO haushalte (name, ist_demo) VALUES (?, ?)');
        $stmt->execute([$data['name'], $istDemo]);
        $newId = $db->lastInsertId();
        if (!empty($data['mit_demo_daten'])) {
            require_once __DIR__ . '/../setup/demo_data.php';
            ladeDemoDaten($db, $newId);
        }
        setAktivenHaushalt($newId);
        echo json_encode(['id' => $newId, 'message' => 'Haushalt erstellt']);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!empty($data['haushalt_id'])) {
            setAktivenHaushalt((int)$data['haushalt_id']);
            echo json_encode(['message' => 'Haushalt gewechselt', 'haushalt_id' => (int)$data['haushalt_id']]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        $countStmt = $db->query('SELECT COUNT(*) as cnt FROM haushalte');
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        if ($count <= 1) { http_response_code(400); echo json_encode(['error' => 'Der letzte Haushalt kann nicht geloescht werden']); exit; }
        $stmt = $db->prepare('DELETE FROM haushalte WHERE id = ?');
        $stmt->execute([$id]);
        if (getAktivenHaushalt() == $id) {
            $next = $db->query('SELECT id FROM haushalte ORDER BY id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            setAktivenHaushalt($next['id']);
        }
        echo json_encode(['message' => 'Haushalt geloescht']);
        break;

    default:
        http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']);
}
