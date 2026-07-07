<?php
$pageTitle = 'Dashboard - Haushaltsplanung';
require_once __DIR__ . '/includes/header.php';
?>

<div id="loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Laden...</span>
    </div>
</div>

<div id="dashboard" style="display: none;">
    
    <!-- Obere Reihe: Karten + Kontostand -->
    <div class="row mb-4">
        <!-- Kennzahlen-Karten -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card einnahme shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs text-uppercase text-muted mb-1">Einnahmen (Jahr)</div>
                            <div class="h5 mb-0 font-weight-bold text-success" id="jahresEinnahmen">0,00 €</div>
                        </div>
                        <div class="stat-icon text-success"><i class="bi bi-arrow-up-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card ausgabe shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs text-uppercase text-muted mb-1">Ausgaben (Jahr)</div>
                            <div class="h5 mb-0 font-weight-bold text-danger" id="jahresAusgaben">0,00 €</div>
                        </div>
                        <div class="stat-icon text-danger"><i class="bi bi-arrow-down-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bilanz shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs text-uppercase text-muted mb-1">Bilanz (Jahr)</div>
                            <div class="h5 mb-0 font-weight-bold" id="jahresBilanz">0,00 €</div>
                        </div>
                        <div class="stat-icon text-primary"><i class="bi bi-calculator"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card ersparnis shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs text-uppercase text-muted mb-1">Ersparnis (Monat)</div>
                            <div class="h5 mb-0 font-weight-bold" id="monatsErsparnis">0,00 €</div>
                        </div>
                        <div class="stat-icon text-info"><i class="bi bi-piggy-bank"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kontostand & Jahresend-Prognose -->
    <div class="row mb-4">
        <!-- Kontostand eingeben -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-bank me-2"></i>Kontostand erfassen
                    </h6>
                </div>
                <div class="card-body">
                    <div id="kontostandInfo" style="display:none;" class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Aktueller Stand:</strong> <span id="aktKontostand">0,00 EUR</span>
                                <span class="text-muted">(Stand: <span id="aktKontostandDatum">-</span>)</span>
                                <span id="aktKontostandBemerkung" class="text-muted ms-2"></span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="bearbeiteKontostand()" title="Bearbeiten"><i class="bi bi-pencil"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="loescheKontostand()" title="Loeschen"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                    <form id="kontostandForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Betrag (€) *</label>
                                <input type="number" step="0.01" class="form-control" id="kontostandBetrag" placeholder="z.B. 5000" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Datum *</label>
                                <input type="date" class="form-control" id="kontostandDatum" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Bemerkung</label>
                                <input type="text" class="form-control" id="kontostandBemerkung" placeholder="z.B. Kontoauszug">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-danger me-2" id="kontostandLoeschen" style="display:none" onclick="loescheKontostand()"><i class="bi bi-trash me-1"></i>Loeschen</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Kontostand speichern
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Jahresend-Prognose -->
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up-arrow me-2"></i>Prognose Jahresende
                    </h6>
                </div>
                <div class="card-body text-center py-4">
                    <div class="text-muted mb-2">Erwarteter Kontostand am 31.12.</div>
                    <div class="display-5 fw-bold" id="jahresEndPrognose" style="font-size: 2.2rem;">0,00 €</div>
                    <div class="text-muted mt-2">
                        <small>Basiert auf aktuellem Kontostand + wiederkehrenden Buchungen</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Diagramme -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-graph-up me-2"></i>Monatsverlauf & Kontostand-Prognose
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="monatsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-pie-chart me-2"></i>Ausgaben nach Kategorie
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="kategorienChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabellen -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-calendar-event me-2"></i>Anstehende Kosten
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabelleAnstehend">
                            <thead><tr><th>Datum</th><th>Kategorie</th><th>Betrag</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-clock-history me-2"></i>Letzte Transaktionen
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tabelleTransaktionen">
                            <thead><tr><th>Datum</th><th>Beschreibung</th><th>Betrag</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="<?= BASE_URL ?>/assets/js/dashboard.js"></script>
