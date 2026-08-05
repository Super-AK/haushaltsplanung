<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../includes/db.php";
requireLogin();
$haushaltId = getAktivenHaushalt();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $filter = 'WHERE b.haushalt_id = ?';
        $params = [$haushaltId];
        if (isset($_GET['kategorie_id'])) { $filter .= ' AND b.kategorie_id = ?'; $params[] = $_GET['kategorie_id']; }
        if (isset($_GET['aktiv'])) { $filter .= ' AND b.aktiv = ?'; $params[] = (int)$_GET['aktiv']; }
        $sql = "SELECT b.*, k.name as kategorie_name, k.typ, k.art, k.farbe FROM buchungen b LEFT JOIN kategorien k ON b.kategorie_id = k.id $filter ORDER BY b.start_datum DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['kategorie_id']) || !isset($data['betrag']) || empty($data['intervall']) || empty($data['start_datum'])) {
            http_response_code(400); echo json_encode(['error' => 'kategorie_id, betrag, intervall und start_datum sind erforderlich']); exit;
        }
        $stmt = $db->prepare('SELECT typ FROM kategorien WHERE id = ? AND haushalt_id = ?');
        $stmt->execute([$data['kategorie_id'], $haushaltId]);
        $kTyp = $stmt->fetchColumn();
        if ($kTyp === false) { http_response_code(400); echo json_encode(['error' => 'Kategorie nicht gefunden']); exit; }
        $betrag = (float)$data['betrag'];
        if ($kTyp === 'ausgabe' && $betrag > 0) $betrag = -$betrag;
        if ($kTyp === 'einnahme' && $betrag < 0) $betrag = -$betrag;
        $stmt = $db->prepare('INSERT INTO buchungen (haushalt_id, kategorie_id, betrag, beschreibung, intervall, start_datum, end_datum) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$haushaltId, $data['kategorie_id'], $betrag, $data['beschreibung'] ?? null, $data['intervall'], $data['start_datum'], $data['end_datum'] ?? null]);
        $neueId = (int)$db->lastInsertId();
        erzeugeAutomatischeZahlungen($db, $neueId);
        echo json_encode(['id' => $neueId, 'message' => 'Buchung erstellt']);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID erforderlich']); exit; }
        if (isset($data['betrag'])) {
            $kid = $data['kategorie_id'] ?? null;
            if (!$kid) {
                $stmt = $db->prepare('SELECT kategorie_id FROM buchungen WHERE id = ?');
                $stmt->execute([$id]);
                $kid = $stmt->fetchColumn();
            }
            if ($kid) {
                $stmt = $db->prepare('SELECT typ FROM kategorien WHERE id = ? AND haushalt_id = ?');
                $stmt->execute([$kid, $haushaltId]);
                $kTyp = $stmt->fetchColumn();
                $betrag = (float)$data['betrag'];
                if ($kTyp === 'ausgabe' && $betrag > 0) $data['betrag'] = -$betrag;
                if ($kTyp === 'einnahme' && $betrag < 0) $data['betrag'] = -$betrag;
            }
        }
        $fields = []; $params = [];
        foreach (['kategorie_id', 'betrag', 'beschreibung', 'intervall', 'start_datum', 'end_datum', 'aktiv'] as $f) {
            if (isset($data[$f])) { $fields[] = "$f = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) { http_response_code(400); echo json_encode(['error' => 'Keine Felder']); exit; }
        $params[] = $id; $params[] = $haushaltId;
        $stmt = $db->prepare('UPDATE buchungen SET ' . implode(', ', $fields) . ' WHERE id = ? AND haushalt_id = ?');
        $stmt->execute($params);
        $db->prepare('DELETE FROM zahlungen WHERE buchung_id = ? AND automatisch = 1')->execute([$id]);
        erzeugeAutomatischeZahlungen($db, (int)$id);
        echo json_encode(['message' => 'Buchung aktualisiert']);
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        $ids = $_GET['ids'] ?? null;
        if ($ids) {
            $idList = array_map('intval', explode(',', $ids));
            $platzhalter = implode(',', array_fill(0, count($idList), '?'));
            $params = array_merge($idList, [$haushaltId]);
            $stmt = $db->prepare("DELETE FROM buchungen WHERE id IN ($platzhalter) AND haushalt_id = ?");
            $stmt->execute($params);
            echo json_encode(['message' => count($idList) . ' Buchungen geloescht']);
        } elseif ($id) {
            $stmt = $db->prepare('DELETE FROM buchungen WHERE id = ? AND haushalt_id = ?');
            $stmt->execute([$id, $haushaltId]);
            echo json_encode(['message' => 'Buchung geloescht']);
        } else {
            http_response_code(400); echo json_encode(['error' => 'ID(s) erforderlich']);
        }
        break;

    default:
        http_response_code(405); echo json_encode(['error' => 'Methode nicht erlaubt']);
}
