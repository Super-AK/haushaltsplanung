<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
requireLogin();

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Nur Admins duerfen User verwalten']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $db->query('SELECT id, benutzername, rolle, email, aktiv, created_at FROM users ORDER BY benutzername');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['benutzername']) || empty($data['passwort'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Benutzername und Passwort erforderlich']);
            exit;
        }
        $hash = password_hash($data['passwort'], PASSWORD_BCRYPT);
        $rolle = $data['rolle'] ?? 'benutzer';
        $stmt = $db->prepare('INSERT INTO users (benutzername, passwort_hash, rolle, email) VALUES (?, ?, ?, ?)');
        try {
            $stmt->execute([$data['benutzername'], $hash, $rolle, $data['email'] ?? null]);
            echo json_encode(['id' => $db->lastInsertId(), 'message' => 'User erstellt']);
        } catch (PDOException $e) {
            http_response_code(400);
            echo json_encode(['error' => 'Benutzername bereits vorhanden']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        
        $fields = [];
        $params = [];
        if (isset($data['rolle'])) { $fields[] = 'rolle = ?'; $params[] = $data['rolle']; }
        if (isset($data['email'])) { $fields[] = 'email = ?'; $params[] = $data['email']; }
        if (isset($data['aktiv'])) { $fields[] = 'aktiv = ?'; $params[] = (int)$data['aktiv']; }
        if (!empty($data['passwort'])) {
            $fields[] = 'passwort_hash = ?';
            $params[] = password_hash($data['passwort'], PASSWORD_BCRYPT);
        }
        
        if (empty($fields)) { http_response_code(400); echo json_encode(['error' => 'Keine Felder']); exit; }
        $params[] = $id;
        $stmt = $db->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?');
        $stmt->execute($params);
        echo json_encode(['message' => 'User aktualisiert']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        if ($id == $_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Eigenen Account kann man nicht loeschen']);
            exit;
        }
        $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['message' => 'User geloescht']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
