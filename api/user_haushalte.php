<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';
requireLogin();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Alle Zuordnungen (Admin) oder nur eigene
        if (isAdmin()) {
            $stmt = $db->query('SELECT uh.*, u.benutzername, h.name as haushalt_name FROM user_haushalte uh JOIN users u ON uh.user_id = u.id JOIN haushalte h ON uh.haushalt_id = h.id ORDER BY u.benutzername, h.name');
        } else {
            $stmt = $db->prepare('SELECT uh.*, u.benutzername, h.name as haushalt_name FROM user_haushalte uh JOIN users u ON uh.user_id = u.id JOIN haushalte h ON uh.haushalt_id = h.id WHERE uh.user_id = ? ORDER BY h.name');
            $stmt->execute([$_SESSION['user_id']]);
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['user_id']) || empty($data['haushalt_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'user_id und haushalt_id erforderlich']);
            exit;
        }
        // Nur Admin oder Besitzer darf zuordnen
        if (!isAdmin()) {
            $stmt = $db->prepare('SELECT recht FROM user_haushalte WHERE user_id = ? AND haushalt_id = ?');
            $stmt->execute([$_SESSION['user_id'], $data['haushalt_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || $row['recht'] !== 'besitzer') {
                http_response_code(403);
                echo json_encode(['error' => 'Kein Recht - nur Besitzer oder Admin']);
                exit;
            }
        }
        $recht = $data['recht'] ?? 'lesen';
        $stmt = $db->prepare('INSERT OR REPLACE INTO user_haushalte (user_id, haushalt_id, recht) VALUES (?, ?, ?)');
        $stmt->execute([$data['user_id'], $data['haushalt_id'], $recht]);
        echo json_encode(['message' => 'Zuordnung gespeichert']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        if (!isAdmin()) {
            $stmt = $db->prepare('SELECT haushalt_id FROM user_haushalte WHERE id = ?');
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || !istBerechtigt($row['haushalt_id'], 'besitzer')) {
                http_response_code(403);
                echo json_encode(['error' => 'Kein Recht']);
                exit;
            }
        }
        $stmt = $db->prepare('DELETE FROM user_haushalte WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['message' => 'Zuordnung entfernt']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
