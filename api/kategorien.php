<?php
/**
 * Kategorien API
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Alle Kategorien
        $filter = '';
        $params = [];
        
        if (isset($_GET['typ'])) {
            $filter = 'WHERE typ = ?';
            $params[] = $_GET['typ'];
        }
        if (isset($_GET['aktiv'])) {
            $filter .= ($filter ? ' AND ' : 'WHERE ') . 'aktiv = ?';
            $params[] = (int)$_GET['aktiv'];
        }
        
        $stmt = $db->prepare("SELECT * FROM kategorien $filter ORDER BY typ, name");
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name']) || empty($data['typ']) || empty($data['art'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name, Typ und Art sind erforderlich']);
            exit;
        }
        
        $stmt = $db->prepare('INSERT INTO kategorien (name, typ, art, farbe) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $data['name'],
            $data['typ'],
            $data['art'],
            $data['farbe'] ?? '#4e73df'
        ]);
        
        echo json_encode(['id' => $db->lastInsertId(), 'message' => 'Kategorie erstellt']);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID erforderlich']);
            exit;
        }
        
        $fields = [];
        $params = [];
        
        foreach (['name', 'typ', 'art', 'farbe', 'aktiv'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(['error' => 'Keine Felder zum Aktualisieren']);
            exit;
        }
        
        $params[] = $id;
        $stmt = $db->prepare('UPDATE kategorien SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
        
        echo json_encode(['message' => 'Kategorie aktualisiert']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID erforderlich']);
            exit;
        }
        
        $stmt = $db->prepare('DELETE FROM kategorien WHERE id = ?');
        $stmt->execute([$id]);
        
        echo json_encode(['message' => 'Kategorie gelöscht']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
