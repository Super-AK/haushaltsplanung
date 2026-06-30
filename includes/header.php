<?php
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
    $dir = dirname($scriptName);
    $appDirs = ['pages', 'api', 'setup', 'assets'];
    if ($dir === '/' || $dir === '.' || $dir === '' || in_array(basename($dir), $appDirs)) {
        define('BASE_URL', '');
    } else {
        define('BASE_URL', $dir);
    }
}

require_once __DIR__ . '/db.php';
$aktiverHaushalt = null;
$alleHaushalte = [];
if ($db) {
    $stmt = $db->query('SELECT * FROM haushalte ORDER BY name');
    $alleHaushalte = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $aktiverHaushalt = getAktivenHaushalt();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Haushaltsplanung' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= BASE_URL ?>/">
                <i class="bi bi-wallet2 me-2"></i>Haushaltsplanung
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'kategorien.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/kategorien.php">
                            <i class="bi bi-tags me-1"></i>Kategorien
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'buchungen.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/buchungen.php">
                            <i class="bi bi-journal-text me-1"></i>Buchungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'zahlungen.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/zahlungen.php">
                            <i class="bi bi-cash-stack me-1"></i>Zahlungen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'haushalte.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/haushalte.php">
                            <i class="bi bi-house-door me-1"></i>Haushalte
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'hilfe.php' ? 'active' : '' ?>" href="<?= BASE_URL ?>/pages/hilfe.php">
                            <i class="bi bi-question-circle me-1"></i>Hilfe
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-house me-1"></i>
                            <?= htmlspecialchars($alleHaushalte[array_search($aktiverHaushalt, array_column($alleHaushalte, 'id'))]['name'] ?? 'Haushalt') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php foreach ($alleHaushalte as $h): ?>
                            <li>
                                <a class="dropdown-item <?= $h['id'] == $aktiverHaushalt ? 'active' : '' ?>" href="#" onclick="wechsleHaushalt(<?= $h['id'] ?>)">
                                    <i class="bi bi-<?= $h['id'] == $aktiverHaushalt ? 'check-circle-fill' : 'circle' ?> me-1"></i>
                                    <?= htmlspecialchars($h['name']) ?>
                                    <?php if ($h['ist_demo']): ?>
                                        <span class="badge bg-warning text-dark ms-1">Demo</span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="oeffneNeuenHaushalt()"><i class="bi bi-plus-circle me-1"></i>Neuer Haushalt</a></li>
                            <?php if (count($alleHaushalte) > 1): ?>
                            <li><a class="dropdown-item" href="#" onclick="oeffneDatenKopieren()"><i class="bi bi-clipboard me-1"></i>Daten kopieren</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Modal: Neuer Haushalt -->
    <div class="modal fade" id="haushaltModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Neuer Haushalt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" class="form-control" id="haushaltName" placeholder="z.B. Familie Mueller" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="haushaltDemo" checked>
                        <label class="form-check-label">Beispieldaten laden</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" onclick="speichereNeuenHaushalt()">Erstellen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Haushalt loeschen -->
    <div class="modal fade" id="haushaltLoeschenModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Haushalt loeschen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Moechten Sie den Haushalt <strong id="loeschName"></strong> wirklich loeschen?</p>
                    <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Alle Daten werden geloescht!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-danger" id="haushaltLoeschenBtn">Endgueltig loeschen</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Daten kopieren -->
    <div class="modal fade" id="datenKopierenModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daten aus anderem Haushalt kopieren</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Kopiere Kategorien, Buchungen und Zahlungen in den aktuellen Haushalt:</p>
                    <div class="mb-3">
                        <label class="form-label">Quell-Haushalt *</label>
                        <select class="form-select" id="kopierQuelle">
                            <option value="">Bitte waehlen...</option>
                            <?php foreach ($alleHaushalte as $h): ?>
                                <?php if ($h['id'] != $aktiverHaushalt): ?>
                                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Es werden Kategorien, Buchungen und vorhandene Zahlungen kopiert.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="button" class="btn btn-primary" onclick="starteKopieren()">Kopieren</button>
                </div>
            </div>
        </div>
    </div>

    <main class="container-fluid py-4">
