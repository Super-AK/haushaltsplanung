<?php
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $appDirs = ['pages', 'api', 'setup', 'assets'];
    $parts = explode('/', trim($scriptName, '/'));
    $base = '';
    foreach ($parts as $i => $part) {
        if (in_array($part, $appDirs)) {
            $base = $i > 0 ? '/' . implode('/', array_slice($parts, 0, $i)) : '';
            break;
        }
    }
    if ($base === '' && !empty($parts) && !in_array($parts[0], $appDirs) && count($parts) > 1) {
        $base = '/' . implode('/', array_slice($parts, 0, -1));
    }
    define('BASE_URL', $base);
}

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$dbDir = __DIR__ . '/../sqlite';
$dbPath = $dbDir . '/haushaltsplanung.db';

if (!is_dir($dbDir)) { mkdir($dbDir, 0775, true); }

// Erstinitialisierung: Tabellen anlegen wenn DB-Datei nicht existiert
if (!file_exists($dbPath)) {
    require_once __DIR__ . '/../setup/init_db.php';
}

// Verbindung herstellen
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB-Fehler: ' . $e->getMessage()]);
    exit;
}

// Migrationen ausfuehren (prueft ob Tabellen/Spalten fehlen)
require_once __DIR__ . '/../setup/migrate.php';
fuehreMigrationenAus($db);

// Session-Wiederherstellung aus Cookie-Fallback (Remote-Zuverlaessigkeit)
// Wenn die PHP-Session ueber AJAX verloren geht, wird der Login aus dem
// user_id-Cookie wiederhergestellt (gesetzt bei login()).
if (empty($_SESSION['user_id']) && !empty($_COOKIE['user_id'])) {
    $stmt = $db->prepare('SELECT id, benutzername, rolle FROM users WHERE id = ? AND aktiv = 1');
    $stmt->execute([(int)$_COOKIE['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_rolle'] = $user['rolle'];
        $_SESSION['user_name'] = $user['benutzername'];
    } else {
        setcookie('user_id', '', time() - 3600, '/');
    }
}

// === AUTH FUNKTIONEN ===

function isLoggedIn() {
    return !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false) {
            http_response_code(401);
            echo json_encode(['error' => 'Login erforderlich', 'redirect' => BASE_URL . '/pages/login.php']);
        } else {
            header('Location: ' . BASE_URL . '/pages/login.php');
        }
        exit;
    }
}

function isAdmin() {
    return ($_SESSION['user_rolle'] ?? '') === 'admin';
}

