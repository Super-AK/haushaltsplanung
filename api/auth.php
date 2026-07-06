<?php
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Logout via GET (fuer Redirect aus Navbar)
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
    header('Location: ' . BASE_URL . '/pages/login.php');
    exit;
}

header('Content-Type: application/json');

switch ($method) {
    case 'POST':
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
            echo json_encode(['message' => 'Login erfolgreich', 'user' => $user]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Ungueltige Zugangsdaten']);
        }
        break;
    
    case 'DELETE':
        logout();
        echo json_encode(['message' => 'Logout erfolgreich']);
        break;
    
    case 'GET':
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
