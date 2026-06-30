<?php
$pageTitle = 'Buchungen - Haushaltsplanung';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-journal-text me-2"></i>Buchungen verwalten</h4>
    <button class="btn btn-primary" onclick="oeffneModal()">
        <i class="bi bi-plus-circle me-1"></i>Neue Buchung
    </button>
</div>

<!-- Filter -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Kategorie</label>
                <select class="form-select" id="filterKategorie">
                    <option value="">Alle</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Intervall</label>
                <select class="form-select" id="filterIntervall">
                    <option value="">Alle</option>
                    <option value="einmalig">Einmalig</option>
                    <option value="woechentlich">Wöchentlich</option>
                    <option value="monatlich">Monatlich</option>
                    <option value="vierteljaehrlich">Vierteljährlich</option>
                    <option value="jaehrlich">Jährlich</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" id="filterAktiv">
                    <option value="">Alle</option>
                    <option value="1">Aktiv</option>
                    <option value="0">Inaktiv</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" onclick="ladeBuchungen()">
                    <i class="bi bi-search me-1"></i>Filtern
                </button>
            </div>
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
                        <th>Kategorie</th>
                        <th>Beschreibung</th>
                        <th>Betrag</th>
                        <th>Intervall</th>
                        <th>Startdatum</th>
                        <th>Status</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody id="buchungenTabelle">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="buchungModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitel">Neue Buchung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="buchungForm">
                <div class="modal-body">
                    <input type="hidden" id="buchungId">
                    
                    <div class="mb-3">
                        <label class="form-label">Kategorie *</label>
                        <select class="form-select" id="buchungKategorie" required>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Betrag *</label>
                        <div class="input-group">
                            <input type="number" step="0.01" class="form-control" id="buchungBetrag" required>
                            <span class="input-group-text">€</span>
                        </div>
                        <small class="text-muted">Positiv = Einnahme, Negativ = Ausgabe</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Beschreibung</label>
                        <input type="text" class="form-control" id="buchungBeschreibung">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Intervall *</label>
                        <select class="form-select" id="buchungIntervall" required>
                            <option value="einmalig">Einmalig</option>
                            <option value="woechentlich">Wöchentlich</option>
                            <option value="monatlich" selected>Monatlich</option>
                            <option value="vierteljaehrlich">Vierteljährlich</option>
                            <option value="jaehrlich">Jährlich</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Startdatum *</label>
                            <input type="date" class="form-control" id="buchungStart" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Enddatum</label>
                            <input type="date" class="form-control" id="buchungEnde">
                            <small class="text-muted">Leer = unbegrenzt</small>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="buchungAktiv" checked>
                        <label class="form-check-label">Aktiv</label>
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
<script src="/assets/js/buchungen.js"></script>