function getAktuellenUser() {
    global $db;
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $db->prepare('SELECT id, benutzername, rolle, email FROM users WHERE id = ? AND aktiv = 1');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function login($benutzername, $passwort) {
    global $db;
    $stmt = $db->prepare('SELECT * FROM users WHERE benutzername = ? AND aktiv = 1');
    $stmt->execute([$benutzername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($passwort, $user['passwort_hash'])) {
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_rolle'] = $user['rolle'];
        $_SESSION['user_name'] = $user['benutzername'];
        setcookie('user_id', $user['id'], time() + 86400 * 30, '/');
        return true;
    }
    return false;
}

function logout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('haushalt_id', '', time() - 3600, '/');
}

function istBerechtigt($haushaltId, $recht = 'lesen') {
    global $db;
    if (isAdmin()) return true;
    if (empty($_SESSION['user_id'])) return false;
    $stmt = $db->prepare('SELECT recht FROM user_haushalte WHERE user_id = ? AND haushalt_id = ?');
    $stmt->execute([$_SESSION['user_id'], $haushaltId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return false;
    $rechteStufen = ['lesen' => 0, 'schreiben' => 1, 'besitzer' => 2];
    return ($rechteStufen[$row['recht']] ?? 0) >= ($rechteStufen[$recht] ?? 0);
}

function getErlaubteHaushalte($recht = 'lesen') {
    global $db;
    if (isAdmin()) {
        $stmt = $db->query('SELECT id FROM haushalte ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    if (empty($_SESSION['user_id'])) return [];
    $stmt = $db->prepare('SELECT haushalt_id FROM user_haushalte WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $alle = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $erlaubt = [];
    foreach ($alle as $hid) {
        if (istBerechtigt($hid, $recht)) $erlaubt[] = $hid;
    }
    return $erlaubt;
}

function getAktivenHaushalt() {
    global $db;
    if (!empty($_SESSION['haushalt_id']) && istBerechtigt($_SESSION['haushalt_id'])) {
        return (int)$_SESSION['haushalt_id'];
    }
    if (!empty($_COOKIE['haushalt_id']) && istBerechtigt($_COOKIE['haushalt_id'])) {
        $_SESSION['haushalt_id'] = (int)$_COOKIE['haushalt_id'];
        return $_SESSION['haushalt_id'];
    }
    $erlaubt = getErlaubteHaushalte();
    if (!empty($erlaubt)) {
        $_SESSION['haushalt_id'] = (int)$erlaubt[0];
        setcookie('haushalt_id', $erlaubt[0], time() + 86400 * 30, '/');
        return $_SESSION['haushalt_id'];
    }
    return null;
}

function setAktivenHaushalt($id) {
    $_SESSION['haushalt_id'] = (int)$id;
    setcookie('haushalt_id', $id, time() + 86400 * 30, '/');
}

// === AUTO-ZAHLUNGEN ===

/**
 * Erzeugt fuer eine aktive Buchung automatisch alle faelligen Zahlungen
 * (Intervall-Termine) bis heute. Nur das laufende Kalenderjahr wird befuellt,
 * da sich Auswertungen darauf beziehen. Bereits vorhandene Zahlungen
 * (manuell oder automatisch) werden nicht dupliziert.
 */
function erzeugeAutomatischeZahlungen($db, $buchungId) {
    $stmt = $db->prepare('SELECT * FROM buchungen WHERE id = ?');
    $stmt->execute([$buchungId]);
    $b = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$b || (int)$b['aktiv'] !== 1) return;

    $heute = new DateTime(date('Y-m-d'));
    $heute->setTime(0, 0, 0);
    $aktuellesJahr = date('Y');

    $intervalle = [
        'woechentlich' => '+7 days',
        'monatlich' => '+1 month',
        'vierteljaehrlich' => '+3 months',
        'halbjaehrlich' => '+6 months',
        'jaehrlich' => '+1 year'
    ];

    $parseDatum = function ($wert) {
        if (empty($wert)) return null;
        try {
            $dt = new DateTime($wert);
            $dt->setTime(0, 0, 0);
            return $dt;
        } catch (Throwable $e) {
            return null;
        }
    };

    $start = $parseDatum($b['start_datum']);
    if (!$start) return;

    if ($b['intervall'] === 'einmalig') {
        if ($start > $heute || $start->format('Y') !== $aktuellesJahr) return;
        $check = $db->prepare('SELECT COUNT(*) FROM zahlungen WHERE buchung_id = ? AND zahlungsdatum = ?');
        $check->execute([$buchungId, $start->format('Y-m-d')]);
        if ($check->fetchColumn() == 0) {
            $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung, automatisch) VALUES (?, ?, ?, ?, 1)')
                ->execute([$buchungId, $b['betrag'], $start->format('Y-m-d'), 'automatisch']);
        }
        return;
    }
    if (!isset($intervalle[$b['intervall']])) return;

    if ($start > $heute) return;

    $end = $parseDatum($b['end_datum'] ?? null);

    $check = $db->prepare('SELECT COUNT(*) FROM zahlungen WHERE buchung_id = ? AND zahlungsdatum = ?');
    $insert = $db->prepare('INSERT INTO zahlungen (buchung_id, betrag, zahlungsdatum, bemerkung, automatisch) VALUES (?, ?, ?, ?, 1)');
    $termin = clone $start;
    $max = 5000;
    while ($termin <= $heute && $max-- > 0) {
        $datumStr = $termin->format('Y-m-d');
        if ($end && $termin > $end) break;
        if ($termin->format('Y') === $aktuellesJahr) {
            $check->execute([$buchungId, $datumStr]);
            if ($check->fetchColumn() == 0) {
                $insert->execute([$buchungId, $b['betrag'], $datumStr, 'automatisch']);
            }
        }
        $termin->modify($intervalle[$b['intervall']]);
    }
}
