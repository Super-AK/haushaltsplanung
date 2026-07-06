<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Login
        $data = json_decode(file_get_contents('php://input'), true);
        $benutzername = $data['benutzername'] ?? '';
        $passwort = $data['passwort'] ?? '';
        
        if (empty($benutzername) || empty($passwort)) {
            http_response_code(400);
            echo json_encode(['error' => 'Benutzername und Passwort erforderlich']);
            exit;
        }
        
        if (login($benutzername, $passwort)) {
            $user = getAktuellenUser();
            echo json_encode([
                'message' => 'Login erfolgreich',
                'user' => $user
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Ungueltige Zugangsdaten']);
        }
        break;
    
    case 'DELETE':
        // Logout
        logout();
        echo json_encode(['message' => 'Logout erfolgreich']);
        break;
    
    case 'GET':
        // Status pruefen
        if (isLoggedIn()) {
            $user = getAktuellenUser();
            echo json_encode(['eingeloggt' => true, 'user' => $user]);
        } else {
            echo json_encode(['eingeloggt' => false]);
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Methode nicht erlaubt']);
}
