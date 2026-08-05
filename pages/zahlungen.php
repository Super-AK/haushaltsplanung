<?php
$pageTitle = 'Zahlungen - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Zahlungen erfassen</h4>
    <div>
        <button class="btn btn-danger me-2" id="btnZahlungMassLoeschen" style="display:none" onclick="loescheAusgewaehlteZahlungen()">
            <i class="bi bi-trash me-1"></i>Ausgewaehlte loeschen (<span id="anzahlZahlungAusgewaehlt">0</span>)
        </button>
        <button class="btn btn-success me-2" id="btnZahlungMassExport" style="display:none" onclick="exportiereAusgewaehlteCSV()">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Export CSV (<span id="anzahlZahlungExport">0</span>)
        </button>
        <button class="btn btn-primary" onclick="oeffneModal()">
            <i class="bi bi-plus-circle me-1"></i>Neue Zahlung
        </button>
    </div>
</div>

<!-- Zusammenfassung -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card einnahme shadow-sm">
            <div class="card-body text-center">
                <div class="text-xs text-uppercase text-muted mb-1">Einnahmen (heute)</div>
                <div class="h4 mb-0 font-weight-bold text-success" id="heuteEinnahmen">0,00 €</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card ausgabe shadow-sm">
            <div class="card-body text-center">
                <div class="text-xs text-uppercase text-muted mb-1">Ausgaben (heute)</div>
                <div class="h4 mb-0 font-weight-bold text-danger" id="heuteAusgaben">0,00 €</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bilanz shadow-sm">
            <div class="card-body text-center">
                <div class="text-xs text-uppercase text-muted mb-1">Bilanz (heute)</div>
                <div class="h4 mb-0 font-weight-bold" id="heuteBilanz">0,00 €</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Kategorie</label><select class="form-select" id="filterZahlungKategorie"><option value="">Alle</option></select></div>
            <div class="col-md-3"><label class="form-label">Typ</label><select class="form-select" id="filterZahlungTyp"><option value="">Alle</option><option value="einnahme">Einnahme</option><option value="ausgabe">Ausgabe</option></select></div>
            <div class="col-md-2"><label class="form-label">Von</label><input type="date" class="form-control" id="filterZahlungVon"></div>
            <div class="col-md-2"><label class="form-label">Bis</label><input type="date" class="form-control" id="filterZahlungBis"></div>
            <div class="col-md-2 d-grid gap-2 align-content-end">
                <button class="btn btn-outline-secondary w-100" onclick="ladeZahlungen()"><i class="bi bi-search me-1"></i>Filtern</button>
                <button class="btn btn-outline-success w-100" onclick="exportiereCSV(aktuelleZahlungen)"><i class="bi bi-download me-1"></i>CSV exportieren</button>
            </div>
        </div>
        <div class="mt-2 text-end">
            <button class="btn btn-sm btn-link" onclick="setzeFilterZurueck()"><i class="bi bi-arrow-counterclockwise me-1"></i>Zuruecksetzen</button>
        </div>
    </div>
</div>

<!-- Tabelle -->
<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><input type="checkbox" class="form-check-input" id="alleZahlungenAuswaehlen" onchange="toggleZahlungAlleAuswahl()"></th>
                        <th>Datum</th>
                        <th>Kategorie</th>
                        <th>Buchung</th>
                        <th>Betrag</th>
                        <th>Bemerkung</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody id="zahlungenTabelle">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="zahlungModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Neue Zahlung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="zahlungForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Buchung *</label>
                        <select class="form-select" id="zahlungBuchung" required>
                            <option value="">Bitte wählen...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Betrag *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="zahlungBetrag" required>
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Zahlungsdatum *</label>
                        <input type="date" class="form-control" id="zahlungDatum" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Bemerkung</label>
                        <input type="text" class="form-control" id="zahlungBemerkung">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<!-- Page Script -->
<script src="<?= BASE_URL ?>/assets/js/zahlungen.js?v=<?= filemtime(__DIR__ . '/../assets/js/zahlungen.js') ?>"></script>
